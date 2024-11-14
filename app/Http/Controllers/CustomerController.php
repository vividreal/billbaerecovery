<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Billing;
use Illuminate\Validation\Rule;
use App\Imports\CustomersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerReportExport;
use App\Helpers\FunctionHelper;
use App\Models\Shop;
use App\Models\Country;
use App\Models\Schedule;
use App\Models\CustomerPendingPayment;
use App\Models\GstTaxPercentage;
use DataTables;
use Validator;
use DB;
use App\Models\Service;
use App\Models\User;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentType;
use Illuminate\Support\Str;
use App\Models\District;
use App\Models\Package;
use App\Models\ActivityLog;
use App\Models\CallLog;
use App\Models\State;
use App\Models\RefundCash;
use App\Models\CustomerComment;
use App\Models\Membership;
use App\Models\BillingItem;
use App\Models\customerMemberships;
use Carbon\Carbon;

class CustomerController extends Controller
{
    protected $title    = 'Customer';
    protected $viewPath = 'customer';
    protected $route    = 'customers';
    protected $link     = 'customers';
    protected $entity   = 'customers';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Responseid
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->timezone     = Shop::where('user_id', auth()->user()->id)->value('timezone');
            $this->time_format  = (Shop::where('user_id', auth()->user()->id)->value('time_format') == 1)?'h':'H';
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

        $visitingStatusCounts = [
            'new' => 0,
            'regular' => 0,
            'vip' => 0,
            'occasional' => 0,
            'former' => 0,
            'weekdays' => 0,
        ];
        
        $customerLists = Customer::where('shop_id', SHOP_ID)->get();
        $regularCustomerCount = 0;
        
        // Loop through customers to calculate the count for each status
        foreach ($customerLists as $customerList) {
            $billings = Billing::where("customer_id", $customerList->id)->get();
            $billingCount = $billings->count();
        
            // Get billings from the last month
            $recentBillings = $billings->where('created_at', '>=', Carbon::now()->subMonth());
            $recentBillingCount = $recentBillings->count();
        
            // Get billings from the last 30 days
            $billingLast30Days = $billings->where('created_at', '>=', Carbon::now()->subDays(30));
            $billingCountLast30Days = $billingLast30Days->count();
        
            // Check if bills are only from weekdays
            $weekdaysBillingCount = $billings->filter(function ($billing) {
                $dayOfWeek = Carbon::parse($billing->created_at)->dayOfWeek;
                return $dayOfWeek != Carbon::SATURDAY && $dayOfWeek != Carbon::SUNDAY;
            })->count();
        
            // Determine the visiting status
            if ($billingCountLast30Days === 1) {
                // New Customer: 1 bill in the last 30 days
                $visitingStatusCounts['new']++;
            } elseif ($recentBillingCount >= 4) {
                // Regular Customer: 4 or more bills within the last month
                $visitingStatusCounts['regular']++;
                $regularCustomerCount++;
            } elseif ($recentBillingCount > 1 && $recentBillingCount > $regularCustomerCount) {
                // VIP Customer: More bills than regular customers
                $visitingStatusCounts['vip']++;
            } elseif ($recentBillingCount > 0) {
                // Occasional Visitor: At least 1 bill within the last month
                $visitingStatusCounts['occasional']++;
            } elseif ($billingCount === 0 || ($billings->last() && $billings->last()->created_at < Carbon::now()->subMonths(6))) {
                // Former Customer: No bill in the last 6 months or no recent bill
                $visitingStatusCounts['former']++;
            } elseif ($weekdaysBillingCount === $billingCount) {
                // Weekdays Customer: Bills only on weekdays
                $visitingStatusCounts['weekdays']++;
            }
        }
        
