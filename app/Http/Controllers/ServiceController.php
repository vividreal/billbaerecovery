<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ServicesImport;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\PackageService;
use App\Models\Hours;
use App\Models\Additionaltax;
use App\Models\ShopBilling;
use Illuminate\Validation\Rule;
use DB;
use DataTables;
use Validator;


class ServiceController extends Controller
{
    protected $title    = 'Service';
    protected $viewPath = 'services';
    protected $route    = 'services';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:service-list', ['only' => ['index','lists']]);
        $this->middleware('permission:service-create', ['only' => ['create','store','import']]);
        $this->middleware('permission:service-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:service-delete', ['only' => ['destroy', 'restore']]);
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
        $page                           = collect();
        $variants                       = collect();
        $page->title                    = $this->title;
        $page->route                    = $this->route; 
        $page->link                     = url($this->route);            
        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  Service::where('shop_id', SHOP_ID)->orderBy('id', 'desc');

        if (isset($request->form)) {
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['value'] == 'deleted') {
                    $detail         = $detail->onlyTrashed();
                }
            }
        }

        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('action', function($detail){
                $action = '';
                if ($detail->deleted_at == null) {          
                    if( auth()->user()->hasPermissionTo('service-edit')) {
                        $action = ' <a  href="' . url(ROUTE_PREFIX.'/services/' . $detail->id . '/edit') . '"" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                    }
                    if( auth()->user()->hasPermissionTo('service-delete')) {
                        $action .= '<a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" data-type="remove" class="btn btn-danger btn-sm btn-icon mr-2 disable-item" title="Deactivate"><i class="material-icons">block</i></a>';    
                    }
                } else {
                    if( auth()->user()->hasPermissionTo('service-delete')) {
                        $action = ' <a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="btn mr-2 cyan restore-item" title="Restore"><i class="material-icons">restore</i></a>';
                    }
                }
                return $action;
            })
            ->addColumn('service_category', function($detail){
                $country = $detail->serviceCategory->name;
                return $country;
            })
            ->addColumn('price', function($detail){
                $price = CURRENCY . ' '. $detail->price;
                return $price;
            })
            ->addColumn('hours', function($detail){
                $country = $detail->hours->name;
                return $country;
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true);                    
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
        $variants->hours                = Hours::pluck('name', 'id'); 
        $variants->service_category     = ServiceCategory::where('shop_id', SHOP_ID)->pluck('name', 'id');   
        $variants->additional_tax       = Additionaltax::where('shop_id', SHOP_ID)->pluck('name', 'id'); 
        $variants->tax_percentage       = DB::table('gst_tax_percentages')->pluck('percentage', 'id'); 
        $variants->additional_tax_ids   = []; 
        return view($this->viewPath . '.create', compact('page', 'variants'));
    }

    /** 
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'name' => [
                'required', Rule::unique('services')->where(function($query) {
                  $query->where('shop_id', '=', SHOP_ID);
              })
            ],
            'hours_id' => 'required',
            'price' => 'required',
        ]);
        if ($validator->passes()) {
            $data                       = new Service();
            $data->shop_id              = SHOP_ID;
            $data->name                 = $request->name;
            $data->slug                 = $request->name;
            $service_category           = ServiceCategory::firstOrCreate(['shop_id' => SHOP_ID, 'name' => $request->search_service_category]);
            if ($service_category) {
                $data->service_category_id  = $service_category->id;
            }
            $data->price                = $request->price;
            $data->tax_included         = ($request->tax_included == 1) ? 1 : 0 ;            
            $data->lead_before          = $request->lead_before;
            $data->lead_after           = $request->lead_after;  
            $data->hours_id             = $request->hours_id;
            // $data->gst_tax              = CustomHelper::serviceGST(SHOP_ID, $request->gst_tax);
            // $data->hsn_code             = CustomHelper::serviceHSN(SHOP_ID, $request->hsn_code);
            $data->gst_tax              = $request->gst_tax;
            $data->hsn_code             = $request->hsn_code;
            $data->save();

            if ($request->additional_tax) {
                $data->additionaltax()->sync($request->additional_tax);
            }
            return ['flagError' => false, 'message' => $this->title. " added successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\State  $state
     * @return \Illuminate\Http\Response
     */
    public function show(State $state)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function edit(Service $service)
    { 
        if ($service) {
            $page                   = collect();
            $variants               = collect();
            $page->title            = $this->title;
            $page->link             = url($this->route);
            $page->route            = $this->route;
            $variants->hours        = Hours::pluck('name', 'id'); 
            $variants->service_category     = ServiceCategory::where('shop_id', SHOP_ID)->pluck('name', 'id'); 
            $variants->tax_percentage       = DB::table('gst_tax_percentages')->pluck('percentage', 'id');  
            $variants->additional_tax       = Additionaltax::where('shop_id', SHOP_ID)->pluck('name', 'id'); 

            if ($service->additionaltax) {
                $variants->additional_tax_ids = [];
                foreach($service->additionaltax as $row) {
                    $variants->additional_tax_ids[] = $row->id;
                }
            }
            return view($this->viewPath . '.create', compact('page', 'variants', 'service'));
        } else {
            return redirect('services')->with('error', $this->title.' not found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {       
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',Rule::unique('services')->where(function($query) use($service) {
                  $query->where('shop_id', '=', SHOP_ID)->where('id', '!=', $service->id);
              })
            ],
            'hours_id' => 'required',
            'price' => 'required',
        ]);
        if ($validator->passes()) {
            if ($service) {
                $service->name                 = $request->name;
                $service_category               = ServiceCategory::firstOrCreate(['shop_id' => SHOP_ID, 'name' => $request->search_service_category]);
                if ($service_category) {
                    $service->service_category_id  = $service_category->id;
                }
                $service->price                = $request->price;
                $service->lead_before          = $request->lead_before;
                $service->lead_after           = $request->lead_after;  
                $service->hours_id             = $request->hours_id;
                $service->tax_included         = ($request->tax_included == 1) ? 1 : 0 ;
                $service->gst_tax              = $request->gst_tax;
                $service->hsn_code             = $request->hsn_code;
                $service->save();
                $service->additionaltax()->sync($request->additional_tax);

                return ['flagError' => false, 'message' => $this->title. " updated successfully"];
            } else {
                return ['flagError' => true, 'message' => "Data not found, Try again!"];
            }
        }
        return ['flagError' => true, 'error'=>$validator->errors()->all()];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        if (count($service->billingItems) > 0) {
            return ['flagError' => true, 'message' => "Cant Inactivate! Service has Billing Information"];
        } 
        $service->updated_by = auth()->user()->id;
        $service->save();

        $service->delete();
        return ['flagError' => false, 'message' => " Service Inactivated successfully"];
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Models\Service $service
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $service   = Service::where('id', $id)->withTrashed()->first();
        $service->restore();
        return ['flagError' => false, 'message' => " Service Activated successfully"];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function import(Request $request) 
    {
        Excel::import(new ServicesImport, $request->file('file')->store('temp'));

        // $import =  new ServicesImport;
        // $import->import(request()->file('file'));
        return redirect('services')->with('success', 'Services Imported Successfully.');
    }

    /**
     * Display a listing of the resource
     * @throws \Exception
     */
    public function getDetails(Request $request) 
    {
        $store_data     = ShopBilling::where('shop_id', SHOP_ID)->first();
        $data_array     = array();
        $ids=$request->data_ids;
        $result         = Service::with('additionaltax', 'gsttax', 'hours', 'leadBefore', 'leadAfter')
                            ->where('shop_id', SHOP_ID)
                            ->when(is_array($ids), function ($query) use ($ids) {
                                return $query->whereIn('id', $ids);
                            }, function ($query) use ($ids) {
                                return $query->where('id', $ids);
                            })
                            ->orderBy('services.id', 'desc')
                            ->get();        
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
            $tax_percentage         = 0;
            $item_count             = 1;
            foreach ($result as $key=>$row) {               
                $minutes            = 0;
                $lead_before        = 0;
                $lead_after         = 0;
                $service_time       = Service::getTimeDetails($row->id);   
                        
                $data_array[]       = array('id' => $row->id, 'name' => $row->name, 'price' => $row->price, 'minutes' => $service_time['total_minutes']);
                if ($store_data->gst_percentage != null) {
                    if ($row->gst_tax != NULL) {
                        $total_percentage           = $row->gsttax->percentage ;
                        $tax_percentage             = $row->gsttax->percentage ;
                    } else {
                        $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
                        $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
                    }
                } 
                $included = ($row->tax_included == 1)?'Tax Included':'Tax Excluded';
                $html.='<tr id="'.$row->id.'">';
                $html.='<td>'.$row->name . ' - '. $row->hours->value . ' mns. ' . ' ( '.$included.' ) </td>';              
                $html.='<td class=""><div class="itemcountSec">';  
                if($request->lastWord=='billings')    {   
                $html.='<a class="mb-6 btn-floating waves-effect waves-light red accent-2 itemCountBtn dec" onclick="manageItemCount(`dec`, ' . $row->id . ','.$row->price.','.$tax_percentage.','.$total_percentage.','.$row->tax_included.','.$row->additionaltax.')"><i class="material-icons left">remove</i></a>';
                if($request->action=='edit')    {   
                foreach($row->billingItems as $billingItem){
                    if($row->id==$billingItem->item_id){
                       $item_count=$billingItem->item_count;
                    }
                }
                }
                else{
                    $item_count=1;
                }
                $html.='<input type="text" data-id="'.$row->id.'" size="2" class="itemCount" id="itemCount'.$row->id.'" value="'.$item_count .'" readonly>';
                $html.='<a class="mb-6 btn-floating waves-effect waves-light red accent-2 itemCountBtn inc" onclick="manageItemCount(`inc`, ' . $row->id . ','.$row->price.','.$tax_percentage.','. $total_percentage.','.$row->tax_included.','.$row->additionaltax.')"><i class="material-icons left">add</i></a>';
                }
                $html.='</td></div>';
                $html.='<td class="right-align">';
                    
                // Calculate TAX Based on the tax Rate
                if ($total_percentage > 0) {                    
                    $total_service_tax          = ($row->price/100) * $total_percentage ;        
                    $tax_onepercentage          = $total_service_tax/$total_percentage;
                    $total_gst_amount           = $tax_onepercentage*$total_percentage ;
                    $total_cgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                    $total_sgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                    $cgst_percentage   = $sgst_percentage = ($tax_percentage / 2);
                    if (count($row->additionaltax) > 0) {
                        foreach ($row->additionaltax as $additional) {
                            $total_percentage   = $total_percentage+$additional->percentage;
                            $additional_amount  += $tax_onepercentage*$additional->percentage;
                        } 
                    }

                    if ($row->tax_included == 1) {
                        $total_payable          = $row->price;
                        $service_value          = ( $row->price/ (1 + ($total_percentage / 100)));
                        $total_cgst_amount = $total_sgst_amount =($service_value *$cgst_percentage)/100;  
                           
                        // $service_value          = $row->price - $total_service_tax ;
                        $service_value          = $service_value - $additional_amount;
                    } else {

                        $total_payable          = $row->price + $total_service_tax  ;
                        $service_value          = $row->price ;
                        $total_payable          = $total_payable + $additional_amount;
                    }
                    $html.='<ul><li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Service value </span><h6 class="invoice-subtotal-value indigo-text">₹ <span id="serviceValue'.$row->id.'">'.number_format($service_value,2).'</span></h6></li>';
                    $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">CGST ('.($tax_percentage/2).'%)  </span>';
                    $html.='<h6 class="invoice-subtotal-value indigo-text cgstAmount'.$row->id.'">₹ '.number_format($total_cgst_amount,2).'</h6></li>';
                    $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">SGST ('.($tax_percentage/2).'%) </span>';
                    $html.='<h6 class="invoice-subtotal-value indigo-text sgstAmount'.$row->id.'">₹ '.number_format($total_sgst_amount,2).'</h6></li>';
                    if (count($row->additionaltax) > 0) {
                        $html.='<li class="divider mt-2 mb-2"></li>';
                        foreach($row->additionaltax as $additional) {
                            $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">' . $additional->percentage . ' % ' . $additional->name. '</span>';
                            $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($tax_onepercentage*$additional->percentage,2).'</h6></li>';
                        }
                    }

                } else {
                    if ($row->tax_included == 1) {
                        $total_payable          = $row->price ;
                        $service_value          = $row->price - $total_service_tax ; 
                    } else {
                        $total_payable          = $row->price + $total_service_tax  ;
                        $service_value          = $row->price ;
                    }
                }

                $html.='<li class="divider mt-2 mb-2"></li>';
                $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Total payable</span><h6 class="invoice-subtotal-value indigo-text totalPayable'.$row->id.'">₹ '.number_format($total_payable,2).'</h6></li>';
                $grand_total = ($grand_total + $total_payable); 
                $index++;
            }
            return response()->json(['flagError' => false, 'grand_total' => $grand_total, 'html' => $html, 'data' => $data_array, 'total_minutes' =>$service_time['total_minutes']]);
        }
        return response()->json(['flagError' => true, 'message' => "Errors Occurred ! Please try again"]);

    }
    public function getServices($id){
        if($id){
            $store_data     = ShopBilling::where('shop_id', SHOP_ID)->first();           
            $data_array     = array();
            $packageServices=PackageService::where('package_id',$id)->get();            
            foreach($packageServices as $key=>$service){   
                $result         = Service::with('additionaltax', 'gsttax', 'hours', 'leadBefore', 'leadAfter')
                                    ->where('shop_id', SHOP_ID)
                                    ->where('id',$service->service_id)
                                    ->orderBy('services.id', 'desc')
                                    ->first();
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
                    // dd($result);
                    // foreach ($result as $row) {
                      
                        $minutes            = 0;
                        $lead_before        = 0;
                        $lead_after         = 0;
                        $service_time       = Service::getTimeDetails($result->id);
                        $data_array[]       = array('id' => $result->id, 'name' => $result->name, 'price' => $result->price, 'minutes' => $service_time['total_minutes']);
        
                        // When Store has GST: Calculate tax with store GST or Item GST
                        if ($store_data->gst_percentage != null) {
                            
                            // When Item has GST: Calculate tax with Item GST
                            if ($result->gst_tax != NULL) {
                                $total_percentage           = $result->gsttax->percentage ;
                                $tax_percentage             = $result->gsttax->percentage ;
                            } else {
                                $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
                                $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
                            }
                        } 
                        
                        $included = ($result->tax_included == 1)?'Tax Included':'Tax Excluded';
                        $html.='<tr id="'.$result->id.'">';
                        $html.='<td>'.($key+1) .' </td>';
                        $html.='<td>'.$result->name . ' - '. $result->hours->value . ' mns. ' . ' ( '.$included.' ) </td>';
        
                        $html.='<td class=""><div class="itemcountSec">';            
                        $html.='<a class="mb-6 btn-floating waves-effect waves-light red accent-2 itemCountBtn dec" onclick="manageItemCount(`dec`, '.$result->id.')"><i class="material-icons left">remove</i></a>';
                        $html.='<input type="text" data-id="'.$result->id.'" size="2" class="itemCount" id="itemCount'.$result->id.'" value="1" readonly>';
                        $html.='<a class="mb-6 btn-floating waves-effect waves-light red accent-2 itemCountBtn inc" onclick="manageItemCount(`inc`, '.$result->id.')"><i class="material-icons left">add</i></a>';
                            
                        $html.='</td></div>';
                        $html.='<td class="right-align">';
                            
                        // Calculate TAX Based on the tax Rate
                        if ($total_percentage > 0) {
                            
                            $total_service_tax          = ($result->price/100) * $total_percentage ;        
                            $tax_onepercentage          = $total_service_tax/$total_percentage;
                            $total_gst_amount           = $tax_onepercentage*$total_percentage ;
                            $total_cgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                            $total_sgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
        
                            if (count($result->additionaltax) > 0) {
                                foreach ($result->additionaltax as $additional) {
                                    $total_percentage   = $total_percentage+$additional->percentage;
                                    $additional_amount  += $tax_onepercentage*$additional->percentage;
                                } 
                            }
        
                            if ($result->tax_included == 1) {
                                $total_payable          = $result->price;
                                $service_value          = $result->price - $total_service_tax ;
                                $service_value          = $service_value - $additional_amount;
                            } else {
        
                                $total_payable          = $result->price + $total_service_tax  ;
        
                                $service_value          = $result->price ;
        
                                $total_payable          = $total_payable + $additional_amount;
                            }
        
        
                            $html.='<ul><li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Service value </span><h6 class="invoice-subtotal-value indigo-text">₹ <span id="serviceValue'.$result->id.'">'.number_format($service_value,2).'</span></h6></li>';
                            $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">CGST ('.($tax_percentage/2).'%)  </span>';
                            $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($total_cgst_amount,2).'</h6></li>';
                            $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">SGST ('.($tax_percentage/2).'%) </span>';
                            $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($total_sgst_amount,2).'</h6></li>';
        
                            if (count($result->additionaltax) > 0) {
                                $html.='<li class="divider mt-2 mb-2"></li>';
                                foreach($result->additionaltax as $additional) {
                                    $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">' . $additional->percentage . ' % ' . $additional->name. '</span>';
                                    $html.='<h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($tax_onepercentage*$additional->percentage,2).'</h6></li>';
                                }
                            }
        
                        } else {
                            if ($result->tax_included == 1) {
                                $total_payable          = $result->price ;
                                $service_value          = $result->price - $total_service_tax ; 
                            } else {
                                $total_payable          = $result->price + $total_service_tax  ;
                                $service_value          = $result->price ;
                            }
                        }
        
                        $html.='<li class="divider mt-2 mb-2"></li>';
                        $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Total payable</span><h6 class="invoice-subtotal-value indigo-text">₹ '.number_format($total_payable,2).'</h6></li>';
                        $grand_total = ($grand_total + $total_payable); 
                        $index++;
                    // }
        
                    return response()->json(['flagError' => false, 'grand_total' => $grand_total, 'html' => $html, 'item_Id' => $html, 'data' => $data_array, 'total_minutes' => $service_time['total_minutes']]);
                }
                return response()->json(['flagError' => true, 'message' => "Errors Occurred ! Please try again"]);
            }
        }
        
    }
    }

