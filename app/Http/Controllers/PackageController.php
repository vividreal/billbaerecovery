<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Additionaltax;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;
use DataTables;
use Validator;
use DB;
use Auth;
use App\Models\ShopBilling;
use App\Helpers\FunctionHelper;
use Carbon\Carbon;


class PackageController extends Controller
{
    protected $title    = 'Package';
    protected $viewPath = 'packages';
    protected $route    = 'packages';


    function __construct()
    {
        $this->middleware('permission:package-create', ['only' => ['create','store']]);
        $this->middleware('permission:package-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:package-delete', ['only' => ['destroy']]);
        $this->middleware('permission:package-list', ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->lists($request); 
        }
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;       
        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page                           = collect();
        $variants                       = collect();
        $page->title                    = $this->title;
        $page->link                     = url($this->route);
        $page->route                    = $this->route;
        $variants->services             = Service::where('shop_id', SHOP_ID)->pluck('name', 'id'); 
        $variants->tax_percentage       = DB::table('gst_tax_percentages')->pluck('percentage', 'id');   
        $variants->additional_tax       = Additionaltax::where('shop_id', SHOP_ID)->pluck('name', 'id');        
        $variants->service_category     = ServiceCategory::where('shop_id', SHOP_ID)->pluck('name', 'id');   
        $variants->additional_tax_ids   = [];     
        return view($this->viewPath . '.create', compact('page', 'variants'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  Package::where('shop_id', SHOP_ID) ->where('status', 1)->orderBy('id', 'desc');
        if (isset($request->form)) {
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['value'] == 'deleted') {
                    $detail         = $detail->onlyTrashed();
                }
            }
        }
        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('action', function($detail) {
                $action = '';
                if ($detail->deleted_at == null) { 
                    if( Auth::user()->hasPermissionTo('package-edit')) {
                        $action .= ' <a  href="' . url(ROUTE_PREFIX.'/'.$this->route.'/' . $detail->id . '/edit') . '"" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                    }
                    if( Auth::user()->hasPermissionTo('package-delete')) {
                        $action .= '<a href="javascript:void(0);" data-id="'. $detail->id .'" data-url="'.url($this->route).'" class="btn btn-danger btn-sm btn-icon mr-2 disable-item" title="Deactivate"><i class="material-icons">block</i></a>';    
                    }
                } else {
                    if( Auth::user()->hasPermissionTo('package-delete')) {
                        $action = ' <a href="javascript:void(0);"data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="btn mr-2 cyan restore-item" title="Restore"><i class="material-icons">restore</i></a>';
                    }
                }
                return $action;
            })
            ->addColumn('price', function($detail){
                $price = '₹ '. $detail->price;
                return $price;
            })
            ->addColumn('offer_price', function($detail){
                $price=$detail->service_price-$detail->price;
                $price = '₹ '.($price > 0 ? $price : '0.0');
                return $price;
            })
            ->addColumn('services', function($detail){
                $services_list  ='';
                $services       = '';
                foreach ($detail->service as $data) {
                    $services_list.=$data->name.', ' ;
                }
                $services_list = rtrim($services_list, ',');
                $services = Str::limit(strip_tags($services_list), 30);
                // if (strlen(strip_tags($services_list)) > 40) {
                    $services .= "<a href='javascript:void(0);' id=' . $detail->id . ' class='view-link' onclick='showMessage($detail->id)'>View</a>";
                // }
                return $services ;
            })
            ->addColumn('activate', function($detail){
                $checked = ($detail->status == 1) ? 'checked' : '';
                $html = '<div class="switch"><label><input type="checkbox" '.$checked.' id="' . $detail->id . '" data-url="'.url($this->route.'/update-status').'" class="manage-status" data-id="'.$detail->id.'"> <span class="lever"></span> </label> </div>';
                return $html;
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true);                    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',Rule::unique('packages')->where(function($query) {
                  $query->where('shop_id', '=', SHOP_ID);
              })
            ],
            'price' => 'required',
        ]);

        if ($validator->passes()) {
            $validity_from                   = FunctionHelper::dateToTimeFormat($request->validity_from);
            $validity_to                   = FunctionHelper::dateToTimeFormat($request->validity_to);
            $package                   = new Package();
            $package->shop_id          = SHOP_ID;
            $package->name             = $request->name;
            $package->slug             = $request->name;
            $package->price            = $request->price;
            $totalPrice                = str_replace(['"', '₹', ','], '', $request->totalPrice);
            $totalPrice                = (float) $totalPrice;
            $package->service_price    = $totalPrice;
            // $package->instore_credit_amount = $request->instore_credit_amount;
            // $package->validity_mode    = $request->validity_mode;
            $package->validity         = $request->validity;
            // $package->tax_included     = ($request->tax_included == 1) ? 1 : 0 ;
            // $package->gst_tax          = $request->gst_tax;
            $package->hsn_code         = $request->hsn_code;
            // $package->validity_from    = $request->validity_from;
            // $today                  =Carbon::parse($request->validity_from);
            // $validity_to            =$today->addDays($request->validity);
            // $package->validity_to      = $validity_to;
            $package->save();

            $package->service()->sync($request->services);
            if ($request->additional_tax) {
                $package->additionaltax()->sync($request->additional_tax);
            }
            return ['flagError' => false, 'message' => $this->title. " added successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=> $validator->errors()->all()];
    }

    /**
     * Display the specified resource.
     * 
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package)
    { 
        if ($package) {
            $page                           = collect();
            $variants                       = collect();
            $page->title                    = $this->title;
            $page->link                     = url($this->route);
            $page->route                    = $this->route;   
            $service_ids                    = array();
            $variants->tax_percentage       = DB::table('gst_tax_percentages')->pluck('percentage', 'id');  
            $variants->additional_tax       = Additionaltax::where('shop_id', SHOP_ID)->pluck('name', 'id'); 
            $variants->services             = Service::where('shop_id', SHOP_ID)->pluck('name', 'id');
            $variants->additional_tax_ids   = [];
            foreach ($package->service as $data) {
                $service_ids[] = $data->id ;
            }
            if ($package->additionaltax) {
                $variants->additional_tax_ids = [];
                foreach($package->additionaltax as $row){
                    $variants->additional_tax_ids[] = $row->id;
                }
            }
            return view($this->viewPath . '.edit', compact('page', 'variants', 'package', 'service_ids'));
        } else {
            return redirect('services')->with('error', $this->title.' not found');
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',Rule::unique('packages')->where(function($query) use($id){
                  $query->where('shop_id', '=', SHOP_ID)->where('id', '!=', $id);;
              })
            ],
            'price' => 'required',
        ]);

        if ($validator->passes()) {
            $data                   = Package::findOrFail($id);
            $data->shop_id          = SHOP_ID;
            $data->name             = $request->name;
            $data->slug             = $request->name;
            $data->price            = $request->price;
            $totalPrice                = str_replace(['"', '₹', ','], '', $request->totalPrice);
            $totalPrice                = (float) $totalPrice;
            $data->service_price    = $totalPrice;
            // $data->instore_credit_amount = $request->instore_credit_amount;
            // $data->discount         = $request->discount;
            // $data->validity_mode    = $request->validity_mode;
            $data->validity         = $request->validity;
            // $data->tax_included     = ($request->tax_included == 1) ? 1 : 0 ;
            // $data->gst_tax          = $request->gst_tax;
            $data->hsn_code         = $request->hsn_code;
            // $data->validity_from    = $request->validity_from;
            // $today                  =Carbon::parse($request->validity_from);
            // $validity_to            =$today->addDays($request->validity);
            // $data->validity_to      = $validity_to;
            $data->save();

            $data->service()->sync($request->services);
            $data->additionaltax()->sync($request->additional_tax);
            return ['flagError' => false, 'message' => $this->title. " updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Package $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(Package $package)
    {

        $billingItems = $package->whereHas('billingItems', function ($query) { 
                            $query->where('item_type', '=', 'packages');
                        })->find($package->id);

        if (!empty($billingItems)) {
            return ['flagError' => true, 'message' => "Cant deactivate! Package has Billing information"];
        } 
        $package->updated_by = Auth::user()->id;
        $package->save();

        $package->delete();
        return ['flagError' => false, 'message' => " Package Inactivated successfully"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function restore($id, Request $request)
    {
        $service   = Package::where('id', $id) ->where('status', 1)->withTrashed()->first();
        $service->restore();
        return ['flagError' => false, 'message' => " Package activated successfully"];
    }

    public function updateStatus(Request $request)
    {
        $package                = Package::findOrFail($request->id);
        if ($package) {
            $status             = ($package->status == 0)?1:0;
            $package->status    = $status;
            $package->save();
            return ['flagError' => false, 'message' => $this->title. " status updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !",  'error'=>$validator->errors()->all()];
    }
    public function getPackageDetails(Request $request) {        
        $dataIds = is_array($request->data_ids) ? $request->data_ids : [$request->data_ids];
        $store_data     = ShopBilling::where('shop_id', SHOP_ID)->first();
        $data_array     = array();
        $result         = Package::with('additionaltax', 'service','gsttax' , 'billingItems')
                             ->where('shop_id', SHOP_ID)
                             ->where('status', 1)
                             ->whereIn('id', $dataIds)
                             ->orderBy('id', 'desc')
                             ->get();
                                
        $no_of_items=0;           
        if ($result) {
            $html                   = '';
            $index                  = 1 ;
            $total_percentage       = 0 ;
            $total_service_tax      = 0 ;
            $total_payable          = 0 ;
            $service_value          = 0 ;
            $grand_total            = 0 ;
            $total_minutes          = 0;
            $additional_tax_array   = array();
            $additional_amount      = 0;
            $package_full_name      = '';
            foreach ($result as $row) {
                $html.='<tr><td colspan="3" style="font-weight: bold; text-align: center;">'.$row->name.'</td></tr>';
                $no_of_items=$row->service->count();
                foreach($row->service as $row1 ){
                    $minutes            = 0;
                    $lead_before        = 0;
                    $lead_after         = 0;
                    $service_time       = Service::getTimeDetails($row1->id);
                    $packagePrice       = $row->service_price-$row->price;
                    $offerPrice         = $packagePrice/$no_of_items;
                    $data_array[]       = array('id' => $row1->id, 'name' => $row1->name, 'price' => $row1->price, 'minutes' => $service_time['total_minutes']);
                    // When Store has GST: Calculate tax with store GST or Item GST
                    if ($store_data->gst_percentage != null) {
                        if ($row1->gst_tax != NULL) {
                            $total_percentage           = $row1->gsttax->percentage ;
                            $tax_percentage             = $row1->gsttax->percentage ;
                        } else {
                            $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
                            $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
                        }
                    } 
                    $included = ($row1->tax_included == 1)?'Tax Included':'Tax Excluded';
                  
                    $html.='<tr id="'.$row1->id.'">';
                    $html.='<td>'.$row1->name . ' - '. $row1->hours->value . ' mns. ' . ' ( '.$included.' ) </td>';
                    $html.='<td class=""><div class="itemcountSec">'; 
                    $html.='<td class="right-align">';
                    // Calculate TAX Based on the tax Rate
                    if ($total_percentage > 0) {                 
                        $total_service_tax          = ($row1->price/100) * $total_percentage ;        
                        $tax_onepercentage          = $total_service_tax/$total_percentage;
                        $total_gst_amount           = $tax_onepercentage*$total_percentage ;
                        $total_cgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                        $total_sgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                        $cgst_percentage   = $sgst_percentage = ($tax_percentage / 2);
                        if (count($row1->additionaltax) > 0) {
                            foreach ($row1->additionaltax as $additional) {
                                $total_percentage   = $total_percentage+$additional->percentage;
                                $additional_amount  += $tax_onepercentage*$additional->percentage;
                            } 
                        }
                        if ($row1->tax_included == 1) {
                            $total_payable          = $row1->price-$offerPrice;
                        $service_value= ( $total_payable / (1 + ($total_percentage / 100)));
                        $total_cgst_amount = $total_sgst_amount =($service_value *$cgst_percentage)/100;  
                        } else {
                            $total_payable          = ($row1->price + $total_service_tax )-$offerPrice ;                       
                            $service_value= ( $total_payable / (1 + ($total_percentage / 100)));
                            $total_cgst_amount = $total_sgst_amount =($service_value *$cgst_percentage)/100; 
                        $total_payable=$service_value+$total_cgst_amount +$total_sgst_amount;
                        
                            $total_payable          = $total_payable + $additional_amount;
                        }
                       

                        $html.='<ul><li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Service value </span><h6 class="invoice-subtotal-value indigo-text">₹ <span id="serviceValue'.$row1->id.'">'.number_format($service_value,2).'</span></h6></li>';
                        $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">CGST ('.($tax_percentage/2).'%)  </span>';
                        $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($total_cgst_amount,2).'</h6></li>';
                        $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">SGST ('.($tax_percentage/2).'%) </span>';
                        $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($total_sgst_amount,2).'</h6></li>';
                        if (count($row1->additionaltax) > 0) {
                            $html.='<li class="divider mt-2 mb-2"></li>';
                            foreach($row1->additionaltax as $additional) {
                                $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">' . $additional->percentage . ' % ' . $additional->name. '</span>';
                                $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($tax_onepercentage*$additional->percentage,2).'</h6></li>';
                            }
                        }

                    } else {
                        if ($row1->tax_included == 1) {
                            $total_payable          = $row1->price ;
                            $service_value          = $row1->price - $total_service_tax ; 
                        } else {
                            $total_payable          = $row1->price + $total_service_tax  ;
                            $service_value          = $row1->price ;
                        }
                    }

                    $html.='<li class="divider mt-2 mb-2"></li>';
                    $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Total payable</span><h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($total_payable,2).'</h6></li>';
                    $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Package Benefit</span><h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($offerPrice,2).'</h6></li>';
                    $grand_total = ($grand_total + $total_payable); 
                    $index++;
            }
            $html.='<hr style="width:100%;">';
            $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Total</span><h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($grand_total,2).'</h6></li>';
           
            
        }
            return response()->json(['flagError' => false, 'grand_total' => $grand_total, 'html' => $html, 'item_Id' => $html, 'data' => $data_array, 'total_minutes' => $total_minutes]);
        }
        return response()->json(['flagError' => true, 'message' => "Errors Occurred ! Please try again"]);
        
    }
    
}
