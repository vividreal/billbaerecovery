<?php
    
namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use App\Models\Designation;
use App\Models\StaffProfile;
use Illuminate\Support\Arr;
use App\Models\StaffDocument;
use App\Models\ScheduleColor;
use App\Models\Shop;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Customer;
use App\Models\Room;
use DataTables;
use Response;
use Validator;
use DB;
use Auth;
use Hash;
use Mail; 
use Carbon\Carbon;
    
class StaffController extends Controller
{
    protected $title        = 'Staffs';
    protected $viewPath     = 'staffs';
    protected $link         = 'staffs';
    protected $route        = 'staffs';
    protected $entity       = 'staffs';
    protected $uploadPath   = 'store/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        //  $this->middleware('permission:user-list|user-create', ['only' => ['index','store']]);
        //  $this->middleware('permission:user-create', ['only' => ['create','store']]);
        //  $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
        //  $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
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
        return view($this->viewPath . '.list', compact('page'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $user_id    = Auth::user()->id;

        $detail     = StaffProfile::with(['user','designationRelation'])->whereHas('user', function ($query) { 
                                    $query->where('shop_id', SHOP_ID)->where('is_active', '!=',  2); 
                                })->where('is_staff', 1);

        if (isset($request->form)) {
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['name'] == 'search_name') {
                    $names = strtolower($search['value']);
                    $detail->where('name', 'like', "%{$names}%");
                }
            }
        }
        $detail->orderBy('id', 'desc');
        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('role', function($detail){
                $roles  = User::find($detail->user->id)->roles;
                $html   = '';
                if ($roles) {
                    foreach ($roles as $role) {
                        $html.= $role->name;
                    }
                }
                return $html;
            })
            ->editColumn('name', function($detail){
                // Assuming you store profile picture path in $detail->user->profile_pic
                if($detail->user->profile){
                $profilePicUrl = asset('storage/store/users/' . $detail->user->profile);
                }else{
                    $profilePicUrl =asset('/images/noimage.jpg');
                }
            
                return '<a href="' . route('getTherapist', ['id' => $detail->user_id]) . '"><img src="' . $profilePicUrl . '" alt="Profile Pic" class="profile-pic" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                         '. $detail->user->name . '</a>';
            })
            ->editColumn('designation', function($detail){
                return $detail->designationRelation->name;
            }) 
            