        // Output the counts for each visiting status
        $variants->visitingStatusCounts=$visitingStatusCounts;
         
        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Htt+p\Response
     */
    public function create()
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route; 
        $store                  = Shop::find(SHOP_ID);        
        $variants->phonecode    = Country::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->pluck('phone_code', 'id');                  
        return view($this->viewPath . '.create', compact('page', 'variants', 'store'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [ 'name' => 'required' ]);
    
        if ($validator->passes()) {
            try{
                
                $customer                   = new Customer();
                $customer->shop_id          = SHOP_ID;
                $customer->name             = $request->name;   
                $customer->gender           = $request->gender;
                $customer->dob              = date("Y-m-d", strtotime($request->dob));                
                $customer->mobile           = preg_replace('/\s+/', '', $request->mobile);               
                $customer->phone_code       = $request->phone_code;
                $customer->email            = $request->email;
                $customer->customer_code    = FunctionHelper::generateCustomerCode();
                $customer->save();
                $current='10';
            $activity_id=NULL;
            $previous=NULL;
            $schedule=NULL;
            $type=NULL;
            $billing=NULL;
            $comment='Customer Created';
            $customer=$customer->id;
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$billing,$comment,$type);
                return ['flagError' => false, "customer" => $customer, 'message' => $this->title. " added successfully", "reload" => false  ];
            }catch(\Exception $e){
                    dd($e->getMessage());
                }
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  Customer::where('shop_id', SHOP_ID)->orderBy('id', 'desc');
        if (isset($request->form)) {
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['value'] == 'deleted') {
                    $detail         = $detail->onlyTrashed();
                }
            }
        }
        
        if ($request->filled('behavioral_status')) {
            $detail->where('behavioral_status', $request->behavioral_status);
        }

        // Apply Visiting Status filter if provided
        if ($request->filled('visiting_status')) {
            $detail->where('visiting_status', $request->visiting_status);
        }
        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('status', function($detail) {
                if ($detail->deleted_at == null) {  
                    $status = '<span class="chip lighten-5 green green-text">Active</span>';
                } else {
                    $status = '<span class="chip lighten-5 red red-text">Removed</span>';
                }
                return $status;
            })
            ->editColumn('name', function($detail) {
                 $visiting_status   = '';
                 
                
                 if ($detail->behavioral_status!=NULL && $detail->behavioral_status == 0) {
                    $behavioral_status = '<span class="behavioral_status calm"></span>';
                } elseif ($detail->behavioral_status == 1) {
                    $behavioral_status = '<span class="behavioral_status neutral"></span>';
                } elseif ($detail->behavioral_status == 2) {
                    $behavioral_status = '<span class="behavioral_status dangerous"></span>';
                } elseif ($detail->behavioral_status == 3) {
                    $behavioral_status = '<span class="behavioral_status warning"></span>';
                } elseif ($detail->behavioral_status == 4) {
                    $behavioral_status = '<span class="behavioral_status alert"></span>';
                } elseif ($detail->behavioral_status == 5) {
                    $behavioral_status = '<span class="behavioral_status critical"></span>';
                }else{
                    $behavioral_status ='';
                }
                
                $html = '';
                // $html.=$visiting_status;
                $html .= '<a href="' . url($this->route. '/' . $detail->id) . '">'.$detail->name.'</a>';
                $html .=$behavioral_status;
                return $html;
            })
            ->editColumn('gender', function($detail) {
                if($detail->gender=='1'){
                    $gender='Male';

                }else if($detail->gender=='2'){
                    $gender='Female';
                }else{
                    $gender='Other';
                }
                
                return $gender;
            })
            ->editColumn('visiting_status', function($detail) {
                $behavioral_status = $detail->behavioral_status;               
                 if ($detail->visiting_status!=NULL && $detail->visiting_status == 0) {
                    $visiting_status = '<span class="">New Customer</span>';
                } elseif ($detail->visiting_status == 1) {
                    $visiting_status = '<span class="">Regular Customer</span>';
                } elseif ($detail->visiting_status == 2) {
                    $visiting_status = '<span class="">VIP Customer</span>';
                } elseif ($detail->visiting_status == 3) {
                    $visiting_status = '<span class="">Occasional Visitor</span>';
                } elseif ($detail->visiting_status == 4) {
                    $visiting_status = '<span class="">Former Customer</span>';
                } elseif ($detail->visiting_status == 5) {
                    $visiting_status = '<span class="">Week Days Customer</span>';
                }else{
                    $visiting_status="";
                }
                return $visiting_status;
            })
            ->editColumn('mobile', function($detail) {
                $phone_code     = (!empty($detail->phoneCode->phonecode) ? '+' .$detail->phoneCode->phonecode : '');
                $mobile         = (!empty($detail->mobile) ? $phone_code . ' ' . $detail->mobile:'');
                return $mobile;
            })
            ->addColumn('action', function($detail){
            if ($detail->deleted_at == null) {  
                $action      = '<a  href="' . url($this->route.'/' . $detail->id . '/edit') . '"" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                $action     .= '<a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="btn btn-danger btn-sm btn-icon mr-2 disable-item" title="Remove"><i class="material-icons">block</i></a>';
            } else {
                $action = ' <a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="btn mr-2 green darken-1 restore-item" title="Restore"><i class="material-icons">restore</i></a>';
                $action .= '<a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" data-type="delete" class="btn btn-danger btn-sm btn-icon mr-2 force-delete-item" title="Delete"><i class="material-icons">delete</i></a>';
            }
                return $action;
            })
            ->addColumn('create_bill', function($detail){
                $action = '<a href="' . url($this->route.'/create-bill/' . $detail->id ) . '"><div class="chip cyan white-text">Add New Bill</div></a>';
                return $action;
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true);                    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        if ($customer) { 
            $page                   = collect();
            $variants               = collect();
            $page->title            = $this->title;
            $page->link             = url($this->route);
            $page->route            = $this->route;               
            $last_activity          = Customer::lastActivity($customer->id);
            $completed_bills        = Customer::paymentHistory($customer->id, 1);
            $pending_bills          = Customer::paymentHistory($customer->id, 0);
            $partial_bills          = Customer::paymentHistory($customer->id, 3); 
            $variants->pendingBillTotal=$pending_bills->sum('amount') ?? '0.00';
            $variants->partialBillTotal= CustomerPendingPayment::where('customer_id',$customer->id)
            ->where('expiry_status',0)
            ->where('removed',0)->sum('current_due');
            $variants->completedBillTotal=Billing::whereIn('payment_status',[1,3,4])->where('customer_id',$customer->id)->sum('actual_amount') ?? '0.00';
           
            $memberships            = Membership::where('shop_id',SHOP_ID)->get();
            $customer_membership    = customerMemberships::where('customer_id',$customer->id)->get();
            $customerPendingLists   = CustomerPendingPayment::where('customer_id',$customer->id)
                                        ->where('expiry_status',0)->where('is_membership',1)
                                        ->where('removed',0)->get();
            $variants->lastOverPaidAmount=$customerPendingLists->sum('over_paid');
            $variants->totalDiscountAmount=BillingItem::where('customer_id',$customer->id)->where('is_discount_used',1)->sum('discount_value');
            $billings=Billing::where('customer_id',$customer->id)->where('payment_status',1)->get();
            $bill_items=collect();
            if($billings){
                foreach($billings as $billing){
                    $bill_items=BillingItem::where('customer_id',$billing->customer_id)->where('item_type','memberships')->get();
                }
                
            }     
            $variants->bill_items=$bill_items;       
            $variants->therapists=User::where('shop_id',SHOP_ID)->get();
            $variants->rooms=Room::where('shop_id',SHOP_ID)->get();
            $variants->services=Service::where('shop_id',SHOP_ID)->get();
            $variants->packages=Package::where('shop_id',SHOP_ID)->get();
            return view($this->viewPath . '.show', compact('page', 'variants', 'customer','customer_membership','last_activity', 'completed_bills', 'pending_bills', 'partial_bills','memberships'));
        } else {
            return redirect('customers')->with('error', $this->title.' not found');
        }  
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        if ($customer) { 
            $page                       = collect();
            $variants                   = collect();
            $page->title                = $this->title;
            $page->link                 = url($this->route);
            $page->route                = $this->route;
            $variants->time_picker  = ($this->time_format === 'h') ? false : true;
            $variants->time_format  = $this->time_format;
            $variants->countries        = Country::pluck('name', 'id');
            $variants->tax_percentage       = DB::table('gst_tax_percentages')->pluck('percentage', 'id');
            // $store                      = Shop::find(SHOP_ID);        
            $variants->phonecode        = Country::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->pluck('phone_code', 'id');
            if ($customer->country_id != null) {
                $variants->states       = DB::table('shop_states')->where('country_id', $customer->country_id)->pluck('name', 'id');
            } else {
                $variants->country_id   = '';
            }
            if ($customer->state_id != null) {
                $variants->districts   = DB::table('shop_districts')->where('state_id', $customer->state_id)->pluck('name', 'id');
            }
         
           
            return view($this->viewPath . '.edit', compact('page', 'customer' ,'variants'));
        } else {
            return redirect('customers')->with('error', $this->title.' not found');
        }   
    }

    /**
     * Update the specified resource in storage. 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required',
            // 'email'  => 'email|unique:customers,email,' . $id,
            // 'mobile' => 'unique:customers,mobile,' . $id,
        ]);
        $validator->sometimes('email', 'email|unique:customers,email,' . $id, function ($input) {
            return !empty($input->email);
        });
        
        $validator->sometimes('mobile', 'unique:customers,mobile,' . $id . ',id', function ($input) {
            return !empty($input->mobile);
        });
        $validityFrom =   Carbon::createFromFormat('d-m-Y H:i:s', $request->validity_from);
        $tempValidityFrom                       = $validityFrom->copy(); 
        $validity_to                            = $validityFrom->copy()->addDays($request->validity);
        // $validityTo = Carbon::createFromFormat('d-m-Y', $request->validity_to)->endOfDay();
        // $validity_from              = FunctionHelper::dateToTimeFormat($request->validity_from);
        // $validity_to                = FunctionHelper::dateToTimeFormat($request->validity_to);
       
       
        if ($validator->passes()) {
            $data                   = Customer::findOrFail($id);
            $data->name             = $request->name;
            $data->gender           = $request->gender;
            $data->dob              = date("Y-m-d", strtotime($request->dob));
            $data->mobile           = preg_replace('/\s+/', '', $request->mobile);
            $data->phone_code       = $request->phone_code;
            $data->country_id       = $request->country_id;
            $data->state_id         = $request->state_id;
            $data->district_id      = $request->district_id;
            $data->pincode          = $request->pincode;
            $data->gst              = $request->gst;            
            $data->email            = $request->email;
            $data->address          = $request->address;
            $data->is_instore_credit= $request->in_store_credit_radio;
            if( $data->customer_code== NULL){
                $data->customer_code    = FunctionHelper::generateCustomerCode();
            }            
            $data->save();
            // $customerInCredit                   = CustomerPendingPayment::where('customer_id',$id)->first();
            // $gst=GstTaxPercentage::where('id',$request->gst_tax)->first();
            // $instoreCreditAmount=$request->in_store_credit-(($request->in_store_credit *$gst->percentage) /100);
            $customerInCredit=new CustomerPendingPayment();
            if($request->in_store_credit_radio == 1){
                $customerInCredit->customer_id             =$id;
                $customerInCredit->current_due             =$customerInCredit->current_due ?? 0;
                $customerInCredit->deducted_over_paid      =$customerInCredit->deducted_over_paid;
                $customerInCredit->over_paid               =$request->in_store_credit ?? 0;
                $customerInCredit->validity_from           =$tempValidityFrom;
                $customerInCredit->validity_to             =$validity_to;
                $customerInCredit->validity                =$request->validity;
                $customerInCredit->gst_id                  =$request->gst_tax;
                $customerInCredit->expiry_status           =0;
                $customerInCredit->is_billed               =0;
                $customerInCredit->removed                 =0;
                $customerInCredit->amount_before_gst       =$request->in_store_credit;
            }else{
                $customerInCredit->over_paid               =0;
            }
            $customerInCredit->save();
       
            $current='11';
            $activity_id=NULL;
            $previous='10';
            $schedule=NULL;
            $type=NULL;
            $billing=NULL;
            $comment='Customer Updated';
            $customer=$id;
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$billing,$comment,$type);

            return ['flagError' => false, 'message' => $this->title. " updated successfully"];
        }
        return ['flagError' => true, 'message' => $validator->errors()->all(),  'error'=> $validator->errors()->all()];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer, Request $request)
    {
        if (!$customer->billings->isEmpty()) {
            $errors = array('Warning!, Cant Remove! Customer has billing information');
            return ['flagError' => true, 'message' => "Warning!, Cant Remove! Customer has billing information",  'error' => $errors];
        }
        $current='12';
            $activity_id=NULL;
            $previous='10';
            $schedule=NULL;
            $type=NULL;
            $billing=NULL;
            $comment='Customer Deleted';
            $customer=$id;
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$billing,$comment,$type);
        $customer->delete();
        return ['flagError' => false, 'message' => " Customer removed successfully"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function hardDelete($id, Request $request)
    {
        $customer   = Customer::where('id', $id)->withTrashed()->first();
        $customer->forceDelete();
        return ['flagError' => false, 'message' => " Customer permanently deleted"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function restore($id, Request $request)
    {
        $customer   = Customer::where('id', $id)->withTrashed()->first();
        $customer->restore();
        return ['flagError' => false, 'message' => " Customer restored successfully"];
    }

    /**
     * Return the list of specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function autocomplete(Request $request)
    {
        $data = array();
        $result   = Customer::select("customers.id", DB::raw("CONCAT(customers.name, ' - ', COALESCE(customers.mobile, '')) as name"))
                                ->where('shop_id', SHOP_ID)->where("name","LIKE","%{$request->search}%")
                                ->orWhere("mobile","LIKE","%{$request->search}%")->get();
        if ($result) {
            foreach($result as $row) {
                $data[] = array(['id' => $row->id, 'name' => $row->name]);
            }
        } else {
            $data = [];
        }
        return response()->json($result);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function import() 
    { 
        $import =  new CustomersImport;
        $import->import(request()->file('file'));
        return redirect('customers')->with('success', 'Customers Imported Successfully.');
    }

    public function exportReport(Request $request) 
    {
        // echo $request->customer_id;
        $data = [
            [
                'name' => 'Povilas',
                'surname' => 'Korop',
                'email' => 'povilas@laraveldaily.com',
                'twitter' => '@povilaskorop'
            ],
            [
                'name' => 'Taylor',
                'surname' => 'Otwell',
                'email' => 'taylor@laravel.com',
                'twitter' => '@taylorotwell'
            ]
        ];
        return Excel::download(new CustomerReportExport($data), 'users.csv');
    }


    public function billReport(Request $request, $id)
    {
        $detail     =  Billing::
                            // ->withTrashed()
                            // ->whereNotNUll('actual_amount')
                           with('schedule','items','customer')
                            ->where('customer_id', $id)
                            ->where('shop_id', SHOP_ID);
        // if( ($from != '') && ($to != '') ) {
        //     $detail->Where(function ($query) use ($from, $to) {
        //         $query->whereBetween('created_at', [$from, $to]);
        //     });
        // }
        if ($request['billing_code'] != '') {
            $billing_code    = $request['billing_code'];
            $detail         = $detail->where(function($query)use($billing_code){
                    $query->where('billing_code', 'like', '%'.$billing_code.'%');
            }); 
        }
        if ($request['payment_status'] != '') {
            $payment_status    = $request['payment_status'];
            if($payment_status!= '6'){           
                $detail         = $detail->where(function($query)use($payment_status){
                        $query->where('payment_status', $payment_status);
                }); 
            }
        }
        $detail = $detail->orderBy('created_at', 'DESC')->get();
        return Datatables::of($detail)
            ->addIndexColumn()
            ->editColumn('billed_date', function($detail) {
                return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-Y');
                // return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-Y '.$this->time_format.':i a');
            })
            ->editColumn('billing_code', function($detail) {
                $billing_code = '';
                // if($detail->payment_status==1){
                    $billing_code .=' <a href="' . url(ROUTE_PREFIX.'/billings/' . $detail->id) . '">'.$detail->billing_code.'</a>';
                // }else{
                //     $billing_code =  $detail->billing_code;
                // }
                return $billing_code ?? '--';
            })
            // ->editColumn('customer_id', function($detail){
            //     $customer = $detail->customer->name;
            //     return $customer;
            // })
            ->editColumn('amount', function($detail) {           
            $amount = $detail->actual_amount; 
            return $amount ?? "0.00";
            })
            ->editColumn('instore', function($detail) {
                $instoreCreditAmountPaid=0;
                $instoreCreditAmountPaid=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->where('over_paid','>',0)->sum('deducted_over_paid'); 
                return $instoreCreditAmountPaid ?? 0.00;
            })
            ->editColumn('discount', function($detail) {
                $discount_amount=0.00;
                foreach($detail->items as $item){
                    if($item->is_discount_used==1){
                        $discount_amount+=$item->discount_value;
                    }
                }
            return $discount_amount;
            })
            ->editColumn('actual_amount', function($detail) {
                $actual_amount = '';
                // $discount=0;
                // if($detail->items){
                //     $discount=$detail->items->sum('discount_value');
                // }
                $actual_amount = $detail->amount ;
                return $actual_amount ?? "0.00";
            })
            ->editColumn('payment_status', function($detail) {
                $status = '';
                $instoreCreditAmount=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->sum('deducted_over_paid');
                if ($detail->payment_status == 0) {
                    $status = '<a href="' . url(ROUTE_PREFIX.'/billings/invoice/' . $detail->id ) . '"" title="Pay Now"><span class="chip lighten-5 red red-text">Unpaid</span></a><br>';
                } elseif ($detail->payment_status == 2) {
                    $status = '<span class="chip lighten-5 orange orange-text">CANCELLED</span>';
                }else if ($detail->payment_status == 3) {
                    $discount=0;
                    if($detail->items){
                        $discount=$detail->items->sum('discount_value');
                     }
                    $instoreDueAmount=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->where('removed',0)->sum('current_due');
                    $instoreCreditAmount=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->where('is_membership',0)->sum('deducted_over_paid');
                    $pending_amount = $detail->actual_amount +$instoreCreditAmount;
                    // dd($detail->actual_amount ,$instoreCreditAmount);
                    $status         = '<span class="chip lighten-5 orange orange-text">Partially paid- ' . CURRENCY . ' ' . number_format($pending_amount, 2) .' </span><br>';
                    if($instoreDueAmount>0){
                        $status         .= '<span class="chip lighten-5 orange orange-text">Due- ' . CURRENCY . ' ' . number_format($instoreDueAmount, 2) .' </span><br>';
                    }
                    if($discount> 0){
                        $status .='<span class="chip lighten-5 orange orange-text">Discount- ' . CURRENCY . ' ' . number_format($discount, 2) .'  </span><br>';
                    }
                }
                elseif ($detail->payment_status == 5) {                   
                    $status = '<span class="chip lighten-5 cyan cyan-text">REFUNDED-'.CURRENCY.''. number_format( $detail->actual_amount, 2) .'</span>';
               
                }
                elseif ($detail->payment_status == 6) {
                    $customer_Paid=$detail->actual_amount-$detail->amount;
                    $status = '<span class="chip lighten-5 cyan cyan-text" style="width: max-content;">PARTIAL REFUND-'.CURRENCY.''. number_format( $customer_Paid, 2) .'</span>';
                    if($detail->items){
                        $discount=$detail->items->sum('discount_value');
                     }
                    if($discount> 0){
                        $status .='<br><span class="chip lighten-5 orange orange-text">Discount- ' . CURRENCY . ' ' . number_format($discount, 2) .' </span><br>';
                    }
                }
                elseif ($detail->payment_status == 1) {
                    $customer_Paid=$detail->amount;
                    $instoreCreditAmountPaid=0;
                    $additionalPaid=0;
                    $instoreDue_deducted=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->where('parent_id','!=',NULL)->where('child_id','!=',NULL)->where('removed',0)->sum('deducted_over_paid');
                    $instoreDue=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->where('removed',1)->sum('current_due');
                    $instoreAdditional_paid=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->where('removed',0)->first();
                    if($detail->parent_id!=NULL){
                        $additionalPaid=CustomerPendingPayment::where('bill_id',$detail->parent_id)->where('is_cancelled',0)->where('customer_id',$detail->customer_id)->where('removed',0)->where('parent_id',NULL)->where('child_id',NULL)->sum('amount_before_gst');
                    }
                    $instoreAdditional_paid_amount=0.00;
                    if($instoreAdditional_paid!==NULL){
                        if($instoreAdditional_paid->amount_before_gst!==NULL){
                            $instoreAdditional_paid_amount= $instoreAdditional_paid->amount_before_gst ?? 0.00;                       
                        }else{
                            $instoreAdditional_paid_amount= $instoreAdditional_paid->over_paid ?? 0.00;
                        }     
                    }  
                    $customer=CustomerPendingPayment::where('bill_id',$detail->id)->where('customer_id',$detail->customer_id)->first();
                    if($customer){
                        $instoreCreditAmountPaid=CustomerPendingPayment::where('bill_id','!=',$detail->id)->where('id',$customer->child_id)->where('customer_id',$detail->customer_id)->sum('current_due');
                    }                
                    $discount=0;
                    $status = '<span class="chip lighten-5 cyan cyan-text" style="width: max-content;">Full Payment-'.CURRENCY.''. number_format( $customer_Paid, 2) .' </span>';
                    if($detail->items){
                        $discount=$detail->items->sum('discount_value');
                    }
                    if($discount> 0){
                        $status .='<br><span class="chip lighten-5 orange orange-text">Discount- ' . CURRENCY . ' ' . number_format($discount, 2) .'  </span><br>';
                    }
                    if($additionalPaid >0){
                        $status .='<br><span class="chip lighten-5 orange orange-text">Additionally paid- ' . CURRENCY . ' ' . number_format($additionalPaid, 2) .'  </span><br>';
                    }
                    if($instoreDue_deducted >0){
                        $status .='<br><span class="chip lighten-5 orange orange-text">Due paid- ' . CURRENCY . ' ' . number_format($instoreDue_deducted, 2) .'  </span><br>';
  
                    }
                    if($instoreDue>0){
                        $status .='<br><span class="chip lighten-5 orange orange-text">Due- ' . CURRENCY . ' ' . number_format($instoreDue, 2) .'  </span><br>';

                    }
                    if( $instoreAdditional_paid_amount >0 &&  $instoreAdditional_paid->is_cancelled==0){
                       
                        $status .='<br><span class="chip lighten-5 orange orange-text">Additionally paid- ' . CURRENCY . ' ' . number_format($instoreAdditional_paid_amount, 2) .'  </span><br>';

                    }
                }
                elseif ($detail->payment_status == 4) {
                    $total=$instoreCreditAmount+$detail->actual_amount;
                    if ($detail->amount > $total) {
                        $additional_amount = $detail->amount - $detail->actual_amount;
                        $status = '<span class="chip lighten-5 green green-text">Over Paid- ' . CURRENCY . ' ' . number_format($additional_amount, 2) .'</span><br><span> amount added to In-store credit/Deducted from Dues </span><br>'; 
                    } else {
                        $status = '<span class="chip lighten-5 green green-text">Paid</span><br>'; 
                    }      
                    if($detail->items){
                        $discount=$detail->items->sum('discount_value');
                     }
                    if($discount> 0){
                        $status .='<br><span class="chip lighten-5 orange orange-text">Discount - ' . CURRENCY . ' ' . number_format($discount, 2) .' Paid </span><br>';
                    }                  
                }
                else{
                    $discount=0;
                    if($detail->items){
                       $discount=$detail->items->sum('discount_value');
                    }
                    
                    if($discount > 0){
                        $status = '<span class="chip lighten-5 green green-text">Discount- ' . CURRENCY . ' ' . number_format($discount, 2) .'</span><br><span> Paid </span><br>'; 

                    }
                }
                return $status;
            })
            ->addColumn('in_out_time', function($detail) {
                if($detail->schedule){
                $checkin_time   =  Carbon::parse($detail->schedule->start)->format($this->time_format.':i a');
                $checkout_time  =  Carbon::parse($detail->schedule->end)->format($this->time_format.':i a');
                $in_out_time    = $checkin_time . ' - ' . $checkout_time;
                }
                return $in_out_time ?? '--';
            })
            ->addColumn('payment_method', function($detail){
                $methods = '';

                if (in_array($detail->payment_status, [1, 3, 4, 5, 6])) {
                    $uniqueMethods = [];
                
                    foreach ($detail->paymentMethods as $row) {
                        if (!in_array($row->payment_type, $uniqueMethods)) {
                            $uniqueMethods[] = $row->payment_type;
                        }
                    }
                
                    // Join the unique payment types into a string
                    $methods = implode(', ', $uniqueMethods);
                }
                
                return empty($methods) ? '-' : $methods;
                
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true);
    }

    public function createBill(Request $request, $id)
    {
        $page                       = collect();
        $variants                   = collect();
        $user                       = Auth::user();
        $store                      = Shop::find($user->shop_id);
        $page->title                = $this->title;
        $page->link                 = url($this->link);
        $page->route                = $this->route;
        $page->entity               = $this->entity;       
        $customer                   = Customer::find($id);                                                                                                                      
        $variants->countries        = DB::table('shop_countries')->where('status',1)->pluck('name', 'id');          
        $variants->services         = Service::where('shop_id', SHOP_ID)->pluck('name', 'id');          
        $variants->packages         = Package::where('shop_id', SHOP_ID)->pluck('name', 'id');
        $variants->payment_types    = PaymentType::where('shop_id', SHOP_ID)->pluck('name', 'id');         
        $variants->time_picker      = ($this->time_format === 'h')?false:true;
        $variants->time_format      = $this->time_format;
        $variants->phonecode        = DB::table('shop_countries')->select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->pluck('phone_code', 'id');
        return view($this->viewPath . '.create-bill', compact('page', 'customer', 'variants', 'store'));
    }
    public function updateInstoreCredit(Request $request){
        try {
            $customer = Customer::findOrFail($request->input('customer_id'));
            $customer->is_instore_credit = 0;
            $customer->save();
            $customerCredit=CustomerPendingPayment::find($request->input('customer_id'));
            if ($customerCredit) {
                $customerCredit->over_paid = 0;
                $customerCredit->save();
            }
        
            return response()->json(['response' => 'Success']);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the customer is not found
            return response()->json(['error' => $e->getMessage()]);
        }
    

    }
    public function listInstoreCredit(Request $request){
        $deductedAmountTotal=0;
        $totalOverPaid      =0;

        if($request->ajax()){
            $detail= CustomerPendingPayment::where('customer_id',$request->customerId)                                           
                                            ->where('is_membership',0)   
                                            ->where('parent_id',NULL)                                  
                                            ->where('current_due',0)                                     
                                            ->orderBy('created_at','DESC')
                                            ->get();
        // Calculate total over_paid amount for rows with removed column 0
        if($detail->count()>0){
            $totalOverPaid = $detail->where('removed',0)->sum('over_paid');                  
        }
        // $detail=$detail->where('deducted_over_paid','!=',0)->where('over_paid','!=',0);
            return Datatables::of($detail)
            
            ->addIndexColumn()
            ->addColumn('start_date', function($detail) {
                return Carbon::parse($detail->validity_from)->format('d-m-Y');
            }) ->addColumn('end_date', function($detail) {
                return Carbon::parse($detail->validity_to)->format('d-m-Y');
            })->addColumn('credit_before_gst', function($detail) {
                return $detail->amount_before_gst ?? 0;
            })->editColumn('gst', function($detail) {
                return $detail->gst->percentage ?? 0 .'%';
            })->addColumn('balance', function($detail) {
                return number_format($detail->over_paid,2) ?? 0;
            }) ->addColumn('credit_used', function($detail) { 
                $creditUsed=0;
                if($detail->is_cancelled==0){
                    $creditUsed=$detail->deducted_over_paid;
                }
                return number_format($creditUsed,2) ?? 0.00;
            }) ->addColumn('balance_credit', function($detail) {                
                $balance = $detail->over_paid ?? 0;
                $balance_credit=0;
                if($detail->is_cancelled==0){
                $credit_used = $detail->deducted_over_paid ?? 0;
                $balance_credit = max(0, $balance - $credit_used); // Ensure balance credit is not negative
                }
                return number_format($balance_credit,2);
            }) ->addColumn('status', function($detail) {
                $status = ''; 
                if($detail->removed== 1){
                    $status='<span style=" background-color: red;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Removed</span>';
                }
                else if($detail->expiry_status== 0){
                    $status.='<span style=" background-color: green;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Active</span>';
                }else{
                    $status.='<span style=" background-color: red;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Expired</span>';
                }
                return $status;
            })->addColumn('action', function($detail) {
                $billing_code = '';
                // if($detail->bill->id){
                    // $billing_code .=' <a href="javascript:void(0);" id="' . $detail->id . '"  onclick="openOnstoreEditModal(this.id)" ><i class="material-icons">mode_edit</i></a>';
                    $billing_code .= ' <a href="' . route('customers.instoreCreditDetailedView', ['customerId' => $detail->customer_id,'id'=>$detail->id]) . '">VIEW</a>';
                // }
                return $billing_code;
            })->rawColumns(['status','action','credited_by'])
            ->setRowClass(function ($detail) {
                return $detail->removed == 1 ? 'strike-through' : '';
            })
            ->with(['totalOverPaid' => $totalOverPaid]) 
            ->make(true);
        }
    }
    // public function listInstoreCredit(Request $request){
    //     $deductedAmountTotal=0;
    //     $totalOverPaid      =0;

    //     if($request->ajax()){
    //         $detail= CustomerPendingPayment::where('customer_id',$request->customerId)
    //                                         ->where('over_paid','>',0)                                            
    //                                         // ->groupBy('bill_id')  
    //                                         ->where('is_membership',0)                                     
    //                                         ->orderBy('created_at','DESC')->get();
    //     // Calculate total over_paid amount for rows with removed column 0
    //     if($detail->count()>0){
    //         $totalOverPaid = $detail->where('removed',0)->sum('over_paid');                  
    //     }
    //     // $detail=$detail->where('deducted_over_paid','!=',0)->where('over_paid','!=',0);
    //         return Datatables::of($detail)
            
    //         ->addIndexColumn()
    //         ->addColumn('action', function($detail) {
    //             $billing_code = '';
    //             // if($detail->bill->id){
    //                 // $billing_code .=' <a href="javascript:void(0);" id="' . $detail->id . '"  onclick="openOnstoreEditModal(this.id)" ><i class="material-icons">mode_edit</i></a>';
    //                 $billing_code .= ' <a href="' . route('customers.instoreCreditDetailedView', ['customerId' => $detail->customer_id,'id'=>$detail->id]) . '">VIEW</a>';
    //             // }
    //             return $billing_code;
    //           }) ->addColumn('start_date', function($detail) {
    //             return Carbon::parse($detail->validity_from)->format('d-m-Y');
    //           }) ->addColumn('end_date', function($detail) {
    //             return Carbon::parse($detail->validity_to)->format('d-m-Y');
    //           }) ->addColumn('balance', function($detail) {
    //             return $detail->over_paid ?? 0;
    //           }) ->addColumn('credit_used', function($detail)use($deductedAmountTotal) {
                
    //             $instoreDeductedAmount=CustomerPendingPayment::where('parent_id',$detail->id)->where('deducted_over_paid', '>', 0)->first();
    //             if($instoreDeductedAmount){
    //                 $deducted_amount=$instoreDeductedAmount->deducted_over_paid ?? 0.00;
    //             }else{
    //             $instoreDeductedAmount=CustomerPendingPayment::where('id',$detail->id)->where('removed', 1)->first();

    //                 $deducted_amount=$instoreDeductedAmount->over_paid ?? 0.00;
    //             }
    //             return $deducted_amount ?? 0.00;
    //           })
    //           ->addColumn('balance_credit', function($detail)use($deductedAmountTotal) {
    //             $instoreDeductedAmount = CustomerPendingPayment::where('parent_id', $detail->id)->where('deducted_over_paid', '>', 0)->first();
    //             if($instoreDeductedAmount==NULL){
    //                 $instoreDeductedAmount = CustomerPendingPayment::where('id', $detail->id)->where('removed',1)->first();
    //                 // dd( $instoreDeductedAmount);
    //             }
    //             // Calculate the balance credit by subtracting credit used from balance
    //             $balance = $detail->over_paid ?? 0;
    //             $credit_used = $instoreDeductedAmount->deducted_over_paid ?? 0;
    //             $balance_credit = max(0, $balance - $credit_used); // Ensure balance credit is not negative
    //             return $balance_credit;
    //           })
    //           ->addColumn('credit_before_gst', function($detail) {
    //             return $detail->amount_before_gst ?? 0;
    //           })
    //           ->editColumn('gst', function($detail) {
    //             return $detail->gst->percentage ?? 0 .'%';
    //           })
              
    //           ->addColumn('status', function($detail) {
    //             if($detail->expiry_status== 0){
    //                 $status='<span style=" background-color: green;
    //                 color: white;
    //                 padding: 4px 8px;
    //                 text-align: center;
    //                 border-radius: 5px;">Active</span>';
    //             }else{
    //                 $status='<span style=" background-color: red;
    //                 color: white;
    //                 padding: 4px 8px;
    //                 text-align: center;
    //                 border-radius: 5px;">Expired</span>';
    //             }
    //             return $status;
    //           })
    //         ->rawColumns(['status','action','credited_by'])
    //         ->with(['totalOverPaid' => $totalOverPaid]) 
    //         ->make(true);
    //     }
    // }
    public function listMembershipInstoreCredit(Request $request) {
        $deductedAmountTotal=0.00;
        $totalOverPaid=0.00;
        $totalServicePrice=0.00;
        $totalCreditPrice=0.00;
        $customerId = $request->input('customerId');
        $membershipId = $request->input('membershipId');
        if($request->ajax()){
            $detail= CustomerPendingPayment::where('customer_id',$customerId)
                                            ->with('bill.schedule','bill','membership')
                                            ->where('over_paid','>',0)                                            
                                            // ->where('deducted_over_paid','>',0)                                            
                                            // ->groupBy('parent_id')                                            
                                            ->orderBy('created_at','ASC')
                                            ->where('is_membership',1)
                                            ;
            if ($membershipId) {
                $detail->where('membership_id', $membershipId);
            }
            $detail=$detail->get();
            if($detail->count()>0){               
                $totalOverPaid = $detail->where('removed',0)->sum('over_paid');                 
                $totalCreditPrice = $detail->sum('deducted_over_paid');     
                
                $totalServicePrice = $detail->filter(function ($item) {
                    return $item->bill != null;
                })->unique('bill_id')
                ->sum(function ($item) {
                    return optional($item->bill)->amount ?? 0;
                });
             
            }
            return Datatables::of($detail)            
            ->addIndexColumn()
                ->addColumn('banner', function($detail) {
                $banner = '';
                // Check if the membership relationship exists
                if ($detail->membership) {
                    $banner .= '<span>' . $detail->membership->name . '</span>';
                }
                return $banner;
            })
                ->addColumn('action', function($detail) {
                $billing_code = '';
                if(CustomerPendingPayment::where('parent_id',$detail->id)->first()){
                    // $billing_code .=' <a href="' . url(ROUTE_PREFIX.'/billings/' . $detail->bill->id) . '">'.$detail->bill->billing_code.'</a>';
                    $billing_code .= ' <a href="' . route('customers.instoreCreditDetailedView', ['customerId' => $detail->customer_id,'id'=>$detail->id]) . '">VIEW</a>';
                }
                return $billing_code;
              }) 
            //   ->addColumn('start_date', function($detail) {
            //     return isset($detail->bill->schedule->created_at) ? Carbon::parse($detail->bill->schedule->created_at)->format('d-m-Y') : Carbon::parse($detail->validity_from)->format('d-m-Y');
            //   }) 
            //   ->addColumn('end_date', function($detail) {
            //     return Carbon::parse($detail->validity_to)->format('d-m-Y');
            //   })
          
              ->addColumn('credit_used', function($detail)use($deductedAmountTotal) {
                // return $detail->deducted_over_paid;
                $instoreDeductedAmount=CustomerPendingPayment::where('parent_id',$detail->id)->first();
              
                return $detail->deducted_over_paid ?? 0;
              })
              ->addColumn('balance_credit', function($detail)use($deductedAmountTotal) {
                // dd($detail);
                $instoreDeductedAmount = CustomerPendingPayment::where('deducted_over_paid', '>', 0)->first();
                // Calculate the balance credit by subtracting credit used from balance
                // $balance = $detail->over_paid ?? 0;             
                // $credit_used = $detail->deducted_over_paid ?? 0;
                // $balance_credit = max(0, $balance - $credit_used); // Ensure balance credit is not negative
                return $detail->over_paid ?? '0.00';
              })
              ->addColumn('service', function($detail) {
                 if ($detail->bill && $detail->bill->items) {
                $item_array = [];
                foreach ($detail->bill->items as $item) {
                                if ($item->item_type != 'memberships') {
                        $item_array[] = $item->item->name;
                    }
                }
            
                // Convert the array into an HTML list if it's not empty
                if (!empty($item_array)) {
                    $item_list = '<ul>';
                    foreach ($item_array as $item) {
                        $item_list .= '<li>' . htmlspecialchars($item) . '</li>';
                    }
                    $item_list .= '</ul>';
                } else {
                    $item_list = '';
                }
            
                // Return the service name or the item list
                return isset($detail->bill->schedule->service->name) ? $detail->bill->schedule->service->name : $item_list;
            } else {
                return '-';
            }
              })
              ->editColumn('therapist', function($detail) {
                return $detail->bill->schedule->user->name ?? '-';
              })
              ->editColumn('service_price', function($detail) {
                static $processedBillIds = [];

            if ($detail->bill) {
                // Check if the bill_id has already been processed
                if (!in_array($detail->bill_id, $processedBillIds)) {
                    // Mark this bill_id as processed
                    $processedBillIds[] = $detail->bill_id;

                    // Return the amount or the item array
                    return isset($detail->bill->amount) ? $detail->bill->amount : (isset($item_array) ? $item_array : '');
                } else {
                    // Return an empty string or a placeholder for duplicate bill_ids
                    return '';
                }
            } else {
                return '-';
            }
                        })
            
             
            ->rawColumns(['action','banner','service'])
            ->with(['totalOverPaid' => $totalOverPaid,'totalServicePrice'=>$totalServicePrice,'totalCreditPrice'=>$totalCreditPrice]) 
            ->make(true);
        }
        
    }
    public function instoreCreditDetailedView(Request $request)
{
    $page = collect();
    $variants = collect();
    $page->title = $this->title;
    $page->link = url($this->route);
    $page->route = $this->route;

    // Retrieve customer pending payments with related bills (including soft-deleted)
    $customerInstoreCreditLists = CustomerPendingPayment::where('customer_id', $request->customerId)
        ->with('instoreCreditParent')
        ->with(['bill' => function ($query) {
            $query->withTrashed(); // Include soft-deleted related models
        }])
        ->where('id', $request->id)
        ->get();

    // Check if any bill is soft-deleted and fetch data from refund_cashes if needed
    $customerInstoreCreditLists->each(function ($item) {
        if ($item->bill && $item->bill->trashed()) {
            // If the bill is soft-deleted, fetch data from refund_cashes
            $refundCash = RefundCash::where('bill_id', $item->bill->id)->first();
            $item->refundCash = $refundCash;
        }
    });

    // Fetch instore credit paid details
    $instoreCreditPaid = CustomerPendingPayment::where('customer_id', $request->customerId)
        ->whereNull('bill_id')
        ->where('removed', 0)
        ->where('is_billed', 0)
        ->where('id', $request->id)
        ->get();

    // Pass the data to the view
    return view($this->viewPath . '.list_instore', compact('page', 'customerInstoreCreditLists', 'instoreCreditPaid'));
}

    public function listCustomerDues(Request $request){
        if($request->ajax()){
            $detail = CustomerPendingPayment::where('customer_id', $request->customerId)
            ->where('bill_id','!=',NULL)
            ->where(function ($query) {
                $query->where('current_due', '!=', 0);
                    //   ->orWhere('deducted_over_paid', '!=', 0);
            })
            // ->whereHas('bill', function($query) {
            //     $query->whereNull('deleted_at');
            // })
            ->orderBy('created_at','DESC')
            ->get();
                    return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('action', function($detail) {
                $customer_bill=CustomerPendingPayment::where('customer_id',  $detail->customer_id)->where('child_id',$detail->id)->first();
                $billing_code = '';
                $paid_billing_code='';
                if ($customer_bill && $customer_bill->bill) {
                    $paid_billing_code=$customer_bill->bill->billing_code;
                    
                    if($detail->bill){
                        $billing_code .=' <a href="' . url(ROUTE_PREFIX.'/billings/' . $customer_bill->bill_id) . '">'.$paid_billing_code ?? ''.'</a>';
                    }
                }else if($detail->bill_id==NULL){
                    $billing_code = 'In-store Payment'; 
                }
                return $billing_code;
              }) ->addColumn('start_date', function($detail) {
                return Carbon::parse($detail->created_at)->format('d-m-Y');
              }) ->addColumn('deducted_due', function($detail) {     
                    if($detail->current_due > 0 && $detail->removed==1){
                        // $deducted_amount=CustomerPendingPayment::where('child_id',$detail->id)->sum('deducted_over_paid');
                    }
                return $detail->deducted_over_paid ?? '';
              }) 
              ->addColumn('due', function($detail) { 
                    $currentDue=floatval($detail->current_due);
                   
                return $currentDue ?? '' ;
              }) 
              ->addColumn('invoice', function($detail) {
               $bill_code=$detail->bill()->withTrashed()->first();

                return  $bill_code->billing_code ?? '--';
              }) 
              ->addColumn('status', function($detail) {
                if($detail->removed==NULL){
                    $status='<span style=" background-color: orange;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">UnPaid</span>';
                }else{
                    $status='<span style=" background-color: green;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Paid</span>';
                }
                return $status;
              })
            ->rawColumns(['status','action'])
            ->make(true);
        }
    }
    public function listCustomerServices(Request $request){
        if ($request->ajax()) {         
            $detail = Schedule::with(['package', 'user', 'customer', 'room', 'billing' => function ($query) {
                $query->withTrashed();
            }])
            ->where('customer_id', $request->customerId)            
            // ->groupBy('billing_id','package_id')
            ->withTrashed()
            ->get();
            $packages = $detail->filter(function ($schedule) {
                return $schedule->item_type === 'packages';
            });
            $services = $detail->filter(function ($schedule) {
                return $schedule->item_type === 'services';
            });
    
            // Group packages by billing_id and package_id
            $groupedPackages = $packages->groupBy(function ($schedule) {
                return $schedule->billing_id . '-' . $schedule->package_id;
            })->map(function ($group) {
                return $group->first(); // Modify this if needed
            })->values();
            $processedSchedules = $groupedPackages->merge($services)->sortByDesc('created_at');
            $filteredSchedules = $processedSchedules->filter(function ($schedule) use ($request) {
                if ($request->has('therapist') && !empty($request->therapist)) {
                    if ($schedule->user_id != $request->therapist) {
                        return false;
                    }
                }
                if ($request->has('room') && !empty($request->room)) {
                    if ($schedule->room_id != $request->room) {
                        return false;
                    }
                }
                if ($request->has('service') && !empty($request->service)) {
                    if ($schedule->item_id != $request->service) {
                        return false;
                    }
                }
                if ($request->has('package') && !empty($request->package)) {
                    if ($schedule->package_id != $request->package) {
                        return false;
                    }
                }
            
                return true; // Keep the schedule if all conditions pass
            });
            return Datatables::of($filteredSchedules)
                ->addIndexColumn()
                ->addColumn('start_date', function($schedule) {
                    $checkin_time   =  Carbon::parse($schedule->start)->format($this->time_format.':i a');
                    $checkout_time  =  Carbon::parse($schedule->end)->format($this->time_format.':i a');
                    $in_out_time    = $checkin_time . ' - ' . $checkout_time;
                    return  Carbon::parse($schedule->start)->format('d-m-Y').'<br>'.$in_out_time;
                }) 
                ->addColumn('type', function($schedule) {  
                    return $schedule->item_type;
                }) 
                ->addColumn('type_name', function($schedule) { 
                    if ($schedule->item_type == 'packages') {
                        return $schedule->package->name ?? '';
                    } else {
                        return $schedule->service->name;
                    }
                }) 
                ->addColumn('therapist', function($schedule) {  
                    return $schedule->user->name ?? '';
                }) 
                ->addColumn('room', function($schedule) {  
                    return $schedule->room->name ?? '';
                }) 
                ->addColumn('paid_amount', function($schedule) { 
                    if ($schedule->item_type == 'packages') {
                        return $schedule->package->price ?? '';
                    } else {
                        return $schedule->service->price;
                    }
                }) 
                ->addColumn('status', function($schedule) {
                    $canceled_status='';
                    $status='';
                    if($schedule->deleted_at==NULL){
                        if($schedule->checked_in==0){
                            $status='<span style=" background-color: red;
                            color: white;
                            padding: 4px 8px;
                            text-align: center;
                            border-radius: 5px;">Not Checked In</span>';
                        }else{
                            $status='<span style=" background-color: green;
                            color: white;
                            padding: 4px 8px;
                            text-align: center;
                            border-radius: 5px;">Checked In</span>';
                        }
                    }
                    if($schedule->deleted_at){
                        $canceled_status='<span style=" background-color: orange;
                        color: white;
                        padding: 4px 8px;
                        text-align: center;
                        border-radius: 5px;">Canceled</span>';
                    }
                    return $status.$canceled_status;
                })
                ->addColumn('payment_status', function($schedule) {
                    if($schedule->billing){
                    switch ($schedule->billing->payment_status) {
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
                            return '<span class="chip lighten-5 cyan cyan-text">PARTIAL REFUND</span>';
                        default:
                            return '<span class="chip lighten-5 blue blue-text">ADDITIONALLY PAID</span>';
                    }
                }
                })
                ->rawColumns(['status', 'payment_status','canceled_status','start_date'])
                ->make(true);
        }
        
    }

    public function reviewAboutCustomer(Request $request) {
       
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->subtitle         = 'show';
        $page->link             = url($this->route);
        $page->sublink          = route('customers.show',['customer'=>$request->customerId]);
        $page->route            = $this->route;
        $customer =Customer::find($request->customerId);  
        $customerComments =CustomerComment::where('customer_id',$request->customerId)->orderBy('created_at','DESC')->get();
        $variants->getHistory   =ActivityLog::with('billing','schedule')-> where('customer_id',$request->customerId)->orderBy('created_at','DESC')->get();
    //    dd($variants->getHistory);
        $variants->calllogs=CallLog::orderBy('created_at','DESC')->get();
        return view($this->viewPath.'.customer_comment',compact('page','customer','customerComments','variants'));
    }
    public function storeComment(Request $request) {
      
        try {
            $rules = [
                'customerId' => 'required|exists:customers,id',
                'title' => 'required',
                'comment' => 'required',
                
            ];
        
            // Custom error messages
            $messages = [
                'customerId.required' => 'Customer ID is required.',
                'customerId.exists' => 'Invalid customer ID.',
                'title.required' => 'Title is required.',
                'comment.required' => 'Comment is required.',
               

            ];
        
            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $messages);
        
            // Check if validation fails
            if ($validator->fails()) {
                return ['flagError' => true, 'message' => $validator->errors()->first()];
            }
        $customer =new CustomerComment();
        $customer->customer_id  = $request->customerId;
        $customer->title = $request->title;
        $customer->comment = $request->comment;
      
        $customer->save(); 
        return ['flagError' => false, 'message' => " Customer Comment Added successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
        
    }
    public function deleteComment(Request $request) {
        try{
            CustomerComment::find($request->commentId)->delete();
            return ['flagError' => false, 'message' => "Comment Deleted successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
       
    }
    public function editComment(Request $request) {

        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $customer =Customer::find($request->customerId);
        $customerComment =CustomerComment::find($request->commentId);
        return view($this->viewPath.'.comment_edit',compact('page','customer','customerComment'));
    }
    public function updateComment(Request $request) {
        try {
            $rules = [
                'customerId' => 'required|exists:customers,id',
                'title' => 'required',
                'comment' => 'required',
                
            ];
        
            // Custom error messages
            $messages = [
                'customerId.required' => 'Customer ID is required.',
                'customerId.exists' => 'Invalid customer ID.',
                'title.required' => 'Title is required.',
                'comment.required' => 'Comment is required.',
               
            ];
        
            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $messages);
        
            // Check if validation fails
            if ($validator->fails()) {
                return ['flagError' => true, 'message' => $validator->errors()->first()];
            }
            $customerComment = CustomerComment::findOrFail($request->commentId);
            $customerComment->title = $request->title;
            $customerComment->comment = $request->comment;
            $customerComment->save(); 
            return ['flagError' => false, 'message' => "Customer Comment updated successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
    }
    public function storeCallLog(Request $request) { 
            $rules = [
                'customerId' => 'required|exists:customers,id',
                'call_log_time' => 'required',
                'call_log_date' => 'required',
                'log_comment' => 'required',
                'call_log_text'=>'required'
            ];
        
            // Custom error messages
            $messages = [
                'customerId.required' => 'Customer ID is required.',
                'customerId.exists' => 'Invalid customer ID.',
                'call_log_time.required' => 'Time is required.',
                'call_log_date.required' => 'Time is required.',
                'log_comment.required' => 'Comment is required.',
                'call_log_text.required'=>'Title required',
            ];
        
            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $messages);
        
            // Check if validation fails
            if ($validator->fails()) {
                return ['flagError' => true, 'message' => $validator->errors()->first()];
            }
            try {
               
            $customer =new CallLog();
            $customer->customer_id   = $request->customerId;
            $customer->title         = $request->call_log_text;
            $customer->customer_logs = $request->log_comment ;
            $customer->call_time     = $request->call_log_time;
            $customer->add_call_log_date     = $request->call_log_date;
        
            $customer->save(); 
            $customer_list=Customer::find($request->customerId);
            $customer_list->visiting_status     =$request->visiting_status;
            $customer_list->behavioral_status   =$request->behavioral_status;
            $customer_list->save();
        return ['flagError' => false, 'message' => " Customer CallLog Added successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
    }
    public function deleteCallLog(Request $request) {
        try{
            CallLog::find($request->callogs)->delete();
            return ['flagError' => false, 'message' => "CallLog Deleted successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
       
    }
    public function editlogs(Request $request) {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        
        $customerLog            =CallLog::find($request->logId);
        $customer               =Customer::find($customerLog->customer_id);
        return view($this->viewPath.'.call_log_edit',compact('page','customer','customerLog'));
    }
    public function updateCallLog(Request $request) {
        try {
            $rules = [
                'customerId' => 'required|exists:customers,id',                
                'log_comment' => 'required',
                'call_log_text'=>'required'
                
            ];
        
            // Custom error messages
            $messages = [
                'customerId.required' => 'Customer ID is required.',
                'customerId.exists' => 'Invalid customer ID.',              
                'log_comment.required' => 'Comment is required.',
                'call_log_text.required'=>'Title is Required'
               
            ];
        
            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $messages);
        
            // Check if validation fails
            if ($validator->fails()) {
                return ['flagError' => true, 'message' => $validator->errors()->first()];
            }
            $customerComment = CallLog::findOrFail($request->callLogId);
            $customerComment->customer_id   = $request->customerId;
            $customerComment->title         = $request->call_log_text;
            $customerComment->customer_logs = $request->log_comment ;
            $customerComment->call_time     = $request->call_log_time;
            $customerComment->add_call_log_date = $request->call_log_date; // Update this line
            $customerComment->save(); 
            $customer_list=Customer::find($request->customerId);
            $customer_list->visiting_status     =$request->visiting_status;
            $customer_list->behavioral_status   =$request->behavioral_status;
            $customer_list->save();
            return ['flagError' => false, 'message' => "Customer CallLog Updated Successfully"];
        } catch (\Exception $e) {
            return ['flagError' => true, 'message' => $e->getMessage()];
        }
    }
    public function createCallLog(Request $request) {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $customers =Customer::where('shop_id',SHOP_ID)->get();  
        return view($this->viewPath.'.create_call_log',compact('page','customers'));
        
    }
    public function getCustomerCallLog(Request $request,$id) {
        $CallLogs=CallLog::where('customer_id',$id)->get();
        if($CallLogs){
            return ['flagError' => false, 'data' => $CallLogs];
        }
        
    }
    public function SubmitCustomerMemership(Request $request)
    {
        if ($request->customerId && $request->membership_id) {
            try {
                DB::beginTransaction();        
                $customer = Customer::find($request->customerId);
                $membership = Membership::find($request->membership_id);
        
                if (!$customer || !$membership) {
                    throw new \Exception('Customer or Membership not found');
                }
        
                $customer->is_membership_holder = 1;
                $customer->save();
        
                $durationType = $membership->duration_type;
                $durationCount = $membership->duration_in_days;
        
                $today = now();
                $customerMembership = new CustomerMemberships();
                $customerMembership->customer_id = $request->customerId;
                $customerMembership->membership_id = $request->membership_id;
                $customerMembership->start_date = $today;
                $customerMembership->end_date = Carbon::parse($customerMembership->start_date)->addUnit($durationType, $durationCount);
                $customerMembership->expiry_status = 0;
                $customerMembership->save();
        
                
                $next_day = Carbon::parse($today)->addUnit($durationType, $durationCount);
                $customer_instore_credit = CustomerPendingPayment::create([
                    'customer_id' => $request->customerId,
                    'current_due' => 0,
                    'over_paid' => $membership->membership_price,
                    'deducted_over_paid' => 0,
                    'expiry_status' => 0,
                    'is_membership'=>1,
                    'gst_id' => $membership->gst_id,
                    'validity_from' => $today,
                    'validity_to' => $next_day,
                    'amount_before_gst' => $membership->membership_price,
                    'bill_id' => null,
                    'is_billed' => 0,
                    'removed' => 0,
                ]);
        
                DB::commit();
        
                return ['flagError' => false, 'message' => "Customer successfully selected Membership"];
            } catch (\Exception $e) {
                DB::rollBack();
                return ['flagError' => true, 'message' => $e->getMessage()];
            }
        }
       
    }
    public function getInstoreData(Request $request) {
       if($request->instore_id){
        $customerInstoreCredit=CustomerPendingPayment::find($request->instore_id);
        return array('flagError' => false, 'view' => view('customer.manage', compact('customerInstoreCredit'))->render());
       }

    }
    public function editInstoreData(Request $request) {
        try{
          
            $validityFrom = Carbon::createFromFormat('d-m-Y H:i:s', trim($request->validity_from));
            $tempValidityFrom                       = $validityFrom->copy(); 
            $validity_to                            = $validityFrom->copy()->addDays($request->validity);
            $customerInstoreCredit                  = CustomerPendingPayment::find($request->instore_id);
            $customerInstoreCredit->over_paid       = $request->in_store_credit;
            $customerInstoreCredit->validity_from   = $tempValidityFrom;
            $customerInstoreCredit->validity_to     = $validity_to;
            $customerInstoreCredit->validity        = $request->validity;
            $customerInstoreCredit->save();
            return ['flagError' => false, 'data' => 'Succesfully Updated!'];
        }catch(\Exception $e){
            return ['flagError' => true, 'data' =>$e->getMessage()];
        }
        
    }
    public function cancelledBillReport(Request $request) {
        if ($request->ajax()) {
            // Get the total refund amount and actual amount per bill_id
            $totals = RefundCash::where('customer_id', $request->customer_id)
                ->select('bill_id')
                ->selectRaw('SUM(amount) as total_refund_amount')
                ->selectRaw('SUM(actual_amount) as total_actual_amount')
                ->groupBy('bill_id')
                ->get()
                ->keyBy('bill_id');
    
            // Fetch details with payment types
            $detail = RefundCash::with('paymentType')
                ->where('customer_id', $request->customer_id)
                ->orderBy('updated_at', 'DESC')
                ->groupBy('bill_id')
                ->get()
                ->each(function($item) use ($totals) {
                    $item->total_refund_amount = $totals[$item->bill_id]->total_refund_amount ?? 0;
                    $item->total_actual_amount = $totals[$item->bill_id]->total_actual_amount ?? 0;
                });
    
            // Group payment types by bill_id
            $paymentTypes = RefundCash::where('customer_id', $request->customer_id)
                ->with('paymentType')
                ->get()
                ->groupBy('bill_id')
                ->map(function($group) {
                    return $group->pluck('paymentType.name')->unique()->implode(', ');
                });
    
            return Datatables::of($detail)
                ->addIndexColumn()
                ->addColumn('billing_code', function($detail) {
                    return '<a href="' . route('cancelBillInvoice', ['billing' => $detail->id]) . '">' . $detail->billing_code . '</a>';
                })
                ->addColumn('start_date', function($detail) {
                    return $detail->created_at->format('Y-m-d');  // Return formatted date if available
                })
                ->addColumn('actual_amount', function($detail) {
                    return $detail->total_actual_amount ?? '0.00';
                })
                ->addColumn('customer_paid', function($detail) {
                    return $detail->billings->amount ?? '0.00';
                })
                ->addColumn('refund_amount', function($detail) {
                    return $detail->total_refund_amount ?? '0.00';
                })
                ->editColumn('cancellation_fee', function($detail) {
                    $duePayment=CustomerPendingPayment::where('bill_id',$detail->bill_id)->where('current_due','>',0)->where('removed',0)->first();
                    $duePaymentDue=$duePayment->current_due ?? 0;
                    if($duePaymentDue>0){
                        $cancellation_fee=$detail->actual_amount-$detail->amount-$duePaymentDue;
                    }else{
                        $cancellation_fee = $detail->total_actual_amount - $detail->total_refund_amount;
                    }
                    return $cancellation_fee;
                })
                ->addColumn('payment_method', function($detail) use ($paymentTypes) {
                    return $paymentTypes[$detail->bill_id] ?? '';
                })
                ->addColumn('status', function($detail) {                   
                    if($detail->total_refund_amount==0){
                        $status='<span style="background-color: red; color: white; padding: 4px 8px; text-align: center; border-radius: 5px;">Cancelled</span>';
                    }else{
                        $status= '<span style="background-color: red; color: white; padding: 4px 8px; text-align: center; border-radius: 5px;">Refunded</span>';
                    }
                    return $status;
                })
                ->rawColumns(['status', 'billing_code'])
                ->make(true);
        }
    }
    
    public function getHistory(Request $request,$id){
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $variants->customer     =Customer::find($id);
        $variants->getHistory   =ActivityLog:: where('customer_id',$id)->orderBy('created_at','DESC')->get();
        return view('customer.history',compact('page','variants'));
    }
    public function customerStatusFilter(Request $request){
        $visitingStatusCounts = [
            'new' => 0,
            'regular' => 0,
            'vip' => 0,
            'occasional' => 0,
            'former' => 0,
            'weekdays' => 0,
        ];

        $customerLists = Customer::where('shop_id', SHOP_ID)->get();
        $regularCustomerCount = 0;

        // Convert request dates to Carbon instances for easier manipulation
        $fromDate = Carbon::createFromFormat('Y-m-d', $request->fromDate)->startOfDay();
        $toDate = Carbon::createFromFormat('Y-m-d', $request->toDate)->endOfDay();
        foreach ($customerLists as $customerList) {
            // Get billings for the current customer within the date range
            $billings = Billing::where("customer_id", $customerList->id)
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->get();
            
            $billingCount = $billings->count();
            
            // Get billings from the last 30 days (relative to the request date)
            $billingLast30Days = Billing::where("customer_id", $customerList->id)
                ->whereBetween('created_at', [$toDate->subDays(30), $toDate])
                ->get();
            $billingCountLast30Days = $billingLast30Days->count();

            // Get billings from the last month (relative to the request date)
            $recentBillings = Billing::where("customer_id", $customerList->id)
                ->whereBetween('created_at', [$toDate->subMonth(), $toDate])
                ->get();
            $recentBillingCount = $recentBillings->count();
            
            // Check if all bills are from weekdays within the specified date range
            $weekdaysBillings = Billing::where("customer_id", $customerList->id)
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->get()
                ->filter(function ($billing) {
                    $dayOfWeek = Carbon::parse($billing->created_at)->dayOfWeek;
                    return $dayOfWeek != Carbon::SATURDAY && $dayOfWeek != Carbon::SUNDAY;
                });
            $weekdaysBillingCount = $weekdaysBillings->count();
            
            // Determine the visiting status
            if ($billingCountLast30Days === 1) {
                // New Customer: 1 bill in the last 30 days
                $visitingStatusCounts['new']++;
            } elseif ($recentBillingCount >= 4) {
                // Regular Customer: 4 or more bills within the last month
                $visitingStatusCounts['regular']++;
                $regularCustomerCount++;
            } elseif ($recentBillingCount > 1 && $recentBillingCount > $regularCustomerCount) {
                // VIP Customer: More bills than regular customers
                $visitingStatusCounts['vip']++;
            } elseif ($recentBillingCount > 0) {
                // Occasional Visitor: At least 1 bill within the last month
                $visitingStatusCounts['occasional']++;
            } elseif ($billingCount === 0 || ($billings->last() && $billings->last()->created_at < $fromDate->subMonths(6))) {
                // Former Customer: No bills in the last 6 months or no recent bill
                $visitingStatusCounts['former']++;
            } elseif ($weekdaysBillingCount === $billingCount) {
                // Weekdays Customer: Bills only on weekdays
                $visitingStatusCounts['weekdays']++;
            }
        }

        return ['flagError' => false, 'data' => $visitingStatusCounts];
        
    }

    
}