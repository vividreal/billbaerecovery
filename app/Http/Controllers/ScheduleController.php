<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Billing;
use App\Models\Customer;
use App\Models\StaffProfile;
use App\Models\ScheduleColor;
use App\Models\Shop;
use App\Models\Room;
use App\Models\Package;
use App\Models\Service;
use Validator;
use App\Helpers\FunctionHelper;
use App\Models\BillingItem;
use App\Models\PackageService;
use App\Models\BillingItemTax;
use App\Models\BillAmount;
use App\Models\PaymentType;
use App\Models\CustomerPendingPayment;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Session;
use App\Models\ScheduleStatus;
class ScheduleController extends Controller
{

    protected $title        = 'Schedule';
    protected $viewPath     = 'schedule';
    protected $route        = 'schedules';
    protected $timezone     = '';
    protected $time_format  = '';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
            $this->middleware(function ($request, $next) {
            $this->timezone     = Shop::where('user_id', auth()->user()->id)->value('timezone');
            $this->time_format  = (Shop::where('user_id', auth()->user()->id)->value('time_format') == 1) ? 'h' : 'H';
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $mode)
    {       
       
        if (in_array($mode, array("therapists", "rooms"))) {
            
            $user_id                    = auth()->user()->id;
            $page                       = collect();
            $variants                   = collect();
            $page->title                = $this->title;
            $page->link                 = url($this->route);
            $page->route                = $this->route;
            $variants->time_picker      = ($this->time_format === 'h') ? false : true;
            $variants->time_format      = $this->time_format;
            $variants->timezone         = $this->timezone;
            $variants->scheduleColors   = ScheduleColor::pluck('name', 'color');;
            $variants->customers        = Customer::where('shop_id', SHOP_ID)->pluck('name', 'id');
            $variants->therapists       = StaffProfile::with('user')->whereIn('designation', [1, 2])->whereHas('user', function ($query) {
                                                $query->where('shop_id', SHOP_ID);
                                            })
                                            ->get()
                                            ->pluck('user.name', 'user.id');        
                                           
            $variants->rooms            = Room::where('shop_id', SHOP_ID)->pluck('name', 'id');
            $variants->paymentTypes    = PaymentType::get();
            $variants->services        = Service::where('shop_id', SHOP_ID)->pluck('name', 'id');
           
            $schedule_data              = Schedule::get(['id', 'user_id as resourceId', 'start', 'end', 'name as title', 'description','payment_status','checked_in']);
            
            // $sales_report               = Billing::select(DB::raw("SUM(amount) as amount"), 'id as row_id', 'payment_status')
                                        // ->where('shop_id', SHOP_ID)->where('payment_status', 1)
                                        // ->whereDate('created_at', Carbon::today())->groupBy(DB::raw("day(created_at)"))
                                        // ->get()->toArray();
            $sales_report               =Billing::select(DB::raw("SUM(amount) as amount"), 'id as row_id', 'payment_status')
                                        ->where('shop_id', SHOP_ID)
                                        ->where('payment_status', 1)
                                        ->whereDate('created_at', Carbon::today())
                                        ->groupBy(DB::raw("day(created_at)"), 'id', 'payment_status')
                                        ->get()
                                        ->toArray();
            return view($this->viewPath . '.create', compact('page', 'variants', 'schedule_data', 'sales_report', 'mode'));
        }
        abort(404);
    }

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function bookings(Request $request)
    {
        
        $resourceFiled  = ($request->resource_val == 'rooms') ? 'room_id as resourceId' : 'user_id as resourceId';
        $data           = Schedule::with(['room', 'user','package'])
        //->whereDate('start', '>=', $request->start)
         //   ->whereDate('end', '<=', $request->end)
            ->where('shop_id', SHOP_ID)
            ->get(['id', $resourceFiled, 'start', 'end','package_id', 'name as title', 'description', 'schedule_color as color', 'room_id', 'user_id','payment_status','checked_in']);
        // echo "<pre>"; print_r($data); exit;
        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function getListByRoom(Request $request)
    {
        $data = Schedule::with('room')
        //->whereDate('start', '>=', $request->start)->whereDate('end', '<=', $request->end)
            ->where('shop_id', SHOP_ID)
            ->get(['id', 'room_id as resourceId', 'start', 'end', 'name as title', 'description', 'schedule_color as color', 'room_id','checked_in']);
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $currentTime             = Carbon::now();
        $currentTimeUtc          = $currentTime->setTimezone('Asia/Kolkata');
        $currentTime12HourFormat = $currentTimeUtc->format('Y-m-d H:i:s A');
        $startTime               = Carbon::createFromFormat('d-m-Y g:i A', $request->start_time, 'Asia/Kolkata');
        $formattedStartDateTime  = $startTime->format('Y-m-d H:i:s A');
       
        // if($request->checked_in!=1){            
        //     if($formattedStartDateTime < $currentTime12HourFormat){               
        //         return ['flagError' => true, 'message' => "Scheduling not allowed at this time. Please schedule after {$currentTime12HourFormat}."];
        //     }
        // }
        // if($formattedStartDateTime < $currentTime12HourFormat){
        //     return ['flagError' => true, 'message' => "Scheduling not allowed at this time. Please schedule after {$currentTime12HourFormat}."];
        // }      
        // Need to be updated
        // $is_therapist_available  = Schedule::checkTherapistAvailability($request->all());      
        // if (count($is_therapist_available) > 0) {
        //     return ['flagError' => true, 'message' => "Therapist is not available now. Please select another Another Therapist !"];
        // }
        // $is_room_available  = Schedule::checkRoomAvailability($request->all()); 
        // if (count($is_room_available) > 0) {
        //     return ['flagError' => true, 'message' => "Room is already booked. Please select another Another Room !"];
        // }
        // $is_available  = Schedule::checkTimeAvailability($request->all()); 
        // if (count($is_available) > 0) {
        //     return ['flagError' => true, 'message' => "The selected customer is already booked for this slot. Please select another Time slot !"];
        // }
       
        $action = '';
        DB::beginTransaction();

        try {
            $rules = [
                'customer_name' => 'required',
                'service_type' => 'required',
                'bill_item' => 'required|array|min:1',
                'bill_item.*' => 'required'

            ];
            $validator = Validator::make($request->all(), $rules);
            // $validator = Validator::make($request->all(), ['customer_name' => 'required','bill_item'=>'required','service_type'=>'required']);   
            if ($validator->passes()) {   
                if ($request->customer_id == null) {
                        $customer                   = new Customer();
                        $customer->shop_id          = SHOP_ID;
                        $customer->name             = $request->customer_name;
                        $customer->mobile           = $request->mobile;
                        $customer->email            = $request->email;
                        $customer->save();
                        $activity_id=NULL;
                        $previous=NULL;
                        $current='10';
                        $type='customer';
                        $schedule=NULL;
                        $bill=NULL;
                        $comment='Customer Created';
                        
                        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer->id,$schedule,$bill,$comment,$type);

                        $request['customer_id']     = $customer->id;
                
                } else {     
                    $customerOpenBill = Customer::checkPendingBill($request->customer_id);   
                    $existingSchedules=Schedule::where('customer_id',$request->customer_id)->where('payment_status',0)->get();
                    $billingIds=[];
                    foreach($existingSchedules as $key=>$existingSchedule){
                        $billingIds[$key]=$existingSchedule->billing_id;
                    }
                    
                    if (count($customerOpenBill) > 0) {       
                        $message    = "";
                        $old_items  =[];                       
                        if($request->service_type==1){
                            $serviceCategory='services';
                        }elseif($request->service_type==2){
                            $serviceCategory='packages';
                        }
                        $isServiceCategoryFound=0;
                        $serviceTypes       =BillingItem::where('customer_id',$request->customer_id)->whereIn('billing_id',$billingIds)->where('item_type',$serviceCategory)->get();
                        
                        if($serviceTypes->count()>0){
                            $billing            = Billing::findOrFail($serviceTypes[0]->billing_id); 
                            
                            foreach($serviceTypes as $key=>$service){
                                if($service->item_type==$serviceCategory){
                                    $isServiceCategoryFound=++$key;
                                }
                            }         
                              
                            foreach ($billing->items as $key => $row) {
                                $old_items[$key] = $row->item_id;
                            }                         
                        }
                                        
                        foreach ($request->bill_item as $key => $row) {
                            $new_items[$key] = $row;                   
                        }  
                        // $new_items = array_map('intval', $new_items);
                        // $more_items     = array_diff($new_items,$old_items); 
                        // if (count($more_items) > 0) {
                            if(count($customerOpenBill) > 0 && $isServiceCategoryFound >0){  
                               
                                $schedules                  = Schedule::where('customer_id',$billing->customer_id)->where('checked_in', 1)->whereDate('start',now())->get();
                                if(count($schedules)){
                                    $request->checked_in=1;
                                }
                                $newItems   = BillingItem::addMore( $billing->id, $new_items, $request);
                                $schedule   = Schedule::addMore( $billing->id, $new_items, $request);
                                if ($request->checked_in == 1) {
                                    Schedule::where('customer_id', $billing->customer_id)->whereDate('start', now())->update(['schedule_color' => ($billing->payment_status == 1) ? "green" : "orange", "checked_in" => 1]);
                                }      
                                if ($newItems && $schedule) {             
                                    $message    = "New items added to the existing schedule.";
                                }
                               
                                DB::commit();
                             return ['flagError' => false, 'redirect' => 'reload', 'billing_id' => $customerOpenBill[0]->id, 'message' => $message];
                            }
                           
                        // } else {
                        //     $message    = "Selected Service is already existing in an open schedule.";
                        // }
                       
                    } 
                  
                }
            } else {
                return ['flagError' => true, 'message' => $validator->errors()->all(),  'error' => $validator->errors()->all()];
            }
            // Generate Bill

           
            if ($request->schedule_id == null) {          
                $billing    = Billing::generateBill($request->all());
                $schedule   = new Schedule();              
               
            } else {
                $schedule   = Schedule::find($request->schedule_id);
                $billing    = Billing::updateBill($request->all(), $schedule->billing_id);
                $current=1;
                $previous=0;
                $type='schedule';
                $comment='Schedule Updated';
                FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer->id,$schedule->id,$billing->id ,$comment,$type);
                $action     = 'updated';
            }
            
            // Calculate Total time for each Packages
            $items_details  = array();
            $total_time     = 0;
            $itemsCount     = 1;
            // $itemsCount     = [];
        
            // Number of items selected
            
            if(isset($request->items)){
                foreach ($request->items as $key => $item) {
                    $newArr             = array_keys($item);
                    $itemsCount[str_replace(' ', '', $key)]   = $newArr[0];
                }
            }
            if ($request->service_type == 1) {  
                foreach ($request->bill_item as $key => $item) {
                    $items_details              = Service::getTimeDetails($item); 
                  
                    if ($key === 0) {
                        $formatted_start_date   = new Carbon($request->start_time);
                        $start_time             = $request->start_time;                       
                    }
                    $total_time                 = $items_details['total_minutes'];                                     
                    $start_time                 = new Carbon($request->start_time);                   
                    $total_time *= $itemsCount;
                    $end_time = $start_time->copy()->addMinutes($total_time);
                    // $end_time                   = $formatted_start_date->addMinutes($total_time * $itemsCount[$item]);
                    // Create Schedule
                    $schedule                   = new Schedule();
                    $schedule->name             = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $start_time->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
                    $schedule->start            = $start_time;
                    $schedule->end              = $end_time;
                    $schedule->user_id          = $request->user_id;
                    $schedule->customer_id      = $billing->customer->id;
                    $schedule->billing_id       = $billing->id;
                    $schedule->item_id          = $item;
                    $schedule->item_type        = "services";
                    $schedule->room_id          = $request->room_id;
                    $schedule->description      = $items_details['description'] . 'Nos: ' . $itemsCount;
                    $schedule->total_minutes    = $items_details['total_minutes'];
                    $schedule->shop_id          = SHOP_ID;
                    $schedules                  = Schedule::where('customer_id',$billing->customer_id)->where('checked_in', 1)->whereDate('start',$start_time)->get();
                    if(count($schedules) > 0 && $billing->customer_id== $request->customer_id){
                        $schedule->schedule_color   = "orange" ;
                        $schedule->checked_in       = 1 ;
                    }else{
                        $schedule->schedule_color   = "#FF5733";
                        $schedule->checked_in       = 0;
                    }
                    $schedule->save();
                    $current='0';
                    $activity_id=NULL;
                    $previous=NULL;
                    $type='schedule';
                    $comment='Schedule Created';
                    $customer=$billing->customer_id;
                    FunctionHelper::statusChangeHistory($activity_id=0, $previous, $current,$customer,$schedule->id,$billing->id,$comment,$type);
                    $action     = 'created';
                    
                     
                }
                
            } else {
              
                foreach ($request->bill_item as $key => $item) {
                    $items_details                  = Package::getScheduleDetails($request->bill_item);
                   
                    if ($key === 0) {
                        $formatted_start_date   = new Carbon($request->start_time);
                        $start_time             = $request->start_time;
                    }
                    $start_time                 = new Carbon($request->start_time);                    
                    if(isset($itemsCount[$item])){
                        $itemsCount=$itemsCount[$item];
                    }else{
                        $itemsCount=1;
                    }
                    // Create Schedule
                    $packages=PackageService::where('package_id',$item)->get();                    
                    foreach($packages as $package){                      
                        $items_details              = Service::getTimeDetails($package->service_id); 
                        
                        $description = $items_details['description'];
                        $pos = strpos($description, ' - ');
                        $items_details['description'] = $pos !== false ? substr($description, 0, $pos) : $description;                    
                        $total_time                 = $items_details['total_minutes'];    
                        $total_time *= $itemsCount;
                        $end_time = $start_time->copy()->addMinutes($total_time);  

                        $schedule                   = new Schedule();
                        $schedule->name             = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $start_time->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
                        $schedule->start            = $start_time;
                        $schedule->end              = $end_time;
                        $schedule->user_id          = $request->user_id;
                        $schedule->customer_id      = $billing->customer->id;
                        $schedule->billing_id       = $billing->id;
                        $schedule->item_id          = $package->service_id;
                        $schedule->package_id       = $package->package_id;
                        $schedule->item_type        = "packages";
                        $schedule->room_id          = $request->room_id;  
                        $schedule->description      = $items_details['description'] .'</br>'. 'Nos: ' . $itemsCount;
                        $schedule->total_minutes    = $items_details['total_minutes'];
                        $schedule->shop_id          = SHOP_ID;
                        $schedule->schedule_color   = ($request->checked_in == 1) ? "orange" : "#FF5733";
                        $schedule->checked_in       = ($request->checked_in == 1) ? 1 : 0;
                        $schedule->save();
                    }
                    // Re-Initialize Items
                    $start_time                 = $end_time;
                    $schedule                   = new Schedule();
                }
                $description                    = $items_details['description'];
            }
            $redirect = ($request->receive_payment == 1) ? 'redirect' : 'reload';
            DB::commit();
            if ($schedule) {
                return ['flagError' => false, 'redirect' => $redirect, 'billing_id' => $billing->id, 'message' => "Schedule " . $action . " successfully"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            return ['flagError' => true, 'message' =>$e->getMessage()];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $schedule   = Schedule::find($id);
        $item_count = BillingItem::where('billing_id', $schedule->billing_id)->where('item_id', $schedule->item_id)->value('item_count');
        if ($schedule) {
            $start_time = new Carbon($schedule->start);
            $item_ids   = [];
            if (isset($schedule->billing->items) && count($schedule->billing->items) > 0) {
                foreach ($schedule->billing->items as $item) {
                    if ($item->item_type == 'packages') {
                        $itemId = $item->package_id;                       
                    } else {
                        $itemId = $item->item_id;
                    }
                    if (!in_array($itemId, $item_ids)) {
                        $item_ids[] = $itemId;
                    }
                }
                // $type = $schedule->billing->items[0]->item_type;
            } 
            // else {
                $type =$schedule->item_type;
            // }
            return response()->json([
                'flagError' => false,
                'data' => $schedule,
                'item_count' => $item_count,
                'customer_name' => $schedule->customer->name,
                'type' => $type, // Use the defined type here
                'start_formatted' => $start_time->format('d-m-Y ' . $this->time_format . ':i A'),
                'item_ids' => $item_ids
            ]);
        } else {
            return response()->json(['flagError' => true, 'data' => null]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
       
    }

    public function reSchedule(Request $request)
    {
        $currentTime             = Carbon::now();
        $currentTimeUtc          = $currentTime->setTimezone('Asia/Kolkata');
        $currentTime12HourFormat = $currentTimeUtc->format('Y-m-d H:i:s A');
        $start_date              = new Carbon($request->start_time);
        $formattedStartDateTime  = $start_date->format('Y-m-d H:i:s A');
        $schedule                = Schedule::find($request->schedule_id);
       
       
        $item_count = BillingItem::where('billing_id', $schedule->billing_id)
                                ->where('customer_id', $schedule->customer_id)
                                ->where('item_id', $schedule->item_id)
                                ->value('item_count');
        if($item_count==0){
            $item_count =1;
        }
        $customer   = Customer::find($schedule->customer_id);
        $formatted_start_date       = new Carbon($request->start_time);
        $start_date                 = new Carbon($request->start_time);
        $end_time                   = $formatted_start_date->addMinutes($schedule->total_minutes * $item_count);
        $schedule->name             = $customer->name . ' - ' . $customer->mobile . ' : ' . $start_date->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
        $schedule->start            = $request->start_time;
        $schedule->end              = $end_time;
        if ($request->mode == 'therapists') {
            $schedule->user_id          = $request->update_id;
        } else {
            $schedule->room_id          = $request->update_id;
        }
        if ($start_date->format('Y-m-d') > Carbon::now()->format('Y-m-d')) {
            $schedule->schedule_color   = "#FF5733";
            $schedule->checked_in       = 0;
            $request->checked_in        = 3;

        }else{                     
            if( $start_date->toDateString()==now()->toDateString()){
                $checkedInCustomerCount     = Schedule::where('customer_id',$schedule->customer_id)->whereDate('start',now())->where('checked_in',1)->get();
                if( $checkedInCustomerCount->count() >0){
                    $schedule->schedule_color   = 'orange';
                    $schedule->checked_in       = 1;
                    $request->checked_in = 1;
                }                       
            }                             
        }   
        $schedule->save();
        Schedule::where('customer_id',$schedule->customer_id)->whereIn('payment_status',[1,3,4,5,6])->update(['schedule_color'=>'green']);
        $current='6';
        $activity_id=NULL; 
        $previous=NULL;
        $type='schedule';
        $comment='Service Rescheduled';
        $billing=NULL;
        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer->id,$schedule->id,$billing,$comment,$type);
        if ($schedule) {
            return ['flagError' => false, 'message' => "Schedule updated successfully"];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Schedule $schedule)
    {   
        DB::beginTransaction();

        try {
            $customer                   = Customer::find($schedule->customer_id);  
            $billing                    = Billing::find($schedule->billing_id);      
            $new_items                  =[];
            $itemsCount                 =1;
            $currentTime             = Carbon::now();
            $currentTimeUtc          = $currentTime->setTimezone('Asia/Kolkata');
            $currentTime12HourFormat = $currentTimeUtc->format('Y-m-d H:i:s A');
            $startTime               =Carbon::createFromFormat('d-m-Y g:i A', $request->start_time, 'Asia/Kolkata');
            $formattedStartDateTime  = $startTime->format('Y-m-d H:i:s A');
            $current_date = Carbon::now()->toDateString();
           
            $allNull = array_reduce($request->bill_item, function($carry, $item) {
                return $carry && is_null($item);
            }, true);
            if ($allNull) {
                $request->bill_item=$request->item_ids;
                
            }
            if($request->receive_payment!=1){
                // if($schedule->checked_in==1){
                //     return ['flagError' => true, 'message' => "Rescheduling is not permitted once you have checked in; please provide a new prompt for a fresh response !"];
                // }
                // if($request->checked_in==1){
                //     if($formattedStartDateTime > $currentTime12HourFormat){
                //         $formattedStartDateTime = $startTime->format('Y-m-d g:i:s A');
                //         return ['flagError' => true, 'message' => "Unable to check in now. Please check-in on or  after {$formattedStartDateTime}."];
                //     }
                // }
                // if($request->checked_in!=1){                
                //     if($formattedStartDateTime < $currentTime12HourFormat){               
                //         return ['flagError' => true, 'message' => "Scheduling not allowed at this time. Please schedule after {$currentTime12HourFormat}."];
                //     }
                // }
            
                //  Need to be updated
                // $is_room_available  = Schedule::checkRoomAvailability($request->all()); 
                // $is_therapist_available  = Schedule::checkTherapistAvailability($request->all()); 
                //     if (count($is_room_available) > 0 || count($is_therapist_available) > 0) {
                //         return ['flagError' => true, 'message' => "The selected room or therapist is not available for the selected time slot."];
                //     }
                // $is_available  = Schedule::checkTimeAvailability($request->all()); 
                // if (count($is_available) > 0) {
                //     return ['flagError' => true, 'message' => "The selected customer is already booked for this slot. Please select another Time slot !"];
                // }
                if(isset($request->items)){
                    foreach ($request->items as $key => $item) {
                        $newArr             = array_keys($item);
                        $itemsCount[str_replace(' ', '', $key)]   = $newArr[0];
                    }
                }
                if(isset($request->bill_item)){
                    foreach ($request->bill_item as $key => $row) {
                        $new_items[$key] = $row;
                    }
                }
                if ($request->service_type == 1) {
                    $oldBillItem      = BillingItem::where('billing_id',$billing->id)->where('item_id',$schedule->item_id)->first();
                    $billingItems     = BillingItem::where('billing_id',$billing->id)->where('item_id',$schedule->item_id)->forceDelete(); // Removed when schedule update
                    $newItems         = BillingItem::updateWithNewItems($schedule->billing_id, $new_items, $request,$oldBillItem);
                
                    foreach ($request->bill_item as $key => $item) {
                        $items_details              = Service::getTimeDetails($item);
                        if ($key === 0) {
                            $formatted_start_date   = new Carbon($request->start_time);
                            $start_time             = $request->start_time;
                        }
                        $total_time                 = $items_details['total_minutes'];
                        $start_time                 = new Carbon($request->start_time);
                        $total_time *= $itemsCount;
                        $end_time                   = $start_time->copy()->addMinutes($total_time);
                        $formatted_start_time       = $start_time->format('h:i A');
                        $formatted_end_time         = $end_time->format('h:i A');                                    
                    
                        Schedule::where('id',$request->schedule_id)->forceDelete();                                      
                        $schedule = new Schedule();                    
                        $schedule->name             = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $formatted_start_time . ' - ' . $formatted_end_time;
                        $schedule->start            = $start_time;
                        $schedule->end              = $end_time;
                        $schedule->user_id          = $request->user_id;
                        $schedule->customer_id      = $billing->customer->id;
                        $schedule->billing_id       = $billing->id;
                        $schedule->item_id          = $item;
                        $schedule->payment_status   = $billing->payment_status;
                        $schedule->item_id          = $item;
                        $schedule->item_type        = "services";
                        $schedule->room_id          = $request->room_id;
                        $schedule->description      = $items_details['description'] . 'Nos: ';
                        $schedule->total_minutes    = $items_details['total_minutes'];;
                        $schedule->shop_id          = SHOP_ID;
                        $start_time                 = $end_time;
                    
                        if ($start_time->format('Y-m-d') > Carbon::now()->format('Y-m-d')) {
                            $schedule->schedule_color   = "#FF5733";
                            $schedule->checked_in       = 0;
                            $request->checked_in        = 3;

                        }else{                     
                            if( $start_time->toDateString()==$current_date && $request->reschedule_status){
                                $checkedInCustomerCount     = Schedule::where('customer_id',$schedule->customer_id)->whereDate('start',$current_date)->where('checked_in',1)->get();
                                if( $checkedInCustomerCount->count() >0){
                                    $schedule->schedule_color   = $checkedInCustomerCount[0]->schedule_color;
                                    $schedule->checked_in       = $checkedInCustomerCount[0]->checked_in;
                                    $request->checked_in = 1;
                                }                       
                            }                             
                        }                   
                        if (in_array($billing->payment_status, [1, 3, 4, 5, 6])) {
                            $schedule->schedule_color   = "green";
                        }
                        $schedule->save();   
                    } 
                    $current='1';
                    $activity_id=NULL;
                    $previous='0';
                    $customer=$billing->customer_id;
                    $type='schedule';
                    $comment='Schedule Updated';
                    FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing,$comment,$type);
                    if ($request->checked_in == 1) {  
                        $checkedIn  = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', $current_date)->update(['schedule_color' =>  "orange", "checked_in" => 1]);
                        $current='3';                       
                        $activity_id=NULL;
                        $previous='4';
                        $customer=$billing->customer_id;
                        $type='schedule';
                        $comment='Customer Check-in';
                        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing,$comment,$type);
                        $checkedIn  = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', $current_date)->whereIn('payment_status',[1,3,4,5,6])->update(['schedule_color' =>  "green"]);

                    }
                    else{
                        if($request->checked_in == null){
                            $current='4';                       
                            $activity_id=NULL;
                            $previous='3';
                            $customer=$billing->customer_id;
                            $type='schedule';
                            $comment='Customer Checkout';
                            FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing,$comment,$type);
                            $checkedOut = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', $current_date)->update(['schedule_color' => "#FF5733", "checked_in" => 0]);
                        }
                        
                        $checkedIn  = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', $current_date)->whereIn('payment_status',[1,3,4,5,6])->update(['schedule_color' =>  "green"]);
                    }
                    $billingItems   = BillingItem::where('billing_id',$billing->id)->where('item_type','services')->with('item')->get();
                    $totalAmount=0;
                    foreach($billingItems as $billingItem){
                        $totalAmount +=$billingItem->item->price * $billingItem->item_count;
                    }
                    $billing->amount=$totalAmount;
                    $billing->save();
                } 
                else {   
                    $itemsCount                 = 1;
                    $start_time                 = Carbon::parse($request->start_time);      
                    $items_details              = Service::getTimeDetails($schedule->item_id); 
                    $total_time                 = $items_details['total_minutes']; 
                    $total_time *= $itemsCount;
                    $end_time = $start_time->copy()->addMinutes($total_time);
                    $existingSchedules = Schedule::where('billing_id', $request->bill_id)
                    ->where('customer_id', $request->customer_id)
                    ->whereIn('package_id', $request->bill_item)
                    ->where('item_id', $request->service_id)
                    ->get();
                    if ($existingSchedules->isEmpty()) {
                        Schedule::where('billing_id', $request->bill_id)
                        ->where('customer_id', $request->customer_id)->forceDelete();
                    }
                    $existingPackageIds = $existingSchedules->pluck('package_id')->toArray();            
                    foreach ($request->bill_item as $bill_item) {                             
                        if (in_array($bill_item, $existingPackageIds)) {                    
                            $existingSchedule = $existingSchedules->where('package_id', $bill_item)->first();                              
                            if ($existingSchedule) {
                                $items_details = Service::getTimeDetails($existingSchedule->item_id);
                                $total_time = $items_details['total_minutes'] * $itemsCount;
                                $end_time = $start_time->copy()->addMinutes($total_time);
                    
                                $existingSchedule->start = $start_time;
                                $existingSchedule->end = $end_time;
                                $existingSchedule->user_id = $request->user_id;
                                $existingSchedule->room_id = $request->room_id;
                                if ($start_time->format('Y-m-d') > Carbon::now()->format('Y-m-d')) {
                                    $existingSchedule->schedule_color = "#FF5733";
                                    $existingSchedule->checked_in = 0;
                                } else {                               
                                    if ($start_time->toDateString()==$current_date && $request->reschedule_status) {
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
                                if (in_array($billing->payment_status, [1, 3, 4, 5, 6])) {
                                    $existingSchedule->schedule_color = "green";
                                }
                                $existingSchedule->save();
                            }
                        } else {                        
                            $packages = PackageService::where('package_id', $bill_item)->get();            
                            foreach ($packages as $package) {
                                $items_details = Service::getTimeDetails($package->service_id);
                                $description = $items_details['description'];
                                $pos = strpos($description, ' - ');
                                $items_details['description'] = $pos !== false ? substr($description, 0, $pos) : $description; 
                                $total_time = $items_details['total_minutes'] * $itemsCount;
                                $end_time = $start_time->copy()->addMinutes($total_time);                    
                                $newSchedule = new Schedule();
                                $newSchedule->name = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $start_time->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
                                $newSchedule->start = $start_time;
                                $newSchedule->end = $end_time;
                                $newSchedule->user_id = $request->user_id;
                                $newSchedule->customer_id = $billing->customer->id;
                                $newSchedule->billing_id = $billing->id;
                                $newSchedule->item_id = $package->service_id;
                                $newSchedule->package_id = $package->package_id;
                                $newSchedule->item_type = "packages";
                                $newSchedule->room_id = $request->room_id;
                                $newSchedule->description = $items_details['description'] .'</br>'. 'Nos: ' . $itemsCount;
                                $newSchedule->total_minutes = $items_details['total_minutes'];
                                $newSchedule->shop_id = SHOP_ID;
                                $newSchedule->schedule_color = "#FF5733";
                                $newSchedule->checked_in = 0;
                    
                                // Handle schedule_color and checked_in based on conditions
                                if ($start_time->isFuture()) {
                                    $newSchedule->schedule_color = "#FF5733";
                                    $newSchedule->checked_in = 0;
                                } else {
                                    if ($start_time->isToday() && $request->reschedule_status) {
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
                    
                                if (in_array($billing->payment_status, [1, 3, 4, 5, 6])) {
                                    $newSchedule->schedule_color = "green";
                                }
                    
                                $newSchedule->save();
                            }
                        }
                    }
                    // Update billing items after schedules have been updated/added
                    $oldBillItems      = BillingItem::where('billing_id',$billing->id)->where('package_id',$request->bill_item)->get();
                    $billingItems = BillingItem::where('billing_id', $billing->id)->whereIn('package_id',$request->bill_item)->forceDelete();
                    $newItems = BillingItem::updateWithNewItems($request->bill_id, $request->bill_item, $request->all(),$oldBillItems);               
                    if ($request->checked_in == 1  ) {  
                        $current='3';
                        $type='schedule';
                        $activity_id=NULL; 
                        $previous='4';
                        $customer=$billing->customer_id;
                        $comment='Customer Checked In';
                        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing,$comment,$type);
                        $checkedIn  = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', $current_date)->update(['schedule_color' =>  "orange", "checked_in" => 1]);
                        if (in_array($billing->payment_status, [1, 3, 4, 5, 6])) {
                            $checkedIn  = Schedule::where('billing_id', $billing->id)->update(['schedule_color' =>  "green"]);
                        }
                    }
                    else{
                        if($request->checked_in == null){
                        $checkedOut = Schedule::where('customer_id', $billing->customer_id)->whereDate('start', $current_date)->update(['schedule_color' => "#FF5733", "checked_in" => 0]);
                        }
                    if (in_array($billing->payment_status, [1, 3, 4, 5, 6])) {
                            $checkedIn  = Schedule::where('billing_id', $billing->id)->update(['schedule_color' =>  "green"]);
                        }
                    }
                    $billingItems   = BillingItem::where('billing_id',$billing->id)->with('package')->groupBy('billing_id', 'package_id')->get();
                    $totalAmount=0;
                    foreach($billingItems as $billingItem){
                        $totalAmount +=$billingItem->package->price;                    
                    }

                    $billing->amount=$totalAmount;
                    $billing->save();
                
                }
            }
        DB::commit();
        if($request->receive_payment== 1){
            DB::commit();
            return ['flagError' => false, 'redirect' => 'refresh','payment_status'=>$billing->status, 'billing_id' => $billing->id, 'message' => "Schedule updated successfully"];
        }else{
            return ['flagError' => false, 'redirect' => 'submit ','payment_status'=>$billing->status, 'billing_id' => $billing->id, 'message' => "Schedule updated successfully"];
        }
    }catch(\Exception $e){
        DB::rollback();
        dd($e);
    }
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * 
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
       
        if($schedule->item_type=='packages'){
            $flag = false;
            $schedules=Schedule::where('package_id',$schedule->package_id)->where('customer_id',$schedule->customer_id)->where('billing_id',$schedule->billing_id)->get();
            $items = BillingItem::with('package')->where('billing_id', $schedule->billing_id)->where('package_id',$schedule->package_id)->get();
            $itemCount=$items->count();            
            foreach ($items as $key=> $item) {
                    $flag = true;
                    $item->delete();
                    $itemCount--;            
            }              
           $billing = Billing::where('id',$schedule->billing_id)->first();
           $customer=$billing->id; 
            $instorePayments=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('removed',0)->get();
            if(count($instorePayments)==0){
                if ($billing) {
                    if ($billing->actual_amount !== null) {
                        $billing->actual_amount -= $schedule->package->price;
                    }
                    $billing->save();
                }
            }
            foreach($schedules as $schedule){
                $schedule->delete();
            }
          
            
        }else {
            $flag = false;        
            $items = BillingItem::where('billing_id', $schedule->billing_id)->get();
            $itemCount=$items->count();
            foreach ($items as $item) {
                if ($item->item_id == $schedule->item_id) {
                    $flag = true;
                    $billing = Billing::where('id',$item->billing_id)->first();
                    $customer=$billing->id; 
                    $instorePayments=CustomerPendingPayment::where('customer_id',$billing->customer_id)->where('bill_id',$billing->id)->where('removed',0)->get();
                    if(count($instorePayments)==0){
                        if ($billing) {
                            if ($billing->amount !== null) {
                                $billing->amount -= $item->item->price;
                            }
                            $billing->save();
                        }
                    }
                    
                    $item->delete();
                    $itemCount--;                    
                }                
            }  
            $schedule->delete();      
           
        }
        $pendingSchedules=Schedule::where('customer_id',$schedule->customer_id)->where('billing_id',$schedule->billing_id)->count();
        // $customerId=$pendingSchedules[0]->customer_id;
        if($pendingSchedules== 0){
            if($itemCount ==0){
               
                Billing::deleteBill($schedule->billing_id);
            }
            if (!$flag) {                
                Billing::deleteBill($schedule->billing_id);
            }
        }
        $current='2';
        $activity_id=NULL;
         $previous=NULL;
        $type='schedule';
        $comment='Schedule Cancelled';
        $customer=$schedule->customer_id;
        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing,$comment,$type);
        return ['flagError' => false, 'message' => "Cancellation Successful"];
        
    }

    public function listCustomerSchedules(Request $request) {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $page->top_search       = 1;      
        if($request->ajax()){
            $detail = Schedule::with('package','user','customer','room','billing')->orderBy('created_at','DESC')->withTrashed() ->groupBy('billing_id','package_id');
            return Datatables::of($detail)
            ->addIndexColumn()
             ->addColumn('date', function($detail) {
                $checkin_time   =  Carbon::parse($detail->start)->format($this->time_format.':i a');
                $checkout_time  =  Carbon::parse($detail->end)->format($this->time_format.':i a');
                $in_out_time    = $checkin_time . ' - ' . $checkout_time;
                return  Carbon::parse($detail->start)->format('d-m-Y').'<br>'.$in_out_time;
       
              }) 
              ->addColumn('type', function($detail) {                
                return $detail->item_type;
              }) 
          
              ->addColumn('customer', function($detail) {     
                if($detail->customer){
                    return $detail->customer->name ?? '';
                }     
              }) 
              ->addColumn('therapist', function($detail) {     
                if($detail->user){
                    return $detail->user->name ?? '';
                } 
              }) 
              ->addColumn('schedule', function($detail) {   
                if ($detail->item_type == 'packages') {
                    return $detail->package->name ?? '';
                } else {
                    return $detail->service->name;
                }
                    // return $detail->description ?? '';              
              }) 
              ->addColumn('room', function($detail) {   
                return $detail->room->name ?? '';          
             }) 
              ->addColumn('status', function($detail) {
                $canceled_status='';
                $status='';
                if($detail->deleted_at==NULL){
                    if($detail->checked_in == 0){
                        $status='<span style=" background-color: #3f51b5;
                        color: white;
                        padding: 4px 8px;
                        text-align: center;
                        border-radius: 5px;">Not Checked-in</span>';
                    }else{
                        $status='<span style=" background-color: green;
                        color: white;
                        padding: 4px 8px;
                        text-align: center;
                        border-radius: 5px;">Checked-in</span>';
                    }
                }
                if($detail->deleted_at){
                    $canceled_status='<span style=" background-color: orange;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Canceled</span>';
                }
                return $status.$canceled_status;
              })
            //   ->addColumn('action', function($detail) {
            //     $status='';
            //     if($detail->deleted_at==NULL){
            //         if ($detail->checked_in == 0) {
            //             $status = '<label class="pure-material-checkbox">
            //                         <input type="checkbox" name="CheckIn" id="CheckIn_'.$detail->id.'" onclick="handleCheckIn('.$detail->id.', \'checkIn\')">
            //                         <span>CheckIn</span>
            //                       </label>';
            //         } else {
            //             $status = '<label class="pure-material-checkbox">
            //                         <input type="checkbox" name="CheckIn" id="CheckIn_'.$detail->id.'" onclick="handleCheckIn('.$detail->id.', \'checkout\')">
            //                         <span>CheckOut</span>
            //                       </label>';
            //         }
                    
            //     }
                
            //     return $status;
            //   })
              ->addColumn('payment_status', function($detail) {
                $payment_status='';
                if($detail->payment_status == 1){
                    $payment_status='<span style=" background-color: green;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Paid</span>';
                }
                elseif($detail->payment_status == 2){
                    $payment_status='<span style=" background-color: red;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">Refund</span>';
                }
                else{
                    $payment_status='<span style=" background-color: orange;
                    color: white;
                    padding: 4px 8px;
                    text-align: center;
                    border-radius: 5px;">UnPaid</span>';
                }
                return $payment_status;
              })
            ->rawColumns(['status','payment_status','schedule','action','date'])
            ->make(true);
        }
        return view($this->viewPath . '.schedule', compact('page', 'variants'));        
    }

    // public function updateCheckInStatus(Request $request){
    //     dd($request->all());
    //     $status=0;
    //     if($request->status=='checkout'){
          
    //         $status=0;
    //     }else{
    //         $schedule=Schedule::where('id',$request->detail_id)->first();
    //         $schedule_start_time = $schedule->start;
    //         $schedule_end_time   = $schedule->end;
    //         $currentTime             = Carbon::now();
    //         $currentTimeUtc          = $currentTime->setTimezone('Asia/Kolkata');
    //         $currentTime12HourFormat = $currentTimeUtc->format('Y-m-d H:i:s A');
            
    //         $formattedStartDateTime  = $schedule_end_time;
    //         if($formattedStartDateTime > $currentTime){
    //             $formattedStartDateTime = $currentTime->format('Y-m-d g:i:s A');
               
    //             return ['flagError' => true, 'message' => "Unable to check in now. Please check-in on or  after {$formattedStartDateTime}."];
    //         }
    //         $status=1;
    //     }
    //    if($request->detail_id){
    //     try{
    //         $schedule=Schedule::where('id',$request->detail_id)->update(['checked_in'=>$status]);
    //         return ['flagError' => false, 'message' => ucfirst($request->status).' Successfully'];

    //     }catch(\Exception $e){
    //         return ['flagError' => true, 'message' => $e->getMessage()];
    //     }
    //    }
        
    // }
    
    public function scheduleFilter(Request $request){

        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();    
        $currentDate = now()->toDateString(); 
 
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $total_bill_value = 0;
        $total_sale_value = 0;
        
        if (empty($request->start)) {
            // Return today's sales amount
           
            $billing_array = Schedule::join('billings', 'billings.id', '=', 'schedules.billing_id')
            ->where('schedules.shop_id', SHOP_ID);
          ////
            $sales_array = Schedule::join('billings', 'billings.id', '=', 'schedules.billing_id')
                ->whereIn('billings.payment_status', [1, 3, 4])
                ->where('billings.shop_id', SHOP_ID);
            $canceled_array=Schedule::leftJoin('billings', 'billings.id', '=', 'schedules.billing_id')                                   
                                    ->where('billings.shop_id', SHOP_ID)
                                    ->onlyTrashed();
                                    // ->count();
            $checked_in_customer=Schedule::where('checked_in', 1)->distinct('customer_id');
            // ->count();
            $not_checked_in_customer=Schedule::where('checked_in', 0)->distinct('customer_id');
            // ->count();
        }
        

        if ($request->filled('year')) {
            $year = $request->year;
            $billing_array->whereYear('schedules.start', $year);
            $sales_array->whereYear('schedules.start', $year);
            $canceled_array->whereYear('schedules.start', $year);
            $checked_in_customer->whereYear('schedules.start', $year);
            $not_checked_in_customer->whereYear('schedules.start', $year);
            
           

        }
        if ($request->filled('fromDate') && $request->filled('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            
            $billing_array->whereBetween('schedules.start', [$fromDate, $toDate]);
            $sales_array->whereBetween('schedules.start', [$fromDate, $toDate]);
            $canceled_array->whereBetween('schedules.start', [$fromDate, $toDate]);
            $checked_in_customer->whereBetween('schedules.start', [$fromDate, $toDate]);
            $not_checked_in_customer->whereBetween('schedules.start', [$fromDate, $toDate]);
            
        }

        if ($request->day == 'today') {           
            $billing_array->whereDate('schedules.start', $currentDate); 
            $sales_array->whereDate('schedules.start', $currentDate); 
            $canceled_array->whereDate('schedules.start', $currentDate); 
            $checked_in_customer->whereDate('schedules.start', $currentDate); 
            $not_checked_in_customer->whereDate('schedules.start', $currentDate); 
            
        } elseif ($request->day == 'week') {
            $billing_array->whereBetween('schedules.start', [$startDate, $endDate]);
            $sales_array->whereBetween('schedules.start', [$startDate, $endDate]);
            $canceled_array->whereBetween('schedules.start', [$startDate, $endDate]);
            $checked_in_customer->whereBetween('schedules.start', [$startDate, $endDate]);
            $not_checked_in_customer->whereBetween('schedules.start', [$startDate, $endDate]);
            

        } elseif ($request->day == 'month') {
            $billing_array->whereMonth('schedules.start', $currentMonth)->whereYear('schedules.start', $currentYear);
            $sales_array->whereMonth('schedules.start', $currentMonth)->whereYear('schedules.start', $currentYear);
            $canceled_array->whereMonth('schedules.start', $currentMonth)->whereYear('schedules.start', $currentYear);            
            $checked_in_customer->whereMonth('schedules.start', $currentMonth)->whereYear('schedules.start', $currentYear);            
            $not_checked_in_customer->whereMonth('schedules.start', $currentMonth)->whereYear('schedules.start', $currentYear);            

        }


        $billing_array          = $billing_array->get();
        $sales_array            =$sales_array-> groupBy('schedules.billing_id')->get();
        $canceled_array         =$canceled_array->count();
        $checked_in_customer    =$checked_in_customer->count();
        $not_checked_in_customer    =$not_checked_in_customer->count();

        foreach ($billing_array as $row) {
            $total_bill_value += $row->amount;
        }
        
        foreach ($sales_array as $row) {
            $total_sale_value += $row->amount;
        }
        
        return response()->json(['flagError' => false, 
        'total_bookings' => count($billing_array), 
        'booking_amount' => $total_bill_value,
         'total_sales' => count($sales_array),
         'total_canceled'=>$canceled_array,
         'sales_amount' => $total_sale_value,
         'checked_in_customer'=>$checked_in_customer,
         'not_checked_in_customer'=>$not_checked_in_customer,
        ]);   
     }

 
}
