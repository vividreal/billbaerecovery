<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use App\Models\Billing;
use App\Models\Customer;
use App\Models\Shop;
use App\Helpers\FunctionHelper;
use App\Models\CustomerPendingPayment;
use App\Models\BillingFormat;
use App\Models\BillingAddres;
use App\Models\BillingItemTax;
use App\Events\SalesCompleted;
use App\Models\BillingItemAdditionalTax;
use App\Models\BillAmount;
use App\Models\BillingItem;
use App\Helpers\TaxHelper;
use App\Models\Service;
use App\Models\Package;
use App\Models\Country;
use App\Models\User;
use App\Models\Room;
use App\Models\PaymentType;
use App\Models\Schedule;
use App\Models\PackageService;
use App\Models\InstorecreditHistory;
use App\Models\customerMemberships;
use App\Models\RefundCash;
use App\Models\Membership;
use App\Models\Cashbook;
use App\Models\ShopBilling;
use App\Models\Rebook;
use App\Models\CashbookCron;
use DataTables;
use Validator;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

// use Illuminate\Support\Arr;
// use Illuminate\Validation\Rule;
// use App\Models\ServiceCategory;
// use App\Models\ShopBilling;
// use App\Models\District;
// use App\Models\State;
use Auth;

class BillingController extends Controller
{
    protected $title        = 'Billing';
    protected $viewPath     = 'billing';
    protected $route        = 'billings';
    protected $link         = 'billings';
    protected $entity       = 'billings';
    protected $timezone     = '';
    protected $time_format  = '';
    protected $store        = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:billing-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:billing-create', ['only' => ['create']]);
        // $this->middleware('permission:billing-edit', ['only' => ['editInvoice', 'invoice']]);
        $this->middleware(function ($request, $next) {
            $this->timezone     = Shop::where('user_id', auth()->user()->id)->value('timezone');
            $this->time_format  = (Shop::where('user_id', auth()->user()->id)->value('time_format') == 1) ? 'h' : 'H';
            $this->store        = Shop::find(auth()->user()->shop_id);
            return $next($request);
        });
        
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