            ->editColumn('email', function($detail){
                return $detail->user->email;
            }) 
            ->editColumn('mobile', function($detail){
                return $detail->user->mobile;
            }) 
            ->addColumn('activate', function($detail){
                $checked = ($detail->user->is_active == 1) ? 'checked' : '';
                $html = '<div class="switch"><label> <input type="checkbox" '.$checked.' id="' . $detail->user->id . '" class="activate-user" data-id="'.$detail->user->id.'" onclick="manageUserStatus(this.id)"> <span class="lever"></span> </label> </div>';
                return $html;
            })
            ->addColumn('action', function($detail){
                $action = ' <a  href="' . url('staffs/' . $detail->user->id . '/edit') . '" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                $action .= ' <a  href="' . url('staffs/' . $detail->user->id . '/manage-document') . '" class="btn mr-2 light-blue" title="Update documents"><i class="material-icons">attach_file</i></a>';
                return $action;
            })
            ->removeColumn('id', 'is_active')
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
        $page               = collect();
        $you                = Auth::user();
        $page->title        = $this->title;
        $page->link         = url($this->link);
        $page->form_url     = url($this->link);
        $page->entity       = $this->entity;
        $page->form_method  = 'POST';
        $page->route        = $this->route;
        $roles              = Role::orderBy('id', 'asc')->pluck('name','name')->all();
        $page->designations = Designation::pluck('name', 'id');  ;
        return view($this->viewPath . '.create', compact('page', 'roles', 'you'));
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            // 'roles' => 'required'
        ]);

        if ($validator->passes()) {
            $input              = $request->all();
            $user_id            = Auth::user()->id;
            $input['parent_id'] = $user_id;
            $user               = User::create($input);


            // $staff_arr          = array('0' => 'Staffs');
            // $user->assignRole($staff_arr);


            $user->assignRole($request->input('roles'));


            if (Auth::user()->parent_id == null) {
                $shop           = new Shop();
                $shop->name     = $input['shop_name'];
                $shop->user_id  = $user->id;
                $shop->save();
                $user->shop_id  = $shop->id; 
            } else {
                $user->shop_id  = Auth::user()->shop_id;
            }
            $token              = Str::random(64);
            $user->password_create_token = $token;
            $user->is_active    = 1;
            $user->save();

            // Staff Profile
            $profile                = new StaffProfile();
            $profile->user_id       = $user->id;
            $profile->designation   = $request->designation;
            $profile->is_staff      = 1;
            $profile->save();
            // Password create link
            // Mail::send('email.passwordCreate', ['token' => $token], function($message) use($request){
            //     $message->to($request->email);
            //     $message->subject('Create New Password Email');
            // });
            return ['flagError' => false, 'message' => "Staff created successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $user = User::find($id);
        // return view('users.show',compact('user'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $staff                  = User::find($id);
        $page                   = collect();
        $you                    = Auth::user();
        $page->title            = $this->title;
        $page->route            = $this->route;
        $page->entity           = $this->entity;
        $page->link             = url($this->link);
        $page->form_url         = url($this->link . '/' . $staff->id);
        $page->form_method      = 'PUT';
        $page->designations     = Designation::pluck('name', 'id'); 
        $roles                  = Role::orderBy('id', 'asc')->pluck('name','name')->all();
        $page->schedule_colors  = ScheduleColor::pluck('name', 'id');
        $userRole               = $staff->roles->pluck('name','name')->all();
        return view($this->viewPath . '.edit',compact('staff','roles', 'you' ,'userRole', 'page'));
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            
            // 'roles' => 'required'
        ]);

        if ($validator->passes()) {
            $input  = $request->all();
            if (!empty($input['password'])) { 
                $input['password'] = Hash::make($input['password']);
            } else {
                $input  = Arr::except($input,array('password'));    
            }
            $user       = User::find($id);
            $user->update($input);

            // Staff Profile
            $profile                    = StaffProfile::where('user_id', $id)->first();
            $profile->designation       = $request->designation;
            $profile->dob               = Carbon::parse($request->dob)->format('Y-m-d');
            $profile->joining_date      = Carbon::parse($request->joining_date)->format('Y-m-d');
            $profile->contract_end_date = Carbon::parse($request->contract_end_date)->format('Y-m-d');
            $profile->schedule_color    = $request->schedule_color;
            $profile->save();

            DB::table('model_has_roles')->where('model_id',$id)->delete();
            $user->assignRole($request->input('roles'));

            return ['flagError' => false, 'message' => "Staff account updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !",  'error'=>$validator->errors()->all()];
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // DB::table('model_has_roles')->where('model_id',$id)->delete();
        // User::find($id)->delete();
        // return redirect()->route('users.index')->with('success','User deleted successfully');
        $user               = User::findOrFail($id);
        $user->is_active    = 2;
        $user->save();
        return ['flagError' => false, 'message' => $this->title. " deactivated successfully"];
    }

    public function isUnique(Request $request)
    { 
        if ($request->user_id == 0) {
            $count = User::where('email', $request->email)->count();
            echo ($count > 0 ? 'false' : 'true');
        } else {
            $count = User::where('email', $request->email)->where('id', '!=' , $request->user_id)->count();
            echo ($count > 0 ? 'false' : 'true');
        }
    }

    public function manageStatus(Request $request)
    {

        $user = User::findOrFail($request->user_id);

        if($user){
            $status = ($user->is_active == 0)?1:0;
            $user->is_active = $status;
            $user->save();
            return ['flagError' => false, 'message' => $this->title. " status updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUserImage(Request $request)
    {

        $user               = User::findOrFail($request->user_id);

        if($user)
        {
            $old_image          = $user->profile;

            if ($old_image != '') {
                \Illuminate\Support\Facades\Storage::delete('public/' . $this->uploadPath . '/users/' . $old_image);
            }
            
            
            // Create storage folder
            $store_path = 'public/' . $this->uploadPath. '/users/';
            Storage::makeDirectory($store_path);

            $image_64   = $request->image; //your base64 encoded data
            $extension  = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace    = substr($image_64, 0, strpos($image_64, ',')+1); 

            $image      = str_replace($replace, '', $image_64); 
            $image      = str_replace(' ', '+', $image); 
            $imageName  = Str::random(20).'.'.$extension;
            Storage::put($store_path.'/'.$imageName, base64_decode($image));


            $user->profile        = $imageName;
            $user->save();

            return ['flagError' => false, 'logo' => asset('storage/store/users/' . $user->profile),  'message' => "Profile image updated successfully"];
        }
        
        return ['flagError' => true, 'message' => "User not found !"];
    }

    public function manageDocument(Request $request, $id)
    {
        $staff              = User::find($id);
        $page               = collect();
        $you                = Auth::user();
        $page->title        = $this->title;
        $page->route        = $this->route;
        $page->entity       = $this->entity;
        $page->link         = url($this->link);
        $page->form_url     = url($this->link . '/' . $staff->id);
        $documents          = StaffDocument::where('user_id', $id)->get();
        $page->form_method  = 'PUT';
        return view($this->viewPath . '.manage-document',compact('staff','page', 'documents'));
    }

    public function getDocument(Request $request)
    {   
        $user           = User::findOrFail($request->staff_id);
        if ($user) {
            $documents  = StaffDocument::where('user_id', $user->id)->where('status', 1)->get();
            if ($documents) {
                $user_documents = view($this->viewPath . '.list-documents', compact('documents'))->render();  
                return ['flagError' => false, 'html' => $user_documents];
            }
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !", 'error'=>$validator->errors()->all()];
    }

    public function uploadIdProofs(Request $request)
    {
        $image      = $request->file('file');
        $imageName  = $image->getClientOriginalName();
        
        // Create storage folder
        $store_path = 'public/' . $this->uploadPath . '/users/documents/';
        Storage::makeDirectory($store_path);

        // Upload storage folder
        Storage::putFileAs($store_path, $image, $imageName);

        $document               = new StaffDocument();
        $document->user_id      = $request->staff_id;
        $document->name         = $imageName;
        // $document->status       = 1;
        $document->uploaded_by  = Auth::user()->id;
        $document->save();
        return response()->json(['success'=>$imageName]);
    }

    public function storeDocuments(Request $request)
    {
        StaffDocument::where('user_id', $request->staff_id)->update(['status' => 1]);
        return ['flagError' => false];
    }

    public function removeTempDocuments(Request $request)
    {
        StaffDocument::where('user_id', $request->staff_id)->where('status', 0)->delete();
        return ['flagError' => false];
    }

    public function removeIdProofs(Request $request)
    {
        $old_image =  $request->get('filename');
        if ($old_image != '') {
            \Illuminate\Support\Facades\Storage::delete('public/' . $this->uploadPath . '/users/documents/' . $old_image);
        }

        StaffDocument::where('name',$old_image)->where('user_id',$request->staff_id)->delete();

        if ($request->data_return_type == 'html') {
            return ['flagError' => false, 'message' => "Document deleted successfully"];
        }
        return $old_image;  
    }

    function downloadFile(Request $request, $document)
    {
        $store_path = 'public/' . $this->uploadPath. '/users/documents/';
        return Storage::download($store_path.'/'.$document);
    }

    function updateDocumentDetails(Request $request)
    {
        $document = StaffDocument::find($request->document_id);

        if ($document) {
            $document->details =$request->details;
            $document->save(); 
            return ['flagError' => false, 'message' => "Details updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !", 'error'=>$validator->errors()->all()];
    }

    public function deleteIdProofs(Request $request)
    {
        // $old_image =  $request->get('filename');
        // if ($old_image != '') {
        //     \Illuminate\Support\Facades\Storage::delete('public/' . $this->uploadPath . '/users/documents/' . $old_image);
        // }
        // StaffDocument::where('name',$old_image)->where('user_id',$request->staff_id)->delete();
        
        // return ['flagError' => false, 'message' => "Document deleted successfully"];  
    }
    public function getTherapist(Request $request,$id) {
        $page           = collect();
        $variant        = collect();
        $page->title    = $this->title;
        $page->link     = url($this->link);
        $page->route    = $this->route;
        $variant->staff = StaffProfile::with('user','attendances','designationRelation','documents')->where('user_id',$id)->first();
        $variant->noOfServices      =Schedule::where('user_id',$id)->count();
        $attendances = Attendance::where('user_id', $id)
        ->whereMonth('in_time', Carbon::now()->month) // Get current month's data
        ->whereYear('in_time', Carbon::now()->year) // Ensure you're also considering the current year
        ->get();

        $baseIncentive = 0;
        $extraIncentive = 0;
        $totalWorkingMinutes = 0;
        $totalOvertimeMinutes = 0;

        // Initialize array to store unique attendance dates
        $uniqueDates = [];

        // Iterate through attendance records
        foreach ($attendances as $attendance) {
            if ($attendance->in_time && $attendance->out_time) {
                $inTime = Carbon::parse($attendance->in_time);
                $outTime = Carbon::parse($attendance->out_time);
                $workingMinutes = $inTime->diffInMinutes($outTime);
                $totalWorkingMinutes += $workingMinutes;

                // Add the date to uniqueDates array
                $uniqueDates[$inTime->format('Y-m-d')] = true; // Using the date as a key to ensure uniqueness

                // Calculate overtime
                if ($workingMinutes > 600) {
                    $totalOvertimeMinutes += ($workingMinutes - 600); 
                }
            }
        }

        // Calculate total working hours
        $totalWorkingHours = $totalWorkingMinutes / 60;

        // Count unique working days from uniqueDates array
        $noOfWorkingDays = count($uniqueDates);

        // Convert total overtime minutes to hours and minutes
        $overtimeHours = floor($totalOvertimeMinutes / 60);
        $overtimeMinutes = $totalOvertimeMinutes % 60;

        // Total days in the current month
        $totalDaysInMonth = Carbon::now()->daysInMonth;

        // Calculate number of leaves
        $noOfLeaves = $totalDaysInMonth - $noOfWorkingDays;

        // Calculate incentives
        if ($variant->noOfServices >= 66) {
            $baseIncentive = 150;
            $extraServices = $variant->noOfServices - 66;
            $extraIncentive = $extraServices * 50;
        }

        // Set variant properties
        $variant->noOfWorkingDays = round($noOfWorkingDays);
        $variant->noOfLeaves = $noOfLeaves >= 0 ? round($noOfLeaves) : 0;
        $variant->incentive = $extraIncentive;
        $variant->customers         = Customer::where('shop_id',SHOP_ID)->get();
        $variant->rooms             = Room::where('shop_id',SHOP_ID)->get();
        return view($this->viewPath . '.staff_detail', compact('page','variant'));
    }
    public function staffWorkHistory(Request $request,$id){      
        $detail = Attendance::with('users')
            ->where('user_id', $id)
            ->get()
            ->groupBy(function($row) {
                return \Carbon\Carbon::parse($row->in_time)->format('Y-m-d');
            });
        return DataTables::of($detail)
        ->addIndexColumn()
        ->addColumn('date', function($row) {
            return \Carbon\Carbon::parse($row->first()->in_time)->format('m/d/Y');
        })
        ->addColumn('in_time', function($row) {
            $firstCheckIn = $row->sortBy('in_time')->first();
            return \Carbon\Carbon::parse($firstCheckIn->in_time)->format('h:i A');
        })
        ->addColumn('out_time', function($row) {
            $lastCheckOut = $row->sortByDesc('out_time')->first();
            return \Carbon\Carbon::parse($lastCheckOut->out_time)->format('h:i A');
        })
        ->addColumn('total_working_time', function($row) {
            $totalWorkingMinutes = 0;
            foreach ($row as $attendance) {
                if ($attendance->in_time && $attendance->out_time) {
                    $inTime = \Carbon\Carbon::parse($attendance->in_time);
                    $outTime = \Carbon\Carbon::parse($attendance->out_time);
                    $totalWorkingMinutes += $inTime->diffInMinutes($outTime);
                }
            }
            $hours = floor($totalWorkingMinutes / 60);
            $minutes = $totalWorkingMinutes % 60;
            return sprintf('%02d:%02d', $hours, $minutes);
        })
        ->addColumn('break_time', function($row) {
            $totalBreakMinutes = 0;
            foreach ($row as $index => $attendance) {
                if (isset($row[$index + 1])) {
                    $currentCheckout = \Carbon\Carbon::parse($attendance->out_time);
                    $nextCheckin = \Carbon\Carbon::parse($row[$index + 1]->in_time);
                    $totalBreakMinutes += $currentCheckout->diffInMinutes($nextCheckin);
                }
            }
            $hours = floor($totalBreakMinutes / 60);
            $minutes = $totalBreakMinutes % 60;
            return sprintf('%02d:%02d', $hours, $minutes);
        })
        ->addColumn('over_time', function($row) {
            $totalWorkingMinutes = 0;
            foreach ($row as $attendance) {
                $checkin = \Carbon\Carbon::parse($attendance->in_time);
                $checkout = \Carbon\Carbon::parse($attendance->out_time);
                $totalWorkingMinutes += $checkin->diffInMinutes($checkout);
            }
            $totalWorkingHours = $totalWorkingMinutes / 60;
            if ($totalWorkingHours > 10) {
                $overtimeMinutes = ($totalWorkingHours - 10) * 60;
                $hours = floor($overtimeMinutes / 60);
                $minutes = $overtimeMinutes % 60;
                return sprintf('%02d:%02d', $hours, $minutes);
            }
            return '0.00'; 
        })
        ->make(true);        
    }
    public function staffServiceHistory(Request $request,$id)  {
        $schedulesQuery = Schedule::with(['billing', 'attendance', 'user', 'customer', 'room'])
        ->where('user_id', $id);

    // Apply filters if they are present
    if ($request->has('billing_code') && !empty($request->billing_code)) {
        $schedulesQuery->whereHas('billing', function($query) use ($request) {
            $query->where('billing_code', 'like', '%' . $request->billing_code . '%');
        });
    }

    if ($request->has('customer_name') && !empty($request->customer_name)) {
        $schedulesQuery->whereHas('customer', function($query) use ($request) {
            $query->where('id',$request->customer_name);
        });
    }

    if ($request->has('room_name') && !empty($request->room_name)) {
        $schedulesQuery->whereHas('room', function($query) use ($request) {
            $query->where('id', $request->room_name);
        });
    }

    $schedules = $schedulesQuery->get();
        $attendanceData = [];
        foreach ($schedules as $schedule) {
            $dateKey = Carbon::parse($schedule->created_at)->format('Y-m-d');
            $attendanceData[] = [
                'invoice' => $schedule->billing?->billing_code,
                'date' => Carbon::parse($schedule->created_at)->format('m/d/Y'),
                'service_type' => $schedule->item_type,
                'name' => $schedule->item_type === 'package' ? $schedule->package->name : $schedule->service->name,
                'start' => Carbon::parse($schedule->start),
                'end' => Carbon::parse($schedule->end),
                'total_minutes' => 0,
                'break_minutes' => 0,
                'over_time_minutes' => 0,
                'customer'=>$schedule->customer->name,
                'room'=>$schedule->room->name
            ];
            foreach ($schedule->attendance ?? [] as $attendance) {
                // Check if $attendance is an object before accessing its properties
                if (is_object($attendance)) {
                    $totalMinutes = ($attendance->in_time && $attendance->out_time)
                        ? Carbon::parse($attendance->out_time)->diffInMinutes(Carbon::parse($attendance->in_time))
                        : 0;
            
                    // Update the total minutes and break minutes
                    $attendanceData[count($attendanceData) - 1]['total_minutes'] += $totalMinutes; 
                    $attendanceData[count($attendanceData) - 1]['break_minutes'] += $attendance->break_time ?? 0; // Update break time
                }
            }
            
        }
        foreach ($attendanceData as &$data) {
            $normalWorkingMinutes = 600; 
            $data['over_time_minutes'] = max(0, $data['total_minutes'] - $normalWorkingMinutes);
        }
        $attendanceDataFormatted = array_map(function ($data) {
            return [
                'invoice' => $data['invoice'],
                'date' => $data['date'],
                'service_type' => $data['service_type'],
                'name' => $data['name'],
                'datetime' => $data['start']->format('m/d/Y h:i A') . ' - ' . $data['end']->format('m/d/Y h:i A'),
                'break_time' => sprintf('%02d:%02d', floor($data['break_minutes'] / 60), $data['break_minutes'] % 60),
                'over_time' => sprintf('%02d:%02d', floor($data['over_time_minutes'] / 60), $data['over_time_minutes'] % 60),
                'customer'=>$data['customer'],
                'room'=>$data['room']
            ];
        }, $attendanceData);
        return DataTables::of($attendanceDataFormatted)
            ->addIndexColumn() 
            ->make(true);
    }
    public function getDateRangeStaffDetails(Request $request,$id)  {
        $fromDate = Carbon::createFromFormat('Y-m-d', $request->fromDate)->startOfDay();
        $toDate = Carbon::createFromFormat('Y-m-d', $request->toDate)->endOfDay();
      
        $noOfServices = Schedule::where('user_id', $id)
            ->whereBetween('start', [$fromDate, $toDate])
            ->count();
        
        // Retrieve attendances within the specified date range
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('in_time', [$fromDate, $toDate])
            ->get();
        
        $baseIncentive = 0;
        $extraIncentive = 0;
        $totalWorkingMinutes = 0;
        $totalOvertimeMinutes = 0;
        
        $uniqueDates = [];
        
        // Iterate through attendance records
        foreach ($attendances as $attendance) {
            if ($attendance->in_time && $attendance->out_time) {
                $inTime = Carbon::parse($attendance->in_time);
                $outTime = Carbon::parse($attendance->out_time);
                $workingMinutes = $inTime->diffInMinutes($outTime);
                $totalWorkingMinutes += $workingMinutes;
                $uniqueDates[$inTime->format('Y-m-d')] = true; 
                if ($workingMinutes > 600) {
                    $totalOvertimeMinutes += ($workingMinutes - 600);
                }
            }
        }
        
        $totalWorkingHours = $totalWorkingMinutes / 60;
        $noOfWorkingDays = count($uniqueDates);
        
        // Convert total overtime minutes to hours and minutes
        $overtimeHours = floor($totalOvertimeMinutes / 60);
        $overtimeMinutes = $totalOvertimeMinutes % 60;
        
        // Calculate number of leaves based on the date range
        $totalDaysInRange = $toDate->diffInDays($fromDate) + 1; // Include the last day
        $noOfLeaves = $totalDaysInRange - $noOfWorkingDays;
        
        // Calculate incentives
        if ($noOfServices >= 66) {
            $baseIncentive = 150;
            $extraServices = $noOfServices - 66;
            $extraIncentive = $extraServices * 50;
        }
        
        // Set the calculated values in the data array
        $data = [
            'noOfService' => $noOfServices,
            'noOfWorkingDays' => round($noOfWorkingDays),
            'noOfLeaves' => max(0, round($noOfLeaves)), // Ensure noOfLeaves is not negative
            'incentive' => $extraIncentive
        ];
        return ['flagError' => false, 'data' => $data];
    }
}