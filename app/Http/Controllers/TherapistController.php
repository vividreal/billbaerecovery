<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class TherapistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {  
        if ($request->ajax()) {
            //Need to optimize - Refer the commented code
            $data           = User::leftjoin('staff_profiles', 'staff_profiles.user_id', '=', 'users.id')
                                        ->leftjoin('schedule_colors', 'staff_profiles.schedule_color', '=', 'schedule_colors.id')
                                        ->where('users.shop_id', SHOP_ID)
                                        ->where('users.is_active', '=',  1)
                                        ->whereIn('staff_profiles.designation', [1, 2])
                                        ->where('users.is_active', '!=',  2)
                                        ->get(['users.id', 'users.name as title']);

            // StaffProfile::whereIn('designation', [1,2])->whereHas('user', function ($query) { $query->where('shop_id', SHOP_ID); })->get();

            if($data)
                return response()->json(['flagError' => false, 'data' => $data]);
            else
                return response()->json(['flagError' => true, 'data' => null]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->ajax()) {
            $data           =  User::find($id);
            if($data)
                return response()->json(['flagError' => false, 'therapist' => $data]);
            else
                return response()->json(['flagError' => true, 'data' => null]);
        }
    }
   
}