        $variants->customers    = Customer::select("customers.id", DB::raw("CONCAT(customers.name, ' - ', COALESCE(customers.mobile, '')) as name"))
            ->rightjoin('billings', 'billings.customer_id', 'customers.id')
            ->where('customers.shop_id', SHOP_ID)
            ->pluck('customers.name', 'customers.id');
            $variants->paymentTypes    = PaymentType::whereIn('shop_id',[SHOP_ID,0])->get();

        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page                       = collect();
        $variants                   = collect();
        $page->title                = $this->title;
        $page->link                 = url($this->route);
        $page->route                = $this->route;
        $variants->time_picker      = ($this->time_format === 'h') ? false : true;
        $variants->time_format      = $this->time_format;
        $variants->countries        = Country::where('status', 1)->pluck('name', 'id');
        $store                  = Shop::find(SHOP_ID);   
        $variants->phonecode    = Country::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->pluck('phone_code', 'id');   
        return view($this->viewPath . '.create', compact('page', 'variants','store'));
    }

    /**
     * Display a listing of the resource in dataTable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  Billing::select(['billing_code', 'customer_id', 'status', 'payment_status', 'actual_amount','amount', 'billed_date', 'id',])
            ->with(['customer'])
            ->where('billings.shop_id', SHOP_ID)
            ->orderByDesc('id');

        if (isset($request->form)) {
            $customer_ids = [];
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['name'] == 'invoice') {
                    $invoice = strtolower($search['value']);
                    $detail->where('billing_code', 'like', "%{$invoice}%");
                }
                if ($search['value'] != NULL && $search['name'] == 'search_customer_id[]') {
                    $customer_ids[] = $search['value'];
                }
                if ($search['value'] != NULL && $search['name'] == 'payment_status') {
                    $detail->where('payment_status', $search['value']);
                }
            }
            if (count($customer_ids) > 0) {
                $detail->whereIn('customer_id', $customer_ids);
            }
        }
        $detail->orderBy('id', 'desc');
        return Datatables::eloquent($detail)
            ->addIndexColumn()
            ->editColumn('billing_code', function ($detail) {
                if ($detail->payment_status == 0) {
                    return '<a href="' . url($this->route . '/invoice/' . $detail->id) . '" class="invoice-action-edit"><div class="chip gradient-45deg-purple-deep-orange gradient-shadow white-text"> Pay Now </div></a>';
                } else {
                    $billing_code     = '<a href="' . url($this->route . '/' . $detail->id) . '" >' . $detail->billing_code . '</a>';
                    return $billing_code ?? '';
                }
            })->editColumn('customer_id', function ($detail) {
                $customer = $detail->customer ? '<a href="' . url('customers/' . $detail->customer->id) . '" >' . $detail->customer->name . '</a>' : '';
                return $customer;
            })->editColumn('billed_date', function ($detail) {
                return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-Y');
            })->editColumn('status', function ($detail) {
                $status = '';
                if ($detail->status == 0) {
                    $status = '<span class="chip lighten-5 dark blue-text">Open</span>';
                } else if ($detail->status == 1) {
                    $status = '<span class="chip lighten-5 dark green green-text">Completed</span>';
                } else {
                    $status = '<span class="chip lighten-5 dark red-text">Cancelled</span>';
                }
                return $status;
            })->editColumn('payment_status', function ($detail) {
                $status = '';
                if ($detail->payment_status == 0) {
                    $status = '<span class="chip lighten-5 red red-text">UNPAID</span>';
                }
                elseif ($detail->payment_status == 1) {
                    $status = '<span class="chip lighten-5 green green-text">PAID</span>';
                }
                elseif ($detail->payment_status == 2) {
                    $status = '<span class="chip lighten-5 orange orange-text">CANCELLED</span>';
                }
                elseif ($detail->payment_status == 3) {
                    $status = '<span class="chip lighten-5 red red-text">PARTIALY PAID</span>';
                } 
                elseif ($detail->payment_status == 5) {
                    $status = '<span class="chip lighten-5 cyan cyan-text">REFUNDED</span>';
                }elseif ($detail->payment_status == 6) {
                    $status = '<span class="chip lighten-5 cyan cyan-text" style="width: max-content;">PARTIAL REFUND</span>';
                }  
                else {
                    $status = '<span class="chip lighten-5 blue blue-text">ADDITIONALY PAID</span>';
                }
                return $status;
            })
            ->addColumn('updated_date', function ($detail) {
                if ($detail->payment_status == 1) {
                    return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-y ' . $this->time_format . ':i a');
                }
            })
            ->addColumn('actual_amount', function ($detail) {
                return ($detail->amount != null) ? CURRENCY . ' ' . $detail->amount : '';
            })
            ->addColumn('action', function ($detail) {
                $action = '<div class="invoice-action">';
                if ($detail->payment_status == 0) {
                    $action .= '<a href="' . url($this->route . '/' . $detail->id . '/edit') . '" class="invoice-action-edit" title="Edit Invoice"><i class="material-icons">edit</i></a>';
                    $action .= '<a href="' . url($this->route . '/invoice/' . $detail->id) . '" class="invoice-action-edit" title="Download Invoice"><i class="material-icons">account_balance_wallet</i></a>';
                    $action .= '<a href="javascript:void(0);" id="' . $detail->id . '" onclick="deleteBill(this.id)" class="invoice-action-view mr-4" title="Delete Bill"><i class="material-icons">delete</i></a>';
                    // $action .= '<a href="" class="invoice-action-view mr-4" data-tooltip="Edit details"><i class="material-icons">mode_edit</i></a>';
                    // $action .= '<a href="" class="btn red btn-sm btn-icon mr-2" title="Delete"><i class="material-icons">delete</i></a>';
                } else {
                    // $action .= ' <a href="' . url( $this->route . '/' . $detail->id) . '"" class="invoice-action-edit" title="View details"><i class="material-icons">remove_red_eye</i></a>';
                    $action .= ' <a href="' . url($this->route . '/invoice-data/generate-pdf/' . $detail->id) . '"" class="invoice-action-edit" title="Download Invoice"><i class="material-icons mr-4">file_download</i></a>';
                    $action .= ' <a href="' . url($this->route . '/invoice-data/print/' . $detail->id) . '"" target="_blank" class="invoice-action-edit" style="" title="Download Invoice"><i class="material-icons mr-4">print</i></a>';
                    // $action .= ' <a href="javascript:void(0);" id="' . $detail->id . '" onclick="cancelBill(this.id)" class="btn orange btn-sm btn-icon mr-2" title="Cancel Bill"><i class="material-icons">cancel</i> </a>';
                }
                $billItemcount=BillingItem::where('billing_id',$detail->id) ->whereIn('item_type', ['services', 'packages'])->count();
                $hasMembership = BillingItem::where('billing_id', $detail->id)->where('item_type', 'memberships')->exists();
                if($billItemcount>0 && !$hasMembership){
                    $action .= ' <a href="javascript:void(0);" id="' . $detail->id . '" onclick="billCancel(this.id, this)" data-payment_status="' . $detail->payment_status . '" class="mr-2 paymentStatus" title="Cancel Bill"><i class="material-icons">cancel</i> </a>';
                }
                $action .= '</div>';
                return $action;
            })
            ->removeColumn('id', 'customer')
            ->escapeColumns([])
            ->make(true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request, $id)
    {
       
        $billing                    = Billing::findOrFail($id);
        if(!$billing) {
            abort(404);
        }
        
        $package =[];
        $service=[];
        if ($billing->items) {
            $packageIds = $billing->items->pluck('package_id')->filter()->unique()->toArray();
            $serviceIds=$billing->items->pluck('item_id')->filter()->unique()->toArray();
            foreach ($packageIds as $packageId) {
                $package[] = Package::find($packageId);
            }
            foreach ($serviceIds as $serviceId) {
                $service[] = Service::find($serviceId);
            }
        }
      
        $page                        = collect();
        $variants                    = collect();
        $page->title                 = $this->title;
        $page->link                  = url($this->route);
        $page->route                 = $this->route;
        $variants->payment_types     = PaymentType::whereIn('shop_id', [0, SHOP_ID])->whereStatus(1)->get();
        $variants->customerMembership= customerMemberships::with('membership')->where('customer_id',$billing->customer_id)->get();        
        $variants->customerPendingAmount=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('removed',0)->where('expiry_status',0)->sum('over_paid');
        $variants->customerDueAmount=CustomerPendingPayment::where('customer_id', $billing->customer_id)
        ->where(function ($query) {
            $query->where('current_due', '!=', 0);  
        })
        ->orderBy('created_at','DESC')
        ->get();
        $variants->shop           =Shop::find(SHOP_ID);
        if ($billing->status === 0) {            
            if ($billing->items) {
                $variants->item_ids         = array_column($billing->items->toArray(), 'item_id');
            }    
            
            return view($this->viewPath . '.invoice', compact('page', 'billing', 'variants','package','service'));
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'customer_id' => 'required',
            'billed_date' => 'required',
            'service_type'=>'required',
            'bill_item'=>'required',
        ];
        $messages = [
            'customer_id.required' => 'Customer ID is required.',
            'service_type.required' => 'Service Type required.',
            'bill_item.required' => 'Services Required',
            'billed_date.required' => 'Bill Date Required',
        ];
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput() ;
        }
        DB::beginTransaction();
        try {
            $customer=CUstomer::find($request->customer_id);
            if($request->service_type== 2 && $customer->is_membership_holder==1){
                     return redirect()->back()->with('error', 'Do not purchase Packages! You have Pending Membership Credits')->withInput();

            }
            $total_amount   = 0; 
          
            if($request->service_type!=3){ 
                
                $customerOpenBill = Customer::checkPendingBill($request->customer_id); 
               
                if (count($customerOpenBill) > 0) {           
                    $message = '';
                    $old_items=[];
                    $billing            = Billing::findOrFail($customerOpenBill[0]->id);     
                                 
                    if($request->service_type==1){
                        $serviceCategory='services';
                    }elseif($request->service_type==2){
                        $serviceCategory='packages';
                    }
                    $serviceTypes       =BillingItem::where('customer_id',$request->customer_id)->where('billing_id',$billing->id)->where('item_type',$serviceCategory)->get();
                    $isServiceCategoryFound=0;
                    foreach($serviceTypes as $key=>$service){
                        if($service->item_type==$serviceCategory){
                            $isServiceCategoryFound=++$key;
                        }
                    }
                    foreach ($billing->items as $key => $row) {
                        $old_items[$key] = $row->item_id;
                    }
                    foreach ($request->bill_item as $key => $row) {
                        $new_items[$key] = $row;
                    }
                    $more_items     = array_diff($new_items, $old_items);                
                    // if (count($more_items) >= 0) {
                        if(count($customerOpenBill) > 0 && $isServiceCategoryFound >0){
                            $newItems = BillingItem::addMore($customerOpenBill[0]->id, $new_items, $request);
                            if ($newItems) {
                                $message = "New Items added to old pending bill";
                                DB::commit();
                                return redirect($this->route . '/invoice/' . $billing->id)->with('info', 'Customer has pending bill. ' . $message);          
                            } else {
                                // Handle case when no new items are added
                                return redirect()->back()->with('error', 'No new items were added to the pending bill.');
                            }
                        }
                    // }
                }
            }
            $billed_date                    = FunctionHelper::dateToTimeFormat($request->billed_date);
            $validity_from                  = FunctionHelper::dateToTimeFormat($request->validity_from);
            $validity_to                    = FunctionHelper::dateToTimeFormat($request->validity_to);
            $today                          = FunctionHelper::dateToUTC($validity_from, 'Y-m-d H:i:s');
            if (is_string($today)) {
                $today = \Carbon\Carbon::parse($today);
            }
            $validity_to                    = $today->addDays($request->validity);
            $billing                        = new Billing();
            $billing->shop_id               = SHOP_ID;
            $billing->customer_id           = $request->customer_id;
            $billing->customer_type         = Customer::isExisting($request->customer_id);
            $billing->billed_date           = FunctionHelper::dateToUTC($billed_date, 'Y-m-d H:i:s');
            $billing->payment_status        = 0;
            $billing->address_type          = ($request->billing_address_checkbox == 0) ? 'company' : 'customer';
            $billing->save();
            if ($request->billing_address_checkbox == 0) {
                $address                    = new BillingAddres();
                $address->shop_id           = SHOP_ID;
                $address->bill_id           = $billing->id;
                $address->customer_id       = $request->customer_id;
                $address->billing_name      = $request->customer_billing_name;
                $address->country_id        = $request->country_id;
                $address->state_id          = $request->state_id;
                $address->district_id       = $request->district_id;
                $address->pincode           = $request->pincode;
                $address->gst               = $request->customer_gst;
                $address->address           = $request->address;
                $address->updated_by        = auth()->user()->id;
                $address->save();
            }
            $itemsCount =[];
            if ($request->items !== null) {
                foreach ($request->items as $key => $item) {
                    $newArr             = array_keys($item);
                    $itemsCount[str_replace(' ', '', $key)]   = $newArr[0];
                }
            }    
            $data_price=[];       
            if($request['service_type'] == 2){                
                if ($request->bill_item!== null) {
                    foreach ($request->bill_item as $key=>$row) {                    
                        $packages=PackageService::where('package_id',$row)->get();
                        foreach($packages as $package){
                            $item               = new BillingItem();
                            $item->billing_id   = $billing->id;
                            $item->customer_id  = $request->customer_id;
                            $item->item_type    = 'packages';
                            $item->item_id      = $package->service_id;
                            $item->package_id   = $package->package_id;                        
                            $item->validity_from= FunctionHelper::dateToUTC($validity_from, 'Y-m-d H:i:s');
                            $item->validity_to  = $validity_to;
                            $item->expiry_status= 0;
                            $item->item_count   = $itemsCount[$row] ?? 1;
                            $item_details       = Package::getTimeDetails($package->service_id);
                            $data_price[$key]   = $package->package->price; 
                            $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                            $item->save();
                            $total_amount   = array_sum($data_price);
                            // $total_amount       = $package->package->price;
                        }
                    }             
                }
            }
            elseif($request['service_type'] == 3){      
                if ($request->bill_item!== null) {
                    foreach ($request->bill_item as $row) {                               
                        $item               = new BillingItem();
                        $item->billing_id   = $billing->id;
                        $item->customer_id  = $request->customer_id;
                        $item->item_type    = 'memberships';
                        $item->item_id      = $row;
                        $item->item_count   = isset($itemsCount[$row]) ? $itemsCount[$row] : 1;
                        $item_details       = Membership::getTimeDetails($row);
                        $item->item_details = $item_details['full_name'];
                        $item->save();
                        $customer           = Customer::find($request->customer_id);
                        $membership         = Membership::find($row);             
                        $customer->is_membership_holder = 1;
                        $customer->save();                        
                        $durationType   = $membership->duration_type;
                        $durationCount  = $membership->duration_in_days;
                        $today          = now();                       
                        $customer_membership =new customerMemberships();                        
                        $customer_membership->customer_id    = $request->customer_id;   
                        $customer_membership->membership_id  = $row;   
                        $customer_membership->bill_id        = $billing->id;                 
                        $customer_membership->start_date     = $today;                 
                        $customer_membership->end_date       = Carbon::parse($customer_membership->start_date)->addUnit($durationType, $durationCount);   
                        $customer_membership->expiry_status  = 0;
                        
                        $customer_membership->save(); 
                        $data_price         = $membership->price * $itemsCount[$row];
                        $total_amount   += $data_price;
                    }
                }
            }        
            else{
                if ($request->bill_item!== null) {                    
                    foreach ($request->bill_item as $key=> $row) {                    
                        $item               = new BillingItem();
                        $item->billing_id   = $billing->id;
                        $item->customer_id  = $request->customer_id;
                        $item->item_type    = 'services';
                        $item->item_id      = $row;
                        $item->item_count   = isset($itemsCount[$row]) ? $itemsCount[$row] : 1;
                        $item_details       = Service::getTimeDetails($row);
                        $data_price[$key]   = Service::getPriceAfterTax($row);
                        $item_details       = Service::getTimeDetails($row);                        
                        if ($item->item_count > 0) {
                            $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                        } else {
                            $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                        }
                        $item->save();
                       
                        $total_amount   = array_sum($data_price);
                        
                    }                   
                }
            }
            $billing->amount            += $total_amount;
            $billing->save();
            $current='7';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type='bill';
            $comment='Bill Created';
            $customer=$billing->customer_id;
                FunctionHelper::statusChangeHistory($activity_id=0, $previous, $current,$customer,$schedule,$billing,$comment,$type);
            DB::commit();
            return redirect($this->route . '/invoice/' . $billing->id);
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePayment(Request $request)
    {
        $item_ids       = $request->item_ids;         
        $discount       = 0;
        $instoreAmount  = 0;
        $validatorRules = [];  
        $packages       = [];     
        $billing        = Billing::findOrFail($request->billing_id);
        $customermembership=customerMemberships::where('customer_id',$billing->customer_id)->first();
        if($customermembership){
            $amountPaid=array_sum($request->payment_value);
            $current_due=$request->sub_total-$amountPaid;
            if($current_due >0){
                return [
                    'flagError' => true,
                    'message' => 'Partial Payment Not Alowed!' 
                ];
            }
        }        
        if ($customermembership !== null) {
            for ($i = 0; $i < 3; $i++) {
                if($i==0){
                     $validatorRules["payment_value.0"] = "required_without_all:" . implode(',', array_diff([ "payment_value.1", "payment_value.2"]));
                }
            } 
        }else{                     
            $paymentValues = $request->input('payment_value');     
            if($paymentValues[0] !=$billing->amount) { 
                if (!empty($paymentValues[0]) && count(array_filter(array_slice($paymentValues, 1))) === 0) {  
                    return [
                        'flagError' => true,
                        'message' => 'Please Choose Any Other Payment Option'
                    ];
                }
            }        
            if (!empty($paymentValues[1])) {  
                for ($i = 2; $i < count($paymentValues); $i++) {               
                    if (!empty($paymentValues[$i])) {
                        $validatorRules["payment_value.$i"] = "required";
                    }
                }
            }
        }
        $validator = Validator::make($request->all(), $validatorRules);        
        if ($validator->fails()) {
            return [
                'flagError' => true,
                'message' => 'Please Choose Any of the Payment method',
                'error' => $validator->errors()->toArray(),
            ];
        }
        try {
                DB::beginTransaction();
                $data = [];
                // Get bill details
                if ($billing) {
                    $billValue          = $request->sub_total; // Bill value
                    $grandTotal         = $request->grand_total; // Bill value + Existing Dues
                    $customerReceived   = array_sum($request->payment_value); // Customer Paid values     
                  
                    // if($customerReceived >$grandTotal ||$customerReceived ==$grandTotal){
                    //     $billValue          = $grandTotal;
                    // }else{
                        $billValue          = $request->sub_total-$request->payment_value[0]-$request->discount;
                    // }
                    $customerDues       = CustomerPendingPayment::where('customer_id', $billing->customer_id)->where('current_due','>',0)
                                        ->where('expiry_status',0)->where('removed',0)->get();
                    $instoreCreditBalance=CustomerPendingPayment::where('customer_id', $billing->customer_id)->where('over_paid','>',0)
                    ->where('expiry_status',0)->where('removed',0)->sum('over_paid');
                   
                    $bill_items_discount=BillingItem::where('billing_id',$billing->id)->sum('discount_value');
                    $payment_status     = 1;
                    $additionalPaid     = 0;
                    $tempCurrentDue     = 0;
                    $current_due        = 0;
                    $over_paid          = 0;
                    $instoreAmount      = 0;
                    $customer_pending_billing_ids=[];
                    foreach($customerDues as $due){
                        $current_due       += floatval($due->current_due);           
                        $over_paid         += floatval($due->over_paid);
                        $customer_pending_billing_ids[]=$due->bill_id;
                    }
                    $additionalPaid = floatval($additionalPaid);
                    $current_due=$temp_current_due    = floatval($current_due);    
                    $over_paid      = floatval($over_paid);       
                    if ($customerReceived <= $grandTotal) {  
                        if ($billValue <= $customerReceived) {     
                                                                                                                              
                            if($request->payment_value[0]==NULL)
                            {                                 
                                // $additionalPaid     = max(0,$grandTotal-$customerReceived - ($billValue+$request->payment_value[0]-$request->discount));
                                $additionalPaid     = $customerReceived-$billValue;                               
                            }else{   
                                $additionalPaid     =max(0, ($customerReceived-$request->payment_value[0])-$billValue); 
                                // $additionalPaid     = $grandTotal - ($billValue+$request->payment_value[0]+$request->discount); 
                                // $additionalPaid     = max(0,($grandTotal - $customerReceived)-$request->discount);   
                            }  
                                                                    
                            if($customerReceived!=($billValue+$request->payment_value[0])){  
                                // $current_due        = max(0,$grandTotal-$customerReceived);  
                                
                                //    $current_due        = $customerReceived-$billValue;    
                                if($grandTotal!==($customerReceived-$request->payment_value[0])){    
                                    // $current_due        =$customerReceived-$billValue;
                                    // $current_due        =max(0,$grandTotal-$customerReceived);                                                       
                                    if(($customerReceived-$request->payment_value[0])<$billValue){
                                        $current_due        =max(0,$billValue- ($customerReceived-$request->payment_value[0]));                                    
                                    } 
                                }                                
                            }

                            if ($current_due == 0) {   
                                $over_paid          = $additionalPaid;                              
                                $current_due        = 0;                        
                            } 
                            // else {                                
                            //     $current_due         = ($current_due > $additionalPaid) ? $current_due - $additionalPaid :  $additionalPaid - $current_due;                             
                            //     $payment_status      = $current_due>0 ?3:1;    
                            // }     
                                               
                            if($additionalPaid > 0){
                                $payment_status     = 4 ;
                            }     
                           
                            if($additionalPaid==$current_due){
                                $payment_status     = 1;
                                $current_due        = 0;
                                $over_paid          =0;
                            } else{     
                                if($additionalPaid >0){                          
                                $over_paid          = $additionalPaid-$current_due;
                                $current_due        = $additionalPaid>$current_due?0:$additionalPaid-$current_due;  
                                }                                
                            }  
                         

                        } else {  
                            if($grandTotal ==$customerReceived ){ 
                                $payment_status     = 1;
                                $current_due        += 0;     
                            }else{   
                                                          
                                $payment_status     = 3;                       
                                $current_due           = $billValue - ($customerReceived-$request->payment_value[0]);
                                $over_paid             =0;     
                                               
                            }                                     
                           
                        } 
                        if($current_due>0){
                            $payment_status     = 3;

                        } 
                                    
                    } else {   
                        // if($request->payment_value[0]>0){
                        //     $customerReceived-=$request->payment_value[0];
                        // }                          
                        // $additionalPaid         = $customerReceived - ($billValue-$current_due);
                        // logic removed for due with discount
                        // $additionalPaid         = $customerReceived - $billValue-$request->payment_value[0]-$request->discount;
                        $additionalPaid         =($customerReceived-$request->payment_value[0])-$billValue;
                             
                        if($customerReceived<$billValue){                             
                            $current_due=$billValue-$customerReceived;
                            $over_paid=0;                                             
                        }     
                           
                        if ($current_due == 0) {
                            $over_paid          = ($additionalPaid)>0 ? $additionalPaid:0;  
                            $current_due        =0;                                   
                        } else {        
                            if ($additionalPaid > $current_due) { 
                                $over_paid      = $additionalPaid - $current_due;
                                $current_due=0;
                                $payment_status     = 1;                               
                            } else {       
                                if($current_due==$additionalPaid)   {  
                                    $over_paid=$current_due - $additionalPaid;
                                    $current_due    = $current_due - $additionalPaid;                                           
                                //    if($current_due==0){
                                        $payment_status = 1; 
                                //    } 
                                
                                }    
                            }                        
                        }  
                        if($current_due>0){
                            $payment_status     = 3;

                        }  
                         
                    }
                  
                  
                    //Additionally added logic for removing reduce due cron
                    if (isset($request->payment_type) && isset($request->payment_amount)) {
                        foreach ($request->payment_type as $index => $paymentType) {
                            if (isset($request->payment_amount[$index]) && floatval($request->payment_amount[$index]) > 0) {
                                $paymentAmount = floatval($request->payment_amount[$index]);
                                $paymentTypeName=PaymentType::where('id',$paymentType)->value('name');
                                if ($paymentTypeName === 'In-store Credit') {
                                    $instoreCreditBalance = bcsub(floatval($instoreCreditBalance), $paymentAmount, 2);
                                }
                            }
                        }
                    } 
                    
                    // if(isset($request->payment_type[0]) && $request->payment_type[0]=='In-store Credit' && $request->payment_amount[0]> 0){                      
                    // //    dd( $instoreCreditBalance,$request->payment_amount[0]);
                    //     $instoreCreditBalance=bcsub(floatval($instoreCreditBalance),floatval($request->payment_amount[0]),2);                       
                    // } 
                    
                    if($instoreCreditBalance >0 && $current_due >0){
                        return ['flagError' => true, 'message' => 'Please use the in-store credit to cover the amount due.'];
                    }
                    if($instoreCreditBalance < $current_due){
                        $current_due=$current_due-$instoreCreditBalance;
                    }   
                              
                    if(count($customer_pending_billing_ids) >=1){                                      
                        foreach($customer_pending_billing_ids as $bill_id){
                            $billing_due=Billing::find($bill_id);
                            $amount                     = $billing_due->actual_amount+$temp_current_due;
                            $billing_due->payment_status= $payment_status;
                            // $billing_due->actual_amount = $amount;
                            $billing_due->save();
                        }
                    }  
                    
                    if($additionalPaid >0){
                        // if($additionalPaid>$temp_current_due){
                            $billValue+=$additionalPaid;
                        // }
                    } 
                // Subtract from In-store credit        
                    // if(isset($request->payment_type[0]) && $request->payment_type[0]=='In-store Credit'){
                    //     if (!empty($request->payment_amount) && isset($request->payment_amount[0])) {
                    //         $instoreAmount =$request->payment_amount[0];                        
                    //     } 
                    // }  
                    if (isset($request->payment_type) && isset($request->payment_amount)) {
                        foreach ($request->payment_type as $index => $paymentType) {
                            if (isset($request->payment_amount[$index]) && floatval($request->payment_amount[$index]) > 0) {
                                $paymentAmount = floatval($request->payment_amount[$index]);
                                $paymentTypeName=PaymentType::where('id',$paymentType)->value('name');
                                if ($paymentTypeName === 'In-store Credit') {
                                    $instoreAmount = $paymentAmount;
                                }
                            }
                        }
                    }
                    
                    //'deducted_over_paid'=>$instoreAmount
                    $over_paids=0;
                    if($over_paid > 0){
                        foreach($request->payment_type as $payment_type){
                            $paymentTypeName=PaymentType::where('id',$paymentType)->value('name');
                            if($paymentTypeName!='In-store Credit'){
                                $over_paids=round($over_paid-(($over_paid *18)/100));
                            }else{
                                $over_paids=$over_paid;
                                
                            }
                        }
                    }  
                        
                    // if (is_array($request->payment_type) && in_array(3, $request->payment_type) && isset($request->payment_value[0]) && $request->payment_value[0]) {
                    //     // if($billValue <  $request->payment_value[0]){
                    //     //     return ['flagError' => true, 'message' => 'Please re-enter the amount! The in-store amount must be less than the subtotal.'];
                    //     // }
                    //     $this->inStoreCreditPayment($billing,$request->payment_value[0]);
                    // }  
                    if (is_array($request->payment_type) && is_array($request->payment_value)) {
                        foreach ($request->payment_type as $index => $paymentType) {
                            $paymentTypeName=PaymentType::where('id',$paymentType)->value('name');
                            if ($paymentTypeName === 'In-store Credit') {
                                $paymentAmount = floatval($request->payment_amount[$index]);
                                $this->inStoreCreditPayment($billing,$paymentAmount);
                            }

                        }
                    }
                    
                    $today = Carbon::now();
                    $temp_additional_paid=$additionalPaid;
                    $next_day=$today->clone()->addMonths(6); 
                    $validity ='180';
                    if ($additionalPaid > 0 ) {      
                        $flag=0;
                        $today = now()->toDateString();
                        $currentDate = now()->timestamp;            
                        $pendingDues = CustomerPendingPayment::where('customer_id', $billing->customer_id)
                            ->where('removed', 0)
                            ->where('current_due', '>', 0)
                            // ->whereDate('created_at', $today)
                            ->get();
                            if($over_paid>0){     
                                $flag+=1;
                                $deducted_amount=0;
                                if($current_due> 0){
                                    $deducted_amount=$over_paids;
                                }                               
                                $pending_account = CustomerPendingPayment::create([
                                    'customer_id'       => $billing->customer_id,
                                    'current_due'       => $current_due,
                                    'over_paid'         => ($current_due > 0) ? 0 : $over_paids,
                                    'deducted_over_paid'=> $deducted_amount,
                                    'expiry_status'     => 0,
                                    'gst_id'            => 4,
                                    'validity_from'     => $today,
                                    'validity_to'       => $next_day,
                                    'validity'          => $validity,
                                    'amount_before_gst' => ($current_due > 0) ? 0 : $over_paid,
                                    'bill_id'           => $billing->id,
                                    'is_billed'         => 0,
                                    'removed'           => 0,
                                ]);
                            }
                            if ($pendingDues->isNotEmpty()) {                                   
                                foreach ($pendingDues as $pendingDue) {
                                    $totalDue = $pendingDue->current_due;            
                                    if ($totalDue > 0) {
                                        $customerOverPaidLists = CustomerPendingPayment::where('expiry_status', 0)
                                            ->where('customer_id', $pendingDue->customer_id)
                                            ->where('removed', 0)
                                            ->get();
                                        $continueLoop = true;
                                        $customerOverPaidLists->sortBy(function ($pendingamount) use ($currentDate) {
                                            return max(0,strtotime($pendingamount->validity_to) - $currentDate);
                                        })->each(function ($pendingamount) use (&$pendingDue, &$totalDue, &$continueLoop, &$additionalPaid,&$billing) {
                                            if($totalDue >0 ){
                                            if ($continueLoop) {                                                 
                                                    $newCustomer = new CustomerPendingPayment();                                                 
                                                if ($totalDue > $additionalPaid) {         
                                                    $current_due=(bcsub(floatVal($totalDue), floatVal($additionalPaid)));               
                                                    $newCustomer->current_due = $current_due;
                                                    $newCustomer->deducted_over_paid = $pendingDue->current_due ?? $pendingamount->current_due;                                        
                                                } else {    
                                                    $newCustomer->current_due = 0;
                                                    $newCustomer->deducted_over_paid =$totalDue;
                                                }
                                                $pendingDue->removed = 1;
                                                $pendingDue->save();                               
                                                $newCustomer->over_paid =  $pendingDue->over_paid;
                                                $newCustomer->bill_id =$billing->id ?? $pendingamount->bill_id;
                                                $newCustomer->customer_id = $pendingamount->customer_id;
                                                // $pendingamount->instoreCreditParent->id ?? $pendingamount->id
                                                $newCustomer->parent_id =$pendingDue->instoreCreditParent->id ?? $pendingDue->id; ;
                                                $newCustomer->child_id = $pendingDue->instoreCreditParent->id ?? $pendingDue->id;
                                                $newCustomer->validity_from = $pendingamount->validity_from;
                                                $newCustomer->validity_to = $pendingamount->validity_to;
                                                $newCustomer->validity = $pendingamount->validity;
                                                $newCustomer->gst_id = $pendingamount->gst_id;
                                                $newCustomer->expiry_status = $pendingamount->expiry_status;
                                                $newCustomer->is_billed = 1;
                                                $newCustomer->removed = 0;
                                                $newCustomer->save();
                                                $additionalPaid -= $additionalPaid;
                                                $totalDue-=$totalDue;
                                                $pendingamount->removed = 1;
                                               
                                                $pendingamount->save();
                                                $continueLoop = false;
                                            }
                                        }
                                        });
                                    }
                                }
                            }else{  
                                if($flag==0){                      
                                    $pending_account = CustomerPendingPayment::create([
                                        'customer_id' => $billing->customer_id,
                                        'current_due' => $current_due,
                                        'over_paid' => ($current_due > 0) ? 0 : $over_paids,
                                        'deducted_over_paid' => 0,
                                        'expiry_status' => 0,
                                        'gst_id' => 4,
                                        'validity_from' => $today,
                                        'validity_to' => $next_day,
                                        'validity' => $validity,                                        
                                        'amount_before_gst' => ($current_due > 0) ? 0 : $over_paid,
                                        // 'bill_id' => ($current_due > 0) ? $billing->id : null,
                                        'bill_id' => $billing->id,
                                        'is_billed' => 0,
                                        'removed' => 0,
                                    ]);
                                }
                            }
                    }else{
                        if($current_due>0){                         
                            $pending_account = CustomerPendingPayment::create([
                                'customer_id' => $billing->customer_id,
                                'current_due' => $current_due,
                                'over_paid' => ($current_due > 0) ? 0 : $over_paids,
                                'deducted_over_paid' => 0,
                                'expiry_status' => 0,
                                'gst_id' => 4,
                                'validity_from' => $today,
                                'validity_to' => $next_day,
                                'amount_before_gst' => ($current_due > 0) ? 0 : $over_paid,
                                'bill_id' => ($current_due > 0) ? $billing->id : null,
                                'is_billed' => 0,
                                'removed' => 0,
                            ]);
                            
                        }
                    }
                  
                    // Store's default billing format
                    $paymentTypes = $request->input('payment_type') ?? [];
                    $paymentAmounts=$request->payment_amount ?? [];
                    $combinedArray = [];

                    foreach ($paymentTypes as $key => $value) {
                        if (isset($paymentAmounts[$key])) {
                            $combinedArray[] = [
                                'payment_type' => $value,
                                'payment_amount' => $paymentAmounts[$key]
                            ];
                        }
                    }
                    $serializedArray = array_map('serialize', $combinedArray);
                    $uniqueSerializedArray = array_unique($serializedArray);
                    $uniqueArray = array_map('unserialize', $uniqueSerializedArray);
                    $default_format     = Billing::getDefaultFormat();
                    
                    if (count($paymentTypes) == 1) {
                        
                        $payment_type_id = ($paymentTypes[0] == -1) ? 3 : $paymentTypes[0];
                        $format = BillingFormat::where('shop_id', SHOP_ID)->where('payment_type', $payment_type_id)->first(); 
                        $format_id = (isset($format)) ? $format->id : $default_format->id;                        
                        $billing_code = FunctionHelper::getBillingCode($format_id);
                    } else {
                        $format_id = $default_format->id;
                        $billing_code = FunctionHelper::getBillingCode($default_format->id);
                    }
                   
                   $customerMembership=Customer::where('id',$billing->customer_id)->first();
                    if($customerMembership->is_membership_holder==1){
                        $instoreCredit=CustomerPendingPayment::where('customer_id',$customerMembership->id)->where('removed',0)->where('expiry_status',0)->sum('over_paid');
                        // if(isset($request->payment_type[0]) && $request->payment_type[0]== 'In-store Credit'){
                        //     if($request->sub_total == 0 && $request->pending_amount== NULL){
                        //         if(array_sum($request->payment_amount)==$request->grand_total){
                        //             // $billValue=$request->grand_total;
                        //             $billValue=0;
                        //         }
                        //         $payment_status=1;
                        //     }
                          
                        // }
                        if (isset($request->payment_type) && isset($request->payment_amount)) {
                            foreach ($request->payment_type as $index => $paymentType) {
                                $paymentTypeName=PaymentType::where('id',$paymentType)->value('name');
                                if ($paymentTypeName === 'In-store Credit') {
                                    if($request->sub_total == 0 && $request->pending_amount== NULL){
                                        if(array_sum($request->payment_amount)==$request->grand_total){
                                            // $billValue=$request->grand_total;
                                            $billValue=0;
                                        }
                                        $payment_status=1;
                                    }

                                }
                            }

                        
                        }
                    }

                        
                   
                   if($bill_items_discount > 0){
                       $actual_value=$billValue-$temp_additional_paid -$current_due +$bill_items_discount;
                       if($actual_value==$request->sub_total || $actual_value==$request->grand_total){
                            if($actual_value==$customerReceived ||$billValue==$customerReceived){
                                $payment_status=1;
                            }
                        }
                   }
                   
                   if($temp_additional_paid ==$current_due){        
                    if($customerMembership->is_membership_holder!=1){
                        $billValue= $billValue-$temp_additional_paid;  
                    }else{
                        if($billValue == 0)
                        $billValue= $grandTotal;                        
                    }  
                   }else{
                        if($customerMembership->is_membership_holder!=1){                            
                            $billValue=$customerReceived-$request->payment_value[0];
                        }else{
                            $billValue=$customerReceived-$request->payment_value[0];                            
                        }                        
                   } 
                    $customerCount=Billing::where('customer_id',$billing->customer_id)->count();
                    $billing->payment_status    = $payment_status;
                    // $billing->amount            = $customerReceived-$temp_additional_paid;
                    $billing->amount            = $request->grand_total;
                    // $billing->actual_amount     =  $customerReceived-$temp_additional_paid;
                    
                    $billing->actual_amount     = $billValue;
                    $billing->status            = 1;
                    $billing->customer_type     = $customerCount > 1 ? 0 : 1;
                    $billing->billing_code      = $billing_code;
                    $billing->customer_address  = Customer::getBillingAddress($billing->customer_id, $billing->address_type);
                    $billing->created_at        = now();
                    $billing->updated_at        = now();
                    $billing->save();
                    if($over_paid >0){
                        $billitem=new BillingItem();
                        $billitem->billing_id         = $billing->id;
                        $billitem->customer_id        = $billing->customer_id;
                        $billitem->item_type          = 'instore';
                        $billitem->item_id            = 0;
                        $billitem->item_count         = 1;
                        $billitem->item_details       = 'In-store Credit';
                        $billitem->is_discount_used   = 0;
                        $billitem->save();
                            $item_tax           = new BillingItemTax();
                            $item_tax->bill_id                  = $billing->id;                        
                            $item_tax->bill_item_id             = $billitem->id;
                            $item_tax->item_id                  = 0;    
                            $item_tax->tax_method               = 'split_2';
                            // there is a doubt regarding cash and other payment type calculation for multiple 
                            $tax_array=$this->additionalPaymentTaxCalculation($billitem,$over_paid);
                            $item_tax->total_tax_percentage     = $tax_array['total_tax_percentage'];
                            $item_tax->cgst_percentage          = $tax_array['cgst_percentage'];
                            $item_tax->sgst_percentage          = $tax_array['sgst_percentage'];
                            $item_tax->cgst_amount              = $tax_array['cgst'];
                            $item_tax->sgst_amount              = $tax_array['sgst'];
                            $item_tax->grand_total              = $tax_array['total_amount'];
                            $item_tax->tax_amount               = $tax_array['amount'];
                            $item_tax->created_at               = now();
                            $item_tax->updated_at               = now();
                            $item_tax->save();  
                        
                    }
                    $processedKeys = [];
                    $totalPayments = count($uniqueArray);
                    $remainingDue = $temp_current_due;
                    foreach ($uniqueArray as $key => $value) {
                        if (!in_array($key, $processedKeys)) {
                            if ($value['payment_amount'] != '' && $value['payment_amount']>0) {                                
                                $bill_amount = new BillAmount();
                                $bill_amount->bill_id = $billing->id;
                                if ($value['payment_type'] != -1) {
                                    $bill_amount->payment_type = PaymentType::where('id', $value['payment_type'])->value('name');
                                } else {
                                    $bill_amount->payment_type = PaymentType::where('id', 3)->value('name');
                                }
                                $bill_amount->payment_type_id = ($value['payment_type'] != -1) ? $value['payment_type'] : 3;
                                $bill_amount->amount = $value['payment_amount'];
                                $bill_amount->billing_format_id = $format_id;
                                $bill_amount->created_at        = now();
                                $bill_amount->updated_at        = now();
                                $bill_amount->save();
                                // Mark this key as processed
                                $processedKeys[] = $key;
                            }
                        }
                    }
                    if ($temp_current_due > 0 && $temp_additional_paid > $temp_current_due) {
                        foreach ($customer_pending_billing_ids as $bill) {
                            $totalPayments = count($uniqueArray);
                            $remainingDue = $temp_current_due;
                            foreach ($uniqueArray as $key => $value) {
                                if ($value['payment_amount'] != '' && $value['payment_amount'] > 0) {
                                        $newBillAmount = new BillAmount();
                                        $newBillAmount->bill_id = $billing->id;
                                        $newBillAmount->parent_bill_id = $bill;
                                        $newBillAmount->payment_type_id = $value['payment_type'];
                                        $newBillAmount->payment_type = PaymentType::where('id', $value['payment_type'])->value('name');
                                         if ($totalPayments > 1) {
                                            $equalShare = ceil($remainingDue / $totalPayments);
                                            $newBillAmount->amount = $equalShare;
                                            $remainingDue -= $equalShare;
                                            $totalPayments--;
                                        } else {
                                            $newBillAmount->amount = $remainingDue;
                                            $remainingDue = 0;
                                        }
                                        
                                        $newBillAmount->billing_format_id = $format_id;
                                        $newBillAmount->created_at = now();
                                        $newBillAmount->updated_at = now();
                                        $newBillAmount->save();
                                  
                                }
                            }
                        }
                    }else if($temp_additional_paid >0 && $temp_additional_paid ==$temp_current_due){
                        foreach ($customer_pending_billing_ids as $bill) {
                            $totalPayments = count($uniqueArray);
                            $remainingDue = $temp_current_due;
                            foreach ($uniqueArray as $key => $value) {
                                if ($value['payment_amount'] != '' && $value['payment_amount'] > 0) {
                                        $newBillAmount = new BillAmount();
                                        $newBillAmount->bill_id = $billing->id;
                                        $newBillAmount->parent_bill_id = $bill;
                                        $newBillAmount->payment_type_id = $value['payment_type'];
                                        $newBillAmount->payment_type = PaymentType::where('id', $value['payment_type'])->value('name');
                                         if ($totalPayments > 1) {
                                            $equalShare = ceil($remainingDue / $totalPayments);
                                            $newBillAmount->amount = $equalShare;
                                            $remainingDue -= $equalShare;
                                            $totalPayments--;
                                        } else {
                                            $newBillAmount->amount = $remainingDue;
                                            $remainingDue = 0;
                                        }
                                        
                                        $newBillAmount->billing_format_id = $format_id;
                                        $newBillAmount->created_at = now();
                                        $newBillAmount->updated_at = now();
                                        $newBillAmount->save();
                                  
                                }
                            }
                        }
                    }
                    if ($billing->items) {
                        $item_ids                       = [];
                        $package                        = [];
                        $package_items                  = BillingItem::where('billing_id',$billing->id)->where('package_id','!=',NULL)->groupBy('package_id')->get();
                        $billing_items_array            = $billing->items->toArray();
                        $item_type                      = $billing_items_array[0]['item_type'];
                        $package_ids                    = array_column($package_items->toArray(), 'package_id');
                        $packages=[];
                        foreach ($billing_items_array as $row) {
                            $item_ids[] = $row['item_id'];
                        }
                        if ($item_type == 'services') {
                            $billing_items = Service::select('services.*', 'billing_items.id as billingItemsId', 'billing_items.billing_id as billingId', 'billing_items.is_discount_used', 'billing_items.discount_type', 'billing_items.discount_value', 'billing_items.item_count','billing_items.customer_id')
                                ->join('billing_items', 'billing_items.item_id', '=', 'services.id')
                                ->where('services.shop_id', SHOP_ID)
                                ->where('billing_items.billing_id', $request->billing_id)
                                ->whereIn('services.id', $item_ids)
                                ->orderBy('services.id', 'desc')
                                ->get();
                        }
                        elseif ($item_type == 'memberships') {
                            $billing_items = Membership::select('memberships.*', 'billing_items.id as billingItemsId', 'billing_items.billing_id as billingId', 'billing_items.is_discount_used', 'billing_items.discount_type', 'billing_items.discount_value', 'billing_items.item_count','billing_items.customer_id')
                                ->join('billing_items', 'billing_items.item_id', '=', 'memberships.id')                                
                                ->where('billing_items.billing_id', $request->billing_id)
                                ->whereIn('memberships.id', $item_ids)
                                ->orderBy('memberships.id', 'desc')
                                ->where('shop_id',SHOP_ID)
                                ->get();
                        }
                        else {                    
                            // $billing_items =BillingItem::where('billing_id', $billing->id)
                            // ->join('packages','packages.id','billing_items.package_id')
                            // ->join('services','services.id','=','billing_items.item_id')
                            // ->join('gst_tax_percentages', 'gst_tax_percentages.id', '=', 'services.gst_tax')
                            // ->join('package_service','packages.id','=','billing_items.package_id')
                            // ->whereIn('package_service.package_id',$package_ids)
                            // ->where('packages.shop_id', SHOP_ID)                           
                            // ->distinct()
                            // ->get();  

                            $billing_items =BillingItem::where('billing_id', $billing->id)
                            ->join('packages','packages.id','billing_items.package_id')
                            ->join('services','services.id','=','billing_items.item_id')
                            ->join('gst_tax_percentages', 'gst_tax_percentages.id', '=', 'services.gst_tax')
                            // ->join('package_service','packages.id','=','billing_items.package_id')
                            ->whereIn('packages.id',$package_ids)
                            ->where('packages.shop_id', SHOP_ID)                           
                            ->distinct()
                            ->get();  
                            $packages= Package::whereIn('id',$package_ids)->get();      
                        }                                    
                        $discount   = array();
                        $packagePriceArray=[];
                        $packagePrice=0;
                        $totalCount=[];
                        if($packages){
                            foreach($packages as $key=> $value){                   
                                $packagePriceArray[$key] = [
                                    'package_id'=>$value->id,
                                    'package_price' => $value->service_price-$value->price,
                                ];
                            } 
                        }else{
                            $total_no_items =$billing_items->count();      
                            if($instoreAmount>0){
                                $instoreAmount=$instoreAmount/$total_no_items;
                            }
                        }
                       
                        foreach ($billing_items as $key => $row) {
                            if($packages){
                                $totalCount=PackageService::where('package_id',$row->package_id)->count();
                                foreach($packagePriceArray as $packageItem){
                                    if($packageItem['package_id']==$row->package_id){
                                        $packagePrice =$packageItem['package_price']/$totalCount;
                                    }
                                }  
                            }
                            $tax_array                          = TaxHelper::simpleTaxCalculation($row,$discount,$instoreAmount,$packagePrice, $request); 
                            if($tax_array['status'] == false){
                                return ['flagError' => true, 'message' => "Please Re-enter Credit Amount",  'error' =>"Please Re-enter Credit Amount"];
                            }               
                            $item_count                         = BillingItem::where('billing_id', $request->billing_id)->where('item_id', $row->id)->value('item_count');
                            $item_tax                           = new BillingItemTax();
                            $item_tax->bill_id                  = $billing->id;
                            if ($item_type == 'services') {                             
                                $item_tax->bill_item_id             = $row->billingItemsId;
                                $item_tax->item_id                  = $row->id;    
                                                           
                            }elseif ($item_type == 'memberships') {
                                $item_tax->bill_item_id             = $row->billingItemsId;
                                $item_tax->item_id                  = $row->id;                               
                            }else{                                
                                $idsssss=[];
                                $billingItemsId     =BillingItem::where('billing_id',$row->billing_id)->where('package_id',$row->package_id)->where('item_id',$row->item_id)->first();
                                $item_tax->bill_item_id             = $billingItemsId->id;
                                $item_tax->item_id                  = $row->service_id;                               
                            }               
                            $item_tax->tax_method               = 'split_2';
                            $item_tax->total_tax_percentage     = $tax_array['total_tax_percentage'];
                            $item_tax->cgst_percentage          = $tax_array['cgst_percentage'];
                            $item_tax->sgst_percentage          = $tax_array['sgst_percentage'];
                            $item_tax->cgst_amount              = $tax_array['cgst'];
                            $item_tax->sgst_amount              = $tax_array['sgst'];
                            $item_tax->grand_total              = $tax_array['total_amount'];
                            $item_tax->tax_amount               = $tax_array['amount'];
                            $item_tax->created_at               = now();
                            $item_tax->updated_at               = now();
                            $item_tax->save();                              
                            if (count($tax_array['additiona_array']) > 0) {
                                foreach ($tax_array['additiona_array'] as $additional) {
                                    $additional_obj                 = new BillingItemAdditionalTax();
                                    $additional_obj->bill_id        = $billing->id;
                                    $additional_obj->bill_item_id   = $row->billingItemsId;
                                    $additional_obj->item_id        = $row->id;
                                    $additional_obj->tax_name       = $additional['name'];
                                    $additional_obj->percentage     = $additional['percentage'];
                                    $additional_obj->percentage     = $additional['percentage'];
                                    $additional_obj->amount         = $additional['amount'];
                                    $additional_obj->created_at     = now();
                                    $additional_obj->updated_at     = now();
                                    $additional_obj->save();
                                }
                            }
                            if ($item_type == 'memberships') {
                                $membership         = Membership::find($row->id);  
                                $durationType   = $membership->duration_type;                          
                                $durationCount  = $membership->duration_in_days;                        
                                $today=now();
                                $next_day      =Carbon::parse($today)->addUnit($durationType, $durationCount);
                                $validity =$today->diffInDays($next_day);
                                $total_over_paid=$membership->membership_price * $item_count;
                                $customer_instore_credit = CustomerPendingPayment::create([
                                    'customer_id'           => $billing->customer_id,
                                    'current_due'           => 0,
                                    'over_paid'             => $total_over_paid,
                                    'deducted_over_paid'    => 0,
                                    'expiry_status'         => 0,
                                    'is_membership'         => 1,
                                    'gst_id'                => $membership->gst_id,
                                    'validity_from'         => $today,
                                    'validity_to'           => $next_day,
                                    'validity'              => $validity,
                                    'amount_before_gst'     => $membership->membership_price,
                                    'bill_id'               => null,
                                    'is_billed'             => 0,
                                    'membership_id'         => $membership->id,
                                    'removed'               => 0,
                                ]);
                              
                            }
                        }
                    }           
                    
                  
                    $schedule = Schedule::where('billing_id', $billing->id)->update(['payment_status' => 1, 'schedule_color' => 'green']);
                    Event::dispatch(new SalesCompleted($billing->id));
                    DB::commit();
                    return ['flagError' => false, 'message' => "Payment submitted successfully !"];
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                $errors = array('Errors Occurred! Please try again.');
                return ['flagError' => true, 'message' =>$e->getMessage(),  'error' => $errors];
            }
    }

    public function getInvoiceData(Request $request)
    {       
        $grand_total                = 0;
        $sub_total                  = 0;
        $inStoreCredit              = 0;
        $customerDues               = 0;
        $package                    ='';
        $billing                    = Billing::findOrFail($request->billingId);
        $package_items              = BillingItem::where('billing_id',$billing->id)->where('package_id','!=',NULL)->groupBy('package_id')->get();
        $discount                   = $request->discount;
        $instoreAmount              = 0;
        $packagePrice               =0;
        if ($billing->items) {
            $billing_items_array    = $billing->items->toArray();           
            $item_type              = $billing_items_array[0]['item_type'] ?? '';
            $item_ids               = array_column($billing->items->toArray(), 'item_id');
            $package_ids             =array_column($package_items->toArray(), 'package_id');
            if ($item_type == 'services') {
                $billing_items['item_type'] = 'services';
                $billing_items = Service::select('services.*', 'billing_items.id as billingItemsId', 'billing_items.billing_id as billingId', 'billing_items.is_discount_used', 'billing_items.discount_type', 'billing_items.discount_value', 'billing_items.item_details', 'billing_items.item_count','billing_items.customer_id')
                    ->join('billing_items', 'billing_items.item_id', '=', 'services.id')
                    ->where('billing_items.deleted_at',NULL)
                    ->where('services.shop_id', SHOP_ID)->where('billing_items.billing_id', $request->billingId)
                    ->whereIn('services.id', $item_ids)
                    ->orderBy('services.id', 'desc')
                    // ->groupBy('services.id')
                    ->get();
            }elseif($item_type == 'memberships'){
                $billing_items['item_type'] = 'memberships';
                $billing_items = Membership::select('memberships.*', 
                'billing_items.id as billingItemsId', 
                'billing_items.billing_id as billingId', 
                'billing_items.is_discount_used', 
                'billing_items.discount_type', 
                'billing_items.discount_value', 
                'billing_items.item_details', 
                'billing_items.item_count', 
                'billing_items.customer_id')
                ->join('billing_items', 'billing_items.item_id', '=', 'memberships.id')
                ->where('billing_items.deleted_at',NULL)
                ->where('billing_items.billing_id',$request->billingId)
                ->whereIn('memberships.id', $item_ids)
                ->groupBy('memberships.id')
                ->orderBy('memberships.id', 'desc')
                ->where('shop_id',SHOP_ID)
                ->get();
            } elseif($item_type == 'packages') {
                    $billing_items['item_type'] = 'packages';
                    // 'gst_tax_percentages.*',
                    $billing_items = PackageService::
                   select('packages.*','services.*','package_service.*','billing_items.*','gst_tax_percentages.*','billing_items.id as billingItemsId','packages.price as packagePrice')
                   ->join('packages','packages.id','=','package_service.package_id')
                    ->join('services','services.id','=','package_service.service_id')
                    ->join('billing_items', function($join) {
                        $join->on('billing_items.item_id', '=', 'services.id')
                             ->on('billing_items.package_id', '=', 'packages.id');
                    })
                    ->join('gst_tax_percentages', 'gst_tax_percentages.id', '=', 'services.gst_tax')
                    ->where('packages.shop_id', SHOP_ID)
                    ->where('billing_items.billing_id', $request->billingId)
                    ->whereIn('package_service.package_id',$package_ids)
                    ->whereIn('package_service.service_id', $item_ids)
                    ->orderBy('packages.id', 'desc')
                    ->whereNull('billing_items.deleted_at') 
                    ->get();
                $package= Package::whereIn('id',$package_ids)->get();                
            }else{
                $billing_items = Billing::where('id',$request->billingId)->first(); // Retrieve the Billing model instance first
                $billing_items['item_type'] = 'dues';
              
            }
           

            $sumArray = array();
            $instoreCreditAmount=0;           
                $customerPendingPayments=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$request->billingId)->where('expiry_status',0)->get();
                foreach ($customerPendingPayments as $key => $pendingPayment) {                
                $instoreCreditAmount+=$pendingPayment->deducted_over_paid ?? 0;    
            } 
            $packagePriceArray=[];
            $packagePrice=0;
            $totalCount=[];
            if($package){
                foreach($package as $key=> $value){                   
                    $packagePriceArray[$key] = [
                        'package_id'=>$value->id,
                        'package_price' => $value->service_price-$value->price,
                    ];
                } 
            }else{
                $total_no_items =$billing_items->count();      
            }
            if ($item_type == 'services') { 
                if($instoreCreditAmount>0){
                    $instoreAmount=$instoreCreditAmount/$total_no_items;
                }
            }
            else{
                $instoreAmount=$instoreCreditAmount;
            }
            $discountTotal=0;
            // if($billing_items['item_type']!=='dues'){
                foreach ($billing_items as $key => $row) {         
                    if($package){
                        $totalCount=PackageService::where('package_id',$row->package_id)->count();
                        foreach($packagePriceArray as $packageItem){
                            if($packageItem['package_id']==$row->package_id){
                                $packagePrice =$packageItem['package_price']/$totalCount;
                            }
                        }  
                    }
                    $row_sum                            = 0;                   
                    $tax_array                          = TaxHelper::simpleTaxCalculation($row, $discount,$instoreAmount,$packagePrice, $request);
                    $billing_items[$key]['tax_array']   = $tax_array;
                    $sub_total                          = ($sub_total + $billing_items[$key]['tax_array']['total_amount']);
                    $row_sum                            = $billing_items[$key]['tax_array']['total_amount'];   
                    if ($billing_items[$key]['tax_array']['discount_applied'] == 1) {
                        $row_sum                        = $row_sum ;
                        // $row_sum                        = $row_sum + $billing_items[$key]['tax_array']['discount_amount'];
                        //  dd($billing_items[$key]['tax_array']['discount_amount'] );
                        $discountTotal                 +=$billing_items[$key]['tax_array']['discount_amount'];
                    }   
                    if ($item_type == 'services') {
                        $billing_items[$key]['item_type'] = 'services';
                    }elseif ($item_type == 'memberships') {
                        $billing_items[$key]['item_type'] = 'memberships';
                    } else {
                        $billing_items[$key]['item_type'] = 'packages';
                    }          
                    $sumArray[$key] = $row_sum;
                }
                $grand_total += array_sum($sumArray);   

            // }else{  
             
        // if($billing_items['item_type']=='dues'){            
        //         $total = $billing_items->amount;   
        //         $grand_total += $total;
               
        //     }
            if($discountTotal >0)    { 
                $grand_total +=$discountTotal;
            }
        }
        $inStoreCreditBalance = 0;
        $over_paid            = 0;
        $customerDues_temp    = 0;
            if (!empty($billing->customer->pendingDues)) {
                foreach($billing->customer->pendingDues as $pendingDue){
                    if($pendingDue->over_paid!=0 && $pendingDue->removed ==0 && $pendingDue->is_membership==0 ){
                            $over_paid += $pendingDue->over_paid;
                    }
                    if($pendingDue->removed ==0){
                    $customerDues_temp                   += ($pendingDue->current_due != '') ? $pendingDue->current_due : '0';
                    }
                    if($pendingDue->bill_id==$request->billingId){
                        $inStoreCreditBalance           += ($pendingDue->deducted_over_paid != '') ? $pendingDue->deducted_over_paid : '0.00';
                    }                 
                }           
                    $inStoreCredit                  =$over_paid;  
            }
            
   
        $sub_total += $customerDues_temp;
            $package_price=0;
            if(is_countable($package) && $package->count() > 1){
                foreach($package as $value){
                    $package_price+=$value->price;
                }
            }
        // if($billing_items['item_type']!=='dues'){
        //     $sub_total +=$package_price+$customerDues_temp;  
            $sub_total-=$inStoreCreditBalance;  
        // }
        
        $customerMembership     = Customer::find($billing->customer_id);
        $customerPendingAmount=0;
        if ($item_type != 'packages') {
            $customerPendingAmount  = CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('removed',0)->where('expiry_status',0)->where('is_membership',1)->sum('over_paid');
        }
        $total_instore_credit       = $customerPendingAmount+$inStoreCredit;
        
        // $grand_total                +=$inStoreCreditBalance;
        $invoice_details = view($this->viewPath . '.invoice-data', compact('billing_items','customerMembership','instoreAmount'))->render();
        return ['flagError' => false,
            'grand_total' => $grand_total, 
            'discountAmount'=>$discountTotal,
            'sub_total' =>  $sub_total,
            'customerDues' => $customerDues_temp, 
            'inStoreCredit' => $inStoreCredit ,
            'instoreCreditBalance'=> $inStoreCreditBalance,
            'instore_amount'=>$instoreAmount,
            'html' => $invoice_details,
            'package'=>$package,
            'customerPendingAmount'=>$customerPendingAmount,
            'total_instore_credit'=>$total_instore_credit
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Request $request, $id)
    {
        $billing                = Billing::findOrFail($id);
        $package_items          = BillingItem::where('billing_id',$billing->id)->where('package_id','!=',NULL)->groupBy('package_id')->get();
         $variants              = collect();
        $variants->shop         = Shop::find(SHOP_ID);
        $store                  = Shop::find(SHOP_ID);
        if ($billing) {

            if ($billing->items) {
                $billing_items_array        = $billing->items->toArray();
                // $item_type                  = $billing_items_array[0]['item_type'];
                $item_types                 =  collect($billing_items_array)->pluck('item_type')->unique()->values()->toArray();
                $package_ids                = array_column($package_items->toArray(), 'package_id');
                foreach ($billing_items_array as $row) {
                    $ids[] = $row['item_id'];
                }
                if (in_array('services', $item_types)|| in_array('rebook', $item_types)) { 
                    $billing_items = collect();
                    if(in_array('services', $item_types)){
                        $service_items  = BillingItem::select(
                            'services.name',
                            'services.price',
                            'services.hsn_code',
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id'
                        )
                
                            ->join('services', 'services.id', '=', 'billing_items.item_id')
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('services.shop_id', SHOP_ID)
                            ->where('billing_items.billing_id', $id)
                            ->whereIn('services.id', $ids)->orderBy('services.id', 'desc')
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id','services.id')
                            ->get();
                            $billing_items = $billing_items->merge($service_items);

                        }    
                        if(in_array('rebook', $item_types)){
                            $rebook_items  = BillingItem::
                        select(
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.item_type',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id'
                        )
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('billing_items.billing_id', $billing->id)                            
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id')
                            ->get();
                            $billing_items = $billing_items->merge($rebook_items);

                        } 
                        if(in_array('instore', $item_types)){
                            $instore_items  = BillingItem::select(
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.item_type',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id')
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('billing_items.billing_id', $billing->id)                            
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id')
                            ->get();
                            $billing_items = $billing_items->merge($instore_items);
                        } 

                }elseif (in_array('memberships', $item_types)){
                    $billing_items = Membership::select(
                        'memberships.name',
                        'memberships.price',
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_items.is_discount_used',
                        'billing_items.discount_type',
                        'billing_items.discount_value',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id'
                    )
                    ->join('billing_items', 'memberships.id', '=', 'billing_items.item_id')
                    ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    ->where('billing_items.billing_id',$id)
                    ->whereIn('memberships.id',$ids)
                    ->whereNull('billing_items.deleted_at')
                    ->groupBy('memberships.id')
                    ->orderBy('memberships.id', 'desc')
                    ->where('shop_id',SHOP_ID)
                    ->get();
                }elseif(in_array('rebook', $item_types)){
                    $billing_items  = BillingItem::
                    select(
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id'
                    )
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id', $billing->id)  
                        ->where('billing_items.item_type', 'rebook')                            
                        ->whereNull('billing_items.deleted_at') 
                        ->groupBy('billing_items.billing_id')
                        ->get();
                        if(in_array('instore', $item_types)){
                            $instore_items  = BillingItem::select(
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.item_type',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id')
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('billing_items.billing_id', $billing->id)     
                            ->where('billing_items.item_type', 'instore') 
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id')
                            ->get();
                            $billing_items = $billing_items->merge($instore_items);
                        } 
                }
                else {
                    $billing_items = collect();
                    // $package_items  = BillingItem::select(
                    //     'packages.id as packageId' ,
                    //     'packages.name',
                    //     'packages.price',
                    //     'packages.hsn_code',
                    //     'billing_items.item_type',
                    //     'billing_items.id as id',
                    //     'billing_items.billing_id as billingId',
                    //     'billing_items.item_details',
                    //     'billing_items.item_count',
                    //     'billing_items.is_discount_used',
                    //     'billing_items.discount_type',
                    //     'billing_items.discount_value',
                    //     'billing_item_taxes.cgst_percentage',
                    //     'billing_item_taxes.sgst_percentage',
                    //     'billing_item_taxes.tax_amount',
                    //     'billing_item_taxes.sgst_amount',
                    //     'billing_item_taxes.grand_total',
                    //     'billing_item_taxes.cgst_amount',
                    // )
                       
                    //     ->join('services', 'services.id', '=', 'billing_items.item_id')
                    //     ->join('packages', 'packages.id', '=', 'billing_items.package_id')
                    //     ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    //     ->where('packages.shop_id', SHOP_ID)
                    //     ->where('billing_items.billing_id', $id)
                    //     ->whereIn('services.id', $ids)
                    //     ->whereIn('packages.id', $package_ids)
                    //     ->orderBy('packages.id', 'desc')
                    //     ->whereNull('billing_items.deleted_at') 
                    //     ->groupBy('billing_items.billing_id','billing_items.item_id','billing_items.package_id')
                    //     ->get();
                    $package_items = BillingItem::
                    select(
                        'packages.id',
                        'packages.name',
                        'packages.price',
                        'packages.hsn_code',
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_type',
                        'billing_items.item_id',
                        'billing_items.package_id',
                        'billing_items.item_count',
                        'billing_items.is_discount_used',
                        'billing_items.discount_type',
                        'billing_items.discount_value',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id'
                    )
                        ->join('packages', 'packages.id', '=', 'billing_items.package_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('packages.shop_id', SHOP_ID)
                        ->where('billing_items.billing_id', $billing->id)
                        ->whereIn('billing_items.item_id', $ids)
                        ->whereIn('packages.id',$package_ids)
                        ->orderBy('billing_items.id', 'desc')
                        ->whereNull('billing_items.deleted_at') 
                        ->groupBy('billing_items.billing_id','billing_items.item_id','billing_items.package_id')
                        ->get();  
                        $billing_items = $billing_items->merge($package_items);
                        if(in_array('rebook', $item_types)){
                            $rebook_items  = BillingItem::
                        select(
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.item_type',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id'
                        )
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('billing_items.billing_id', $billing->id)                            
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id')
                            ->get();
                            $billing_items = $billing_items->merge($rebook_items);

                        } 
                        if(in_array('instore', $item_types)){
                            $instore_items  = BillingItem::select(
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.item_type',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id')
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('billing_items.billing_id', $billing->id)                            
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id')
                            ->get();
                            $billing_items = $billing_items->merge($instore_items);
                        } 
                    
                }
                $grand_total        =  $billing_items->sum('grand_total');
            }

            $data       = ['billing' => $billing, 'billing_items' => $billing_items, 'grand_total' => $grand_total, 'store' => $store];
            $pdf        = PDF::loadView($this->viewPath . '.invoice-pdf-download', compact('store', 'billing', 'billing_items', 'grand_total','variants'));
            $bill_title = str_replace(' ', '-', strtolower($billing->customer->name));
            return $pdf->download($bill_title.'-invoice.pdf');

            // return view($this->viewPath . '.invoice-pdf', compact('store', 'billing', 'billing_items', 'grand_total'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */
    public function printPDF(Billing $billing)
    {
        if (!$billing->items) {
            abort(404);
        } 
            $package_items              = BillingItem::where('billing_id',$billing->id)->where('package_id','!=',NULL)->groupBy('package_id')->get();
            $variants                   = collect();
            $variants->shop             = Shop::find(SHOP_ID);
            $billing_items_array        = $billing->items->toArray();
            // $item_type                  = $billing_items_array[0]['item_type'];
            $item_types                 =  collect($billing_items_array)->pluck('item_type')->unique()->values()->toArray();
            $package_ids                = array_column($package_items->toArray(), 'package_id');

            foreach ($billing_items_array as $row) {
                $ids[] = $row['item_id'];
            }
            if (in_array('services', $item_types)|| in_array('rebook', $item_types)) { 
                $billing_items = collect();
                if(in_array('services', $item_types)){
                    $service_items   = BillingItem::select(
                    'services.name',
                    'services.price',
                    'services.hsn_code',
                    'billing_items.id as id',
                    'billing_items.billing_id as billingId',
                    'billing_items.item_details',
                    'billing_items.item_type',
                    'billing_items.item_count',
                    'billing_items.is_discount_used',
                    'billing_items.discount_type',
                    'billing_items.discount_value',
                    'billing_item_taxes.cgst_percentage',
                    'billing_item_taxes.sgst_percentage',
                    'billing_item_taxes.tax_amount',
                    'billing_item_taxes.sgst_amount',
                    'billing_item_taxes.grand_total',
                    'billing_item_taxes.cgst_amount',
                    'billing_items.customer_id'
                )
                    ->join('services', 'services.id', '=', 'billing_items.item_id')
                    ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    ->where('services.shop_id', SHOP_ID)->where('billing_items.billing_id', $billing->id)
                    ->whereIn('services.id', $ids)->orderBy('services.id', 'desc')
                    ->groupBy('billing_items.billing_id','services.id')
                    ->whereNull('billing_items.deleted_at') 
                    ->get();
                    $billing_items = $billing_items->merge($service_items);

                }    
                if(in_array('rebook', $item_types)){
                    $rebook_items  = BillingItem::
                select(
                    'billing_items.id as id',
                    'billing_items.billing_id as billingId',
                    'billing_items.item_details',
                    'billing_items.item_count',
                    'billing_items.item_type',
                    'billing_item_taxes.cgst_percentage',
                    'billing_item_taxes.sgst_percentage',
                    'billing_item_taxes.tax_amount',
                    'billing_item_taxes.sgst_amount',
                    'billing_item_taxes.grand_total',
                    'billing_item_taxes.cgst_amount',
                    'billing_items.customer_id'
                )
                    ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    ->where('billing_items.billing_id', $billing->id)                            
                    ->whereNull('billing_items.deleted_at') 
                    ->groupBy('billing_items.billing_id')
                    ->get();
                    $billing_items = $billing_items->merge($rebook_items);

                } 
                if(in_array('instore', $item_types)){
                    $instore_items  = BillingItem::select(
                    'billing_items.id as id',
                    'billing_items.billing_id as billingId',
                    'billing_items.item_details',
                    'billing_items.item_count',
                    'billing_items.item_type',
                    'billing_item_taxes.cgst_percentage',
                    'billing_item_taxes.sgst_percentage',
                    'billing_item_taxes.tax_amount',
                    'billing_item_taxes.sgst_amount',
                    'billing_item_taxes.grand_total',
                    'billing_item_taxes.cgst_amount',
                    'billing_items.customer_id')
                    ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    ->where('billing_items.billing_id', $billing->id)                            
                    ->whereNull('billing_items.deleted_at') 
                    ->groupBy('billing_items.billing_id')
                    ->get();
                    $billing_items = $billing_items->merge($instore_items);
                } 
            }
            elseif (in_array('memberships', $item_types)){
                $billing_items  = BillingItem::select(
                    'memberships.name',
                    'memberships.price',
                    'billing_items.id as id',
                    'billing_items.billing_id as billingId',
                    'billing_items.item_details',
                    'billing_items.item_type',
                    'billing_items.item_count',
                    'billing_items.is_discount_used',
                    'billing_items.discount_type',
                    'billing_items.discount_value',
                    'billing_item_taxes.cgst_percentage',
                    'billing_item_taxes.sgst_percentage',
                    'billing_item_taxes.tax_amount',
                    'billing_item_taxes.sgst_amount',
                    'billing_item_taxes.grand_total',
                    'billing_item_taxes.cgst_amount',
                    'billing_items.customer_id'
                )
                    ->join('memberships', 'memberships.id', '=', 'billing_items.item_id')
                    ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    ->where('billing_items.billing_id', $billing->id)
                    ->whereIn('memberships.id', $ids)->orderBy('memberships.id', 'desc')
                    ->distinct()
                    ->whereNull('billing_items.deleted_at') 
                    ->get();
            }elseif(in_array('rebook', $item_types)){
                $billing_items  = BillingItem::
                select(
                    'billing_items.id as id',
                    'billing_items.billing_id as billingId',
                    'billing_items.item_details',
                    'billing_items.item_count',
                    'billing_item_taxes.cgst_percentage',
                    'billing_item_taxes.sgst_percentage',
                    'billing_item_taxes.tax_amount',
                    'billing_item_taxes.sgst_amount',
                    'billing_item_taxes.grand_total',
                    'billing_item_taxes.cgst_amount',
                    'billing_items.customer_id'
                )
                    ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                    ->where('billing_items.billing_id', $billing->id)                            
                    ->whereNull('billing_items.deleted_at') 
                    ->groupBy('billing_items.billing_id')
                    ->get();
                    if(in_array('instore', $item_types)){
                        $instore_items  = BillingItem::select(
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_items.item_type',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id', $billing->id)                            
                        ->whereNull('billing_items.deleted_at') 
                        ->groupBy('billing_items.billing_id')
                        ->get();
                        $billing_items = $billing_items->merge($instore_items);
                    } 
            }
             else {
                $billing_items = collect();
                // $package_items  = BillingItem::select(
                //     'packages.name',
                //     'packages.price',
                //     'packages.hsn_code',
                //     'billing_items.id as id',
                //     'billing_items.billing_id as billingId',
                //     'billing_items.item_details',
                //     'billing_items.item_type',
                //     'billing_items.is_discount_used',
                //     'billing_items.discount_type',
                //     'billing_items.discount_value',
                //     'billing_item_taxes.cgst_percentage',
                //     'billing_item_taxes.sgst_percentage',
                //     'billing_item_taxes.tax_amount',
                //     'billing_item_taxes.sgst_amount',
                //     'billing_item_taxes.grand_total',
                //     'billing_item_taxes.cgst_amount',
                // )
                //     ->join('services', 'services.id', '=', 'billing_items.item_id')
                //     ->join('packages', 'packages.id', '=', 'billing_items.package_id')
                //     ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                //     ->where('packages.shop_id', SHOP_ID)
                //     ->where('billing_items.billing_id', $billing->id)
                //     ->whereIn('services.id', $ids)
                //     ->whereIn('packages.id', $package_ids)
                //     ->orderBy('packages.id', 'desc')
                //     // ->distinct()
                //     ->groupBy('billing_items.billing_id','billing_items.item_id','billing_items.package_id')
                //     ->whereNull('billing_items.deleted_at') 
                //     ->get();
                $package_items  = BillingItem::
                        select(
                            'packages.id',
                            'packages.name',
                            'packages.price',
                            'packages.hsn_code',
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_type',
                            'billing_items.item_id',
                            'billing_items.package_id',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id'
                        )
                            ->join('packages', 'packages.id', '=', 'billing_items.package_id')
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('packages.shop_id', SHOP_ID)
                            ->where('billing_items.billing_id', $billing->id)
                            ->whereIn('billing_items.item_id', $ids)
                            ->whereIn('packages.id',$package_ids)
                            ->orderBy('billing_items.id', 'desc')
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id','billing_items.item_id','billing_items.package_id')
                            ->get();
                    $billing_items = $billing_items->merge($package_items);
                    if(in_array('rebook', $item_types)){
                        $rebook_items  = BillingItem::
                    select(
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_items.item_type',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id'
                    )
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id', $billing->id)                            
                        ->whereNull('billing_items.deleted_at') 
                        ->groupBy('billing_items.billing_id')
                        ->get();
                        $billing_items = $billing_items->merge($rebook_items);
    
                    } 
                    if(in_array('instore', $item_types)){
                        $instore_items  = BillingItem::select(
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_items.item_type',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id', $billing->id)                            
                        ->whereNull('billing_items.deleted_at') 
                        ->groupBy('billing_items.billing_id')
                        ->get();
                        $billing_items = $billing_items->merge($instore_items);
                    } 
            }

        $grand_total    =  $billing_items->sum('grand_total');
        $store          = $this->store;
        $data           = ['billing' => $billing, 'billing_items' => $billing_items, 'grand_total' => $grand_total, 'store' => $this->store];
       
        $pdf            = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true, 'scale' =>60])->setPaper([0,0, 200, 10000]);

        $pdf            = $pdf->loadView($this->viewPath . '.invoice-pdf', compact('store', 'billing', 'billing_items', 'grand_total','variants'));
        // return view($this->viewPath . '.invoice-pdf', compact('store', 'billing', 'billing_items', 'grand_total'));
        $bill_title     = str_replace(' ', '-', strtolower($billing->customer->name));
        $output         = $pdf->output(); 
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'inline; filename="'.$bill_title.'"',
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $billing                = Billing::with('customerPendingAmount')->findOrFail($id);
        $package_items          = BillingItem::where('billing_id',$billing->id)->where('package_id','!=',NULL)->groupBy('package_id')->get();
        $grand_total                = 0;
        $sub_total                  = 0;
        $inStoreCredit              = 0;
        $customerDues               = 0;
        $discount                   = 0;
        $instoreAmount              =0;
        abort_if(!$billing, 404);
        $variants->shop           =Shop::find(SHOP_ID);
        if ($billing) {
            if ($billing->status === 1) {
                // $billing                = Billing::with('customerPendingAmount')->findOrFail($id);
                $variants->store        = Shop::find(SHOP_ID);
                if ($billing->items) {
                    $billing_items_array        = $billing->items->toArray();                    
                    $item_types                 =  collect($billing_items_array)->pluck('item_type')->unique()->values()->toArray();
                    // $item_type                  = $billing_items_array[0]['item_type'];
                    
                    $item_ids                   = array_column($billing->items->toArray(), 'item_id');
                    $package_ids                = array_column($package_items->toArray(), 'package_id');
                    foreach ($billing_items_array as $row) {
                        $ids[] = $row['item_id'];
                    }
                    $billing_items = collect();
                    if(in_array('rebook', $item_types)){
                        $rebook_items  = BillingItem::select(
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_items.item_type',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id', $billing->id)                            
                        ->where('billing_items.item_id', 0)   
                        ->where('billing_items.item_type', 'rebook')   
                        ->whereNull('billing_items.deleted_at') 
                        // ->groupBy('billing_items.billing_id')
                        ->get();
                       
                        $billing_items = $billing_items->merge($rebook_items);
                    }  
                    if(in_array('instore', $item_types)){
                        $instore_items  = BillingItem::select(
                        'billing_items.id as id',
                        'billing_items.billing_id as billingId',
                        'billing_items.item_details',
                        'billing_items.item_count',
                        'billing_items.item_type',
                        'billing_item_taxes.cgst_percentage',
                        'billing_item_taxes.sgst_percentage',
                        'billing_item_taxes.tax_amount',
                        'billing_item_taxes.sgst_amount',
                        'billing_item_taxes.grand_total',
                        'billing_item_taxes.cgst_amount',
                        'billing_items.customer_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id', $billing->id)                            
                        ->where('billing_items.item_type', 'instore')                            
                        ->whereNull('billing_items.deleted_at') 
                        ->groupBy('billing_items.billing_id')
                        ->get();
                        $billing_items = $billing_items->merge($instore_items);
                    } 
                    if (in_array('services', $item_types)) {     
                            $service_items  = BillingItem::
                            select(
                                'services.id',
                                'services.name',
                                'services.price',
                                'services.hsn_code',
                                'billing_items.id as id',
                                'billing_items.billing_id as billingId',
                                'billing_items.item_details',
                                'billing_items.item_type',
                                'billing_items.item_count',
                                'billing_items.is_discount_used',
                                'billing_items.discount_type',
                                'billing_items.discount_value',
                                'billing_item_taxes.cgst_percentage',
                                'billing_item_taxes.sgst_percentage',
                                'billing_item_taxes.tax_amount',
                                'billing_item_taxes.sgst_amount',
                                'billing_item_taxes.grand_total',
                                'billing_item_taxes.cgst_amount',
                                'billing_items.customer_id'
                            )
                                ->join('services', 'services.id', '=', 'billing_items.item_id')
                                ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                                ->where('services.shop_id', SHOP_ID)
                                ->where('billing_items.billing_id', $billing->id)
                                ->whereIn('services.id', $ids)
                                ->orderBy('services.id', 'desc')
                                ->whereNull('billing_items.deleted_at') 
                                ->groupBy('billing_items.billing_id','services.id')
                                ->get();
                            $billing_items = $billing_items->merge($service_items)->sortByDesc('id');  
                            
                    } elseif(in_array('memberships', $item_types)){
                        $billing_items = Membership::select(
                            'memberships.id',
                            'memberships.name',
                            'memberships.price',
                            'billing_items.id AS id',
                            'billing_items.billing_id AS billingId',
                            'billing_items.item_details',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id'
                        )
                        ->join('billing_items', 'memberships.id', '=', 'billing_items.item_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('billing_items.billing_id',$billing->id)
                        ->whereIn('memberships.id',$ids)
                        ->whereNull('billing_items.deleted_at')
                        ->groupBy('memberships.id')
                        ->orderBy('memberships.id', 'desc')
                        ->where('shop_id',SHOP_ID)
                        ->get();
                        
                    }
                    elseif (in_array('packages', $item_types)) {                            
                        $packageItems  = BillingItem::
                        select(
                            'packages.id',
                            'packages.name',
                            'packages.price',
                            'packages.hsn_code',
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_type',
                            'billing_items.item_id',
                            'billing_items.package_id',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id'
                        )
                            ->join('packages', 'packages.id', '=', 'billing_items.package_id')
                            ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                            ->where('packages.shop_id', SHOP_ID)
                            ->where('billing_items.billing_id', $billing->id)
                            ->whereIn('billing_items.item_id', $ids)
                            ->whereIn('packages.id',$package_ids)
                            ->orderBy('billing_items.id', 'desc')
                            ->whereNull('billing_items.deleted_at') 
                            ->groupBy('billing_items.billing_id','billing_items.item_id','billing_items.package_id')
                            ->get();
                        $billing_items = $billing_items->merge($packageItems)->sortByDesc('id'); 
                        
                    }
                    $grand_total = $billing_items->sum('grand_total');
                    }
                // dd($billing_items);
                return view($this->viewPath . '.invoice-view', compact('page', 'billing', 'billing_items', 'grand_total', 'item_types', 'variants'));
            }
            abort(404);
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */
    public function edit(Billing $billing)
    {
        $ids                    =[];
        $service_type           =0;
        $item_type              =0;
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $variants->states       = array();
        $variants->districts    = array();
        $variants->country      = country::where('status', 1)->pluck('name', 'id');
        $variants->services     = Service::where('shop_id', SHOP_ID)->pluck('name', 'id');
        $variants->packages     = Package::where('shop_id', SHOP_ID)->pluck('name', 'id');
        $variants->phoneCode    = country::select("id", DB::raw('CONCAT("+", phonecode) AS phone_code'))->where('status', 1)->pluck('phone_code', 'id');
        $billing_items          = BillingItem::select('package_id')->where('billing_id',$billing->id)->groupBy('package_id')->get();
        if ($billing) {
            if ($billing->status === 0) {
                $variants->store            = Shop::find(SHOP_ID);
                if (isset($billing->billingaddress->country_id)) {
                    $variants->states       = DB::table('shop_states')->where('country_id', $billing->billingaddress->country_id)->pluck('name', 'id');
                }
                if (isset($billing->billingaddress->state_id)) {
                    $variants->districts    = DB::table('shop_districts')->where('state_id', $billing->billingaddress->state_id)->pluck('name', 'id');
                }
               if ($billing->items && count($billing->items) > 0) {
                    $billing_items_array    = $billing->items->toArray();
                    $package_array          = $billing_items->toArray();
                    $item_type          = $billing_items_array[0]['item_type'];
                    $ids=[];
                    $package_ids=[];
                    foreach($package_array as $row){
                        $package_ids[]=$row['package_id'];
                    }
                    foreach ($billing_items_array as $row) {
                        $ids[] = $row['item_id'];
                    }
                    if ($item_type == 'services') {
                        $service_type       = 1;
                        $item_type          = 'services';
                    }elseif ($item_type == 'memberships') {
                        $service_type       = 3;
                        $item_type          = 'memberships';
                    } 
                     else {
                        $service_type       = 2;
                        $item_type          = 'packages';
                    }
                }
                if ($billing) {
                    $variants->item_ids     = $ids;
                    $variants->package_ids  = $package_ids;
                    $variants->bill_id      = $billing->id;
                    $variants->time_picker  = ($this->time_format === 'h') ? false : true;
                    $variants->time_format  = $this->time_format;
                    $store                  = Shop::find(SHOP_ID);   
                    $variants->phonecode    = Country::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->pluck('phone_code', 'id');   
                    return view($this->viewPath . '.edit-invoice', compact('page', 'billing', 'service_type','billing_items' ,'item_type', 'variants','store'));
                }
            }
            abort(404);
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Billing $billing)
    {
        $data_price=[];
        $current_date = Carbon::now()->toDateString();
        if ($billing) {
            $billed_date                = FunctionHelper::dateToTimeFormat($request->billed_date);
            $checkin_time               = FunctionHelper::dateToTimeFormat($request->checkin_time);
            $checkout_time              = FunctionHelper::dateToTimeFormat($request->checkout_time);
            $billing->customer_id       = $request->customer_id;
            $billing->amount            = $request->grand_total;
            $billing->billed_date       = FunctionHelper::dateToUTC($billed_date, 'Y-m-d H:i:s');
            $validity_from              = FunctionHelper::dateToTimeFormat($request->validity_from);
            $validity_to                = FunctionHelper::dateToTimeFormat($request->validity_to);
            $today = FunctionHelper::dateToUTC($validity_from, 'Y-m-d H:i:s');
                if (is_string($today)) {
                    $today = \Carbon\Carbon::parse($today);
                }
                $validity_to = $today->addDays($request->validity);
            $address                    = BillingAddres::where('bill_id', $billing->id)->where('customer_id', $request->customer_id)->first();
            $billing_address_checkbox   = $request->has('billing_address_checkbox') ? 1 : 0;
            if ($billing_address_checkbox == 0) {
                if ($address) {
                    $address->customer_id           = $request->customer_id;
                    $address->billing_name          = $request->customer_billing_name;
                    $address->country_id            = $request->country_id;
                    $address->state_id              = $request->state_id;
                    $address->district_id           = $request->district_id;
                    $address->pincode               = $request->pincode;
                    $address->gst                   = $request->customer_gst;
                    $address->address               = $request->address;
                    $address->updated_by            = auth()->user()->id;
                    $address->save();
                    $billing->address_type          = 'company';
                } else {
                    $new_address                    = new BillingAddres();
                    $new_address->shop_id           = SHOP_ID;
                    $new_address->bill_id           = $billing->id;
                    $new_address->customer_id       = $request->customer_id;
                    $new_address->billing_name      = $request->customer_billing_name;
                    $new_address->country_id        = $request->country_id;
                    $new_address->state_id          = $request->state_id;
                    $new_address->district_id       = $request->district_id;
                    $new_address->pincode           = $request->pincode;
                    $new_address->gst               = $request->customer_gst;
                    $new_address->address           = $request->address;
                    $new_address->updated_by        = auth()->user()->id;
                    $new_address->save();
                    $billing->address_type      = 'company';
                }
            } else {
                if ($address) {
                    $address->delete();
                    $billing->address_type      = 'customer';
                }
            }
            $billing->save();
            // $old_bill_items                 = BillingItem::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->forceDelete();
            $scheduleCount                  = Schedule::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->count();
            
            $itemsCount = [];
            if ($request->items !== null) {
                foreach ($request->items as $key => $item) {
                    $newArr = array_keys($item);
                    $itemsCount[str_replace(' ', '', $key)] = $newArr[0]; 
                }
            }
            if($request['service_type'] == 2){
                $itemsCount                 = 1;
                if($scheduleCount > 0){
                    $scheduleIds=Schedule::where('billing_id', $request->billing_id)->where('customer_id', $request->customer_id)->groupBy('package_id')->pluck('package_id')->toArray();
                    $diff = array_diff($scheduleIds, $request->bill_item);
                    if($diff > 0){
                        Schedule::where('billing_id', $request->billing_id)
                        ->where('customer_id', $request->customer_id)->whereIn('package_id',$diff)->forceDelete();
                    $billingItems = BillingItem::where('billing_id', $billing->id)->whereIn('package_id',$diff)->forceDelete();

                    }
                    $temp_existing_schedules= $existingSchedules  = Schedule::where('billing_id', $request->billing_id)
                    ->where('customer_id', $request->customer_id)
                    ->whereIn('package_id', $request->bill_item)
                    ->get();
                    
                    if ($existingSchedules->isEmpty()) {
                        Schedule::where('billing_id', $request->billing_id)
                        ->where('customer_id', $request->customer_id)->forceDelete();
                    }                
                    $existingPackageIds = $existingSchedules->pluck('package_id')->toArray(); 
                    foreach ($request->bill_item as $bill_item) {    
                        if (in_array($bill_item, $existingPackageIds)) {   
                            $existingSchedule = $existingSchedules->where('package_id', $bill_item)->first();
                            if ($existingSchedule) {
                                $start_time                 = Carbon::parse($existingSchedule->start); 
                                $items_details = Service::getTimeDetails($existingSchedule->item_id);
                                $total_time = $items_details['total_minutes'] * $itemsCount;
                                $end_time = $start_time->copy()->addMinutes($total_time);
                                $existingSchedule->start = $start_time;
                                $existingSchedule->end = $end_time;
                                $existingSchedule->user_id = $existingSchedule->user_id ?? $temp_existing_schedules[0]->user_id;
                                $existingSchedule->room_id = $existingSchedule->room_id ?? $temp_existing_schedules[0]->room_id;
                                if ($start_time->format('Y-m-d') > Carbon::now()->format('Y-m-d')) {
                                    $existingSchedule->schedule_color = "#FF5733";
                                    $existingSchedule->checked_in = 0;
                                } else {                               
                                    if ($start_time->toDateString()==$current_date) {
                                        $checkedInCustomerCount = Schedule::where('customer_id', $existingSchedule->customer_id)
                                            ->whereDate('start', today())
                                            ->where('checked_in', 1)
                                            ->count();
                    
                                        if ($checkedInCustomerCount > 0) {
                                            $existingSchedule->schedule_color = $existingSchedules[0]->schedule_color;
                                            $existingSchedule->checked_in = $existingSchedules[0]->checked_in;
                                            $request->checked_in = 1;
                                        }
                                    }
                                }
                    
                                if ($billing->payment_status == 1) {
                                    $existingSchedule->schedule_color = "green";
                                }
                    
                                $existingSchedule->save();
                            }
                        } else {  
                            $start_time                 = Carbon::parse($temp_existing_schedules[0]->start);                  
                            $packages = PackageService::where('package_id', $bill_item)->get();            
                            foreach ($packages as $package) {
                                $items_details  = Service::getTimeDetails($package->service_id);
                                $description    = $items_details['description'];
                                $pos            = strpos($description, ' - ');
                                $items_details['description'] = $pos !== false ? substr($description, 0, $pos) : $description; 
                                $total_time = $items_details['total_minutes'] * $itemsCount;
                                $end_time   = $start_time->copy()->addMinutes($total_time);
                    
                                $newSchedule = new Schedule();
                                $newSchedule->name              = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $start_time->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
                                $newSchedule->start             = $start_time;
                                $newSchedule->end               = $end_time;
                                $newSchedule->user_id           = $temp_existing_schedules[0]->user_id;
                                $newSchedule->customer_id       = $billing->customer->id;
                                $newSchedule->billing_id        = $billing->id;
                                $newSchedule->item_id           = $package->service_id;
                                $newSchedule->package_id        = $package->package_id;
                                $newSchedule->item_type         = "packages";
                                $newSchedule->room_id           = $temp_existing_schedules[0]->room_id;
                                $newSchedule->description       = $items_details['description'] . 'Nos: ' . $itemsCount;
                                $newSchedule->total_minutes     = $items_details['total_minutes'];
                                $newSchedule->shop_id           = SHOP_ID;
                                $newSchedule->schedule_color    = "#FF5733";
                                $newSchedule->checked_in        = 0;
                    
                                // Handle schedule_color and checked_in based on conditions
                                if ($start_time->format('Y-m-d') > Carbon::now()->format('Y-m-d')) {
                                    $newSchedule->schedule_color = "#FF5733";
                                    $newSchedule->checked_in = 0;
                                } else {
                                    if ($start_time->toDateString()==$current_date) {
                                        $checkedInCustomerCount = Schedule::where('customer_id', $newSchedule->customer_id)
                                            ->whereDate('start', today())
                                            ->where('checked_in', 1)
                                            ->count();
                    
                                        if ($checkedInCustomerCount > 0) {
                                            $newSchedule->schedule_color = $existingSchedules[0]->schedule_color;
                                            $newSchedule->checked_in = $existingSchedules[0]->checked_in;
                                            $request->checked_in = 1;
                                        }
                                    }
                                }
                    
                                if ($billing->payment_status == 1) {
                                    $newSchedule->schedule_color = "green";
                                }
                    
                                $newSchedule->save();
                            }
                        }
                    }
                }
                $billingItemids = BillingItem::where('billing_id', $billing->id)->groupBy('package_id')->pluck('package_id')->toArray();
                $result = array_diff($billingItemids, $request->bill_item);
                if($result) {
                    $billingItems = BillingItem::where('billing_id', $billing->id)->whereIn('package_id',$result)->forceDelete();               
                }
                $billingItems = BillingItem::where('billing_id', $billing->id)->whereIn('package_id',$request->bill_item)->forceDelete();
                $newItems = BillingItem::updateWithNewItems($request->billing_id, $request->bill_item, $request->all());
                if ($request->checked_in == 1  ) {                    
                    $checkedIn  = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', now())->update(['schedule_color' => ($billing->payment_status == 1) ? "green" : "orange", "checked_in" => 1]);
                }
                else{
                    $checkedOut = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', now())->update(['schedule_color' => ($billing->payment_status == 1) ? "green" :"#FF5733", "checked_in" => 0]);
                }
                $billingItems   = BillingItem::where('billing_id',$billing->id)->with('package')->groupBy('billing_id', 'package_id')->get();
                $totalAmount=0;
                foreach($billingItems as $billingItem){
                    $totalAmount +=$billingItem->package->price;                    
                }
    
                $billing->amount=$totalAmount;
                $billing->save();


                // if ($request->bill_item!== null) {
                //     $schedule_exising=Schedule::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->first();
                //      Schedule::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->whereNotIn('item_id', $request->bill_item)->forceDelete();
                //     foreach ($request->bill_item as $row) {
                //         $packages=PackageService::where('package_id',$row)->get();
                //         foreach($packages as $package){
                //             $item               = new BillingItem();
                //             $item->billing_id   = $billing->id;
                //             $item->customer_id  = $request->customer_id;
                //             $item->item_type    = 'packages';
                //             $item->item_id      = $package->service_id ;
                //             $item->package_id   = $package->package_id;
                //             $item->validity_from= FunctionHelper::dateToUTC($validity_from, 'Y-m-d H:i:s');
                //             $item->validity_to  = $validity_to;
                //             $item->item_count   = $itemsCount[$row] ?? 1;
          
                //             $item_details       = Package::getTimeDetails($package->service_id);
                //             $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                //             $item->save();
                //         }                    
                //     }
                // }
                // $old_bill_items                 = BillingItem::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->whereIn('package_id',$request->bill_item)->forceDelete();
                // $newItems = BillingItem::updateWithNewItems($billing->id, $request->bill_item, $request->all());
               
                
            } elseif($request['service_type'] == 3){           
                if ($request->bill_item!== null) {
                    $old_bill_items                 = BillingItem::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->forceDelete();
                    foreach ($request->bill_item as $row) {                            
                        $item               = new BillingItem();
                        $item->billing_id   = $billing->id;
                        $item->customer_id  = $request->customer_id;
                        $item->item_type    = 'memberships';
                        $item->item_id      = $row;
                        $item->item_count   = isset($itemsCount[$row]) ? $itemsCount[$row] : 1;
                        $item_details       = Membership::where('shop_id',SHOP_ID)->getTimeDetails($row);
                        $item->item_details = $item_details['full_name'];
                        $item->save();
                        $customer           = Customer::find($request->customer_id);
                        $membership         = Membership::find($row);
                       
                        $customer->is_membership_holder = 1;
                        $customer->save();
                        
                        $durationType   = $membership->duration_type;
                        $durationCount  = $membership->duration_in_days;
                        $today          = now();
                        $customer_membership =customerMemberships::where('bill_id',$billing->id)->where('customer_id',$request->customer_id)->first(); 
                                               
                        if( $customer_membership){
                        $customer_membership->customer_id    = $request->customer_id; 
                        $customer_membership->bill_id        = $billing->id;    
                        $customer_membership->membership_id  = $row;                       
                        $customer_membership->start_date     = $today;                 
                        $customer_membership->end_date       = Carbon::parse($customer_membership->start_date)->addUnit($durationType, $durationCount);   
                        $customer_membership->expiry_status  = 0;
                       
                        $customer_membership->save(); 
                        }
                       
                    }
                }
                $billingItems   = BillingItem::where('billing_id',$billing->id)->with('membership')->groupBy('billing_id')->get();
                $totalAmount=0;
                foreach($billingItems as $billingItem){
                    $totalAmount +=$billingItem->membership->price;                    
                }
    
                $billing->amount=$totalAmount;
                $billing->save();
            }else{
                
                $total_amount=0;
                $old_bill_items                 = BillingItem::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->forceDelete();
                if ($request->bill_item!== null) {
                    $schedule_exising=Schedule::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->first();
                     Schedule::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->whereNotIn('item_id', $request->bill_item)->forceDelete();
                   foreach ($request->bill_item as $key=> $row) {                                         
                        $item               = new BillingItem();
                        $item->billing_id   = $billing->id;
                        $item->customer_id  = $request->customer_id;
                        $item->item_type    = 'services';
                        $item->item_id      = $row;
                        $item->item_count   = isset($itemsCount[$row]) ? $itemsCount[$row] : 1;                       
                        $item_details       = Service::getTimeDetails($row);
                        $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                        $data_price[$key]   = Service::getPriceAfterTax($row);  
                        $item->save();
                        if($scheduleCount > 0){
                            $itemsCount=1;
                            $total_time                 = $item_details['total_minutes'];
                            $schedule=Schedule::where('billing_id', $billing->id)->where('customer_id', $request->customer_id)->where('item_id',$row)->first();
                            $start_time                 = $billing->created_at;
                            $total_time *= $itemsCount;
                            $end_time                   = $start_time->copy()->addMinutes($total_time);
                            $formatted_start_time       = $start_time->format('h:i A');
                            $formatted_end_time         = $end_time->format('h:i A'); 
                            if(isset($schedule) && $schedule->item_id==$row){                               
                                $schedule=$schedule;
                            }else{                             
                                $schedule= new Schedule();
                                $schedule->name             = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $formatted_start_time . ' - ' . $formatted_end_time;
                                $schedule->user_id          = $schedule_exising->user_id;
                                $schedule->customer_id      = $billing->customer->id;
                                $schedule->room_id          = $schedule_exising->room_id;
                                $schedule->billing_id       = $schedule_exising->billing_id;
                                $schedule->start            = $schedule_exising->start;
                                $schedule->end              = $schedule_exising->end;
                                if( $start_time->toDateString()==now()->toDateString()){
                                    $checkedInCustomerCount     = Schedule::where('customer_id',$billing->customer_id)->whereDate('start',now())->where('checked_in',1)->get();
                                    if( $checkedInCustomerCount->count() >0){
                                       $schedule->schedule_color   = $checkedInCustomerCount[0]->schedule_color;
                                       $schedule->checked_in       = $checkedInCustomerCount[0]->checked_in;
                                     
                                   }
                                   else{
                                    $schedule->schedule_color   = "#FF5733";
                                    $schedule->checked_in       = 0;
                                    }
                            
                              
                                }else{
                                    $schedule->schedule_color   = "#FF5733";
                                    $schedule->checked_in       = 0;
                                }
                            
                                

                            }
                            // if($schedule){
                                $schedule->item_id          = $row;                                
                                $schedule->payment_status   = $billing->payment_status;
                                $schedule->item_type        = "services";                           
                                $schedule->description      = $item_details['description'] . 'Nos: ';
                                $schedule->total_minutes    = $item_details['total_minutes'];;
                                $schedule->shop_id          = SHOP_ID;
                                $start_time                 = $end_time;
                            
                                if($billing->payment_status== 1){
                                    $schedule->schedule_color   = "green";
                                }
                            
                                $schedule->save();
                            // }
                            
                            }
                
                                            
                    }

                }
                $billingItems   = BillingItem::where('billing_id',$billing->id)->where('item_type','services')->with('item')->get();
                $totalAmount=0;
                foreach($billingItems as $billingItem){
                    $totalAmount +=($billingItem->item->price)*$billingItem->item_count;                    
                }
    
                $billing->amount=$totalAmount;
                $billing->save();
            }
            
            $current='8';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type='bill';
            $comment='Bill Updated';
            $customer=$billing->customer_id;
                FunctionHelper::statusChangeHistory($activity_id=0, $previous, $current,$customer,$schedule,$billing,$comment,$type);
            return redirect($this->route . '/invoice/' . $billing->id);
        }
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */

    public function destroy(Billing $billing)
    {
        if ($billing->address_type == "company")
            $billing_addres = BillingAddres::where('bill_id', $billing->id)->delete();
            $customerPendingItems=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('expiry_status',0)->get();
            foreach($customerPendingItems as $item){
                if($item->deducted_over_paid!=0){
                    $item->over_paid+=$item->deducted_over_paid;
                    $item->deducted_over_paid=0;
                    $item->save();
                }
            }
            $current='9';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type='bill';
            $comment='Bill Deleted';
            $customer=$billing->customer_id;
                FunctionHelper::statusChangeHistory($activity_id=0, $previous, $current,$customer,$schedule,$billing,$comment,$type);
        $billing_items      = BillingItem::where('billing_id', $billing->id)->delete();
        $chedules           = Schedule::where('billing_id', $billing->id)->delete();
        $bill_amount        = BillAmount::where('bill_id', $billing->id)->delete();
        $BillingItemTax     =BillingItemTax::where('bill_id', $billing->id)->delete();
        $billing            = $billing->delete();
        return ['flagError' => false, 'message' => $this->title . " details deleted successfully"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Billing  $billing
     * @return \Illuminate\Http\Response
     */
    public function cancelBill($billing)
    {
       try{
        DB::beginTransaction();
            $billing                = Billing::findOrFail($billing);
            $billing->status        = 2;
            $billing->save();
            $current='9';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type='bill';
            $comment='Bill Cancelled';
            $customer=$billing->customer_id;
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$billing,$comment,$type);
            if($billing){
                Schedule::where('billing_id', $billing->id)->delete();
                BillingItem::where('billing_id', $billing->id)->delete();
                $billing->delete();
            }
        DB::commit();
        return ['flagError' => false, 'message' => " Bill cancelled successfully"];
       }
       catch(\Exception $e){
        DB::rollBack();
        return ['flagError' => true, 'message' => " Internal Server Error"];

       }
    }

    /**
     * Return amount after discount
     *
     * @return \Illuminate\Http\Response
     */
    public function manageDiscount(Request $request)
    {
        $billing_item = BillingItem::with('billing')->findOrFail($request->billing_item_id);
        $price = $billing_item->item->price;
        if ($request->discount_action == 'add') {
            try {
                $validator = $request->validate([
                    'discount_value' => [
                        'required',
                        function ($attribute, $value, $fail) use ($request, $price) {
                            if ($request->discount_type === 'percentage') {                                
                                $discountValue = $price * ($request->discount_value / 100);
                                if ($discountValue > $price) {
                                    throw new \Illuminate\Validation\ValidationException(
                                        validator()->make([], [])
                                    );
                                }
                            } elseif ($request->discount_type === 'amount') {
                                if ($request->discount_value > $price) {
                                    throw new \Illuminate\Validation\ValidationException(
                                        validator()->make([], [])
                                    );
                                }
                            }
                        },
                    ],
                ]);
        
                $billing_item->is_discount_used = 1;
                $billing_item->discount_type = $request->discount_type;
                $billing_item->discount_value = $request->discount_value;
                $billing_item->save();
        
                return response()->json(['flagError' => false]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'flagError' => true,
                    'message' => 'The Discount Amount must not be greater than the total amount.',
                    'error' => $e->errors(),
                ]);
            }
        } else {
                $billing_item->is_discount_used = 0;
                $billing_item->discount_type = null;
                $billing_item->discount_value = null;
                $billing_item->save();
        
                return response()->json(['flagError' => false]);
           
        }
        
    }

    // public function inStoreCreditPayment($billing,$instoreCreditAmount){  
    //     $customerId         = $billing->customer_id;
    //     $instoreAmountPaid  = $instoreCreditAmount;
    //     // $grandTotal         = $request->input('grandTotal');
    //     // $billingId          = $request->input('billingId');
       
    //     $discount           = 0;
    //     $grand_total        = 0;
    //     $packagePrice       = 0;
    //     $billing_items      = array();
    //     $packages           = [];                 
    //     $sub_total              =0;
    //     $sumArray               = array();
    //     $dates                  = [];
    //     $currentDate            = now()->timestamp;
    //     $nearestDate            = null;
    //     $nearestDifference      = PHP_INT_MAX; 
    //     $customerPendingAmounts =CustomerPendingPayment::where('customer_id',$customerId)
    //                     ->where('expiry_status',0)
    //                     ->where('removed',0)
    //                     ->orderBy('validity_to','ASC')->get();  
    //         $totalInstoreAmount= $customerPendingAmounts->sum('over_paid');
    //         $total_due_amount=$customerPendingAmounts->sum('current_due');
    //         $customerMembership=Customer::find($customerId);            
    //         if($totalInstoreAmount > 0 && $instoreAmountPaid > 0){  
    //             $customerPendingAmounts->sortBy(function ($pendingamount) use ($currentDate) {
    //                 return abs(strtotime($pendingamount->validity_to) - $currentDate);
    //             })
    //             ->each(function ($pendingamount) use (&$instoreAmountPaid,$billing) {         
    //                     $instoreAmount = min($instoreAmountPaid, $pendingamount->over_paid);                                                                               
    //                     if ($pendingamount->is_billed != 1) {
    //                         if ($instoreAmount < $pendingamount->over_paid) {             
    //                             $pendingamount->removed = 1;
    //                             $pendingamount->bill_id = $billing->id;
    //                             $pendingamount->deducted_over_paid = $instoreAmount;
    //                             $pendingamount->save();       
    //                                 $newCustomerPending = new CustomerPendingPayment();                                        
    //                                 $newCustomerPending->over_paid          = $pendingamount->over_paid - $instoreAmount;
    //                                 $newCustomerPending->customer_id        = $pendingamount->customer_id;
    //                                 $newCustomerPending->deducted_over_paid = NULL;
    //                                 $newCustomerPending->bill_id            = NULL;
    //                                 $newCustomerPending->parent_id          = $pendingamount->id;
    //                                 $newCustomerPending->validity_from      = $pendingamount->validity_from;
    //                                 $newCustomerPending->validity_to        = $pendingamount->validity_to;
    //                                 $newCustomerPending->gst_id             = $pendingamount->gst_id;
    //                                 $newCustomerPending->expiry_status      = $pendingamount->expiry_status;
    //                                 $newCustomerPending->is_billed          = 1;
    //                                 $newCustomerPending->removed            = 0;
    //                                 $newCustomerPending->is_membership      = $pendingamount->is_membership;
    //                                 $newCustomerPending->membership_id      = $pendingamount->membership_id;
    //                                 $instoreAmountPaid                     -= $instoreAmount;
    //                                 $newCustomerPending->save();
    //                         } else {                             
    //                             $newCustomerPending = new CustomerPendingPayment();                                        
    //                             $newCustomerPending->over_paid          = $pendingamount->over_paid - $instoreAmount;
    //                             $newCustomerPending->customer_id        = $pendingamount->customer_id;
    //                             $newCustomerPending->deducted_over_paid = NULL;
    //                             $newCustomerPending->bill_id            = NULL;
    //                             $newCustomerPending->parent_id          = $pendingamount->instoreCreditParent->id?? $pendingamount->id;;
    //                             $newCustomerPending->validity_from      = $pendingamount->validity_from;
    //                             $newCustomerPending->validity_to        = $pendingamount->validity_to;
    //                             $newCustomerPending->gst_id             = $pendingamount->gst_id;
    //                             $newCustomerPending->expiry_status      = $pendingamount->expiry_status;
    //                             $newCustomerPending->is_billed          = 1;
    //                             $newCustomerPending->removed            = 0;
    //                             $newCustomerPending->is_membership      = $pendingamount->is_membership;
    //                             $newCustomerPending->membership_id      = $pendingamount->membership_id;
    //                             $instoreAmountPaid                     -= $instoreAmount;
    //                             $newCustomerPending->save();
    //                             $pendingamount->removed = 1;
    //                             $pendingamount->bill_id = $billing->id;
    //                             $pendingamount->deducted_over_paid = $instoreAmount;
    //                             $pendingamount->save();                                    
    //                         }
    //                     } else {  
    //                         $instoreAmount = min($instoreAmountPaid, $pendingamount->over_paid);
    //                         $newCustomerPending = new CustomerPendingPayment();
    //                         $newCustomerPending->over_paid          = $pendingamount->over_paid - $instoreAmount;
    //                         $newCustomerPending->customer_id        = $pendingamount->customer_id;
    //                         $newCustomerPending->deducted_over_paid = NULL;
    //                         $newCustomerPending->bill_id            = NULL;
    //                         $newCustomerPending->parent_id          = $pendingamount->id;
    //                         $newCustomerPending->validity_from      = $pendingamount->validity_from;
    //                         $newCustomerPending->validity_to        = $pendingamount->validity_to;
    //                         $newCustomerPending->gst_id             = $pendingamount->gst_id;
    //                         $newCustomerPending->expiry_status      = $pendingamount->expiry_status;
    //                         $newCustomerPending->is_billed          = 1;
    //                         $newCustomerPending->removed            = 0;
    //                         $newCustomerPending->is_membership      = $pendingamount->is_membership;
    //                         $newCustomerPending->membership_id      = $pendingamount->membership_id;
    //                         $instoreAmountPaid                      -= $instoreAmount;
    //                         $newCustomerPending->save();
    //                         $pendingamount->removed = 1;
    //                         $pendingamount->bill_id = $billing->id;
    //                         $pendingamount->deducted_over_paid = $instoreAmount;
    //                         $pendingamount->save();
    //                     }
                    
    //             });
                
    //         }
    // }

    public function inStoreCreditPayment($billing, $instoreCreditAmount)
    {
        $customerId = $billing->customer_id;
        $instoreAmountPaid = $instoreCreditAmount;
        $currentDate = now()->timestamp;    
        $customerPendingAmounts = CustomerPendingPayment::where('customer_id', $customerId)
            ->where('expiry_status', 0)
            ->where('removed', 0)
            ->orderBy('validity_to', 'ASC')
            ->get();
    
        $totalInstoreAmount = $customerPendingAmounts->sum('over_paid');    
        if ($totalInstoreAmount > 0 && $instoreAmountPaid > 0) {
            $customerPendingAmounts->sortBy(function ($pendingamount) use ($currentDate) {
                return abs(strtotime($pendingamount->validity_to) - $currentDate);
            })
            ->each(function ($pendingamount) use (&$instoreAmountPaid, $billing) {
                if ($instoreAmountPaid > 0 && $pendingamount->over_paid > 0) {
                    $instoreAmount = min($instoreAmountPaid, $pendingamount->over_paid);    
                    $pendingamount->update([
                        'removed' => 1,
                        'bill_id' => $billing->id,
                        'deducted_over_paid' => $instoreAmount
                    ]);
    
                    $instoreAmountPaid -= $instoreAmount;
    
                    if ($instoreAmount < $pendingamount->over_paid) {
                        $remainingAmount = $pendingamount->over_paid - $instoreAmount;                        
                        $pendingamount->save();
                        $newCustomerPending = new CustomerPendingPayment();
                        $newCustomerPending->fill($pendingamount->toArray());
                        $newCustomerPending->over_paid = $remainingAmount;
                        $newCustomerPending->removed = 0;
                        $newCustomerPending->bill_id = null;
                        $newCustomerPending->deducted_over_paid = null;
                        $newCustomerPending->amount_before_gst = $remainingAmount; // Ensure amount_before_gst is copied
                        $newCustomerPending->save();
                    }
                }
            });
        }
    }
    
    
    
    // public function inStoreCreditPayment(Request $request)
    // {  
    //     $customerId         = $request->input('customerId');
    //     $finalCreditAmount  = $request->input('finalCreditAmount');
    //     $instoreAmountPaid  = $tempInstoreAmountPaid = $request->input('instoreAmount');
    //     $grandTotal         = $request->input('grandTotal');
    //     $billingId          = $request->input('billingId');
    //     if($request->subTotal <  $instoreAmountPaid){
    //         return ['flagError' => true, 'message' => 'Please re-enter the amount! The in-store amount must be less than the subtotal.'];
    //     }
    //     $billing_package    = BillingItem::where('billing_id',$request->billingId)->where('package_id','!=',NULL)->groupBy('package_id')->get();
    //     $discount           = 0;
    //     $grand_total        = 0;
    //     $packagePrice       = 0;
    //     $billing_items      = array();
    //     $packages            =[];
    //     $billing       = Billing::find($billingId);       
    //     if ($billing->items) {
    //         $billing_items_array    = $billing->items->toArray();
    //         $item_type              = $billing_items_array[0]['item_type'];
    //         $item_ids               = array_column($billing->items->toArray(), 'item_id');
    //         $package_ids               = array_column($billing_package->toArray(), 'package_id');
    //         if ($item_type == 'services') {
    //             $billing_items['item_type'] = 'services';
    //             $billing_items = Service::select('services.*', 'billing_items.id as billingItemsId', 'billing_items.billing_id as billingId', 'billing_items.is_discount_used', 'billing_items.discount_type', 'billing_items.discount_value', 'billing_items.item_details', 'billing_items.item_count','billing_items.customer_id')
    //                 ->join('billing_items', 'billing_items.item_id', '=', 'services.id')
    //                 ->where('services.shop_id', SHOP_ID)
    //                 ->where('billing_items.billing_id', $request->billingId)
    //                 ->whereIn('services.id', $item_ids)
    //                 ->whereNull('billing_items.deleted_at') 
    //                 ->orderBy('services.id', 'desc')
    //                 ->get();
                    
    //         } else {
    //             $billing_items['item_type'] = 'packages';               
    //             $packages=Package::whereIn('id',$package_ids)->get();               
    //             $billing_items = PackageService::
    //             join('packages','packages.id','=','package_service.package_id')
    //             ->join('services','services.id','=','package_service.service_id')
    //             ->join('billing_items', function($join) {
    //                 $join->on('billing_items.item_id', '=', 'services.id')
    //                      ->on('billing_items.package_id', '=', 'packages.id');
    //             })
    //             // ->join('gst_tax_percentages', 'gst_tax_percentages.id', '=', 'services.gst_tax')
    //             ->where('packages.shop_id', SHOP_ID)
    //             ->where('billing_items.billing_id', $request->billingId)
    //             ->whereIn('package_service.package_id',$package_ids)
    //             ->whereIn('package_service.service_id', $item_ids)
    //             ->orderBy('packages.id', 'desc')
    //             // ->distinct()
    //             ->whereNull('billing_items.deleted_at') 
    //             ->get();
    //             // $billing_items = PackageService::
    //             // select('packages.*','services.*','package_service.*','billing_items.*','billing_items.id as billingItemsId','packages.price as packagePrice')
    //             // ->join('packages','packages.id','=','package_service.package_id')
    //             //  ->join('services','services.id','=','package_service.service_id')
    //             //  ->join('billing_items', function($join) {
    //             //      $join->on('billing_items.item_id', '=', 'services.id')
    //             //           ->on('billing_items.package_id', '=', 'packages.id');
    //             //  })
    //             //  // ->join('gst_tax_percentages', 'gst_tax_percentages.id', '=', 'services.gst_tax')
    //             //  ->where('packages.shop_id', SHOP_ID)
    //             //  ->where('billing_items.billing_id', $request->billingId)
    //             //  ->whereIn('package_service.package_id',$package_ids)
    //             //  ->whereIn('package_service.service_id', $item_ids)
    //             //  ->orderBy('packages.id', 'desc')
    //             //  ->whereNull('billing_items.deleted_at') 
    //             //  ->get();
    //         }
    //         $sub_total              =0;
    //         $sumArray               = array();
    //         $dates                  = [];
    //         $currentDate            = now()->timestamp;
    //         $nearestDate            = null;
    //         $nearestDifference      = PHP_INT_MAX; 
    //         $total_no_items         =$billing_items->count();
    //         $customerPendingAmounts =CustomerPendingPayment::where('customer_id',$customerId)
    //                     ->where('expiry_status',0)
    //                     ->where('removed',0)
    //                     ->orderBy('validity_to','ASC')->get();  
    //         $totalInstoreAmount= $customerPendingAmounts->sum('over_paid');
    //         $total_due_amount=$customerPendingAmounts->sum('current_due');
    //         $customerMembership=Customer::find($customerId); 
    //         if($totalInstoreAmount > 0){               
    //             if ($instoreAmountPaid > 0) {
    //                 $customerPendingAmounts->sortBy(function ($pendingamount) use ($currentDate) {
    //                         return abs(strtotime($pendingamount->validity_to) - $currentDate);
    //                     })
    //                     ->each(function ($pendingamount) use (&$instoreAmountPaid, $request) { 
    //                         if ($instoreAmountPaid > 0) {          
    //                             $instoreAmount = min($instoreAmountPaid, $pendingamount->over_paid);                                                        
    //                             if ($pendingamount->is_billed != 1) {
    //                                 if ($instoreAmount < $pendingamount->over_paid) {                                                                    
    //                                     if ($pendingamount->over_paid != 0) {                                                                    
    //                                         $pendingamount->removed = 1;
    //                                         $pendingamount->save();       
    //                                         $newCustomerPending = new CustomerPendingPayment();                                        
    //                                         $newCustomerPending->over_paid          = $pendingamount->over_paid - $instoreAmount;
    //                                         $newCustomerPending->customer_id        = $pendingamount->customer_id;
    //                                         $newCustomerPending->deducted_over_paid = $instoreAmount;
    //                                         $newCustomerPending->bill_id            = $request->billingId;
    //                                         $newCustomerPending->parent_id          = $pendingamount->id;
    //                                         $newCustomerPending->validity_from      = $pendingamount->validity_from;
    //                                         $newCustomerPending->validity_to        = $pendingamount->validity_to;
    //                                         $newCustomerPending->gst_id             = $pendingamount->gst_id;
    //                                         $newCustomerPending->expiry_status      = $pendingamount->expiry_status;
    //                                         $newCustomerPending->is_billed          = 1;
    //                                         $newCustomerPending->removed            = 0;
    //                                         $newCustomerPending->is_membership      = $pendingamount->is_membership;
    //                                         $newCustomerPending->membership_id      = $pendingamount->membership_id;
    //                                         $instoreAmountPaid                     -= $instoreAmount;
    //                                         $newCustomerPending->save();
                                            
    //                                     }
    //                                 } else {                                    
    //                                     if ($pendingamount->over_paid != 0) {
    //                                     $newCustomerPending = new CustomerPendingPayment();                                        
    //                                     $newCustomerPending->over_paid          = $pendingamount->over_paid - $instoreAmount;
    //                                     $newCustomerPending->customer_id        = $pendingamount->customer_id;
    //                                     $newCustomerPending->deducted_over_paid = $instoreAmount;
    //                                     $newCustomerPending->bill_id            = $request->billingId;
    //                                     $newCustomerPending->parent_id          = $pendingamount->instoreCreditParent->id?? $pendingamount->id;;
    //                                     $newCustomerPending->validity_from      = $pendingamount->validity_from;
    //                                     $newCustomerPending->validity_to        = $pendingamount->validity_to;
    //                                     $newCustomerPending->gst_id             = $pendingamount->gst_id;
    //                                     $newCustomerPending->expiry_status      = $pendingamount->expiry_status;
    //                                     $newCustomerPending->is_billed          = 1;
    //                                     $newCustomerPending->removed            = 0;
    //                                     $newCustomerPending->is_membership      = $pendingamount->is_membership;
    //                                     $newCustomerPending->membership_id      = $pendingamount->membership_id;
    //                                     $instoreAmountPaid                     -= $instoreAmount;
    //                                     $newCustomerPending->save();
    //                                     $pendingamount->removed = 1;
    //                                     $pendingamount->save();
    //                                     }                                        
    //                                 }
    //                             } else {           
    //                                 $instoreAmount = min($instoreAmountPaid, $pendingamount->over_paid);
    //                                 if ($pendingamount->over_paid != 0) {  
    //                                     $newCustomerPending = new CustomerPendingPayment();
    //                                     $newCustomerPending->over_paid          = $pendingamount->over_paid - $instoreAmount;
    //                                     $newCustomerPending->customer_id        = $pendingamount->customer_id;
    //                                     $newCustomerPending->deducted_over_paid = $instoreAmount;
    //                                     $newCustomerPending->bill_id            = $request->billingId;
    //                                     // $newCustomerPending->parent_id          = $pendingamount->instoreCreditParent->id?? $pendingamount->id;
    //                                     $newCustomerPending->parent_id          = $pendingamount->id;
    //                                     $newCustomerPending->validity_from      = $pendingamount->validity_from;
    //                                     $newCustomerPending->validity_to        = $pendingamount->validity_to;
    //                                     $newCustomerPending->gst_id             = $pendingamount->gst_id;
    //                                     $newCustomerPending->expiry_status      = $pendingamount->expiry_status;
    //                                     $newCustomerPending->is_billed          = 1;
    //                                     $newCustomerPending->removed            = 0;
    //                                     $newCustomerPending->is_membership      = $pendingamount->is_membership;
    //                                     $newCustomerPending->membership_id      = $pendingamount->membership_id;
    //                                     $instoreAmountPaid                      -= $instoreAmount;
    //                                     $newCustomerPending->save();
    //                                     $pendingamount->removed = 1;
    //                                     $pendingamount->save(); 
    //                                 }
    //                             }
    //                         }
    //                     });
    //             }
    //         }
                
    //         // else{
    //         //     $instoreAmountPaid =0;
    //         //     $tempInstoreAmountPaid=0;
    //         // }
    //         $packagePriceArray=[];
    //         $packagePrice=0;
    //         $totalCount=[];
    //         if($packages){
    //             foreach($packages as $key=> $value){                   
    //                 $packagePriceArray[$key] = [
    //                     'package_id'=>$value->id,
    //                     'package_price' => $value->service_price-$value->price,
    //                 ];
    //             } 
    //         }else{
    //             $total_no_items =$billing_items->count();      
    //         }
    //         if ($item_type == 'services') {     
    //             $instoreAmount          =$tempInstoreAmountPaid/$total_no_items;  
    //         }else{
    //             $instoreAmount          =$tempInstoreAmountPaid;  
    //         }
    //         $discountTotal=0;
    //         foreach ($billing_items as $key => $row) {   
    //             if($packages){
    //                 $totalCount=PackageService::where('package_id',$row->package_id)->count();
    //                 foreach($packagePriceArray as $packageItem){
    //                     if($packageItem['package_id']==$row->package_id){
    //                         $packagePrice =$packageItem['package_price']/$totalCount;
    //                     }
    //                 }  
    //             }                     
    //             $row_sum                            = 0;  
    //             $tax_array                          = TaxHelper::simpleTaxCalculation($row, $discount,$instoreAmount,$packagePrice, $request);
              
    //             if($tax_array['status'] == false){
    //                 return ['flagError' => true, 'message' => "Please Re-enter Credit Amount",  'error' =>"Please Re-enter Credit Amount"];
    //             } 
    //             $billing_items[$key]['tax_array']   = $tax_array;
    //             $sub_total                          = ($sub_total + $billing_items[$key]['tax_array']['total_amount']);
    //             $row_sum                            = $billing_items[$key]['tax_array']['total_amount'];
    //             if ($billing_items[$key]['tax_array']['discount_applied'] == 1) {
    //                 $row_sum                        = $row_sum+$billing_items[$key]['tax_array']['discount_amount'];                    
    //             }
    //             $billing_items[$key]['item_type'] = ($item_type == 'services') ? 'services' : 'packages';
    //             $sumArray[$key] = $row_sum ;
    //         }         
    //         $grand_total += array_sum($sumArray);   
    //     //   if($request->discountAmount > 0){
    //     //     $grand_total-=$request->discountAmount;
    //     //   }
    //     }
    //    if (!empty($billing->customer->pendingDues)) {
    //     $inStoreCreditBalance=0;
    //     $inStoreCredit=0;
    //     $customerDues=0;
    //     $over_paid=0;
    //        foreach($billing->customer->pendingDues as $pendingdue){             
    //             if($pendingdue->removed ==0 && $pendingdue->is_membership==0){
    //                     $over_paid += $pendingdue->over_paid;                   
    //                 // $inStoreCredit         += ($pendingdue->over_paid != '') ? $pendingdue->over_paid : '0.00';
    //                 $customerDues          += ($pendingdue->current_due != '') ? $pendingdue->current_due : '0.00';
                  
    //             }
    //             if($pendingdue->removed ==0 && $pendingdue->bill_id==$request->billingId){
    //                 $inStoreCreditBalance   += ($pendingdue->deducted_over_paid != '') ? $pendingdue->deducted_over_paid : '0.00';
    //            }
    //        }
    //         $inStoreCredit                  =$over_paid;
    //     //     dd($tempInstoreAmountPaid,$grand_total,$inStoreCreditBalance);
    //     // if($tempInstoreAmountPaid!=$grand_total){
    //     //       $grand_total           += $inStoreCreditBalance;
    //     // }
    //    }
     
    //    if($tempInstoreAmountPaid!=$grand_total){
    //         $sub_total  += $customerDues;
    //     }else{
    //         $sub_total+=0;
    //     }
       
    //    if($packages){
    //         $package_price=0;
    //         foreach($packages as $value){
    //             $package_price+=$value->price;
    //         } 
    //         // dd($package_price ,$inStoreCreditBalance,$tempInstoreAmountPaid);
    //         $sub_total = ($package_price -$inStoreCreditBalance) +$customerDues;
    //         // $grand_total           -= $inStoreCreditBalance;
    //     }
        
    //     if($tempInstoreAmountPaid > 0 && !$packages){
    //         $sub_total -=$tempInstoreAmountPaid;
    //     }
        
    //     $customerPendingAmount=0;
    //     if ($item_type != 'packages') {     
    //         $customerPendingAmount=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('removed',0)->where('expiry_status',0)->where('is_membership',1)->sum('over_paid');
    //     }
    //     $total_instore_credit=$customerPendingAmount+$inStoreCredit;
        
    //     $invoice_details = view($this->viewPath . '.invoice-data', compact('billing_items','customerMembership'))->render();
    // return [
    //     'flagError'            => false,
    //     'grand_total'          => $grand_total,
    //     'sub_total'            => $sub_total,
    //     'customerDues'         => $customerDues,
    //     'instoreAmount'        => $instoreAmountPaid,
    //     'inStoreCredit'        => $inStoreCredit,
    //     'instoreCreditBalance' => $inStoreCreditBalance,
    //     'html'                 => $invoice_details,
    //     'billing_items'        => $billing_items,
    //     'customerPendingAmount'=>$customerPendingAmount,
    //     'total_instore_credit'=>$total_instore_credit
    // ];
    
    // }

    public function removeInStoreCreditPayment(Request $request)
    {
        $customerId         = $request->input('customerId');
        $billingId          = $request->input('billingId');
        $discount           = 0;
        $grand_total        = 0;
        $packagePrice       =0;
        $billing_items      = array();
        $package            =[];
        $billing_package    =BillingItem::where('billing_id',$request->billingId)->where('package_id','!=',NULL)->groupBy('package_id')->get();
        $billing            = Billing::find($billingId);
        $instoreAmount      = 0;
        if ($billing->items) {
            $billing_items_array    = $billing->items->toArray();
            $item_type              = $billing_items_array[0]['item_type'];
            $item_ids               = array_column($billing->items->toArray(), 'item_id');
            $package_ids               = array_column($billing_package->toArray(), 'package_id');
            if ($item_type == 'services') {
                $billing_items['item_type'] = 'services';
                $billing_items              = Service::select('services.*', 'billing_items.id as billingItemsId', 'billing_items.billing_id as billingId', 'billing_items.is_discount_used', 'billing_items.discount_type', 'billing_items.discount_value', 'billing_items.item_details', 'billing_items.item_count','billing_items.customer_id')
                    ->join('billing_items', 'billing_items.item_id', '=', 'services.id')->where('services.shop_id', SHOP_ID)->where('billing_items.billing_id', $request->billingId)->whereIn('services.id', $item_ids)
                    ->whereNull('billing_items.deleted_at') 
                    ->orderBy('services.id', 'desc')->get();
            } else {
                $billing_items['item_type'] = 'packages';               
                $package=Package::whereIn('id',$package_ids)->get();               
                $billing_items = PackageService::
                join('packages','packages.id','=','package_service.package_id')
                ->join('services','services.id','=','package_service.service_id')
                ->join('billing_items', function($join) {
                    $join->on('billing_items.item_id', '=', 'services.id')
                         ->on('billing_items.package_id', '=', 'packages.id');
                })
                //->join('gst_tax_percentages', 'gst_tax_percentages.id', '=', 'services.gst_tax')
                ->where('packages.shop_id', SHOP_ID)
                ->where('billing_items.billing_id', $request->billingId)
                ->whereIn('package_service.package_id',$package_ids)
                ->whereIn('package_service.service_id', $item_ids)
                ->orderBy('packages.id', 'desc')
                ->whereNull('billing_items.deleted_at') 
                ->get();
               
            }
           
            $sub_total=0;
            $sumArray = array();
            $dates                  =[];
            $currentDate            = now()->timestamp;
            $nearestDate            = null;
            $nearestDifference      = PHP_INT_MAX; 
            $total_no_items         =$billing_items->count();
            $customerMembership=Customer::find($customerId);
           
            $customerPendingAmounts =CustomerPendingPayment::where('customer_id',$customerId)
            ->where('bill_id',$request->billingId)->where('expiry_status',0)
            // ->where('removed',0)
            ->get();
            if($customerPendingAmounts->count() > 0){
            $continueLoop=true;
            $customerPendingAmounts->sortBy(function ($pendingamount) use ($currentDate,$request ) {
                return abs(strtotime($pendingamount->validity_to) - $currentDate);
            })
            ->each(function ($pendingamount)use($continueLoop,$request) {              
              CustomerPendingPayment::where('id',$pendingamount->parent_id)->update(['removed'=>0]);          
                if($pendingamount->removed!=1){                    
                    if($continueLoop){
                        $pendingamount->over_paid             += $pendingamount->deducted_over_paid;
                        $pendingamount->deducted_over_paid     = 0;
                        $pendingamount->bill_id                = NULL;
                        $pendingamount->parent_id              = NULL;
                        $pendingamount->is_billed              = 0;
                        $pendingamount->removed                = 0;
                        $pendingamount->current_due            = 0;
                        $pendingamount->validity               = $pendingamount->validity;
                        $pendingamount->is_membership          = $pendingamount->is_membership;
                        $pendingamount->save();
                        $continueLoop=false; 
                       
                    }
                }
                $pendingamount->delete();
            });
         }                 
            $packagePriceArray=[];
            $packagePrice=0;
            $totalCount=[];
            if($package){
                foreach($package as $key=> $value){                   
                    $packagePriceArray[$key] = [
                        'package_id'=>$value->id,
                        'package_price' => $value->service_price-$value->price,
                    ];
                } 
            }else{
                $total_no_items =$billing_items->count();      
            }
            if ($item_type == 'services') {
                $instoreAmount=$instoreAmount/$total_no_items;
            }
            else{
                $instoreAmount=$instoreAmount;
            }
            $discountTotal=0;
            foreach ($billing_items as $key => $row) {
                if($package){
                    $totalCount=PackageService::where('package_id',$row->package_id)->count();
                    foreach($packagePriceArray as $packageItem){
                        if($packageItem['package_id']==$row->package_id){
                            $packagePrice =$packageItem['package_price']/$totalCount;
                        }
                    }  
                }
                $row_sum                            = 0;              
                $tax_array                          = TaxHelper::simpleTaxCalculation($row, $discount,$instoreAmount,$packagePrice, $request);
                $billing_items[$key]['tax_array']   = $tax_array;
                $sub_total                          = ($sub_total + $billing_items[$key]['tax_array']['total_amount']);
                $row_sum                            = $billing_items[$key]['tax_array']['total_amount'];  
                if ($billing_items[$key]['tax_array']['discount_applied'] == 1) {
                    // $sub_total                      = ($sub_total - $billing_items[$key]['tax_array']['discount_amount']);
                    // $row_sum                        = $row_sum - $billing_items[$key]['tax_array']['discount_amount'];
                    $discountTotal                 +=$billing_items[$key]['tax_array']['discount_amount'];
                }
                $billing_items[$key]['item_type'] = ($item_type == 'services') ? 'services' : 'packages';
                $sumArray[$key] = $row_sum;
            }
            $grand_total += array_sum($sumArray);
            if ($discountTotal >0) {
                $grand_total +=$discountTotal;
            }
        }  
        $inStoreCreditBalance=0;
        $inStoreCredit=0;
        $customerDues=0;
        $over_paid=0;
        if (!empty($billing->customer->pendingDues)) {
            foreach ($billing->customer->pendingDues as $key => $pendingDue) {
            if($pendingDue->removed ==0 && $pendingDue->is_membership==0 ){
                    $over_paid+= $pendingDue->over_paid;
                
             $customerDues               += ($pendingDue->current_due != '') ? $pendingDue->current_due : '0.00';
            }
            if($pendingDue->removed ==0 && $pendingDue->bill_id==$request->billingId ){
                $inStoreCreditBalance       = ($pendingDue->deducted_over_paid != '') ? $pendingDue->deducted_over_paid : '0.00';
            }
            }
                $inStoreCredit                  =$over_paid;
            // $grand_total                += $customerDues;
            $grand_total += $inStoreCreditBalance ;
        }
        $sub_total+= $customerDues;
        if($package){
            $package_price=0;
            foreach($package as $value){
                $package_price+=$value->price;
            }
            $sub_total =($package_price -$inStoreCreditBalance) + $customerDues;
            $grand_total -= $inStoreCreditBalance ;
        }
            $customerPendingAmount=0;
            if ($item_type != 'packages') {     
                $customerPendingAmount=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('removed',0)->where('expiry_status',0)->where('is_membership',1)->sum('over_paid');
            }
            $total_instore_credit=$customerPendingAmount+$inStoreCredit;
            $invoice_details = view($this->viewPath . '.invoice-data', compact('billing_items','customerMembership'))->render();
    return [
        'flagError'            => false,
        'grand_total'          => $grand_total,
        'sub_total'            => $sub_total,
        'customerDues'         => $customerDues,
        'inStoreCredit'        => $inStoreCredit,
        'instoreCreditBalance' => $inStoreCreditBalance,
        'html'                 => $invoice_details,
        'billing_items'        => $billing_items,
        'over_paid'            => $inStoreCredit,
        'customerPendingAmount'=>$customerPendingAmount,
        'total_instore_credit'=>$total_instore_credit
    ];
       

    }
    public function billOverview(Request $request)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $currentDate = Carbon::today(); 
        //->where('payment_status',1)
        $totalSaleAmount= Billing::where('shop_id',SHOP_ID)->withTrashed()
        ->whereDate('created_at', $currentDate)
        ->sum('amount');
        // $totalSaleAmount=0;
        // foreach ($totalSaleAmounts as $key => $saleamount) {
        //     $totalSaleAmount+=$saleamount->amount;
         
        //      if($saleamount->customerPendingMembership && $saleamount->customerPendingMembership->deducted_over_paid!==0){
        //       $totalSaleAmount-=$saleamount->customerPendingMembership->deducted_over_paid;           
        //      }
             
        //  }
        //  $canceledPaidBills=Billing::where('shop_id',SHOP_ID)->whereIn('payment_status',[2,5,6])
        //     ->whereDate('created_at', $currentDate)->onlyTrashed()
        //  ->sum('actual_amount');
        //  $totalSaleAmount=$totalSaleAmount +$canceledPaidBills;
        $serviceAmount =Billing::where('shop_id',SHOP_ID)->whereIn('payment_status',[1,3,4])->whereDate('created_at', $currentDate
        )->with('items.item')->whereHas('items',function($query){
            $query->where('item_type','services');
        })->sum('actual_amount');
        $totalServiceAmount =Billing::where('shop_id',SHOP_ID)->whereDate('created_at', $currentDate)->whereHas('items',function($query){
            $query->where('item_type','services');
        })->sum('amount');
        $TotalDiscountAmount = Billing::whereDate('created_at', $currentDate)->where('shop_id',SHOP_ID)
            ->with(['items' => function ($query) use($currentDate){
                $query->where('is_discount_used', 1);
            }])
            ->get()
            ->sum(function ($billing) {
                return $billing->items->sum('discount_value');
            });
            
        $packageAmount=Billing::where('shop_id',SHOP_ID)->whereDate('created_at', $currentDate)->whereHas('items',function($query){
            $query->where('item_type','packages');
        })->sum('amount');
        $totalPackageAmount=Billing::where('shop_id',SHOP_ID)->where('payment_status',1)->whereDate('created_at', $currentDate)->whereHas('items',function($query){
            $query->where('item_type','packages');
        })->sum('amount');
        $unpaidAmount=Billing::where('shop_id',SHOP_ID)->where('payment_status',0)->whereDate('created_at', $currentDate)->sum('amount');
        $paidAmount=Billing::where('shop_id',SHOP_ID)->whereDate('created_at', $currentDate)->sum('actual_amount');
        $additionallyPaidAmount  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID)->where('is_membership',0);
        })->sum('amount_before_gst');
        $paidAmount-=$additionallyPaidAmount;
        $total_dues     = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })->where('expiry_status',0)->where('removed',0)->whereDate('created_at', $currentDate)->sum('current_due');
        $total_instore  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('removed',0)->where('is_membership',0)
        // ->whereDate('created_at', $currentDate)
        ->sum('over_paid');
        $total_refund=RefundCash::whereHas('billings',function($query){
            $query->where('shop_id',SHOP_ID);
        })->wherehas('PaymentType',function($query){
            $query->where('name','!=','In-store Credit');
        }) ->whereDate('created_at', $currentDate)->sum('amount');
        $total_canceled_bill_amount=Billing::where('shop_id',SHOP_ID)->onlyTrashed()->where('payment_status','!=',0)->whereDate('created_at', $currentDate)->sum('amount');
        $upiTotalAmount=BillAmount::whereNotIn('payment_type',['Cash','In-store Credit'])->whereDate('created_at', $currentDate)->sum('amount');
        // $cardTotalAmount=BillAmount::where('payment_type','Card Payment')->whereDate('created_at', $currentDate)->sum('amount');
        $cashTotalAmount=BillAmount::where('payment_type','Cash')->whereDate('created_at', $currentDate)->sum('amount');
        $creditTotalAmount=BillAmount::where('payment_type','In-store Credit')->whereDate('created_at', $currentDate)->sum('amount');
        
        $total_instore_credited  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('is_membership',0)
        // ->whereDate('created_at', $currentDate)
        ->sum('over_paid');
        $total_instore_credit_balance  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
            
        }) ->where('expiry_status',0)
        ->where('is_membership',0)
        ->where('removed',0)
        // ->whereDate('created_at', $currentDate)
        ->sum('over_paid');
        $total_instore_credit_used  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
            
        }) ->where('expiry_status',0)->where('is_membership',0)
        ->where('removed',1)
        // ->whereDate('created_at', $currentDate)
        ->sum('deducted_over_paid');


        $total_membership_instore_credited  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('is_membership',1)
        // ->whereDate('created_at', $currentDate)
        ->sum('amount_before_gst');
        $total_membership_instore_credit_used  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('removed',1)->where('is_membership',1)
        // ->whereDate('created_at', $currentDate)
        ->sum('deducted_over_paid');
        $total_membership_instore_credit_balance = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)
        ->where('is_membership',1)
        ->where('removed',0)
        // ->whereDate('created_at', $currentDate)
        ->sum('over_paid');
        //where('shop_id',SHOP_ID)->
        $paymentTypes=PaymentType::whereIn('shop_id',[SHOP_ID,0])->get();
        $customers=Customer::where('shop_id',SHOP_ID)->get();
        $therapists=User::where('shop_id',SHOP_ID)->where('is_active',1)->get();
        $rooms=Room::where('shop_id',SHOP_ID)->where('status',1)->get();
        //Cashbokk Logic
        // $businessCashBalance =  CashbookCron::with('cashbook') ->whereHas('cashbook',function($query){
        //     $query->where('shop_id',SHOP_ID);
        // })->whereDate('created_at', $currentDate)->value('opening_business_cash_balance');
        // $businessCashclosingBalance = CashbookCron::with('cashbook') ->whereHas('cashbook',function($query){
        //     $query->where('shop_id',SHOP_ID);
        // })->whereDate('created_at', $currentDate)->whereNotNull('closing_business_cash_balance')->value('closing_business_cash_balance');
        // $cashbooks = Cashbook::where('cash_book', 1)->with('cashbook') ->whereHas('cashbook',function($query){
        //     $query->where('shop_id',SHOP_ID);
        // })->get();
       
      $data = [
    'page' => $page,  // Assuming this is not a numeric value
    'totalSaleAmount' => (is_numeric($totalSaleAmount) && $totalSaleAmount > 0) ? number_format($totalSaleAmount, 2, '.', '') : null,
    'serviceAmount' => (is_numeric($serviceAmount) && $serviceAmount > 0) ? number_format($serviceAmount, 2, '.', '') : null,
    'packageAmount' => (is_numeric($packageAmount) && $packageAmount > 0) ? number_format($packageAmount, 2, '.', '') : null,
    'paidAmount' => (is_numeric($paidAmount) && $paidAmount > 0) ? number_format($paidAmount, 2, '.', '') : null,
    'unpaidAmount' => (is_numeric($unpaidAmount) && $unpaidAmount > 0) ? number_format($unpaidAmount, 2, '.', '') : null,
    'total_dues' => (is_numeric($total_dues) && $total_dues > 0) ? number_format($total_dues, 2, '.', '') : null,
    'total_instore' => (is_numeric($total_instore) && $total_instore > 0) ? number_format($total_instore, 2, '.', '') : null,
    'total_refund' => (is_numeric($total_refund) && $total_refund > 0) ? number_format($total_refund, 2, '.', '') : null,
    'total_canceled_bill_amount' => (is_numeric($total_canceled_bill_amount) && $total_canceled_bill_amount > 0) ? number_format($total_canceled_bill_amount, 2, '.', '') : null,
    'upiTotalAmount' => (is_numeric($upiTotalAmount) && $upiTotalAmount > 0) ? number_format($upiTotalAmount, 2, '.', '') : null,
    'cashTotalAmount' => (is_numeric($cashTotalAmount) && $cashTotalAmount > 0) ? number_format($cashTotalAmount, 2, '.', '') : null,
    'creditTotalAmount' => (is_numeric($creditTotalAmount) && $creditTotalAmount > 0) ? number_format($creditTotalAmount, 2, '.', '') : null,
    'paymentTypes' => $paymentTypes,  // Keep this unchanged
    'customers' => $customers,  // Keep this unchanged
    'rooms' => $rooms,  // Keep this unchanged
    'therapists' => $therapists,  // Keep this unchanged
    'TotalDiscountAmount' => (is_numeric($TotalDiscountAmount) && $TotalDiscountAmount > 0) ? number_format($TotalDiscountAmount, 2, '.', '') : null,
    'total_instore_credited' => (is_numeric($total_instore_credited) && $total_instore_credited > 0) ? number_format($total_instore_credited, 2, '.', '') : null,
    'total_membership_instore_credited' => (is_numeric($total_membership_instore_credited) && $total_membership_instore_credited > 0) ? number_format($total_membership_instore_credited, 2, '.', '') : null,
    'total_membership_instore_credit_used' => (is_numeric($total_membership_instore_credit_used) && $total_membership_instore_credit_used > 0) ? number_format($total_membership_instore_credit_used, 2, '.', '') : null,
    'totalServiceAmount' => (is_numeric($totalServiceAmount) && $totalServiceAmount > 0) ? number_format($totalServiceAmount, 2, '.', '') : null,
    'totalPackageAmount' => (is_numeric($totalPackageAmount) && $totalPackageAmount > 0) ? number_format($totalPackageAmount, 2, '.', '') : null,
    'total_membership_instore_credit_balance' => (is_numeric($total_membership_instore_credit_balance) && $total_membership_instore_credit_balance > 0) ? number_format($total_membership_instore_credit_balance, 2, '.', '') : null,
    'total_instore_credit_balance' => (is_numeric($total_instore_credit_balance) && $total_instore_credit_balance > 0) ? number_format($total_instore_credit_balance, 2, '.', '') : null,
    'total_instore_credit_used' => (is_numeric($total_instore_credit_used) && $total_instore_credit_used > 0) ? number_format($total_instore_credit_used, 2, '.', '') : null,
];



        
        return view('billing.bill-history', $data);
        
    }
    public function searchByPaymentType(Request $request)
    {
        $totalAmounts=[];
        $cumulative_totals = [];
        $running_total = 0;
        $currentYear = now()->year; 
        $today = Carbon::today();                
        $data = BillingItem::with([
                    'billing' => function ($query) {
                        $query->where('shop_id',SHOP_ID)->withTrashed(); 
                    },
                    'billing.paymentMethods' => function ($query) {
                        $query->withTrashed();
                    }
                ])->withTrashed()
                ->whereHas('billing', function ($query) {
                    // Ensure that only `billing` records with the current `shop_id` are included
                    $query->where('shop_id', SHOP_ID)->withTrashed(); 
                })
                ->where(function ($query) {
                 
                    $query->where(function ($subQuery) {
                        $subQuery->whereHas('item')
                            ->orWhereHas('package')
                            ->orWhereHas('membership');
                    })
                    ->orWhere('item_id', 0); 
                })
             
                ->when($request->has('paymentType') && $request->paymentType != '0', function ($query) use ($request) {
                    $query->whereHas('billing.paymentMethods', function ($subQuery) use ($request) {
                        $subQuery->where('payment_type_id', $request->paymentType);
                    });
                })
                ->when($request->has('therapist_list') && $request->therapist_list != '0', function ($query) use ($request) {
                    $query->whereHas('billing.schedule', function ($subQuery) use ($request) {
                        $subQuery->where('user_id', $request->therapist_list);
                    });
                })
                ->when($request->has('room_list') && $request->room_list != '0', function ($query) use ($request) {
                    $query->whereHas('billing.schedule', function ($subQuery) use ($request) {
                        $subQuery->where('room_id', $request->room_list);
                    });
                })
                ->when($request->has('customer_list') && $request->customer_list != '0', function ($query) use ($request) {
                    $query->whereHas('billing.customer', function ($subQuery) use ($request) {
                        $subQuery->where('id', $request->customer_list);
                    });
                });
            $data=$data->get();
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {              
                static $previousDate = null;
                $currentDate = Carbon::parse($row->created_at)->format('d-m-Y');
                if ($previousDate === $currentDate) {                   
                    return '';
                }
                 $previousDate = $currentDate;
            return $currentDate;
            })
            ->editColumn('invoice', function ($row) {
                if ($row->billing) { // Check if billing is not null
                    if ($row->billing->deleted_at === null) {
                        return '<a href="' . route('billings.show', [$row->billing->id]) . '">' . $row->billing->billing_code . '</a>';
                    } else {
                        $billing = RefundCash::where('bill_id', $row->billing->id)->first();
                        if ($billing) { // Check if billing is not null
                            return '<a href="' . route('cancelBillInvoice', [$billing->id]) . '" style="color:red;">' . $billing->billing_code . '</a>';
                        }
                    }
                }
                return '--';
            })            
            ->editColumn('guest_name', function ($row) {
                $guest_name = '<span class="guest_name">' . ($row->billing?->customer?->name ?? '--');                 
                $status = $row->billing?->customer_type == 0 ? 'Existing' : 'New';
                if ($status == 'New') {
                    $guest_name .= '<span class="guest_name_badge">' . $status . '</span>';
                }
                $guest_name .= '</span>';
                return $guest_name;
            })->addColumn('service', function ($row) {   
                if($row->package_id!=NULL){
                    $service=$row->package->name?? '';
                    $itemName = $row->item->name ?? '';
                    if ($itemName) {
                        $service .= " ($itemName)"; // Append the item name in brackets
                    }
                }else if($row->item_id!=NULL){
                    $service=$row->item->name ?? 'Cancellation Fee' ;
                }elseif($row->item_id==0){
                    if($row->item_type=='rebook'){
                        $service='Cancellation Fee' ;
                    }else if($row->item_type=='instore'){
                        $service='In-store Credit' ;
                    }
                }
                else{
                    $service=$row->membership->name;
                }
                
                return $service?? '-';
            }) ->addColumn('price', function ($row) {    
                if($row->package_id!=NULL){
                    $service_price=$row->package->price;
                }else if($row->package_id==NULL){
                    $service_price=$row->item->price ?? '' ;
                }else{
                    $service_price=$row->membership->price;
                }  
                return $service_price ?? '--';
            })->addColumn('timeIn', function ($row) {
                static $previoustimeIn = null;
                $formattedtimeIn=$row->billing?->schedule?->start ? Carbon::parse($row->billing->schedule->start)->format('h:i:s A') 
                : '--'; 
                if ($previoustimeIn === $formattedtimeIn) {
                    return '--';
                }
                $previoustimeIn = $formattedtimeIn;
                return $formattedtimeIn;       
               
            }) ->addColumn('timeOutBy', function ($row) {
                static $previoustimeOutBy = null;
                $formattedtimeOutBy=$row->billing?->schedule?->end ? Carbon::parse($row->billing->schedule->end)->format('h:i:s A') : '--'; 
                if ($previoustimeOutBy === $formattedtimeOutBy) {
                    return '--';
                }
                $previoustimeOutBy = $formattedtimeOutBy;
                return $formattedtimeOutBy;       
            })
            ->addColumn('discount', function ($row) {
                $discountAmount=0;
                if($row->is_discount_used==1){
                    $discountAmount=$row->discount_value;
                }
                return number_format($discountAmount,2);
                   
            })
            ->addColumn('cancellationfee', function ($row) {
                static $previouscancellationFee = null;
                $actual_amount=RefundCash::where('bill_id',$row->billing_id)->sum('actual_amount');
                $refund_amount=RefundCash::where('bill_id',$row->billing_id)->sum('amount');
                $cancellationFee=$actual_amount-$refund_amount;
                $formattedcancellationFee = number_format($cancellationFee, 2);
                if ($previouscancellationFee === $formattedcancellationFee) {
                    return number_format(0, 2);
                }
                $previouscancellationFee = $formattedcancellationFee;
                return $formattedcancellationFee;                   
            })
            ->addColumn('instoreCredit', function ($row) {
                static $previousInstoreCredit = null;
                if ($row->billing && $row->billing->paymentMethods) {
                    $instoreCredit = $row->billing->paymentMethods->where('payment_type', 'In-store Credit')->sum('amount');
                    $formattedInstoreCredit = number_format($instoreCredit, 2);
                    if ($previousInstoreCredit === $formattedInstoreCredit) {
                        return number_format(0, 2);
                    }
                    $previousInstoreCredit = $formattedInstoreCredit;
                    return $formattedInstoreCredit;
                }
                return '--';
            }) ->addColumn('cash', function ($row) {
                static $previousCash = null;
                if ($row->billing && $row->billing->paymentMethods) {
                    $cashAmount = $row->billing->paymentMethods->where('payment_type', 'Cash')->sum('amount');
                    $formattedCash = number_format($cashAmount, 2);
                    if ($previousCash === $formattedCash) {
                        return number_format(0, 2);
                    }
                    $previousCash = $formattedCash;
                    return $formattedCash;
                }
                return '--'; 
            })->addColumn('pay_cardOnline', function ($row) {
                static $previousOnline = null;
                if ($row->billing && $row->billing->paymentMethods) {
                    $onlineAmount = $row->billing->paymentMethods->whereNotIn('payment_type', ['Cash', 'In-store Credit'])->sum('amount');
                    $formattedOnline = number_format($onlineAmount, 2);
                    if ($previousOnline === $formattedOnline) {
                        return number_format(0, 2);
                    }
                    $previousOnline = $formattedOnline;
                    return $formattedOnline;
                }
                return '--'; // Return a default value if billing is null
            })->addColumn('totalPerClient', function ($row) {
                static $processedCustomerDates = [];
                if ($row->billing) {
                    $currentCustomerId = $row->billing->customer_id;
                    $currentBillingDate = $row->billing->created_at->format('Y-m-d');
                    $customerDateKey = $currentCustomerId . '_' . $currentBillingDate;
                    if (!isset($processedCustomerDates[$customerDateKey])) {
                        $totalAmounts = Billing::where('customer_id', $currentCustomerId)
                            ->whereDate('created_at', $currentBillingDate)
                            ->with('paymentMethods')
                            ->where('deleted_at',NULL)
                            ->get()
                            ->sum(function ($billing) {
                                return $billing->paymentMethods ->where('deleted_at',NULL)->where('payment_type', '!=', 'In-store Credit')->sum('amount');
                            });
                        $totalPerClient = number_format($totalAmounts, 2);
                        $processedCustomerDates[$customerDateKey] = $totalPerClient;
                        return $totalPerClient;
                    }
                }
                return number_format(0, 2); // Return 0 if already processed or billing is null
            })
            ->addColumn('total', function ($row) {
                static $running_total = 0.00;
                static $processedBillingIds = [];
                if ($row->billing && $row->billing->deleted_at === null && !in_array($row->billing_id, $processedBillingIds)) {
                    $currentTotal = $row->billing->paymentMethods ->where('deleted_at',NULL)->where('payment_type', '!=', 'In-store Credit')->sum('amount');
                    $running_total += $currentTotal;
                    $processedBillingIds[] = $row->billing_id;
                    return number_format($running_total, 2);
                }
                return number_format(0, 2); // Return 0 if already processed or billing is null
            })->addColumn('min', function ($row) {   
                $start = $row->billing?->schedule?->start ? Carbon::parse($row->billing->schedule->start) : null;
                $end = $row->billing?->schedule?->end ? Carbon::parse($row->billing->schedule->end) : null;
                $differenceFormatted = '';                
                if ($start && $end) {
                    $differenceInSeconds = $start->diffInSeconds($end);
                    $leadTimeBefore = $row->item->leadBefore->name ?? '';
                    $leadTimeAfter = $row->item->leadAfter->name ?? '';  
                    $minutes1 = (int) filter_var($leadTimeBefore, FILTER_SANITIZE_NUMBER_INT);
                    $minutes2 = (int) filter_var($leadTimeAfter, FILTER_SANITIZE_NUMBER_INT); 
                    $leadTimeInSeconds = ($minutes1 + $minutes2) * 60;     
                    $adjustedTimeInSeconds = $differenceInSeconds - $leadTimeInSeconds;    
                    $differenceFormatted = gmdate('H:i:s', max(0, $adjustedTimeInSeconds));
                }                         
                return $differenceFormatted ? $differenceFormatted : '--';
            })->addColumn('therapist', function ($row) {     
                static $previoustherapist = null;
                $formattedtherapist=$row->billing?->schedule?->user->name ?? '--';
                if ($previoustherapist === $formattedtherapist) {
                    return '--';
                }
                $previoustherapist = $formattedtherapist;
                return $formattedtherapist;         
               
            })->addColumn('roomNo', function ($row) {  
                static $previousRoom = null;
                $formattedRoom=$row->billing?->schedule?->room->name ?? '--';;
                if ($previousRoom === $formattedRoom) {
                    return '--';
                }
                $previousRoom = $formattedRoom;
                return $formattedRoom;   
               
            })->addColumn('gender', function ($row) {
                // Safely check for 'customer' and 'gender'
                $gender = $row->billing?->customer?->gender;
                return $gender == 2 ? 'F' : ($gender == 1 ? 'M' : 'Other');
            })
            ->rawColumns(['guest_name','invoice'])  // Ensure HTML is not escaped
            ->make(true);
                      
        return response()->json();
    }
    public function cashbookList(Request $request)
    {        
        $startDate = null;
        $endDate = null;
        $previousClosingBalance = 0;
        $dateRange = explode(' - ', $request->input('cashbook_date_range'));

        if (count($dateRange) === 2) {
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dateRange[0]));
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dateRange[1]));
        }
        $today = Carbon::today();   
        $businessCashBalance = CashbookCron::with('cashbook')
        ->whereHas('cashbook',function($query){
            $query->where('shop_id',SHOP_ID);
        })
        ->whereDate('created_at', $today)
        ->whereNotNull('opening_business_cash_balance')
        ->value('opening_business_cash_balance');
    
        $yesterday = Carbon::yesterday()->toDateString();
        $businessCashclosingBalance = CashbookCron::with('cashbook')
        ->whereHas('cashbook',function($query){
            $query->where('shop_id',SHOP_ID);
        })
                ->whereDate('created_at', $yesterday)
                ->whereNotNull('closing_business_cash_balance')
                ->value('closing_business_cash_balance');
        $cashbooks = Cashbook::where('cash_book', 1)->where('shop_id',SHOP_ID)->with('cashbook');
        
        // Apply date filters if available
        // if ($startDate && $endDate) {
        //     $cashbooks = $cashbooks->whereBetween('created_at', [$startDate, $endDate]);
        //     $businessCashBalance = CashbookCron::with('cashbook')
        //         ->whereBetween('created_at', [$startDate, $endDate])
        //         ->whereNotNull('opening_business_cash_balance')
        //         ->value('opening_business_cash_balance');
        
        //     $businessCashclosingBalance = CashbookCron::with('cashbook')
        //         ->whereBetween('created_at', [$startDate, $endDate])
        //         ->whereNotNull('closing_business_cash_balance')
        //         ->value('closing_business_cash_balance');
        // }
        if ($startDate && $endDate) {
            if ($startDate == $endDate) {
                $cashbooks = $cashbooks->whereDate('created_at', '=', $startDate);                
                $businessCashBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereDate('created_at', '=', $startDate)
                    ->whereNotNull('opening_business_cash_balance')
                    ->value('opening_business_cash_balance');        
                $businessCashclosingBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereDate('created_at', '=', $startDate)
                    ->whereNotNull('closing_business_cash_balance')
                    ->value('closing_business_cash_balance');
            } else {
                $cashbooks = $cashbooks->whereBetween('created_at', [$startDate, $endDate]);        
                $businessCashBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('opening_business_cash_balance')
                    ->value('opening_business_cash_balance');
        
                $businessCashclosingBalance = CashbookCron::with('cashbook')
                 ->whereHas('cashbook',function($query){
            $query->where('shop_id',SHOP_ID);
        })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('closing_business_cash_balance')
                    ->value('closing_business_cash_balance');
            }
        }        
        
        if($request->cashbook_transaction_type!=0){
            $cashbooks = $cashbooks->where('transaction',$request->cashbook_transaction_type);
        }        
        $cashbooks = $cashbooks->get();
        
        $previousDate = null; // Track the last processed date
        $closingBalance = 0; // Initialize the closing balance
        $formattedData = []; // Array to hold formatted rows
        foreach ($cashbooks as $key => $cashbookList) {
            $currentDate = Carbon::parse($cashbookList->created_at)->format('d-m-Y');
            if ($previousDate !== $currentDate) {
                if ($previousDate !== null) {
                    $formattedData[] = [
                        'date'              => "<span style='color: blue;'>Date: $previousDate | Closing Balance: " . number_format($closingBalance, 2)."</span>",
                        'transaction_type'  => '',
                        // 'transaction_amount' => '',
                        'balance_amount'    => '',
                        'debit'             => '',
                        'credit'            => '',
                        'narration'         => '',
                        'closing_balance'   => ''
                    ];
                }
                $openingBalance = 0.0;
                if ($cashbookList->cashbook->isNotEmpty()) {
                    foreach ($cashbookList->cashbook as $index => $cashbalance) {
                        if ($cashbalance->opening_business_cash_balance !== null) {
                            $openingBalance = $cashbalance->opening_business_cash_balance;
                            break; 
                        }
                    }
                }
                if ($openingBalance === 0.0) {
                    $openingBalance = $closingBalance;
                }
                $formattedData[] = [
                    'date'              => "<span style='color: green;'>Date: $currentDate | Opening Balance: " . number_format($openingBalance, 2)."</span>",
                    'transaction_type'  => '',
                    // 'transaction_amount' => '',
                    'balance_amount'    => '',
                    'debit'             => '',
                    'credit'            => '',
                    'narration'         => '',
                    'closing_balance'   => ''
                ];
                $previousDate = $currentDate;
            }
            $closingBalance = $cashbookList->balance_amount;
            $formattedData[] = [
                'date' => $currentDate,
                'transaction_type' => $cashbookList->cash_book == 1 ? 'Business Cash' : 'Petty Cash',
                // 'transaction_amount' => number_format($cashbookList->transaction_amount ?? 0, 2),
                'balance_amount' => number_format($cashbookList->balance_amount ?? 0, 2),
                'debit' => $cashbookList->transaction == 1 ? "<span style='color: green;'>".number_format($cashbookList->transaction_amount ?? 0, 2)."</span>":'',
                'credit'=> $cashbookList->transaction == 2 ? "<span style='color: orange;'>".number_format($cashbookList->transaction_amount ?? 0, 2)."</span>":'',
                'narration' => $cashbookList->message ?? '--',
                'closing_balance' => '' // Leave closing_balance empty for regular transactions
            ];
        }
        if ($previousDate !== null) {
            $formattedData[] = [
                'date'              => "<span style='color: blue;'>Date: $previousDate | Closing Balance: " . number_format($closingBalance, 2)."</span>",
                'transaction_type'  => '',
                // 'transaction_amount' => '',
                'balance_amount'    => '',
                'debit'             => '',
                'credit'            => '',
                'narration'         => '',
                'closing_balance'   => ''
            ];
        }
        return Datatables::of($formattedData)
            ->addIndexColumn()
            ->rawColumns(['date','debit','credit'])
            ->with([
                'businessCashBalance' => $businessCashBalance ?? 0, // Last balance if needed
                'businessCashclosingBalance' => $businessCashclosingBalance
            ])
            ->make(true);
        
            
            return response()->json(['payments' => $payments]);
    }
    
    public function pettyCashbookList(Request $request)
    {        
        $startDate = null;
        $endDate = null;
        $previousClosingBalance = 0;
        $dateRange = explode(' - ', $request->input('petty_date_range'));

        if (count($dateRange) === 2) {
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dateRange[0]));
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dateRange[1]));
        }
        $today = Carbon::today();   
        $pettyCashBalance = CashbookCron::with('cashbook')
                                            ->whereHas('cashbook',function($query){
                                                $query->where('shop_id',SHOP_ID);
                                            })
                                            ->whereDate('created_at', $today)
                                            ->whereNotNull('opening_petty_cash_balance')
                                            ->value('opening_petty_cash_balance');
        
        $yesterday = Carbon::yesterday()->toDateString();
        $pettyCashCloseBalance = CashbookCron::with('cashbook')
                                                ->whereHas('cashbook',function($query){
                                                    $query->where('shop_id',SHOP_ID);
                                                })
                                                ->whereDate('created_at', $yesterday)
                                                ->whereNotNull('closing_petty_cash_balance')
                                                ->value('closing_petty_cash_balance');
        $cashbooks = Cashbook::where('cash_book', 2)->where('shop_id',SHOP_ID)->with('cashbook');
        if ($startDate && $endDate) {
            if ($startDate == $endDate) {
                $cashbooks = $cashbooks->whereDate('created_at', '=', $startDate);                
                $pettyCashBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereDate('created_at', '=', $startDate)
                    ->whereNotNull('opening_petty_cash_balance')
                    ->value('opening_petty_cash_balance');
        
                $pettyCashCloseBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereDate('created_at', '=', $startDate)
                    ->whereNotNull('closing_petty_cash_balance')
                    ->value('closing_petty_cash_balance');
            } else {                
                $cashbooks = $cashbooks->whereBetween('created_at', [$startDate, $endDate]);        
                $pettyCashBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('opening_petty_cash_balance')
                    ->value('opening_petty_cash_balance');
        
                $pettyCashCloseBalance = CashbookCron::with('cashbook')
                ->whereHas('cashbook',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('closing_petty_cash_balance')
                    ->value('closing_petty_cash_balance');
            }
        }
        
        if($request->petty_transaction_type!=0){
            $cashbooks = $cashbooks->where('transaction',$request->petty_transaction_type);
        }
        
        $cashbooks = $cashbooks->get();        
        $previousDate = null; 
        $closingBalance = 0;
        $formattedData = []; 
        foreach ($cashbooks as $key => $cashbookList) {
            $currentDate = Carbon::parse($cashbookList->created_at)->format('d-m-Y');
            if ($previousDate !== $currentDate) {
                if ($previousDate !== null) {
                    $formattedData[] = [
                        'date'                  => "<span style='color: blue;'>Date: $previousDate | Closing Balance: " . number_format($closingBalance, 2)."</span>",
                        'transaction_type'      => '',
                        // 'transaction_amount' => '',
                        'balance_amount'        => '',
                        'debit'                 => '',
                        'credit'                => '',
                        'narration'             => '',
                        'closing_balance'       => ''
                    ];
                }
                $openingBalance = 0.0;
                if ($cashbookList->cashbook->isNotEmpty()) {
                    foreach ($cashbookList->cashbook as $index => $cashbalance) {
                        if ($cashbalance->opening_petty_cash_balance !== null) {
                            $openingBalance = $cashbalance->opening_petty_cash_balance;
                            break; 
                        }
                    }
                }
                if ($openingBalance === 0.0) {
                    $openingBalance = $closingBalance; 
                }
                $formattedData[] = [
                    'date'                      => "<span style='color: green;'>Date: $currentDate | Opening Balance: " . number_format($openingBalance, 2)."</span>",
                    'transaction_type'          => '',
                    // 'transaction_amount'     => '',
                    'balance_amount'            => '',
                    'debit'                     => '',
                    'credit'                    => '',
                    'narration'                 => '',
                    'closing_balance'           => ''
                ];
                $previousDate = $currentDate;
            }
        
            $closingBalance = $cashbookList->balance_amount;
            $formattedData[] = [
                'date' => $currentDate,
                'transaction_type' => $cashbookList->cash_book == 1 ? 'Business Cash' : 'Petty Cash',
                // 'transaction_amount' => number_format($cashbookList->transaction_amount ?? 0, 2),
                'balance_amount' => number_format($cashbookList->balance_amount ?? 0, 2),
                // 'cash_from' => $cashbookList->transaction == 1 ? "<span style='color: green;'>Credit</span>" : "<span style='color: orange;'>Debit</span>",
                'debit' => $cashbookList->transaction == 1 ? "<span style='color: green;'>".number_format($cashbookList->transaction_amount ?? 0, 2)."</span>" : "",
                'credit' =>$cashbookList->transaction == 2 ? "<span style='color: orange;'>".number_format($cashbookList->transaction_amount ?? 0, 2)."</span>" : "",
                'narration' => $cashbookList->message ?? '--',
                'closing_balance' => ''
            ];
        }
        if ($previousDate !== null) {
            $formattedData[] = [
                'date'                  => "<span style='color: blue;'>Date: $previousDate | Closing Balance: " . number_format($closingBalance, 2)."</span>",
                'transaction_type'      => '',
                // 'transaction_amount' => '',
                'balance_amount'        => '',
                'debit'                 => '',
                'credit'                => '',
                'narration'             => '',
                'closing_balance'       => ''
            ];
        }        
        return Datatables::of($formattedData)
            ->addIndexColumn()
            ->with([
                'pettyCashBalance' => $pettyCashBalance ?? 0,
                'pettyCashCloseBalance' => $pettyCashCloseBalance
            ])
            ->rawColumns(['date','debit','credit'])
            ->make(true);    
            return response()->json(['payments' => $payments]);
    }
    public function cancelBillPayment(Request $request) {
        $billing            = Billing::find($request->id);
        if($billing){
            $refund_obj=new RefundCash();
            $refund_obj->customer_id    =$billing->customer_id;
            $refund_obj->bill_id        =$billing->id;
            $refund_obj->actual_amount  =$billing->actual_amount;
            $refund_obj->save();
            if ($billing->address_type == "company")
            $billing_addres = BillingAddres::where('bill_id', $billing->id)->delete();
            $customerPendingItems=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('expiry_status',0)->get();
            foreach($customerPendingItems as $item){
                if($item->deducted_over_paid!=0){
                    $item->over_paid+=$item->deducted_over_paid;
                    $item->deducted_over_paid=0;
                    $item->save();
                }
            }
            $billing_items      =BillingItem::where('billing_id', $billing->id)->delete();
            $chedules           =Schedule::where('billing_id', $billing->id)->delete();
            $bill_amount        =BillAmount::where('bill_id', $billing->id)->delete();
            $BillingItemTax     =BillingItemTax::where('bill_id', $billing->id)->delete();
            if($billing->payment_status!=0){
                $billing->payment_status=2;
                $billing->save();
            }
            $current='9';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type='bill';
            $comment='Bill Cancelled';
            $customer=$billing->customer_id;
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$billing,$comment,$type);
            $billing            = $billing->delete();
            return ['flagError' => false, 'message' => $this->title . "Bill Canceld Successfully"];
        }else{
            return ['flagError' => true, 'message' => $this->title . "No Data Found"];
        }
        
    }
    public function refundBill(Request $request) {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $variants->paymentTypes    = PaymentType::get();
        $data=[
            'page'=>$page,
            'variants'=>$variants
        ];
        if($request->ajax()){    
            $details = RefundCash::select(
                'refund_cashes.id as id',
                'refund_cashes.bill_id as bill_id',
                'refund_cashes.payment_type',
                'refund_cashes.billing_code',
                'refund_cashes.item_id',
                DB::raw('SUM(refund_cashes.amount) AS total_refund_amount'), // Aggregated amount
                'billings.billed_date',
                'billings.customer_id',
                'billings.amount AS bill_amount',
                'billings.payment_status',
                DB::raw('GROUP_CONCAT(DISTINCT payment_types.name ORDER BY payment_types.name ASC SEPARATOR ", ") AS refund_type'),
                DB::raw('SUM(DISTINCT refund_cashes.item_id) AS distinct_items_count'),
                DB::raw('(
                    SELECT SUM(actual_amount)
                    FROM refund_cashes AS rc
                    WHERE rc.bill_id = refund_cashes.bill_id
                ) AS total_bill_amount')
            )
            ->with(['customer', 'billings' => function ($query) {
                $query->withTrashed();
            }])
            ->leftJoin('payment_types', 'payment_types.id', '=', 'refund_cashes.payment_type')
            ->join('billings', 'refund_cashes.bill_id', '=', 'billings.id')
            ->whereHas('billings', function ($query) {
                $query->onlyTrashed();
            })
            ->groupBy(
                'refund_cashes.bill_id',
                'refund_cashes.id',               // Add these fields to GROUP BY
                'refund_cashes.payment_type',
                'refund_cashes.billing_code',
                'refund_cashes.item_id',
                'billings.billed_date',
                'billings.customer_id',
                'billings.amount',
                'billings.payment_status'
            )
            ->orderBy('refund_cashes.id', 'DESC')
            ->get();
        return Datatables::of($details)
            ->addIndexColumn()
            ->editColumn('billing_code', function ($detail) {
                return '<a href="' . route('cancelBillInvoice', $detail->id) . '" class="invoice-action-edit">' . $detail->billing_code ?? '' . '</a>';
            })
            ->editColumn('refund_type', function ($detail) {
                return $detail->refund_type ?? '';
            })
            ->editColumn('customer_id', function ($detail) {
                $customer = $detail->customer ? '<a href="' . url('customers/' . $detail->customer->id) . '" >' . $detail->customer->name . '</a>' : '';
                return $customer;
            })
            ->editColumn('billed_date', function ($detail) {
                return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-Y');
            })
            ->editColumn('status', function ($detail) {
                if ($detail->payment_status == 0) {
                    return '<span class="chip lighten-5 dark blue-text">Open</span>';
                } elseif ($detail->payment_status == 1) {
                    return '<span class="chip lighten-5 dark green green-text">Completed</span>';
                } else {
                    return '<span class="chip lighten-5 dark red-text">Cancelled</span>';
                }
            })
            ->editColumn('payment_status', function ($detail) {
                switch ($detail->payment_status) {
                    case 0:
                        return '<span class="chip lighten-5 red red-text">UNPAID</span>';
                    case 1:
                        return '<span class="chip lighten-5 green green-text">PAID</span>';
                    case 2:
                        return '<span class="chip lighten-5 orange orange-text">CANCELLED</span>';
                    case 3:
                        return '<span class="chip lighten-5 red red-text">PARTIALLY PAID</span>';
                    case 5:
                        return '<span class="chip lighten-5 cyan cyan-text">REFUNDED</span>';
                    case 6:
                        return '<span class="chip lighten-5 cyan cyan-text">PARTIALLY REFUNDED</span>';
                    default:
                        return '<span class="chip lighten-5 blue blue-text">ADDITIONALLY PAID</span>';
                }
            })
            ->addColumn('updated_date', function ($detail) {
                return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-y ' . $this->time_format . ':i a');
            })
            ->addColumn('actual_amount', function ($detail) {
                return $detail->total_bill_amount ?? '0.00';
            })
            ->addColumn('amount', function ($detail) {
                return $detail->bill_amount ?? '0.00';
            })
            ->addColumn('refund_amount', function ($detail) {
                return number_format($detail->total_refund_amount, 2);
            })
            ->addColumn('cancellation', function ($detail) {
                $duePayment=CustomerPendingPayment::where('bill_id',$detail->bill_id)->where('current_due','>',0)->where('deducted_over_paid','>',0)->where('removed',1)->first();
                $duePaymentDue=$duePayment->current_due ?? 0;
                $cancellation=$detail->total_bill_amount-$detail->total_refund_amount-$duePaymentDue;
                return number_format($cancellation, 2);
            })
            // ->addColumn('action', function ($detail) {
            //     if ($detail->payment_status != '5') {
            //         $action = '<div class="invoice-action">';
            //         $action .= ' <a href="javascript:void(0);" id="' . $detail->id . '" onclick="billrefund(this.id)" class=" mr-2" title="Cash Refund"><i class="material-icons"  style="font-size:34px;">assignment_return</i> </a>';
            //         $action .= '</div>';
        
            //         return $action;
            //     }
            // })
            ->removeColumn('id', 'customer')
            ->escapeColumns([])
            ->make(true);
              
            }
        return view('billing.refund_bill_list',$data); 
    }
    public function refundBillPayment(Request $request)
    { 
        $rules = [
            'bill_id' => 'required|exists:billings,id',
            'refund_amount' => 'required', 
            'comment' => 'required',
        ];
        $messages = [
            'bill_id.required' => 'Bill ID is required.',
            'bill_id.exists' => 'Invalid Bill ID.',
            'refund_amount.required' => 'Amount is required.',
            'refund_amount.numeric' => 'Amount must be a number.',
            'refund_amount.min' => 'Amount must be at least :min.',
            'comment.required' => 'Comment is required.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return ['flagError' => true, 'message' => $validator->errors()->first()];
        }
        try {
            DB::beginTransaction();    
            $billing            = Billing::find($request->bill_id);            
            $instorePayments    = CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('over_paid','>',0)->get();
            $instoreDuePayments = CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('removed',0)->get();
            $balanceDueAmount   = $instoreDuePayments->sum('current_due'); 
            $instoreUsed        = $instorePayments->sum('deducted_over_paid');  
            $deducted_due       = $instoreDuePayments->where('parent_id','!=',NULL)->where('child_id','!=',NULL)->sum('deducted_over_paid');            
            $instoreRefunded    = $instorePayments->sum('over_paid');  
            
            $instoreUsed=$instoreUsed >0? $instoreUsed:0;
            $cancelPackage = $request->cancel_package ?: [];
            $cancelService = $request->cancel_service ?: [];   
            $totalDiscount=0;                    
            if (empty($cancelService) && empty($cancelPackage)) {   
                if($request->schedule_package_id!=NULL){
                    $servicePrice        = Package::where('id',$request->schedule_package_id)->sum('price');
                    $cancelPackage=[$request->schedule_package_id];
                }else{      
                    $totalDiscount       = BillingItem::where('billing_id',$billing->id)->where('item_id',$request->service_item_id)->where('customer_id',$billing->customer_id)->where('item_type','services')->sum('discount_value'); 
                    $servicePrice        = Service::where('id',$request->service_item_id)->sum('price');
                    if($totalDiscount>0){
                        $servicePrice-=$totalDiscount;
                    }
                    $cancelService=[$request->service_item_id];
                }
            }else{
                if (!empty($cancelService)) {
                    $totalDiscount           = BillingItem::where('billing_id',$billing->id)->whereIn('item_id',$request->cancel_service)->where('customer_id',$billing->customer_id)->where('item_type','services')->sum('discount_value');  
                    $servicePrice            = Service::whereIn('id',$request->cancel_service)->sum('price');   
                    if($totalDiscount>0){
                        $servicePrice-=$totalDiscount;
                    }                 
                }elseif (!empty($cancelPackage)) {
                    $servicePrice = Package::whereIn('id', $cancelPackage)->sum('price');                   
                }
            }  
            
            $schedules = Schedule::where('billing_id', $billing->id)->where(function ($query) use ($cancelPackage, $cancelService) {
                if (!empty($cancelPackage)) {
                    $query->whereIn('package_id', $cancelPackage);
                }
                if (!empty($cancelService)) {
                    $query->orWhereIn('item_id', $cancelService);
                }
            })->get();  
            if (!$billing) {
                return ['flagError' => true, 'message' => 'Billing record not found.'];
            }
            if (array_sum($request->refund_amount) >  $servicePrice) {
                return ['flagError' => true, 'message' => 'Refund amount cannot exceed Service Price.'];
            }
            $refundAmounts = json_decode($request->refundAmounts, true); 
            $instoreRefundAmount = 0;
            $cashRefundAmount=0;
            $otherRefundAmounts = 0;    
            $containsOnlyInstore = true;        
            foreach ($refundAmounts as $refundAmount) {
                if ($refundAmount['name'] === 'In-store Credit') {
                    $instoreRefundAmount += (int)$refundAmount['amount'];
                }else if($refundAmount['name'] === 'Cash'){
                    $cashRefundAmount +=(int)$refundAmount['amount'];
                    $containsOnlyInstore = false;
                } else {
                    $otherRefundAmounts+=(int)$refundAmount['amount'];
                    $containsOnlyInstore = false;
                }
            }    
            $billAmounts=BillAmount::where('bill_id',$billing->id)->where('parent_bill_id',NULL)->get();            
            $billAmountsMap = [];
            $reundAmountMap=[];
            foreach ($billAmounts as $billAmount) {
                if($billAmount->payment_type!="In-store Credit"){
                    $billAmountsMap[$billAmount->payment_type] = $billAmount->amount;
                }
            }
            foreach ($refundAmounts as $refundAmount) {
                if($refundAmount['amount']!='' && $refundAmount['name']!="In-store Credit"){
                    $reundAmountMap[$refundAmount['name']]=(int) $refundAmount['amount'];
                }
            }
            
            if(in_array($billing->payment_status,[1,4,3]) && array_sum($reundAmountMap) >array_sum($billAmountsMap)){
                if($billing->payment_status!=3 && $billing->actual_amount!=0 ){
                return [
                                'flagError' => true,
                                'message' => 'Refund only allowed using In-store Credit.!',
                        ];
                    }
            }            
            if($cashRefundAmount > 0){
                $cashbook=Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 1)->orderBy('created_at', 'desc')->value('balance_amount');
                if(empty($cashbook)|| $cashbook < $cashRefundAmount){
                    return ['flagError' => true, 'message' => 'Refund from cash is not allowed now due to insufficient balance! Please choose any other payment method..'];
                }
            }
            if($billing->payment_status==3 && $billing->actual_amount==0){
                if($balanceDueAmount>$servicePrice){
                    $balanceDueAmount=$servicePrice;
                }                
            }
           
            $balanceAmount=$servicePrice-$balanceDueAmount;
            if($balanceAmount >0 && array_sum($request->refund_amount)>$balanceAmount){
                return ['flagError' => true,'message' => 'Refund amount exceeds the allowed limit of  RS ' . $balanceAmount . ' because there is a pending amount RS '.$balanceDueAmount.' on this bill.'];
            }            
            $balanceAmountForInstore=0;
            if($servicePrice < $instoreUsed){
                $balanceAmountForInstore=$instoreUsed-$servicePrice;
            }
            if ($instoreRefundAmount < $instoreUsed && $servicePrice < $instoreRefundAmount) {
                return ['flagError' => true, 'message' => 'Refund amount using In-store Credit must equal the amount originally paid using In-store Credit.'];
            }
            if ($billing->payment_status == 1 && $billing->actual_amount==0) {
                foreach ($refundAmounts as $refundAmount) {
                    if (count($instorePayments) > 0 && $refundAmount['amount'] != NULL && $refundAmount['name'] !== "In-store Credit") {
                        return ['flagError' => true, 'message' => 'Refund can only be made using In-store Credit'];
                    }
                }
            }
            $currentDueAmount=0;
            if($servicePrice<$balanceDueAmount){
                $currentDueAmount=$balanceDueAmount-$servicePrice;
            }
            $rebookAmount=$servicePrice-array_sum($request->refund_amount);
            if($balanceDueAmount>0){
                $rebookAmount=($servicePrice-$balanceDueAmount)-array_sum($request->refund_amount);
            }
            $filteredRefundAmounts = array_filter($refundAmounts, function ($refundAmount) {
                return !empty($refundAmount['amount']);
            });
            $refundCount =$tempRefundCount       = count($filteredRefundAmounts);       
            $billItems          =BillingItem::where('billing_id',$billing->id);
            if(count($cancelPackage)>0){
                $billItems=$billItems->get();
            }else{
                $billItems=$billItems->get();
            }
            $billAmounts     =BillAmount::where('bill_id',$billing->id)->where('parent_bill_id',NULL)->get(); 
            $billAmount_Items=BillAmount::where('parent_bill_id',$billing->id)->get();
            $billAmounts = $billAmounts->merge($billAmount_Items);

            // if($billAmount_Items->count()>0){
            //     $totalBillAmount    =$billAmounts->where('payment_type','!=','In-store Credit')->sum('amount');

            // }else{
                 $totalBillAmount    =$billAmounts->where('payment_type','!=','In-store Credit')->sum('amount');
            // }
            if($deducted_due>0){
                $totalBillAmount-=$deducted_due;                
            }
            
            $billItemCount      =count($billItems);
            $billAmountCount    =count($billAmounts); 
            $service_array=[]; 
            if ($billItemCount == 1 && count($cancelService)>0) {             
                $service = Service::find($cancelService[0]);     //check the logic for schedule cancel and bill class separetly
                foreach ($refundAmounts as $refundAmount) {
                    if (!empty($refundAmount['amount']) && $refundAmount['amount'] !== "0" && $refundAmount['amount'] !=='') {
                        $this->createRefund($billing, $request, $service, $refundAmount, $refundCount,$balanceDueAmount);
                    }
                }
            }      
            // Handle refunds for cancel service items
            if (!empty($cancelService) && count($cancelService)>0) {            
                foreach ($cancelService as $cancelServiceItem) {
                    $service = Service::find($cancelServiceItem);           
                    foreach ($refundAmounts as $refundAmount) {
                        if (!empty($refundAmount['amount']) && $refundAmount['amount'] !== "0" && $refundAmount['amount'] !=='') {
                            $this->createRefund($billing, $request, $service, $refundAmount, $refundCount,$balanceDueAmount);
                        }else{                              
                            if(!in_array($service->id,$service_array) && array_sum($request->refund_amount)==0){                                
                                $discount_value = BillingItem::where('item_id', $service->id)->where('billing_id', $billing->id)->value('discount_value');                                
                                $refund_obj = new RefundCash();
                                $refund_obj->customer_id   = $billing->customer_id;
                                $refund_obj->bill_id       = $request->bill_id;
                                $refund_obj->billing_code  = $billing->billing_code;
                                $refund_obj->amount        = 0;
                                if($billing->payment_status==3 && $billing->actual_amount==0){
                                    $refund_obj->actual_amount = $service->price;
                                }else{
                                    $refund_obj->actual_amount = $service->price- $discount_value; 
                                }
                                $refund_obj->item_id       = $service->id;
                                $refund_obj->comments      = $request->comment;
                                $refund_obj->save();
                                $service_array[]=$service->id;
                            }
                        }
                    }
                }
            }
            if (!empty($cancelPackage) && count($cancelPackage)>0) {
                foreach ($cancelPackage as $cancelPackageItem) {
                    $service = Package::find($cancelPackageItem);
                    foreach ($refundAmounts as $refundAmount) {
                        $amount=0;
                        if ($refundAmount['amount'] !== NULL && $refundAmount['amount'] !== '' && $refundAmount['amount'] !== "0") {
                            if($refundCount >0){
                                $amount=$refundAmount['amount'];
                            }else{
                                $amount=0;
                            }
                            $existingRefund = RefundCash::where('customer_id', $billing->customer_id)
                            ->where('bill_id', $request->bill_id)
                            ->where('package_id', $service->id)
                            ->where('payment_type',$refundAmount['id'])
                            ->first();
                            $anyRefundForItem = RefundCash::where('bill_id', $request->bill_id)->where('package_id', $service->id)->exists();
                            if(!$existingRefund){
                                $refund_obj = new RefundCash();
                                $refund_obj->customer_id  = $billing->customer_id;
                                $refund_obj->bill_id      = $request->bill_id;
                                $refund_obj->billing_code = $billing->billing_code;
                                $refund_obj->amount       = $amount;
                                $refund_obj->payment_type = $refundAmount['id'];
                                
                                if($billing->payment_status==3 && $billing->actual_amount==0){
                                    $refund_obj->actual_amount = $service->price;
                                }else{
                                    $refund_obj->actual_amount= $anyRefundForItem ? 0 : $service->price-$balanceDueAmount;
                                }
                                $refund_obj->package_id   = $service->id;
                                $refund_obj->comments     = $request->comment;
                                $refund_obj->save();   
                            } 
                            $refundCount--;                        
                        }
                        else{                                                         
                            if(!in_array($service->id,$service_array) && array_sum($request->refund_amount)==0){
                                $refund_obj = new RefundCash();
                                $refund_obj->customer_id   = $billing->customer_id;
                                $refund_obj->bill_id       = $request->bill_id;
                                $refund_obj->billing_code  = $billing->billing_code;
                                $refund_obj->amount        = 0;
                                
                                if($billing->payment_status==3 && $billing->actual_amount==0){
                                    $refund_obj->actual_amount = $service->price;
                                }else{
                                    $refund_obj->actual_amount = $service->price-$balanceDueAmount;
                                }
                                $refund_obj->package_id    = $service->id;
                                $refund_obj->comments      = $request->comment;
                                $refund_obj->save();
                                $service_array[]=$service->id;  
                            }
                        }
                    }
                }
            }  
            $bill_array=[];
            $sumOfInstoreAmounts=0;
            $balanceRefundAmount=$servicePrice-array_sum($request->refund_amount);
            if($balanceDueAmount>0){
                $balanceRefundAmount-=$balanceDueAmount; 
            }       
            if(count($cancelService)> 0 && $billItemCount >1 || $balanceRefundAmount >0 || count($cancelPackage)> 0 && $billItemCount >2 ){                                       
                if($billAmounts->count()>0){                    
                    foreach ($billAmounts as $key => $billAmount) {       
                        if($billing->actual_amount==$billAmount->amount && $billAmount->payment_type==5){                                      
                            $billing_code = FunctionHelper::getBillingCode($billAmount->billing_format_id);                          
                        }elseif($billing->actual_amount==$billAmount->amount && $billAmount->payment_type==3){
                            $billing_code = FunctionHelper::getBillingCode($billAmount->billing_format_id);
                        }else{
                            $billing_code = FunctionHelper::getBillingCode($billAmount->billing_format_id);
                        }
                    }  
                } else{
                    $default_format     = Billing::getDefaultFormat();
                     $billing_code      = FunctionHelper::getBillingCode($default_format->id);
                } 
               
                $newBill=new Billing();                
                $newBill->billing_code             = $billing_code;
                $newBill->parent_id                = $billing->id;
                $newBill->shop_id                  = $billing->shop_id;
                $newBill->customer_id              = $billing->customer_id;
                $newBill->customer_address         = $billing->customer_address;
                $newBill->customer_type            = $billing->customer_type;
                $newBill->address_type             = $billing->address_type;
                $newBill->payment_status           = $billing->payment_status;
                // $newBill->amount                   = $billing->amount-array_sum($request->refund_amount)-$rebookAmount;
                // $newBill->actual_amount            = $billing->actual_amount-array_sum($request->refund_amount)-$rebookAmount;
                $filteredInstoreAmounts             = array_filter($refundAmounts, function ($refundAmount) {
                    return $refundAmount['name'] == 'In-store Credit' && !empty($refundAmount['amount']);
                });
                $Instore_amounts = array_column($filteredInstoreAmounts, 'amount');
                $Instore_amounts = array_map(function ($amount) {
                    return (int) $amount;
                }, $Instore_amounts);
                $sumOfInstoreAmounts = array_sum($Instore_amounts);                
                $total_instore_difference=$sumOfInstoreAmounts-$instoreUsed;                
                if($total_instore_difference > 0){
                    $sumOfInstoreAmounts=$total_instore_difference;
                }
                $filteredRefundAmounts             = array_filter($refundAmounts, function ($refundAmount) {
                    return $refundAmount['name'] !== 'In-store Credit' && !empty($refundAmount['amount']);
                });
                
                $amounts = array_column($filteredRefundAmounts, 'amount');
                $amounts = array_map(function ($amount) {
                    return (int) $amount;
                }, $amounts);
                $sumOfRefundAmounts = array_sum($amounts);   
                if(count($cancelService)>0){
                    if($billItemCount==1){
                        // dd($balanceRefundAmount,array_sum($request->refund_amount),$balanceDueAmount);
                        $newBill->amount                   = $billing->amount-array_sum($request->refund_amount)- $totalDiscount-$balanceDueAmount;                         
                        if($instoreUsed>0){                          
                            if($billing->payment_status==1 && $billing->actual_amount == 0){
                                $actual_amount=$billing->actual_amount;
                            }else{   
                                if($balanceRefundAmount>0){
                                    $actual_amount=$balanceRefundAmount;
                                }else{
                                    $actual_amount=$billing->actual_amount-(array_sum($request->refund_amount)-$instoreRefundAmount);
                                }   
                            }
                        }else{
                            
                            $actual_amount=$billing->actual_amount > 0?$balanceRefundAmount :$billing->actual_amount; 
                        }   
                        // $billing->actual_amount > 0?$balanceRefundAmount :$billing->actual_amount;           
                        $newBill->actual_amount            =  $actual_amount;
                    }else{ 
                        if($servicePrice<$balanceDueAmount){
                            $newBill->amount =$billing->amount-$servicePrice;
                        }   
                        else {                                
                        $newBill->amount                   = $billing->amount-array_sum($request->refund_amount)- $totalDiscount-$balanceDueAmount;                     
                       
                        }//$newBill->actual_amount            = $billing->actual_amount > 0 ?$billing->actual_amount -$sumOfRefundAmounts :$billing->actual_amount;
                      
                        if($instoreUsed>0){                                        
                            if($billing->payment_status==1 && $billing->actual_amount == 0){
                                $actual_amount=$billing->actual_amount;
                            } else{                                  
                                if($servicePrice<$balanceDueAmount){
                                    $actual_amount=$billing->actual_amount;
                                }else{  
                                    $refundAmountToBillAmount=(array_sum($request->refund_amount)-$instoreUsed);
                        // dd($billing->actual_amount-($refundAmountToBillAmount>0?$refundAmountToBillAmount:0));
                                                     
                                    $actual_amount=$billing->actual_amount-($refundAmountToBillAmount>0?$refundAmountToBillAmount:0); 
                                }  
                             }     
                            // $newBill->actual_amount            = $billing->actual_amount >0 ? $billing->actual_amount-(array_sum($request->refund_amount)-$instoreUsed) :  $billing->actual_amount;
                            $newBill->actual_amount            =$actual_amount;                          
                        }else{  
                            
                            if($servicePrice<$balanceDueAmount){
                                $newBill->actual_amount            = $billing->actual_amount;
                            }else{                                
                                $newBill->actual_amount            = $billing->actual_amount >0 ? $billing->actual_amount-(array_sum($request->refund_amount)+$deducted_due) :  $billing->actual_amount;
                            }

                        }                        
                    }
                }else{                    
                    if($billItemCount==2){
                        $newBill->amount                   = $balanceRefundAmount-$totalDiscount; 
                        $newBill->actual_amount            =$billing->actual_amount > 0 ? $balanceRefundAmount:$billing->actual_amount;
                    }else{ 
                        if($instoreUsed>0){ 
                            if($billing->payment_status==1 && $billing->actual_amount == 0){
                                $actual_amount=$billing->actual_amount;
                            } else{  
                                if($servicePrice<$balanceDueAmount){
                                    $actual_amount=$billing->actual_amount;
                                }else{
                                $actual_amount=$billing->actual_amount-(array_sum($request->refund_amount)-$instoreUsed);      
                                }
                            } 
                            $newBill->amount                   = $billing->amount-array_sum($request->refund_amount)-$totalDiscount-$balanceDueAmount; 

                        }  else{
                            if($servicePrice<$balanceDueAmount){
                               
                                $newBill->amount = $billing->amount-$servicePrice;
                                $actual_amount   = $billing->actual_amount;
                            }else{
                                $newBill->amount                   = $billing->amount-array_sum($request->refund_amount)-$totalDiscount-$balanceDueAmount; 
                                $actual_amount= $billing->actual_amount > 0 ?$billing->actual_amount -$sumOfRefundAmounts-$sumOfInstoreAmounts :$billing->actual_amount;
                            }
                        }              
                        $newBill->actual_amount            = $actual_amount;                        
                    }
                }
                
                $newBill->billed_date              = $billing->billed_date;
                $newBill->paid_date                = $billing->paid_date;
                $newBill->status                   = 1;
                $newBill->save(); 
                if($billing->payment_status==3 && $billing->actual_amount==0){
                    $customerPendingDue=CustomerPendingPayment::where('customer_id',$billing->customer_id)
                                        ->where('bill_id',$billing->id)
                                        ->where('current_due','>',0)                
                                        ->first();
                    if($customerPendingDue){                    
                        $newDue= CustomerPendingPayment::create([
                            'customer_id'           => $billing->customer_id,
                            'current_due'           => $newBill->amount,
                            'over_paid'             => 0,
                            'deducted_over_paid'    => 0,
                            'expiry_status'         => $customerPendingDue->expiry_status,
                            'gst_id'                => $customerPendingDue->gst_id,
                            'validity_from'         => $customerPendingDue->validity_from,
                            'validity_to'           => $customerPendingDue->validity_to,
                            'validity'              => $customerPendingDue->validity,
                            'amount_before_gst'     => null,
                            'bill_id'               => $newBill->id,
                            'is_billed'             => 0,
                            'removed'               => 0,
                        ]);
                        $customerPendingDue->deducted_over_paid=$servicePrice;
                        $customerPendingDue->removed=1;
                        $customerPendingDue->save();

                    }  
                }
                $latestBillAmount=0;
                $refund_amount_total =$temp_refund_amount= array_sum($request->refund_amount)-$instoreRefundAmount;
                // -$instoreRefundAmount; remove this for cancel payment of additional due paid using another bill
                if($balanceRefundAmount>0 && $instoreRefundAmount>$instoreUsed){
                    $refund_amount_total+=($instoreRefundAmount-$instoreUsed); 
                }      
                if($tempRefundCount==1 && $instoreRefundAmount >0 ){
                    $refund_amount_total= 0;             
                }      
                if($tempRefundCount==1 && $instoreRefundAmount >0 && $instoreRefundAmount>$instoreUsed){  
                    $refund_amount_total=array_sum($request->refund_amount)-$instoreUsed;               
                }
                  if($billing->actual_amount < $billing->amount && $billing->payment_status==1){
                    $customerDueList=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('current_due','>',0)->get();
                    foreach($customerDueList as $dueList){
                        $customerDueList=CustomerPendingPayment::where('customer_id',$dueList->customer_id)->where('parent_id',$dueList->id)->get();
                        if($customerDueList->count()>0){
                            $refund_amount_total =$temp_refund_amount= array_sum($request->refund_amount)-$instoreRefundAmount;
                        }
                    }


                }
                // dd($refund_amount_total);
                $reduceDueAmount=$deducted_due/($billAmounts->count());  
                // $x=[]; 
                foreach ($billAmounts as $key => $billAmount) {                     
                    if(count($cancelService)>0){
                        if($billAmount->payment_type!='In-store Credit' ) {   
                            if($billItemCount!=1){
                                if($instoreRefundAmount==$servicePrice){  
                                    if($instoreRefundAmount <$instoreUsed && $tempRefundCount==1 ){
                                        $latestBillAmount    = ($billAmount->amount-$reduceDueAmount);
                                    }else{
                                        $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                        $removableAmount     = $refund_amount_total*$proportion;
                                        $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount; 
                                    }  
                                    // $latestBillAmount    = ($billAmount->amount-$reduceDueAmount);
                                }else{               
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount; 
                                }
                            }
                            else{                       
                                if($tempRefundCount > 1){ 
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount;                                    
                                }else{  
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;                               
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount; 
                                }   
                                                    
                            }
                        }else{      
                            if($billing->payment_status==1 && $billing->actual_amount==0){  
                                    $latestBillAmount=($billAmount->amount-$reduceDueAmount)-array_sum($request->refund_amount);  
                            } elseif(($billAmount->amount-$reduceDueAmount) > $instoreRefundAmount ){  // && ($billAmount->amount-$reduceDueAmount) >$refund_amount_total 
                                
                                if($instoreRefundAmount>0){   
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$instoreRefundAmount;
                                 
                                    //added logic for due paid using next bill
                                    
                                }else{
                                    // $latestBillAmount    = ($billAmount->amount-$reduceDueAmount);
                                    // $refund_amount_total=$refund_amount_total>0 ?$refund_amount_total-$latestBillAmount:0;                        
                                // newly added logic - if the due payment is done by adding additional amount to the new bill
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount;
                            }                                
                            }else{                               
                                if($instoreUsed<$instoreRefundAmount){
                                    $latestBillAmount=($billAmount->amount-$reduceDueAmount)-$instoreUsed;
                                }else{
                                    $latestBillAmount=($billAmount->amount-$reduceDueAmount)-$instoreUsed;
                                      
                                }
                               
                            }
                            // dd($billAmount->amount,$totalBillAmount,$latestBillAmount);
                        }     
                    }else{                       
                        if($billAmount->payment_type!='In-store Credit' ) {   
                            if($billItemCount != 2){
                                if($instoreRefundAmount==$servicePrice){  
                                    if($instoreRefundAmount <$instoreUsed && $tempRefundCount==1 ){
                                        $latestBillAmount    = ($billAmount->amount-$reduceDueAmount);
                                    }else{
                                        $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                        $removableAmount     = $refund_amount_total*$proportion;
                                        $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount; 
                                    }  
                                }else{                                                    
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount;     
                                }
                            }
                            else{                       
                                if($tempRefundCount > 1){ 
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount;
                                    
                                }else{  
                                    $proportion          = ($billAmount->amount-$reduceDueAmount)/$totalBillAmount;                               
                                    $removableAmount     = $refund_amount_total*$proportion;
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$removableAmount; 
                                }   
                                                    
                            }
                        }else{        
                            if($billing->payment_status==1 && $billing->actual_amount==0){  
                                    $latestBillAmount=($billAmount->amount-$reduceDueAmount)-array_sum($request->refund_amount);  
                            } elseif(($billAmount->amount-$reduceDueAmount) > $instoreRefundAmount ){  // && ($billAmount->amount-$reduceDueAmount) >$refund_amount_total 
                                
                                if($instoreRefundAmount>0){                                
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount)-$instoreRefundAmount;
                                }else{
                                    $latestBillAmount    = ($billAmount->amount-$reduceDueAmount);
                                    $refund_amount_total=$refund_amount_total>0 ?$refund_amount_total-$latestBillAmount:0;                        
                                }                                
                            }else{
                                if($instoreUsed<$instoreRefundAmount){
                                    $latestBillAmount=($billAmount->amount-$reduceDueAmount)-$instoreUsed;
                                }
                            }
                        }  

                    }
                //    $x[]=$latestBillAmount;
                    // $refund_amount_total!=$billAmount->amount && 
                    // if($billAmount->payment_type!='In-store Credit' ){ 
                    if(round($latestBillAmount) > 0){    
                        $existingBillAmount = BillAmount::where('bill_id', $newBill->id)
                        ->where('payment_type_id', $billAmount->payment_type_id)
                        ->first();
            
                    if ($existingBillAmount) {
                        // Update existing amount
                        $existingBillAmount->amount += $latestBillAmount;
                        $existingBillAmount->save();
                    } else {
                        // Create new bill amount
                        $newBillAmount = new BillAmount();
                        $newBillAmount->bill_id = $newBill->id;
                        $newBillAmount->billing_format_id = $billAmount->billing_format_id;
                        $newBillAmount->payment_type_id = $billAmount->payment_type_id;
                        $newBillAmount->payment_type = $billAmount->payment_type;                   
                        $newBillAmount->amount = $latestBillAmount;
                        $newBillAmount->save();
                    }
                    // }
                    }
                    if($deducted_due>0){
                        $deducted_due-=$reduceDueAmount;
                        $reduceDueAmount=$deducted_due;

                    }
                    $billAmount->delete();                    
                }   
                // dd($x);
                $bill_amount_list_total=BillAmount::where('bill_id',$newBill->id)->where('payment_type','!=','In-store Credit')->sum('amount'); 
                $newBill->actual_amount=$bill_amount_list_total;
                $newBill->save();
                if($rebookAmount > 0){                  
                    $rebookBillItem=new BillingItem();
                    $rebookBillItem->billing_id         = $newBill->id;
                    // $rebookBillItem->billing_id         = $rebookBilling->id;
                    // $rebookBillItem->customer_id        = $rebookBilling->customer_id;
                    $rebookBillItem->customer_id        = $newBill->customer_id;
                    $rebookBillItem->item_type          = 'rebook';
                    $rebookBillItem->item_id            = 0;
                    $rebookBillItem->item_count         = 1;
                    $rebookBillItem->item_details       = 'Cancellation Fee';
                    $rebookBillItem->is_discount_used   = 0;
                    $rebookBillItem->save();
                    
                    $discount       = 0;
                    $instoreAmount  = 0;
                    $packagePrice   = 0;
                 
                    $tax_array                          = $this->rebookTaxCalculation($rebookBillItem,$rebookAmount);                     
                    if($tax_array['status'] == false){
                        return ['flagError' => true, 'message' => "Please Re-enter Credit Amount",  'error' =>"Please Re-enter Credit Amount"];
                    }               
                    $item_count                         = 1;
                    $item_tax                           = new BillingItemTax();
                    $item_tax->bill_id                  = $newBill->id;
                    if ($rebookBillItem->item_type == 'rebook') {                             
                        $item_tax->bill_item_id             = $rebookBillItem->id;
                        $item_tax->item_id                  = 0;  
                    }        
                    $item_tax->tax_method               = 'split_2';
                    $item_tax->total_tax_percentage     = $tax_array['total_tax_percentage'];
                    $item_tax->cgst_percentage          = $tax_array['cgst_percentage'];
                    $item_tax->sgst_percentage          = $tax_array['sgst_percentage'];
                    $item_tax->cgst_amount              = $tax_array['cgst'];
                    $item_tax->sgst_amount              = $tax_array['sgst'];
                    $item_tax->grand_total              = $tax_array['total_amount'];
                    $item_tax->tax_amount               = $tax_array['amount'];
                    $item_tax->created_at               = now();
                    $item_tax->updated_at               = now();
                    $item_tax->save();   
                    $rebook=new Rebook();  
                    $rebook->parent_bill_id = $newBill->parent_id;
                    $rebook->child_bill_id  = $newBill->id;
                    $rebook->amount         = $rebookAmount;
                    $rebook->save();
                }
            }else{
                if($billAmountCount==1){
                    BillAmount::where('bill_id',$billing->id)->delete();
                }
            }
             $billing->status         =2;
             $billing->payment_status =2;
             $billing->save();
             
            foreach ($refundAmounts as $refundAmount) {                     
                if ($refundAmount['amount'] !== NULL && $refundAmount['amount'] !== '' && $refundAmount['amount'] !== "0") {                    
                    if ($refundAmount['name'] == 'In-store Credit' && $refundAmount['amount'] !== NULL && $refundAmount['amount'] !== "0") {
                        $today = Carbon::now();
                        $next_day = $today->clone()->addMonths(6);
                        $validity = '180';
                        $today = now()->toDateString();
                        $customerPendingPayments=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('deducted_over_paid','>',0)->get();
                        
                        $customerPendingPayment=$customerPendingPayments->sum('deducted_over_paid');    
                        $deducted_over_paid=0;              
                        foreach($customerPendingPayments as $payment){     
                            if($payment->removed!=1){
                                $payment->deducted_over_paid=0;
                                $payment->save();
                            }
                        }
                        
                        $deducted_over_paid=$customerPendingPayment > 0 ? $customerPendingPayment - $refundAmount['amount'] : 0;
                        if($deducted_over_paid>0){
                            $pending_account = CustomerPendingPayment::create([
                                'customer_id'           => $billing->customer_id,
                                'current_due'           => 0,
                                'is_cancelled'           => 1,
                                'over_paid'             => $refundAmount['amount'],
                                'deducted_over_paid'    => $deducted_over_paid >0 ?$deducted_over_paid:0,
                                'expiry_status'         => 0,
                                'gst_id'                => 4,
                                'validity_from'         => $today,
                                'validity_to'           => $next_day,
                                'validity'              => $validity,
                                'amount_before_gst'     => $refundAmount['amount'],
                                'bill_id'               => $newBill->id ?? NULL,
                                'is_billed'             => 0,
                                'removed'               => 0,
                            ]);
                        }else{
                            $newCredit = CustomerPendingPayment::create([
                                'customer_id'           => $billing->customer_id,
                                'current_due'           => 0,
                                'is_cancelled'          => 1,
                                'over_paid'             => $refundAmount['amount'],
                                'deducted_over_paid'    => 0,
                                'expiry_status'         => 0,
                                'gst_id'                => 4,
                                'validity_from'         => $today,
                                'validity_to'           => $next_day,
                                'validity'              => $validity,
                                'amount_before_gst'     => $refundAmount['amount'],
                                'bill_id'               => $newBill->id ?? NULL,
                                'is_billed'             => 0,
                                'removed'               => 0,
                            ]);
                        }
                       
                       
                    }
                    // $customerPendingPayment=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('deducted_over_paid','>',0)->first();
                    // if($customerPendingPayment){
                    //     $customerPendingPayment->bill_id                = $newBill->id;
                    //     $customerPendingPayment->deducted_over_paid    -= $refundAmount['amount'];
                    //     $customerPendingPayment->save();
                    // }
                    if ($refundAmount['name'] == 'Cash' && $refundAmount['amount'] !== NULL) {
                        $current_balance = Cashbook::where('shop_id', SHOP_ID)
                            ->where('cash_book', 1)
                            ->orderBy('created_at', 'desc')
                            ->value('balance_amount');
            
                        $cashbook = new Cashbook();
                        $cashbook->shop_id              = SHOP_ID;
                        $cashbook->cash_book            = 1;
                        $cashbook->bill_id              = $newBill->id;
                        $cashbook->transaction_amount   = $refundAmount['amount'];
                        $cashbook->balance_amount       = $current_balance - $refundAmount['amount'];
                        $cashbook->transaction          = 2;
                        $cashbook->cash_from            = 3;
                        $cashbook->message              = "Debit - Cash debited to Business cash book from sales";
                        $cashbook->done_by              = Auth::user()->id;
                        $cashbook->save();
                    }
                }
            }
           
            if($servicePrice<$balanceDueAmount){                
                $pending_account = CustomerPendingPayment::where('bill_id',$billing->id)->where('customer_id',$billing->customer_id)->where('removed',1)->where('over_paid','>',0)->first();
                if($pending_account){
                    $instoreCreditPayment = CustomerPendingPayment::create([
                        'customer_id'           => $billing->customer_id,
                        'current_due'           => 0,
                        'is_cancelled'          => 1,
                        'over_paid'             => $pending_account->over_paid,
                        'deducted_over_paid'    => $pending_account->deducted_over_paid,
                        'expiry_status'         => 0,
                        'gst_id'                => 4,
                        'validity_from'         => $pending_account->validity_from,
                        'validity_to'           => $pending_account->validity_to,
                        'validity'              => $pending_account->validity,
                        'amount_before_gst'     => $pending_account->amount_before_gst,
                        'bill_id'               => $newBill->id ?? NULL,
                        'is_billed'             => 0,
                        'removed'               => $pending_account->removed,
                    ]);
                    $pending_account->delete();
                }
            }
            if($currentDueAmount>0){
                $pending_account = CustomerPendingPayment::create([
                    'customer_id'           => $newBill->customer_id,
                    'current_due'           => $currentDueAmount,
                    'is_cancelled'           => 1,
                    'over_paid'             => 0,
                    'deducted_over_paid'    => 0,                    
                    'bill_id'               => $newBill->id ?? NULL,
                    'is_billed'             => 0,
                    'removed'               => 0,
                    'expiry_status'         => 0,
                ]);
                $customerDue=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('removed',0)->where('current_due','!=',NULL)->delete();
                
            }
            if(isset($newBill)){
            $customerDue=CustomerPendingPayment::where('customer_id',$newBill->customer_id)->where('bill_id',$newBill->id)->where('removed',0)->where('current_due','!=',NULL)->sum('current_due');
            if($customerDue == 0){
                    $bill_status=Billing::find($newBill->id);
                    $bill_status->payment_status                   = 1;
                    $bill_status->save();
                }
            }
            if($balanceAmount >0){
                $amountForDuePayment=$servicePrice-$balanceAmount;  
                $customerPendingPayments = CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('removed',0)->where('current_due','!=',0)->get();    
                if($customerPendingPayments->count()>0){
                     foreach($customerPendingPayments as $pendingPayment){
                         $pendingPayment->deducted_over_paid  =$amountForDuePayment;
                         $pendingPayment->removed             =1;
                         $pendingPayment->save();
                     }
                 }
             }
             if(array_sum($request->refund_amount)==0){
                $usedInstoreCtredits = CustomerPendingPayment::where('bill_id',$billing->id)->where('customer_id',$billing->customer_id)->where('removed',1)->get();    
                foreach($usedInstoreCtredits as $credit){
                    $credit->bill_id=$newBill->id;
                    $credit->is_cancelled=0;
                    $credit->save();
                }
             }
            
            foreach ($schedules as $key => $schedule) {
                $schedule->payment_status=2;
                $schedule->save();
            }
            $billingItemTaxes=BillingItemTax::where('bill_id',$billing->id)->get();            
            $billingItemTaxCount=count($billingItemTaxes);
            if($billItemCount==1){
                BillingItem::where('billing_id',$billing->id)->delete();
                BillingItemTax::where('bill_id',$billing->id)->delete();
                BillAmount::where('bill_id',$billing->id)->delete();
            }else{                         
                $newBillItemIds = [];   
                $deleteBillIds=[];
                foreach ($billItems as $billItem) {     
                        if(count($cancelPackage)>0) {
                            if (in_array($billItem->package_id,$cancelPackage)) {
                                $deleteBillIds[]=$billItem->package_id;
                                $billItem->delete();
                            } else {  
                                if(isset($newBill)){                        
                                    if(!in_array($billItem->package_id, $deleteBillIds)) {
                                        $newBillItem = new BillingItem();
                                        $newBillItem->billing_id        = $newBill->id;
                                        $newBillItem->customer_id       = $billItem->customer_id;
                                        $newBillItem->item_type         = $billItem->item_type;
                                        $newBillItem->item_id           = $billItem->item_id;
                                        $newBillItem->package_id        = $billItem->package_id;
                                        $newBillItem->item_count        = $billItem->item_count;
                                        $newBillItem->item_details      = $billItem->item_details;
                                        $newBillItem->is_discount_used  = $billItem->is_discount_used;
                                        $newBillItem->discount_type     = $billItem->discount_type;
                                        $newBillItem->discount_value    = $billItem->discount_value;
                                        $newBillItem->discount_amount   = $billItem->discount_amount;
                                        $newBillItem->save();                             
                                        $newBillItemIds[$billItem->id] = $newBillItem->id; // Map old bill_item_id to new bill_item_id
                                        
                                    }    
                                }                           
                                $billItem->delete();
                                $billItemCount--;
                            } 
                        
                        }else{        
                            if (in_array($billItem->item_id,$cancelService)) {
                                $deleteBillIds[]=$billItem->item_id;
                                $billItem->delete();
                            } else {  
                                if(isset($newBill)){    
                                    if(!in_array($billItem->item_id, $deleteBillIds)) {
                                        $newBillItem = new BillingItem();
                                        $newBillItem->billing_id        = $newBill->id;
                                        $newBillItem->customer_id       = $billItem->customer_id;
                                        $newBillItem->item_type         = $billItem->item_type;
                                        $newBillItem->item_id           = $billItem->item_id;
                                        $newBillItem->package_id        = $billItem->package_id;
                                        $newBillItem->item_count        = $billItem->item_count;
                                        $newBillItem->item_details      = $billItem->item_details;
                                        $newBillItem->is_discount_used  = $billItem->is_discount_used;
                                        $newBillItem->discount_type     = $billItem->discount_type;
                                        $newBillItem->discount_value    = $billItem->discount_value;
                                        $newBillItem->discount_amount   = $billItem->discount_amount;
                                        $newBillItem->save();
                                        $newBillItemIds[$billItem->id] = $newBillItem->id; // Map old bill_item_id to new bill_item_id
                                        
                                    }    
                                }
                                $billItem->delete();
                                $billItemCount--;                       
                            
                            } 
                        }    
                }
              
                $billitemPackageId=[];
                $billitemTax=[];
                foreach ($billingItemTaxes as $billingItemTax) {         
                    if($request->schedule_package_id !=NULL) {                                                   
                        $billitems = BillingItem::where('billing_id',$billing->id)->whereIn('package_id',$cancelPackage)->get();
                        foreach($billitems as $billitems){
                            $billitemPackageId[]=$billitems->item_id;
                        }
                        if (in_array($cancelPackage, $billitemPackageId)) {
                            $billingItemTax->delete();
                        } else {   
                            if(isset($newBill)){                           
                                if (isset($newBillItemIds[$billingItemTax->bill_item_id])) {                                
                                    if(!in_array($billitemTax,$newBillItemIds)){                                   
                                        $newBillingItemTax = new BillingItemTax();
                                        $newBillingItemTax->bill_id              = $newBill->id;
                                        $newBillingItemTax->bill_item_id         = $newBillItemIds[$billingItemTax->bill_item_id]; // Correct mapping
                                        $newBillingItemTax->item_id              = $billingItemTax->item_id;
                                        $newBillingItemTax->tax_method           = $billingItemTax->tax_method;
                                        $newBillingItemTax->total_tax_percentage = $billingItemTax->total_tax_percentage;
                                        $newBillingItemTax->cgst_percentage      = $billingItemTax->cgst_percentage;
                                        $newBillingItemTax->sgst_percentage      = $billingItemTax->sgst_percentage;
                                        $newBillingItemTax->cgst_amount          = $billingItemTax->cgst_amount;
                                        $newBillingItemTax->sgst_amount          = $billingItemTax->sgst_amount;
                                        $newBillingItemTax->grand_total          = $billingItemTax->grand_total;
                                        $newBillingItemTax->tax_amount           = $billingItemTax->tax_amount;
                                        $newBillingItemTax->save();
                                        array_push($billitemTax, $newBillingItemTax->bill_item_id);
                                    }
                                    
                                }
                            }
                            $billingItemTax->delete();
                            $billingItemTaxCount--;
                        }
                        
                    } else{          
                            if (in_array($billingItemTax->item_id,$cancelService)) {                 
                                $billingItemTax->delete();
                            } else {
                                if(isset($newBill)){    
                                    if (isset($newBillItemIds[$billingItemTax->bill_item_id])) {
                                        $newBillingItemTax = new BillingItemTax();
                                        $newBillingItemTax->bill_id              = $newBill->id;
                                        $newBillingItemTax->bill_item_id         = $newBillItemIds[$billingItemTax->bill_item_id]; // Correct mapping
                                        $newBillingItemTax->item_id              = $billingItemTax->item_id;
                                        $newBillingItemTax->tax_method           = $billingItemTax->tax_method;
                                        $newBillingItemTax->total_tax_percentage = $billingItemTax->total_tax_percentage;
                                        $newBillingItemTax->cgst_percentage      = $billingItemTax->cgst_percentage;
                                        $newBillingItemTax->sgst_percentage      = $billingItemTax->sgst_percentage;
                                        $newBillingItemTax->cgst_amount          = $billingItemTax->cgst_amount;
                                        $newBillingItemTax->sgst_amount          = $billingItemTax->sgst_amount;
                                        $newBillingItemTax->grand_total          = $billingItemTax->grand_total;
                                        $newBillingItemTax->tax_amount           = $billingItemTax->tax_amount;
                                        $newBillingItemTax->save();
                                    
                                    }
                                }
                                $billingItemTax->delete();
                                $billingItemTaxCount--;
                            }
                        }
                }
                
            }
                foreach ($schedules as $key => $schedule) {    
                    $existingSchedules=Schedule::where('billing_id', $billing->id)->where('id','!=',$schedule->id)->get(); 
                    foreach ($existingSchedules as $key => $existingSchedule) {    
                        if($request->cancel_package !=NULL || $request->schedule_package_id!==NULL) {
                            if (!in_array($existingSchedule->package_id,$cancelPackage)) {                       
                                $newSchedule=new Schedule();
                                $newSchedule->shop_id              = $existingSchedule->shop_id;
                                $newSchedule->name                 = $existingSchedule->name;
                                $newSchedule->description          = $existingSchedule->description;
                                $newSchedule->user_id              = $existingSchedule->user_id;
                                $newSchedule->customer_id          = $existingSchedule->customer_id;
                                $newSchedule->billing_id           = $newBill->id ?? '';
                                $newSchedule->item_id              = $existingSchedule->item_id;;
                                $newSchedule->package_id           = $existingSchedule->package_id;
                                $newSchedule->item_type            = $existingSchedule->item_type;
                                $newSchedule->room_id              = $existingSchedule->room_id;
                                $newSchedule->start                = $existingSchedule->start;
                                $newSchedule->end                  = $existingSchedule->end;
                                $newSchedule->checked_in           = $existingSchedule->checked_in;
                                $newSchedule->total_minutes        = $existingSchedule->total_minutes;
                                $newSchedule->payment_status       = $existingSchedule->payment_status;
                                $newSchedule->schedule_color       = $existingSchedule->schedule_color;
                                $newSchedule->status               = $existingSchedule->status;
                                $newSchedule->save();                   
                            
                            }   
                            
                        }
                        else{
                                if (!in_array($existingSchedule->item_id, $request->cancel_service)) {                       
                                    $newSchedule=new Schedule();
                                    $newSchedule->shop_id              = $existingSchedule->shop_id;
                                    $newSchedule->name                 = $existingSchedule->name;
                                    $newSchedule->description          = $existingSchedule->description;
                                    $newSchedule->user_id              = $existingSchedule->user_id;
                                    $newSchedule->customer_id          = $existingSchedule->customer_id;
                                    $newSchedule->billing_id           = $newBill->id ?? '';
                                    $newSchedule->item_id              = $existingSchedule->item_id;;
                                    $newSchedule->package_id           = $existingSchedule->package_id;
                                    $newSchedule->item_type            = $existingSchedule->item_type;
                                    $newSchedule->room_id              = $existingSchedule->room_id;
                                    $newSchedule->start                = $existingSchedule->start;
                                    $newSchedule->end                  = $existingSchedule->end;
                                    $newSchedule->checked_in           = $existingSchedule->checked_in;
                                    $newSchedule->total_minutes        = $existingSchedule->total_minutes;
                                    $newSchedule->payment_status       = $existingSchedule->payment_status;
                                    $newSchedule->schedule_color       = $existingSchedule->schedule_color;
                                    $newSchedule->status               = $existingSchedule->status;
                                    $newSchedule->save();                      
                                
                                }  
                        }                        
                        $existingSchedule->delete();
                    }
                    $schedule->delete();
                }
            
            
            $billing->delete();
            $current='5';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type='bill';
            $comment='Bill Cancelled';
            $customer=$billing->customer_id;
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$billing,$comment,$type);
            DB::commit();    
            return ['flagError' => false, 'message' => 'Cancellations successful'];
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage()); // For debugging purposes
            return ['flagError' => true, 'message' => 'Internal Server Error'];
        }
    }

    public function rebookTaxCalculation($rebookBillItem,$rebookAmount) {  
      if($rebookBillItem->item_count== null){
        $rebookBillItem->item_count =1;
      }     
        $store_data = ShopBilling::where('shop_id', SHOP_ID)->first();
        $total_percentage       = 0;
        $price                  = 0;
        $total_service_tax      = 0;
        $total_payable          = 0;
        $service_value          = 0;
        $discount_amount        = 0;
        $discount_type          = '';
        $discount_value         = 0;
        $total_cgst_amount      = 0;
        $total_sgst_amount      = 0;
        $cgst_percentage        = 0;
        $sgst_percentage        = 0;
        $tax_onepercentage      = 0;
        $additional_amount      = 0;
        $tax_percentage         = 0;        
        $tax_array              = array();        
        if($rebookBillItem->item_type=='rebook'){
            $total_price =$rebookAmount;
        }
        
        if ($store_data->gst_percentage != null) {
            $total_percentage   = $store_data->GSTTaxPercentage->percentage;
            $tax_percentage     = $store_data->GSTTaxPercentage->percentage;

        }
        // Calculate total service tax
        if ($total_percentage > 0) {
            $total_service_tax = ($total_price / (1 + ($total_percentage / 100)));
            $tax_onepercentage = $total_service_tax / $total_percentage;           
            $cgst_percentage   = $sgst_percentage = ($tax_percentage / 2);
            $total_cgst_amount = $total_service_tax * $cgst_percentage / 100;
            $total_sgst_amount = $total_service_tax * $sgst_percentage / 100;
            $total_gst_amount  = $total_cgst_amount + $total_sgst_amount;
        }
        $gstAmount = ($total_price * $tax_percentage) / 100;     
        $additional_amount    = 0;
        $additional_tax_array = [];
        $tax_sum              = 0;
        $included             = 'Tax Included';
        if($rebookBillItem->item_type=='rebook'){
            $total_payable =$rebookAmount;            
        }
        $service_value      =( $total_payable / (1 + ($total_percentage / 100)));                
        $total_cgst_amount  = $total_sgst_amount =($service_value *$cgst_percentage)/100;           
        $tax_array = [
            'status'                =>true,
            'name'                  => $rebookBillItem->item_type,
            'tax_method'            => $included,
            'hsn_code'              => "",
            'amount'                => $service_value ,
            'price'                 => $rebookAmount,
            'total_tax_percentage'  => $total_percentage,
            'cgst_percentage'       => $cgst_percentage,
            'sgst_percentage'       => $sgst_percentage,
            'cgst'                  => number_format($total_cgst_amount, 2),
            'sgst'                  => number_format($total_sgst_amount, 2),
            'total_amount'          => $total_payable
            
        ];   
        return $tax_array;
        
    }
   
    public function createRefund($billing, $request, $service, $refundAmount, &$refundCount,$balanceDueAmount) { 
        if($refundCount >0){
            $amount=$refundAmount['amount'];
        }else{
            $amount=0;
        }        
        $existingRefund = RefundCash::where('bill_id', $request->bill_id)
            ->where('item_id', $service->id)
            ->where('payment_type',$refundAmount['id'])
            ->first();

        $anyRefundForItem = RefundCash::where('bill_id', $request->bill_id)->where('item_id', $service->id)->exists();
        $discount_value = BillingItem::where('item_id', $service->id)->where('billing_id', $billing->id)->value('discount_value');                                
        if(!$existingRefund ){
            $refund_obj = new RefundCash();
            $refund_obj->customer_id    = $billing->customer_id;
            $refund_obj->bill_id        = $request->bill_id;
            $refund_obj->billing_code   = $billing->billing_code;
            $refund_obj->amount         = $amount;
            $refund_obj->payment_type   = $refundAmount['id'];
            
            if($billing->payment_status==3 && $billing->actual_amount==0){
                $refund_obj->actual_amount = $service->price;
            }else{
                $refund_obj->actual_amount  = $anyRefundForItem ? 0:$service->price-$discount_value;
            }
            $refund_obj->item_id        = $service->id;
            $refund_obj->comments       = $request->comment;
            $refund_obj->save();
        }       
        $refundCount--;
    }
    
   
    public function cancelBillInvoice(Request $request,$id){
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $refundBill             = RefundCash::findOrFail($id);       
        $billing                = Billing::with('customerPendingAmount')->withTrashed()->findOrFail($refundBill->bill_id);
        $package_items          = BillingItem::where('billing_id',$billing->id)->onlyTrashed()->where('package_id','!=',NULL)->groupBy('package_id')->get();
        $grand_total                = 0;
        $sub_total                  = 0;
        $inStoreCredit              = 0;
        $customerDues               = 0;
        $discount                   = 0;
        $instoreAmount              = 0;
        abort_if(!$billing, 404);
        $variants->shop           =Shop::find(SHOP_ID);
        $variants->store        = Shop::find(SHOP_ID);
        if ($refundBill) { 
                if ($billing->items) {
                    $billing_items_array        = $billing->items()->onlyTrashed()->get()->toArray();
                    // $item_type                  = $billing_items_array[0]['item_type'];
                    $item_type                 =  collect($billing_items_array)->pluck('item_type')->unique()->values()->toArray();
                    $item_ids                   = array_column($billing->items()->onlyTrashed()->get()->toArray(), 'item_id');
                    $package_ids                = array_column($package_items->toArray(), 'package_id');
                    foreach ($billing_items_array as $row) {
                        $ids[] = $row['item_id'];
                        $packageIds[]=$row['package_id'];
                    }
                    $billing_items=collect();
                    $packageIds = array_unique($packageIds);
                    if(in_array('rebook', $item_type)){  
                        $filteredIds = array_filter($ids, function($id) {
                            return $id === 0;
                        });
                        $rebook_items  =RefundCash::select(
                            'billing_items.id AS id',
                            'billing_items.billing_id AS billingId',
                            'billing_items.item_details',
                            'billing_items.item_id',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id',
                            'refund_cashes.billing_code',
                            'refund_cashes.id AS refundId',
                            'refund_cashes.item_id AS serviceId',
                            DB::raw('SUM(refund_cashes.amount) AS totalRefundAmount'), // Calculate total refund amount
                            DB::raw('SUM(refund_cashes.actual_amount) AS totalActualAmount'), // Calculate total refund amount
                            'refund_cashes.amount AS refundAmount' // Individual refund amount
                        )
                        ->join('billing_items', 'billing_items.billing_id', '=', 'refund_cashes.bill_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('refund_cashes.bill_id', $billing->id) // Replace with the actual billing ID variable
                        ->whereIn('billing_items.item_id', $filteredIds) // Replace with the actual item IDs array
                        ->whereNotNull('billing_items.deleted_at') // Ensuring these are not null
                        ->whereNotNull('billing_item_taxes.deleted_at') // Ensuring these are not null
                        ->groupBy(
                            'billing_items.id',
                            'billing_items.billing_id', 
                        )
                        ->get();
                        $billing_items = $billing_items->merge($rebook_items);
                           
                    } 
                    if(in_array('instore', $item_type)){  
                        $filteredIds = array_filter($ids, function($id) {
                            return $id === 0;
                        });
                        $instore_items  =RefundCash::select(
                            'billing_items.id AS id',
                            'billing_items.billing_id AS billingId',
                            'billing_items.item_details',
                            'billing_items.item_id',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id',
                            'refund_cashes.billing_code',
                            'refund_cashes.id AS refundId',
                            'refund_cashes.item_id AS serviceId',
                            DB::raw('SUM(refund_cashes.amount) AS totalRefundAmount'), // Calculate total refund amount
                            DB::raw('SUM(refund_cashes.actual_amount) AS totalActualAmount'), // Calculate total refund amount
                            'refund_cashes.amount AS refundAmount' // Individual refund amount
                        )
                        ->join('billing_items', 'billing_items.billing_id', '=', 'refund_cashes.bill_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('refund_cashes.bill_id', $billing->id) // Replace with the actual billing ID variable
                        ->whereIn('billing_items.item_id', $filteredIds) // Replace with the actual item IDs array
                        ->whereNotNull('billing_items.deleted_at') // Ensuring these are not null
                        ->whereNotNull('billing_item_taxes.deleted_at') // Ensuring these are not null
                        ->groupBy(
                            'billing_items.id',
                            'billing_items.billing_id', 
                        )
                        ->get();
                        $billing_items = $billing_items->merge($instore_items);
                           
                    } 
                    if (in_array('services', $item_type)) {
                        $service_items  =RefundCash::select(
                            'services.id',
                            'services.name',
                            'services.price',
                            'services.hsn_code',
                            'billing_items.id AS id',
                            'billing_items.billing_id AS billingId',
                            'billing_items.item_details',
                            'billing_items.item_id',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id',
                            'refund_cashes.billing_code',
                            'refund_cashes.id AS refundId',
                            'refund_cashes.item_id AS serviceId',
                            DB::raw('SUM(refund_cashes.amount) AS totalRefundAmount'), // Calculate total refund amount
                            DB::raw('SUM(refund_cashes.actual_amount) AS totalActualAmount'), // Calculate total refund amount
                            'refund_cashes.amount AS refundAmount' // Individual refund amount
                        )
                        ->where('refund_cashes.bill_id',$billing->id)
                        ->join('billing_items', 'billing_items.billing_id', '=', 'refund_cashes.bill_id')  // Join billing_items
                         ->join('services', 'services.id', '=', 'billing_items.item_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('services.shop_id', SHOP_ID)
                        // ->where('billing_items.billing_id', $billing->id)
                        ->whereIn('services.id', $ids)
                        ->orderBy('services.id', 'desc')
                        ->whereNotNull('billing_items.deleted_at') 
                        ->whereNotNull('billing_item_taxes.deleted_at') 
                        ->groupBy('billing_items.billing_id','billing_items.item_id')
                        ->get();
                        $billing_items = $billing_items->merge($service_items);                       
                           
                    } 
                    else {                       
                        $package_items  =RefundCash::
                        select(
                            'packages.name',
                            'packages.price',
                            'packages.hsn_code',
                            'billing_items.id as id',
                            'billing_items.billing_id as billingId',
                            'billing_items.item_details',
                            'billing_items.item_type',
                            'billing_items.item_id',
                            'billing_items.package_id',
                            'billing_items.item_count',
                            'billing_items.is_discount_used',
                            'billing_items.discount_type',
                            'billing_items.discount_value',
                            'billing_item_taxes.cgst_percentage',
                            'billing_item_taxes.sgst_percentage',
                            'billing_item_taxes.tax_amount',
                            'billing_item_taxes.sgst_amount',
                            'billing_item_taxes.grand_total',
                            'billing_item_taxes.cgst_amount',
                            'billing_items.customer_id',
                            'refund_cashes.billing_code',
                            'refund_cashes.id AS refundId',
                            DB::raw('SUM(refund_cashes.amount) AS totalRefundAmount'), // Calculate total refund amount
                            DB::raw('SUM(refund_cashes.actual_amount) AS totalActualAmount'), // Calculate total actual amount
                            'refund_cashes.amount AS refundAmount' // Individual refund amount
                        )
                        ->where('refund_cashes.bill_id', $billing->id)
                        ->join('billing_items', 'billing_items.billing_id', '=', 'refund_cashes.bill_id')  // Join billing_items
                        ->join('services', 'services.id', '=', 'billing_items.item_id')
                        ->join('packages', 'packages.id', '=', 'billing_items.package_id')
                        ->join('billing_item_taxes', 'billing_item_taxes.bill_item_id', '=', 'billing_items.id')
                        ->where('services.shop_id', SHOP_ID)
                        ->where('billing_items.billing_id', $billing->id)
                        ->whereIn('services.id', $ids)
                        ->whereIn('packages.id', $packageIds)                      
                        ->whereNotNull('billing_items.deleted_at')
                        ->whereNotNull('billing_item_taxes.deleted_at')
                        ->groupBy(
                            'billing_items.billing_id', 'billing_items.item_id', 'billing_items.package_id'
                        )
                        ->orderBy('packages.id', 'asc') // Order by package id
                        ->orderBy('services.id', 'desc')
                        ->get();
                        $billing_items = $billing_items->merge($package_items);
                        
                    }
                    
                    $serviceLists=$service_items ?? $package_items;                    
                    $customerDue=[];
                    // $cancelItems=RefundCash::where('bill_id',$billing->id)->distinct()->pluck('item_id');  
                    $cancelItems = RefundCash::where('bill_id', $billing->id)
                    ->distinct()
                    ->selectRaw('COALESCE(item_id, package_id) as item_or_package')
                    ->pluck('item_or_package');                
             
                    foreach($serviceLists as $serviceList){
                        $customerDueList=CustomerPendingPayment::where('bill_id',$serviceList->billingId)->where('current_due','>',0)->where('deducted_over_paid','>',0)->where('removed',1)->first();
                        $refundId = $serviceList->refundId;
                        $dueAmount = $customerDueList->current_due ?? 0;
                    
                        // Use refundId as the key to avoid duplication
                        if (!isset($customerDue[$refundId])) {
                            $customerDue[$refundId] = [
                                'refundId' => $refundId,
                                'dueAmount' => $dueAmount,
                            ];
                        } else {
                            // Optionally update the existing entry if needed
                            $customerDue[$refundId]['dueAmount'] = max($customerDue[$refundId]['dueAmount'], $dueAmount);
                        }
                    }

                    $customerDue = array_values($customerDue);
                    $grand_total        =  $billing_items->sum('grand_total');
                    }
                return view($this->viewPath . '.cancel-invoice', compact('page','refundBill', 'billing', 'billing_items', 'grand_total', 'item_type', 'variants','customerDue','cancelItems'));
            
        }
        abort(404);
    }
 
    public function paymentTypeFilter(Request $request){
        $currentDate = now()->toDateString();    
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year; 
            $fromDate = $request->input('fromDate'); 
            $toDate = $request->input('toDate');
            $payment_type_total_amounts = BillAmount::query();
            if ($request->filled('year')) {
                $payment_type_total_amounts->whereYear('created_at', $request->year);
            }
            if ($request->filled(['fromDate', 'toDate'])) {
                $payment_type_total_amounts->whereBetween('created_at', [
                    Carbon::parse($request->fromDate)->startOfDay(),
                    Carbon::parse($request->toDate)->endOfDay()
                ]);
            }
            if ($request->day == 'today') {
                $payment_type_total_amounts->whereDate('created_at', $currentDate);    
            } elseif ($request->day == 'week') {
                $payment_type_total_amounts->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($request->day == 'month') {
                $payment_type_total_amounts->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear);
            }
            $filteredPayments = $payment_type_total_amounts->get();
                $sumOfAmounts = $filteredPayments->groupBy('payment_type')
                    ->map(function ($items) {
                        return $items->sum('amount');
                    });   
            $allPaymentTypes = PaymentType::all()->pluck('name')->toArray(); // Assuming PaymentType is your model
            foreach ($allPaymentTypes as $paymentType) {
                if (!isset($sumOfAmounts[$paymentType])) {
                    $sumOfAmounts[$paymentType] = 0;
                }
            }
            return response()->json(['flagError' => false, 'data' => $sumOfAmounts]);
    }

    public function salesPaymentTypeFilter(Request $request){
        
        $currentDate = now()->toDateString(); 
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        
        $totalSaleAmount= Billing::where('shop_id',SHOP_ID)->withTrashed();   
        $serviceAmount =Billing::where('shop_id',SHOP_ID)->where('payment_status',1)->whereHas('items',function($query){
            $query->where('item_type','services');
        });
        // ->sum('actual_amount');
        $totalServiceAmount =Billing::where('shop_id',SHOP_ID)->whereHas('items',function($query){
            $query->where('item_type','services');
        });
        // ->sum('amount');
        $TotalDiscountAmount = Billing::where('shop_id',SHOP_ID)
            ->with(['items' => function ($query) {
                $query->where('is_discount_used', 1);
            }]);
        $packageAmount=Billing::where('shop_id',SHOP_ID)->whereHas('items',function($query){
            $query->where('item_type','packages');
        });
        // ->sum('amount');
        $totalPackageAmount=Billing::where('shop_id',SHOP_ID)->where('payment_status',1)->whereHas('items',function($query){
            $query->where('item_type','packages');
        });
        // ->sum('amount');
        $unpaidAmount=Billing::where('shop_id',SHOP_ID)->where('payment_status',0);
        // ->sum('amount');
        $paidAmount=Billing::where('shop_id',SHOP_ID)->withTrashed();
        // ->sum('actual_amount');
        $additionallyPaidAmount  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID)->where('is_membership',0);
        });
        $total_dues     = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })->where('expiry_status',0)->where('removed',0);
        // ->sum('current_due');
        $total_instore  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('removed',0);
        // ->sum('over_paid');
        $total_refund=RefundCash::whereHas('billings',function($query){
            $query->where('shop_id',SHOP_ID);
        });

        // ->sum('amount');
        $total_canceled_bill_amount=Billing::where('shop_id',SHOP_ID)->onlyTrashed()->where('payment_status','!=',0);
       
        
        $total_instore_credited  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('is_membership',0);
       
        
        $total_instore_credit_balance  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
            
        }) ->where('expiry_status',0)
        ->where('is_membership',0)
        ->where('removed',0);
        // ->sum('over_paid');
        $total_instore_credit_used  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
            
        }) ->where('expiry_status',0)->where('is_membership',0)->where('removed',1);

        $total_membership_instore_credited  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)->where('is_membership',1);
       
        $total_membership_instore_credit_used = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)
        ->where('is_membership',1)
        ->where('removed',1);

        $total_membership_instore_credit_balance = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        }) ->where('expiry_status',0)
        ->where('is_membership',1)
        ->where('removed',0);
       
            $queryVars = [
                'totalSaleAmount', 'serviceAmount', 'totalServiceAmount', 'TotalDiscountAmount',
                'packageAmount', 'totalPackageAmount', 'unpaidAmount', 'paidAmount', 'additionallyPaidAmount',
                'total_dues', 'total_instore', 'total_refund', 'total_canceled_bill_amount',
                'total_instore_credited', 'total_instore_credit_balance', 'total_instore_credit_used',
                'total_membership_instore_credit_used', 'total_membership_instore_credited',
                'total_membership_instore_credit_balance'
            ];
        if ($request->filled('year')) {
            $year = $request->year;

            foreach ($queryVars as $var) {
                $$var->whereYear('created_at', $year);
            }
        }

        if ($request->filled('fromDate') && $request->filled('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');

            foreach ($queryVars as $var) {
                if ($fromDate === $toDate) {
                    $$var->whereDate('created_at', $fromDate);
                } else {
                    $$var->whereBetween('created_at', [$fromDate, $toDate]);
                }
            }
        }
        if ($request->day) {
            switch ($request->day) {
                case 'today':
                    foreach ($queryVars as $var) {
                        $$var->whereDate('created_at', $currentDate);
                    }
                    break;

                case 'week':
                    foreach ($queryVars as $var) {
                        $$var->whereBetween('created_at', [$startDate, $endDate]);
                    }
                    break;

                case 'month':
                    foreach ($queryVars as $var) {
                        $$var->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear);
                    }
                    break;
            }
        }

        $totalSaleAmount               =$totalSaleAmount->sum('amount');
        $serviceAmount                      =$serviceAmount ->sum('actual_amount');
        $totalServiceAmount                 =$totalServiceAmount->sum('amount');
        $TotalDiscountAmount                =$TotalDiscountAmount->get() ->sum(function ($billing) {
                                                    return $billing->items->sum('discount_value');
                                                });
        $packageAmount                      =$packageAmount->sum('amount');
        $totalPackageAmount                 =$totalPackageAmount->sum('amount');
        $unpaidAmount                       =$unpaidAmount->sum('amount');
        $paidAmount                         =$paidAmount ->sum('actual_amount');
        $additionallyPaidAmount=$additionallyPaidAmount->sum('amount_before_gst');
        $paidAmount-=$additionallyPaidAmount;
        $total_dues                         =$total_dues->sum('current_due');
        $total_instore                      =$total_instore ->sum('over_paid');
        $total_refund                       =$total_refund->sum('amount');
        $total_canceled_bill_amount           =$total_canceled_bill_amount->sum('amount');
        $total_instore_credited             = $total_instore_credited ->sum('over_paid');
        $total_instore_credit_balance=$total_instore_credit_balance->sum('over_paid');
        $total_instore_credit_used=$total_instore_credit_used->sum('deducted_over_paid');
        $total_membership_instore_credit_used=$total_membership_instore_credit_used->sum('deducted_over_paid');
        $total_membership_instore_credited=   $total_membership_instore_credited->sum('amount_before_gst');
        $total_membership_instore_credit_balance=$total_membership_instore_credit_balance->sum('over_paid');
        $paymentTypes=PaymentType::where('shop_id',SHOP_ID)->get();
        $data = [
            // 'page' => $page,
            'totalSaleAmount' => is_numeric($totalSaleAmount) ? number_format($totalSaleAmount, 2, '.', '') : $totalSaleAmount,
            'serviceAmount' => is_numeric($serviceAmount) ? number_format($serviceAmount, 2, '.', '') : $serviceAmount,
            'packageAmount' => is_numeric($packageAmount) ? number_format($packageAmount, 2, '.', '') : $packageAmount,
            'paidAmount' => is_numeric($paidAmount) ? number_format($paidAmount, 2, '.', '') : $paidAmount,
            'unpaidAmount' => is_numeric($unpaidAmount) ? number_format($unpaidAmount, 2, '.', '') : $unpaidAmount,
            'total_dues' => is_numeric($total_dues) ? number_format($total_dues, 2, '.', '') : $total_dues,
            'total_instore' => is_numeric($total_instore) ? number_format($total_instore, 2, '.', '') : $total_instore,
            'total_refund' => is_numeric($total_refund) ? number_format($total_refund, 2, '.', '') : $total_refund,
            'total_canceled_bill_amount' => is_numeric($total_canceled_bill_amount) ? number_format($total_canceled_bill_amount, 2, '.', '') : $total_canceled_bill_amount,
            'paymentTypes' => $paymentTypes,  // Keep this unchanged
            'TotalDiscountAmount' => is_numeric($TotalDiscountAmount) ? number_format($TotalDiscountAmount, 2, '.', '') : $TotalDiscountAmount,
            'total_instore_credited' => is_numeric($total_instore_credited) ? number_format($total_instore_credited, 2, '.', '') : $total_instore_credited,
            'totalServiceAmount' => is_numeric($totalServiceAmount) ? number_format($totalServiceAmount, 2, '.', '') : $totalServiceAmount,
            'totalPackageAmount' => is_numeric($totalPackageAmount) ? number_format($totalPackageAmount, 2, '.', '') : $totalPackageAmount,
            'total_instore_credit_balance' => is_numeric($total_instore_credit_balance) ? number_format($total_instore_credit_balance, 2, '.', '') : $total_instore_credit_balance,
            'total_instore_credit_used' => is_numeric($total_instore_credit_used) ? number_format($total_instore_credit_used, 2, '.', '') : $total_instore_credit_used,
            'total_membership_instore_credit_used' => is_numeric($total_membership_instore_credit_used) ? number_format($total_membership_instore_credit_used, 2, '.', '') : $total_membership_instore_credit_used,
            'total_membership_instore_credited' => is_numeric($total_membership_instore_credited) ? number_format($total_membership_instore_credited, 2, '.', '') : $total_membership_instore_credited,
            'total_membership_instore_credit_balance' => is_numeric($total_membership_instore_credit_balance) ? number_format($total_membership_instore_credit_balance, 2, '.', '') : $total_membership_instore_credit_balance
        ];
        
        return response()->json(['flagError' => false, 'data' => $data]);

    }

    public function scheduleServiceLists(Request $request){  
        if ($request->bill_id) {
            $scheduleList = Schedule::where('billing_id', $request->bill_id);
            $billing = Billing::where('id', $request->bill_id)->first();
            $billItems=BillingItem::where('billing_id', $request->bill_id);
            // if($billing->parent_id!=NULL){
            //     // $instore=CustomerPendingPayment::where('bill_id',$billing->parent_id)->sum('deducted_over_paid');
            //     // $instoreRefunded=CustomerPendingPayment::where('bill_id',$request->bill_id)->sum('over_paid');
            //     // $instoreCreditUsed=$instore> 0?$instore-$instoreRefunded:0;   
            //     $totalDueAmount=0;             
            // }else{
            $totalDueAmount =CustomerPendingPayment::where('bill_id',$request->bill_id)->where('removed',0)->sum('current_due');
               
            // }
            $instoreCreditUsed=CustomerPendingPayment::where('bill_id',$request->bill_id)->where('over_paid','>',0)->sum('deducted_over_paid');
            if ($request->packageId) {
                // Separate the schedules into services and packages
                $packages = $scheduleList->with('package')->groupBy('package_id')->get();
                $billItems= $billItems->where('item_type','packages');
            }else{
                $services= $scheduleList->with('service')->get();
                $billItems= $billItems->where('item_type','services');

            }
            $billItems= $billItems->get();
            return response()->json([
                'flagError' => false,
                'data' => [
                    'services' => $services ?? [],
                    'packages' => $packages ?? [],
                    'instoreCreditUsed'=>$instoreCreditUsed,
                    'billItems'=>$billItems,
                    'totalDueAmount'=>$totalDueAmount,
                ]
            ]);
            
        } else {
            return response()->json(['flagError' => true, 'message' => 'No Data Found']);
        }
        
    }
    public function cancelServiceLists(Request $request){
        if ($request->bill_id) {
            $billList = Billing::where('id', $request->bill_id)->first();
            $billItems = BillingItem::where('billing_id', $request->bill_id)->get();
            $instoreCreditUsed = CustomerPendingPayment::where('bill_id', $request->bill_id)->where('over_paid','>',0)->sum('deducted_over_paid');
            $totalDueAmount =CustomerPendingPayment::where('bill_id',$request->bill_id)->where('removed',0)->sum('current_due');
           
            $packages = BillingItem::with('package')
                ->where('billing_id', $request->bill_id)
                ->where('item_type', 'packages')
                ->whereNotNull('package_id')
                ->groupBy('package_id')
                ->get();

            // Group by services
            $services = BillingItem::with('item')
                ->where('billing_id', $request->bill_id)
                ->where('item_type', 'services')
                ->whereNull('package_id')
                ->groupBy('item_id')
                ->get();
                return response()->json([
                    'flagError' => false,
                    'data' => [
                        'services' => $services ?? [],
                        'packages' => $packages ?? [],
                        'instoreCreditUsed'=>$instoreCreditUsed,
                        'billItems'=>$billItems,
                        'totalDueAmount'=>$totalDueAmount,
                    ]
                ]);
            
            
        } else {
            return response()->json(['flagError' => true, 'message' => 'No Data Found']);
        }
    }
    public function additionalPaymentTaxCalculation($billitem,$additionalPaid) {  
        if($billitem->item_count== null){
          $billitem->item_count =1;
        }     
          $store_data = ShopBilling::where('shop_id', SHOP_ID)->first();
          $total_percentage       = 0;
          $price                  = 0;
          $total_service_tax      = 0;
          $total_payable          = 0;
          $service_value          = 0;
          $total_cgst_amount      = 0;
          $total_sgst_amount      = 0;
          $cgst_percentage        = 0;
          $sgst_percentage        = 0;
          $tax_onepercentage      = 0;
          $tax_percentage         = 0;        
          $tax_array              = array();        
          if($billitem->item_type=='instore'){
              $total_price =$additionalPaid;
          }
          
          if ($store_data->gst_percentage != null) {
              $total_percentage   = $store_data->GSTTaxPercentage->percentage;
              $tax_percentage     = $store_data->GSTTaxPercentage->percentage;
  
          }
          
          // Calculate total service tax
          if ($total_percentage > 0) {
              $total_service_tax = ($total_price / (1 + ($total_percentage / 100)));
              $tax_onepercentage = $total_service_tax / $total_percentage;           
              $cgst_percentage   = $sgst_percentage = ($tax_percentage / 2);
              $total_cgst_amount = $total_service_tax * $cgst_percentage / 100;
              $total_sgst_amount = $total_service_tax * $sgst_percentage / 100;
              $total_gst_amount  = $total_cgst_amount + $total_sgst_amount;
          }
          $gstAmount = ($total_price * $tax_percentage) / 100;     
          $additional_amount    = 0;
          $additional_tax_array = [];
          $tax_sum              = 0;
          $included             = 'Tax Included';
          if($billitem->item_type=='instore'){
              $total_payable =$additionalPaid;            
          }
          $service_value      =( $total_payable / (1 + ($total_percentage / 100)));                
          $total_cgst_amount  = $total_sgst_amount =($service_value *$cgst_percentage)/100;             
         
          $tax_array = [
              'status'                =>true,
              'name'                  => $billitem->item_type,
              'tax_method'            => $included,
              'hsn_code'              => "",
              'amount'                => $service_value ,
              'price'                 => $additionalPaid,
              'total_tax_percentage'  => $total_percentage,
              'cgst_percentage'       => $cgst_percentage,
              'sgst_percentage'       => $sgst_percentage,
              'cgst'                  => number_format($total_cgst_amount, 2),
              'sgst'                  => number_format($total_sgst_amount, 2),
              'total_amount'          => $total_payable
              
          ];   
          return $tax_array;
          
      }
      public function reduceDueAmountFromInstore($billing){
        $current_due = 0;
        $over_paid = 0;
        $currentDate = now()->timestamp;
        $nearestDifference = PHP_INT_MAX;
        $totalDue=0;
            $store=Shop::where('id',SHOP_ID)->first();
            if($store){
                $billingFormat=BillingFormat::where('shop_id',$store->id)->first();       
                $customer_dues = CustomerPendingPayment::where('expiry_status', 0)->where('customer_id',$billing->customer_id) ->where('removed', 0)->get();
                foreach ($customer_dues as $customer_due) {
                    $current_due = 0;
                    $over_paid = 0;
                    $customers = CustomerPendingPayment::where('expiry_status', 0)
                        ->where('customer_id', $customer_due->customer_id)
                        ->where('removed', 0)
                        ->where('current_due', '>', 0)// check this additionaly added
                        ->get();
                           
                        foreach ($customers as $customer) {
                            $totalDue = $customer->current_due;  
                            if ($totalDue > 0) {
                                    $customerOverPaidLists = CustomerPendingPayment::where('expiry_status', 0)
                                    ->where('customer_id', $customer->customer_id)
                                    ->where('removed', 0)
                                    ->where('over_paid', '>', 0)
                                    ->get();
                                    $continueLoop = true;
                                    $customerOverPaidLists->sortBy(function ($pendingamount) use ($currentDate,$billingFormat) {
                                        return abs(strtotime($pendingamount->validity_to) - $currentDate);
                                    })->each(function ($pendingamount) use (&$customer, &$totalDue, &$continueLoop,&$billingFormat) {    
                                        if ($continueLoop) {
                                            $currentDue = 0;
                                            $deductedDue = 0;                                            
                                            if ($totalDue > 0) {                                 
                                                if ($customer->current_due < $pendingamount->over_paid) {
                                                    $currentDue = 0;
                                                    $over_paid = $pendingamount->over_paid - $totalDue;                                  
                                                    $deductedDue = $totalDue;                                 
                                                } else {
                                                    $over_paid = 0;
                                                    $currentDue = $totalDue - $pendingamount->over_paid;                                    
                                                    $deductedDue = $pendingamount->over_paid;
                                                }
                                                // dd($customer->id,$pendingamount->id);
                                                $newCustomer = new CustomerPendingPayment();
                                                $newCustomer->over_paid             = $over_paid;
                                                $newCustomer->customer_id           = $pendingamount->customer_id;
                                                $newCustomer->current_due           = $currentDue;
                                                $newCustomer->deducted_over_paid    = $deductedDue;
                                                $newCustomer->bill_id               = NULL;//$customer->bill_id;
                                                $newCustomer->parent_id             = $pendingamount->id;
                                                $newCustomer->child_id              = $customer->instoreCreditParent->id ?? $customer->id;
                                                $newCustomer->validity_from         = $pendingamount->validity_from;
                                                $newCustomer->validity_to           = $pendingamount->validity_to;
                                                $newCustomer->gst_id                = $pendingamount->gst_id;
                                                $newCustomer->expiry_status         = $pendingamount->expiry_status;
                                                $newCustomer->is_billed             = 1; 
                                                $newCustomer->is_cron               = 1;
                                                $newCustomer->is_membership         = $pendingamount->is_membership;
                                                $newCustomer->membership_id         = $pendingamount->membership_id;
                                                $newCustomer->removed               = 0;
                                                $newCustomer->save();
                                            
                                                $totalDue -= $deductedDue;
                                            }
                                    
                                            $customer->removed = 1;  
                                            $customer->save();                                             
                                            $pendingamount->removed = 1;
                                            $pendingamount->save();   
                                            $billing=Billing::where('payment_status','!=',1)->find($customer->bill_id);                                            
                                            $billing_items=BillingItem::where('billing_id',$billing->id)->sum('discount_value');
                                            $deducted_instore=CustomerPendingPayment::where('bill_id',$billing->id)->where('customer_id',$billing->customer_id)->where('removed',0)->sum('deducted_over_paid');
                                            
                                            if($billing){                                                     
                                                $totalDeductedDue=$billing->actual_amount+ $deductedDue+$billing_items;                                                
                                                if((float)$billing->amount==$totalDeductedDue){
                                                    $billing->payment_status=1;           
                                                }else{
                                                    $billing->payment_status=3;
                                                }               
                                                $billing->actual_amount=$totalDeductedDue;
                                                // $billing->actual_amount=$billing->actual_amount;                                   
                                                $billing->save();   
                                               
                                            }
                                            $BillAmount=new BillAmount();
                                            $BillAmount->bill_id=$customer->bill_id;
                                            $BillAmount->billing_format_id=$billingFormat->id;
                                            $BillAmount->payment_type_id=3;
                                            $BillAmount->payment_type="In-store Credit";
                                            $BillAmount->amount=$deductedDue;
                                            $BillAmount->save();                                            
                                            $continueLoop = false; // Set the flag to false to exit the loop after processing the first item
                                        }
                                    });
                            }
                        }                
                }
             }
       
        
      }
}
