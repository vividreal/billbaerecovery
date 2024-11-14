<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use App\Models\Country;
use App\Models\User;
use Validator;
use Hash;
use DB;
/**
 * Class PageController
 */

class ProfileController extends Controller
{
    protected $title        = 'Profile';
    protected $viewPath     = 'profile';
    protected $route        = 'profile';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:manage-store', ['only' => ['index', 'update', 'updateLogo']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page                   = collect();
        $variants               = collect();
        $user                   = auth()->user();
        $page->title            = $this->title;
        $page->link             = url($this->route);
        $page->route            = $this->route; 
        $variants->phoneCode    = Country::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->where('status',1)->pluck('phone_code', 'id'); 
        return view($this->viewPath . '.profile', compact('page', 'user', 'variants'));
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
        $validator = Validator::make($request->all(), 
                            [ 'name' => 'required', 'email' => 'required|email|unique:users,email,'.$id, 'mobile' => 'nullable|min:3|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,email,'.$id],
                            [ 'name.required' => 'Please enter First name', 'email.required' => 'Please enter E-mail'] );

        if ($validator->passes()) {
            $user               = auth()->user();
            $user->name         = $request->name;
            $user->email        = $request->email;
            $user->mobile       = $request->mobile;
            $user->phone_code   = $request->phone_code;
            $user->save();
            return ['flagError' => false, 'message' => "Details updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$validator->errors()->all()];
    }

    /**
     * Update User Password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_password_confirmation' => ['same:new_password'],
        ]);
        if ($validator->passes()) {

            if(strcmp($request->get('old_password'), $request->get('new_password')) == 0){
                $errors = array('Old and new passwords cannot be same !');
                return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$errors];
            }

            User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
            return ['flagError' => false, 'message' => $this->title. " password updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$validator->errors()->all()];
    }

    /**
     * Update User Photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateUserPhoto(Request $request)
    {
        $validation = $request->validate([
            'image' => 'required',
        ], [
            'image.required' => 'The image is required.',
        ]);
        
        if ($validation) {
            $image_64 = $request->image; // your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1); 
            $image = str_replace($replace, '', $image_64); 
            $image = str_replace(' ', '+', $image); 
            $decodedImage = base64_decode($image);
        
            // Validate the decoded image size
            if (strlen($decodedImage) > 1024 * 1024) {
                return response()->json([
                    'flagError' => true,
                    'message' => "The image must not be greater than 1 MB.",
                    'errors' => ['image' => ["The image must not be greater than 1 MB."]]
                ], 422);
            }
        
            if (auth()->user()->profile != '') {
                \Illuminate\Support\Facades\Storage::delete('public/store/users/' . auth()->user()->profile);
            }
        
            // Create storage folder
            $path = 'public/store/users/';
        
            if (!\Illuminate\Support\Facades\Storage::exists($path)) {
                \Illuminate\Support\Facades\Storage::makeDirectory($path, 0755, true);
            }
        
            $imageName = time() . auth()->user()->id . '.' . $extension;
            try {
                \Illuminate\Support\Facades\Storage::put($path . $imageName, $decodedImage);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error storing image: ' . $e->getMessage());
            }
        
            $user = \Auth::user();
            $user->profile = $imageName;
            $user->save();
            return ['flagError' => false, 'logo' => auth()->user()->profile_url, 'message' => "Photo updated successfully"];
        }else{
            return ['flagError' => true, 'message' => 'Validation failed', 'errors' => $validation->errors()];
        }
    }
    
}
