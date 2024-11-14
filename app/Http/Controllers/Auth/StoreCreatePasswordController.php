<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Str;
use App\Models\User; 
use Carbon\Carbon;
use Validator;
use Crypt;
use Mail; 
use Hash;
use DB; 

class StoreCreatePasswordController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function get($token)
    {
        return view('auth.createPasswordForm', ['token' => $token]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required'
        ]);

        if ($validator->passes()) {

            $updatePassword = User::where([
                                'email'         => $request->email, 
                                'verify_token'  => Crypt::decryptString($request->token)
                                ])->first();

            if(!$updatePassword) {
                $errors = array('Errors Occurred. Invalid token! !');
                return ['flagError' => true, 'message' => "Invalid token!", 'error'=> $errors];
            }

            $user = User::where('email', $request->email)
                        ->update(['password' => Hash::make($request->password), 'verify_token' => null]);

            return ['flagError' => false, 'message' => "Your password has been changed successfully!"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=> $validator->errors()->all()];
    }
}
