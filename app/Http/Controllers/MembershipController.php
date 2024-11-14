<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GstTaxPercentage;
use App\Models\Membership;
use App\Models\ShopBilling;
use Illuminate\Support\Str;
use DataTables;

class MembershipController extends Controller
{
    protected $title    = 'Membership';
    protected $viewPath = '/admin/membership';
    protected $link     = 'admin/membership';
    protected $route    = 'membership';
    protected $entity   = 'membership';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page           = collect();
        $page->title    = $this->title;
        $page->link     = url($this->link);
        $page->route    = $this->route;
        $page->entity   = $this->entity;
        if ($request->ajax()) {
            return $this->lists($request); 
        }
     
           
        return view('admin.membership.list', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $detail = Membership::where('shop_id',SHOP_ID)->orderBy('id', 'desc');
        return Datatables::of($detail)
        ->addIndexColumn()
        ->addColumn('action', function($detail) {
            $action = '';               
            $action .= ' <a class="" href="' . route('membership.edit', ['membership' => $detail->id]) . '"><i class="material-icons">mode_edit</i></a>';
            $action .= ' <a class="delete-membership" data-membership-id="' . $detail->id . '" href="javascript:void(0);"><i class="material-icons">delete</i></a>';
            return $action;
        })
        ->addColumn('sellingprice', function($detail){
            $price = '₹ '. $detail->price;
            return $price;
        })
        ->addColumn('membershipprice', function($detail){
            $price=$detail->membership_price;
            $price = '₹ '.($price > 0 ? $price : '0.0');
            return $price;
        })
        ->addColumn('duration', function($detail){               
            return $detail->duration_type.'-'.$detail->duration_in_days.' '.'Days' ;
        })
        ->addColumn('membership', function($detail){               
            return $detail->name ;
        })
        ->addColumn('description', function($detail){               
            return $detail->description ;
        })
        ->addColumn('taxstatus', function($detail){  
            $status='';        
            if($detail->is_tax_included== 0){
                $status='<span style=" background-color: green;
                color: white;
                padding: 4px 8px;
                text-align: center;
                border-radius: 5px;">Excluded</span>';
            }else{
                $status='<span style=" background-color: red;
                color: white;
                padding: 4px 8px;
                text-align: center;
                border-radius: 5px;">Included</span>';
            }     
            return $status ;
        })
        ->removeColumn('id')
        ->escapeColumns([])
        ->rawColumns(['taxstatus', 'action'])
        ->make(true);  
    
    }
    public function create()
    {
        $page           = collect();
        $variants       = collect();
        $page->title    = $this->title;
        $page->link     = url($this->link);
        $page->route    = $this->route;
        $page->entity   = $this->entity;
        $variants->gst  = GstTaxPercentage::pluck('percentage', 'id'); 
        return view($this->viewPath . '.create', compact('page','variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        try 
        {
            // dd($request->all());
            $request->validate([
                'membership' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'membership_price' => 'required|numeric|min:0',
                'duration_type' => 'required|string',
                'duration_in_days' => 'required|integer|min:1',
                'gst_tax' => 'required|exists:gst_tax_percentages,id', // Assuming gst_tax is referencing an existing GST ID
            ]);
        
            $membership = new Membership();
            $membership->name             = $request->membership;
            $membership->shop_id          = SHOP_ID;
            $membership->description      = $request->description;
            $membership->price            = $request->price;
            $membership->membership_price = $request->membership_price;
            $membership->duration_type    = $request->duration_type;
            $membership->duration_in_days = $request->duration_in_days;
            $membership->gst_id           = $request->gst_tax;
            $membership->expiry_status    = 0;
            $membership->is_tax_included  = $request->tax_included;
            $membership->save();
            return ['flagError' => false, 'message' => "Membership added successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
            $page                       = collect();
            $variants                   = collect();
            $page->title                = $this->title;
            $page->link                 = url($this->route);
            $page->route                = $this->route;
            $variants->gst  = GstTaxPercentage::pluck('percentage', 'id'); 
            $membership=Membership::where('shop_id',SHOP_ID)->with('gst')->find($id);
            return view($this->viewPath . '.edit', compact('page', 'membership','variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'membership' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'membership_price' => 'required|numeric|min:0',
                'duration_type' => 'required|string',
                'duration_in_days' => 'required|integer|min:1',
                'gst_tax' => 'required|exists:gst_tax_percentages,id', // Assuming gst_tax is referencing an existing GST ID
            ]);
        
            $membership = Membership::findOrFail($request->membershipId);
            $membership->name               = $request->membership;
            $membership->shop_id            = SHOP_ID;
            $membership->description        = $request->description;
            $membership->price              = $request->price;
            $membership->membership_price   = $request->membership_price;
            $membership->duration_type      = $request->duration_type;
            $membership->duration_in_days   = $request->duration_in_days;
            $membership->gst_id             = $request->gst_tax;
            $membership->is_tax_included    = $request->tax_included; // Assuming you have this field in your database
            $membership->save();
        
            return ['flagError' => false, 'message' => "Membership updated successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            Membership::find($id)->delete();
            return ['flagError' => false, 'message' => "Membership Deleted successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
    }
    public function getAllMemberships(Request $rtequest){
        try{
            $memberships=Membership::where('shop_id',SHOP_ID)->get();
            return ['flagError' => false, 'data' =>$memberships ];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }

    }
    public function getMembershipsDetails(Request $request) {
        try{
            $store_data     = ShopBilling::where('shop_id', SHOP_ID)->first();
            $dataIds = is_array($request->data_ids) ? $request->data_ids : [$request->data_ids];            
            $data_array     = array();
            $result         = Membership::whereIn('id', $dataIds)->where('shop_id',SHOP_ID)
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
                $additional_amount      = 0;
                foreach ($result as $key=>$row) {
                  
                    if ($store_data->gst_percentage != null) {
                        if ($row->gst_tax != NULL) {
                            $total_percentage           = $row->gsttax->percentage ;
                            $tax_percentage             = $row->gsttax->percentage ;
                        } else {
                            $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
                            $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
                        }
                    } 
                    $included = ($row->is_tax_included == 1)?'Tax Included':'Tax Excluded';
                    $html.='<tr id="'.$row->id.'">';
                    $html.='<td>'.($key+1).'</td>';
                    $html.='<td>'.$row->name. ' ( '.$included.' ) </td>';
                    $html.='<td class=""><div class="itemcountSec">';    
                    $html.='<a class="mb-6 btn-floating waves-effect waves-light red accent-2 itemCountBtn dec" onclick="manageItemCount(`dec`, ' . $row->id . ','.$row->price.','.$tax_percentage.','.$total_percentage.','.$row->is_tax_included.','.$additional_amount.')"><i class="material-icons left">remove</i></a>';
                    $html.='<input type="text" data-id="'.$row->id.'" size="2" class="itemCount" id="itemCount'.$row->id.'" value="1" readonly>';
                    $html.='<a class="mb-6 btn-floating waves-effect waves-light red accent-2 itemCountBtn inc" onclick="manageItemCount(`inc`, ' . $row->id . ','.$row->price.','.$tax_percentage.','. $total_percentage.','.$row->is_tax_included.','.$additional_amount.')"><i class="material-icons left">add</i></a>';
                    $html.='</td></div>';
                    $html.='<td class="right-align">';
                        
                    // Calculate TAX Based on the tax Rate
                    if ($total_percentage > 0) {                   
                        $total_service_tax          = ($row->price/100) * $total_percentage ;        
                        $tax_onepercentage          = $total_service_tax/$total_percentage;
                        $total_gst_amount           = $tax_onepercentage*$total_percentage ;
                        $total_cgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                        $total_sgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
                        $cgst_percentage            = $sgst_percentage = ($tax_percentage / 2);
                      
    
                        if ($row->is_tax_included == 1) {
                            $total_payable          = $row->price;
                            $service_value          = ( $row->price/ (1 + ($total_percentage / 100)));
                            $total_cgst_amount = $total_sgst_amount =($service_value *$cgst_percentage)/100;  
                               
                            // $service_value          = $row->price - $total_service_tax ;
                            $service_value          = $service_value ;
                        } else {
    
                            $total_payable          = $row->price + $total_service_tax  ;
                            $service_value          = $row->price ;
                            $total_payable          = $total_payable ;
                        }
    
    
                        $html.='<ul><li class="display-flex justify-content-between"><span class="invoice-subtotal-title">Service value </span><h6 class="invoice-subtotal-value indigo-text">₹ <span id="serviceValue'.$row->id.'">'.number_format($service_value,2).'</span></h6></li>';
                        $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">CGST ('.($tax_percentage/2).'%)  </span>';
                        $html.='<h6 class="invoice-subtotal-value indigo-text cgstAmount'.$row->id.'">₹ '.number_format($total_cgst_amount,2).'</h6></li>';
                        $html.='<li class="display-flex justify-content-between"><span class="invoice-subtotal-title">SGST ('.($tax_percentage/2).'%) </span>';
                        $html.='<h6 class="invoice-subtotal-value indigo-text sgstAmount'.$row->id.'">₹ '.number_format($total_sgst_amount,2).'</h6></li>';
    
                        
    
                    } else {
                        if ($row->is_tax_included == 1) {
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
                return response()->json(['flagError' => false, 'grand_total' => $grand_total, 'html' => $html, 'item_Id' => $html, 'data' => $data_array]);
            }
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
    }

}
