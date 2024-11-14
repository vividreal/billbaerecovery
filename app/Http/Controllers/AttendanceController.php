<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Shop;
use App\Models\AttendanceTime;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use App\Helpers\FunctionHelper;
use Carbon\Carbon;
use DB;

class AttendanceController extends Controller
{

    protected $title    = 'Attendance';
    protected $viewPath = 'attendance';
    protected $route    = 'attendance';

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
        $variants->time_picker  = ($this->time_format === 'h') ? false : true;
        $variants->time_format  = $this->time_format;
        $variants->times        = AttendanceTime::all();
        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Display a listing of the resource in table.
     * @throws \Exception
     */
    public function lists(Request $request)
    {

        $staffs       = StaffProfile::with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('shop_id', SHOP_ID)->where('is_active', '!=',  2);
            })
            // ->whereHas('attendances', function ($query) {
            //     $query->whereDate('attendances.created_at', Carbon::today());
            // })
            ->where('is_staff', 1)
            ->orderBy('staff_profiles.id', 'DESC')
            ->get();

        $attendance_on_date = ($request->markDate != '') ? $request->markDate : Carbon::today();


        foreach ($staffs as $staff) {
            $attendances = Attendance::where('user_id', $staff->user_id)
                ->whereDate('attendances.created_at', $attendance_on_date)
                ->get();

            if ($attendances) {
                $staff[$staff->user_id] = $attendances;
            }
            $attendances = [];
        }

        $editable   = $request->editable;

        $attendance_table = view($this->viewPath . '.attendance-table', compact('staffs', 'attendance_on_date', 'editable'))->render();
        return ['flagError' => false, 'html' => $attendance_table];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $variants->times        = AttendanceTime::all();
        $variants->staffs       = StaffProfile::select('staff_profiles.*', 'attendances.id as attendanceID', 'attendances.in_time', 'attendances.out_time')
            ->with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('shop_id', SHOP_ID)->where('is_active', '!=',  2);
            })
            ->leftJoin('attendances', function ($join) {
                $join->on('attendances.user_id', '=', 'staff_profiles.user_id')
                    ->whereDate('attendances.created_at', Carbon::today())
                    ->whereRaw('attendances.id IN (select MAX(a2.id) from attendances as a2 join staff_profiles as u2 on u2.user_id = a2.user_id group by u2.id)');
            })
            ->orderBy('staff_profiles.id', 'DESC')
            ->get();
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
        $attendance             = Attendance::where('user_id', $request->userId)->whereDate('attendances.created_at', Carbon::today())->latest()->first();

        if (!empty($attendance) && $attendance->out_time == null) {
            $attendance->out_time = FunctionHelper::dateToTimeZone(now()->toDateTimeString(), 'Y-m-d H:i:s');
            $attendance->save();
        } else {
            $attendance                 = new Attendance();
            $attendance->user_id        = $request->userId;
            $attendance->staff_id       = $request->staffId;
            $attendance->marked_by      = auth()->user()->id;
            $attendance->in_time        = FunctionHelper::dateToTimeZone(now()->toDateTimeString(), 'Y-m-d H:i:s');
            $attendance->save();
        }
        return ['flagError' => false, 'message' => $this->title . " marked successfully"];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function editMarking(Request $request)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route;
        $variants->time_picker  = ($this->time_format === 'h') ? false : true;
        $variants->time_format  = $this->time_format;
        return view($this->viewPath . '.edit-list', compact('page', 'variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        $attendances    = Attendance::where('staff_id', $attendance->staff_id)->where('out_time', '!=', null)->whereDate('created_at', $attendance->created_at->format('Y-m-d'))->orderBy('created_at', 'ASC')->get();
        $newTime        = new Carbon($request->attendance_time);
        $action         = false;
        // echo "<pre>"; print_r($attendances); 
        // echo 'Actual time: ' . $attendance->in_time->format('h:i A');
        // echo '<br>';
        // echo 'New time: ' . date('h:i A', strtotime($request->attendance_time));
        // exit;

        if ($request->markingAction == "inTime") {
            $dataArray  = array('field' => 'in_time', 'time' => $request->attendance_time);
            if ($attendance->in_time->gt($newTime)) {
                
                $previous   = Attendance::where('id', '<', $attendance->id)->where('staff_id', $attendance->staff_id)->whereDate('created_at', $attendance->created_at->format('Y-m-d'))->orderBy('id', 'desc')->first();

                if (!empty($previous)) {

                    $in_time    = new Carbon($previous->out_time);
                    $out_time   = new Carbon($attendance->in_time);
                    $check      = $newTime->between($in_time->format('h:i A'), $out_time->format('h:i A'), true);

                    if ($check) {
                        Attendance::updateAttendance($attendance, $dataArray);
                        return ['flagError' => false, 'message' => "Updated successfully"];
                    }

                } else {
                    Attendance::updateAttendance($attendance, $dataArray);
                    return ['flagError' => false, 'message' => "Updated successfully"];
                }


            } else {

                $in_time    = new Carbon($attendance->in_time);
                $out_time   = new Carbon($attendance->out_time);

                $check      = $newTime->between($in_time->format('h:i A'), $out_time->format('h:i A'), true);

                if ($check) {
                    Attendance::updateAttendance($attendance, $dataArray);
                    return ['flagError' => false, 'message' => "Updated successfully"];
                }
            }
        } else {
            $dataArray  = array('field' => 'out_time', 'time' => $request->attendance_time);
            if ($attendance->out_time->gt($newTime)) {

                $in_time    = new Carbon($attendance->in_time);
                $out_time   = new Carbon($attendance->out_time);

                $check      = $newTime->between($in_time->format('h:i A'), $out_time->format('h:i A'), true);

                if ($check) {
                    Attendance::updateAttendance($attendance, $dataArray);
                    return ['flagError' => false, 'message' => "Updated successfully"];
                }


            } else {
                $next       = Attendance::where('id', '>', $attendance->id)->where('staff_id', $attendance->staff_id)->whereDate('created_at', $attendance->created_at->format('Y-m-d'))->orderBy('id')->first();
                if (!empty($next)) {

                    $in_time    = new Carbon($next->in_time);
                    $out_time   = new Carbon($attendance->out_time);
                    $check      = $newTime->between($in_time->format('h:i A'), $out_time->format('h:i A'), true);

                    if ($check) {
                        Attendance::updateAttendance($attendance, $dataArray);
                        return ['flagError' => false, 'message' => "Updated successfully"];
                    }

                } else {
                    Attendance::updateAttendance($attendance, $dataArray);
                    return ['flagError' => false, 'message' => "Updated successfully"];
                }
                
            }

        }
        return ['flagError' => true, 'message' => "Can't update! Invalid time"];
    }

    public function updateAttendance($attendance,)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
