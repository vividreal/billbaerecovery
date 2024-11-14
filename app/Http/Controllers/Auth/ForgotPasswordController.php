<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request; 
use Illuminate\Support\Str;
use App\Models\User; 
use Carbon\Carbon;
use Validator;
use Mail; 
use Hash;
use DB; 

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    // use SendsPasswordResetEmails;

    /**
       * Write code on Method
       *
       * @return response()
       */
      public function showForgetPasswordForm()
      {
        return view('auth.forgot-password');
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitForgetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
          ]);
  
          $token = Str::random(64);
  
          DB::table('password_resets')->insert([
              'email' => $request->email, 
              'token' => $token, 
              'created_at' => Carbon::now()
            ]);
  
          Mail::send('email.forgetPassword', ['token' => $token], function($message) use($request){
              $message->to($request->email);
              $message->subject('Reset Password');
          });
  
          return back()->with('message', 'We have e-mailed your password reset link!');
      }
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showResetPasswordForm($token) { 
         return view('auth.forgetPasswordLink', ['token' => $token]);
      }
        /**
       * Write code on Method
       *
       * @return response()
       */
      public function showCreatePasswordForm($token) { 
        return view('auth.createPasswordLink', ['token' => $token]);
     }
      
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|confirmed',
              'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
          if(!$updatePassword){
              return back()->withInput()->with('error', 'Invalid token!');
          }
  
          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
  
          return redirect('/login')->with('message', 'Your password has been changed!');
      }

      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitCreatePasswordForm(Request $request)
      {
          $messages = [
            'email.exists' => 'Not a registered email id'
          ];

          $validator = Validator::make($request->all(), 
              [
                'email' => 'required|email|exists:users',
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required'
              ], $messages);

  
          if ($validator->passes()) {

            $is_valid_user = User::where('email', $request->email)->where('password_create_token', $request->token)->first();

            if(!$is_valid_user){
              $messages = [ 'error' => 'Token expired or Email id not matching !'];
              return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$messages];
            }

            $user = User::where('email', $request->email)->update(['password' => Hash::make($request->password), 'password_create_token' => null]);
            return ['flagError' => false, 'message' => "Your password updated successfully"];
          }
          return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
      }
}
