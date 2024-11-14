<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\FunctionHelper;
class Schedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public function billing()
    {
        return $this->belongsTo(Billing::class, 'billing_id');
    }
    
    public function package(){
        return $this->belongsTo(Package::class,'package_id');

    }
    public function service(){
        return $this->belongsTo(Service::class,'item_id');

    }
    public function attendance(){
        return $this->belongsTo(Attendance::class,'user_id','user_id');

    }
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public static function checkTherapistAvailability($request){
        $formatted_start_date   = new Carbon\Carbon($request['start_time']);
        $start_date             = new Carbon\Carbon($request['start_time']);    
      
        if($request['schedule_id']){
            $schedule=self::find($request['schedule_id']);
            $item_count = BillingItem::where('billing_id', $schedule->billing_id)
                                ->where('customer_id', $schedule->customer_id)
                                ->where('item_id', $schedule->item_id)
                                ->value('item_count');
                                $item_count= ($item_count)? $item_count:1;
            $end_time           = $formatted_start_date->addMinutes($schedule->total_minutes * $item_count);

            if(isset($request['form_method']) && $request['form_method'] == 'PUT') {        
                if($schedule->user_id==$request['user_id']){                   
                    $data = self::where('user_id', $schedule->user_id)
                    ->where('id', '!=', $schedule->id)
                    ->where(function ($query) use ($start_date, $end_time) {
                        $query->whereBetween('start', [$start_date, $end_time])
                        ->orWhereBetween('end', [$start_date, $end_time]);
                    })
                    ->get();

                }else{
                    $data = self::where('user_id','!=', $schedule->user_id)
                    ->where('id', '!=', $schedule->id)
                    ->where(function ($query) use ($start_date, $end_time) {
                        $query->whereBetween('start', [$start_date, $end_time])
                            ->orWhereBetween('end', [$start_date, $end_time]);
                    })
                    ->get();
                }
            }
            else{
                $data = self::where('user_id', $schedule->user_id)
                ->where('id', '!=', $schedule->id)
                ->where(function ($query) use ($start_date, $end_time) {
                    $query->whereBetween('start', [$start_date, $end_time])
                        ->orWhereBetween('end', [$start_date, $end_time]);
                })
                ->get();
            }
            // dd($data);
                  
        }else{
            $end_time               = $formatted_start_date->addMinutes($request['total_minutes']);
            $data                   = self::where(function($query) use ($start_date, $end_time) {
                                        $query->where(function($query) use ($start_date, $end_time) {
                                            $query->where('start', '>', $start_date)
                                                ->where('start', '<', $end_time);
                                        })->orWhere(function($query) use ($start_date, $end_time) {
                                            $query->where('end', '<', $start_date)
                                                ->where('end', '>', $end_time);
                                        });
                                    })
                                    ->orWhere(function($query) use ($start_date, $end_time) {
                                        $query->where('start', '<', $start_date)
                                            ->where('end', '>', $end_time);
                                    })
                                    ->where('user_id', $request['user_id'])
                                   ->get();
                                   
                                  
        }   
        
        if ($data) 
            return $data;
    }
    public static function checkRoomAvailability($request){
        $formatted_start_date   = new Carbon\Carbon($request['start_time']);
        $start_date             = new Carbon\Carbon($request['start_time']);     
        if($request['schedule_id']){
            $schedule=self::find($request['schedule_id']);
            $item_count = BillingItem::where('billing_id', $schedule->billing_id)
                                ->where('customer_id', $schedule->customer_id)
                                ->where('item_id', $schedule->item_id)
                                ->value('item_count');
            
                                $item_count= ($item_count)? $item_count:1;
            $end_time           = $formatted_start_date->addMinutes($schedule->total_minutes * $item_count);
      
            if(isset($request['form_method']) && $request['form_method'] == 'PUT') {
                if($schedule->room_id==$request['room_id']){
                    $data = self::where('room_id', '=',$schedule->room_id)
                    ->where('id', '!=',$schedule->id)
                    ->where(function ($query) use ($start_date, $end_time) {
                        $query->whereBetween('start', [$start_date, $end_time])
                            ->orWhereBetween('end', [$start_date,$end_time]);
                    })
                    ->whereNull('deleted_at')
                    ->get();
                    // dd($data);
                }else{
                $data = self::where('room_id', '!=',$schedule->room_id)
                ->where('id', '!=',$schedule->id)
                ->where(function ($query) use ($start_date, $end_time) {
                    $query->whereBetween('start', [$start_date, $end_time])
                        ->orWhereBetween('end', [$start_date,$end_time]);
                })
                ->whereNull('deleted_at')
                ->get();
                // dd($data);
                }
                
            }else{
                $data = self::where('room_id', $schedule->room_id)
                ->where('id', '!=', $schedule->id)
                ->where(function ($query) use ($start_date, $end_time) {
                    $query->whereBetween('start', [$start_date, $end_time])
                        ->orWhereBetween('end', [$start_date,$end_time]);
                })
                ->whereNull('deleted_at')
                ->get();
            }
          
             
        }else{
           
            $end_time               = $formatted_start_date->addMinutes($request['total_minutes']);
            $data                   = self::where(function($query) use ($start_date, $end_time) {
                                        $query->where(function($query) use ($start_date, $end_time) {
                                            $query->where('start', '>', $start_date)
                                                ->where('start', '<', $end_time);
                                        })->orWhere(function($query) use ($start_date, $end_time) {
                                            $query->where('end', '<=', $start_date)
                                                ->where('end', '>=', $end_time);
                                        });
                                    })
                                    ->orWhere(function($query) use ($start_date, $end_time) {
                                        $query->where('start', '<=', $start_date)
                                            ->where('end', '>=', $end_time);
                                    })
                                    ->where('room_id', $request['room_id'])
                                   ->get();
        }   
        
        if ($data) 
            return $data;
    }
    public static function checkTimeAvailability($request)
    {
        
        $formatted_start_date   = new Carbon\Carbon($request['start_time']);
        $start_date             = new Carbon\Carbon($request['start_time']);
       
        if($request['schedule_id']){
            
            $schedule=self::find($request['schedule_id']);
            $item_count = BillingItem::where('billing_id', $schedule->billing_id)
                                ->where('customer_id', $schedule->customer_id)
                                ->where('item_id', $schedule->item_id)
                                ->value('item_count');
                                $item_count= ($item_count)? $item_count:1;
            $end_time = $formatted_start_date->copy()->addMinutes($schedule->total_minutes * $item_count);

            $data                = self::where(function($query) use ($start_date, $end_time) {
                $query->where(function($query) use ($start_date, $end_time) {
                    $query->where('start', '>', $start_date)
                        ->where('start', '<', $end_time);
                })->orWhere(function($query) use ($start_date, $end_time) {
                    $query->where('end', '<=', $start_date)
                        ->where('end', '>=', $end_time);
                });
            });
          
            if(isset($request['form_method']) && $request['form_method'] == 'PUT') {
                $data=$data->where('user_id','=', $schedule->user_id)
                ->where('customer_id','=', $schedule->customer_id)->where('id', '!=', $schedule->id)
                ->get();
            }else{
            $data=$data-> where('user_id','!=', $schedule->user_id)
            ->where('customer_id','!=', $schedule->customer_id)->where('id', '=', $schedule->id)
            ->get();
            }
            // dd($data);
           
        }else{
            $end_time               = $formatted_start_date->addMinutes($request['total_minutes']);
            $data                   = self::where('user_id', $request['user_id'])
                                        ->where(function($query) use ($start_date, $end_time) {
                                            $query->where(function($query) use ($start_date, $end_time) {
                                                $query->where('start', '>=', $start_date)
                                                    ->where('start', '<', $end_time);
                                            })->orWhere(function($query) use ($start_date, $end_time) {
                                                $query->where('end', '>', $start_date)
                                                    ->where('end', '<=', $end_time);
                                            });
                                        })
                                        ->orWhere(function($query) use ($start_date, $end_time) {
                                            $query->where('start', '<=', $start_date)
                                                ->where('end', '>=', $end_time);
                                        })
                                        ->where('customer_id','=', $request['customer_id'])
                                        ->where('id', '!=', $request['schedule_id'])
                                        ->get();
        }
       
        if ($data) 
            return $data;
    }

    public static function addMore($id, $new_item_ids, $data) {     
        $scheduleItem               = Schedule::where('billing_id', $id)->orderBy('id', 'desc')->first();
        $billing                  = Billing::find($id);
        $i  = 0;
        $formatted_start_date   = new Carbon\Carbon($data['start_time']);
        $start_date             = new Carbon\Carbon($data['start_time']);   
        $formatted_start_date   = null;
        $start_time             = null;
        $schedule               = null;
        $itemsCount             =[];
        if(isset($data['items'])) {
            foreach ($data['items'] as $key => $item) {
                $newArr             = array_keys($item);
                $itemsCount[str_replace(' ', '', $key)]   = $newArr[0];
            }
        }
        if(isset($new_item_ids)){
            $data_price=[];
            $total_amount   =0;
               if($data['service_type']==1){
                    foreach($new_item_ids as $key => $itemId) {
                        $items_details              = Service::getTimeDetails($itemId);  
                        $item_id                    = $itemId;  
                        $package_id                 = NULL;
                        $data_price[$key]           = Service::getPriceAfterTax($itemId);
                        if( $i === 0 ) {                        
                            // if ($scheduleItem !== null) {                        
                                $formatted_start_date = new Carbon\Carbon($data['start_time']);                        
                                $start_time = new Carbon\Carbon($data['start_time']);
                            // }
                        }
                        $itemCount = isset($itemsCount[$itemId]) ? $itemsCount[$itemId] : 1;
                        $itemCount = (int)$itemCount;
                        $total_time                 = $items_details['total_minutes'];                           
                        if ($start_time !== null) {
                            $start_time                 = new Carbon\Carbon($start_time);
                            // $end_time                   = $formatted_start_date->addMinutes($total_time * $itemsCount);
                            $end_time = $formatted_start_date->copy()->addMinutes($total_time * $itemCount);
                            
                            $schedule = new Schedule();
                            $schedule->name             = $billing->customer->name . ' - '. $billing->customer->mobile .' : '. $start_time->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
                            $schedule->start            = $start_time;
                            $schedule->end              = $end_time;
                            $schedule->user_id          = $data->user_id;
                            $schedule->customer_id      = $billing->customer->id;
                            $schedule->billing_id       = $billing->id;
                            $schedule->item_id          = $item_id;
                            $schedule->package_id       =  NULL;
                            $schedule->item_type        = ($data->service_type == 1) ? "services" : "packages";
                            $schedule->room_id          = $data->room_id;
                            $schedule->description      = $items_details['description'] . 'Nos: ' . implode(', ', $itemsCount);
                            $schedule->total_minutes    = $items_details['total_minutes'];
                            $schedule->shop_id          = SHOP_ID;
                            $schedules=Schedule::where('customer_id',$billing->customer_id)->where('checked_in', 1)->whereDate('start',$start_time)->get();
                            if(count($schedules) > 0 && $billing->customer_id== $data['customer_id']){
                                $schedule->schedule_color   = "orange" ;
                                $schedule->checked_in       = 1 ;
                            }else{
                                $schedule->schedule_color   = "#FF5733";
                                $schedule->checked_in       = 0;
                            }
                            $schedule->save();    
                            // Re-Initialize Items
                            $start_time                 = $end_time;               
                            $i++;
                        }
                        $total_amount   = array_sum($data_price);
                        $previous='';
                        $current='0';
                        $comment='New items added to the existing schedule';
                        $type='schedule';
                        $activity_id=NULL;
                        $customer=$billing->customer_id;
                        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing->id,$comment,$type);
                    }
               }else{    
                
                    foreach($new_item_ids as $key => $itemId) {            
                        $itemCount = 1 ;            
                        $packages=PackageService::where('package_id',$itemId)->get();                        
                        foreach($packages as $package){                      
                            $items_details              = Service::getTimeDetails($package->service_id); 
                            $description = $items_details['description'];
                            $pos = strpos($description, ' - ');
                            $items_details['description'] = $pos !== false ? substr($description, 0, $pos) : $description; 
                            $total_time                 = $items_details['total_minutes'];    
                            $total_time *= $itemCount;
                            $start_time             = new Carbon\Carbon($data['start_time']);
                            $end_time = $start_time->copy()->addMinutes($total_time);                      
                            $schedule                   = new Schedule();
                            $schedule->name             = $billing->customer->name . ' - ' . $billing->customer->mobile . ' : ' . $start_time->format('h:i:s A') . ' - ' . $end_time->format('h:i:s A');
                            $schedule->start            = $start_time;
                            $schedule->end              = $end_time;
                            $schedule->user_id          = $data->user_id;
                            $schedule->customer_id      = $billing->customer->id;
                            $schedule->billing_id       = $billing->id;
                            $schedule->item_id          = $package->service_id;
                            $schedule->package_id       = $package->package_id;
                            $schedule->item_type        = "packages";
                            $schedule->room_id          = $data->room_id;   
                            $schedule->description      = $items_details['description'] .'</br>'. 'Nos: ' . implode(', ', $itemsCount);
                            $schedule->total_minutes    = $items_details['total_minutes'];
                            $schedule->shop_id          = SHOP_ID;
                            $schedule->schedule_color   = ($data['checked_in'] == 1) ? "orange" : "#FF5733";
                            $schedule->checked_in       = ($data['checked_in'] == 1) ? 1 : 0;
                            $schedule->save();
                            $previous='';
                        $current='0';
                        $comment='New items added to the existing schedule';
                        $type='schedule';
                        $activity_id=NULL;
                        $customer=$billing->customer_id;
                        FunctionHelper::statusChangeHistory($activity_id, $previous, $current,$customer,$schedule->id,$billing->id,$comment,$type);
                        }
                    }
               }     
               
            
            
        }
        return $billing->id;
    }

    public static function resetSchedule()
    {
    }
}
