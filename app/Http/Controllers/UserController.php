<?php
    
namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\StaffProfile;
use Illuminate\Support\Str;
use Validator;
use DataTables;
use DB;

// use Illuminate\Support\Facades\Crypt;
// use App\Http\Controllers\Controller;
// use App\Rules\MatchOldPassword;
// use Auth;
// use Hash;
// use Illuminate\Support\Arr;
// use App\Models\Shop;
// use Mail; 

    
class UserController extends Controller
{
    protected $title    = 'Users';
    protected $viewPath = 'users';
    protected $route    = 'users';

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
        if ($request->ajax()) {
            return $this->lists($request); 
        }
        $page           = collect();
        $page->title    = $this->title;
        $page->link     = url($this->route);
        $page->route    = $this->route;
        return view($this->viewPath . '.list', compact('page'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail     = User::select(['name', 'mobile', 'email', 'is_active', 'id', 'deleted_at'])->where('parent_id', auth()->user()->id)->orderBy('id', 'desc');

        if (isset($request->form)) {
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['value'] == 'deleted') {
                    $detail         = $detail->onlyTrashed();
                }
            }
        }

        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('role', function($detail) {
                $html = '';
                if ($detail->deleted_at == null) { 
                    if ($detail->roles) {
                        foreach($detail->roles as $role) {
                            $html.= $role->name;
                        }
                    }
                }
                return $html;
            })
            ->addColumn('activate', function($detail){
                if ($detail->is_active != 2) {
                    $checked    = ($detail->is_active == 1) ? 'checked' : '';
                    $html       = '<div class="switch"><label> <input type="checkbox" '.$checked.' data-url="'.url($this->route.'/update-status').'" class="manage-status" data-id="'.$detail->id.'"> <span class="lever"></span> </label> </div>';
                    return $html;
                }
            })
            ->addColumn('action', function($detail){
                 if ($detail->deleted_at == null) { 
                    $action     = ' <a  href="' . url('users/' . $detail->id . '/edit') . '" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                    $action     .= '<a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="btn btn-danger btn-sm btn-icon mr-2 disable-item" title="Inactivate"><i class="material-icons">block</i></a>';
                } else {
                    $action     = ' <a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="btn mr-2 green restore-item" title="Restore"><i class="material-icons">restore</i></a>';
                    // $action .= '<a href="javascript:void(0);" id="' . $detail->id . '" onclick="hardDelete(this.id)" data-type="delete" class="btn btn-danger btn-sm btn-icon mr-2" title="Delete"><i class="material-icons">delete</i></a>';
                }
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
        $page->title        = $this->title;
        $page->link         = url($this->route);
        $page->route        = $this->route;
        $roles              = Role::where('shop_id', SHOP_ID)->orderBy('id', 'asc')->pluck('name','name')->all();
        return view($this->viewPath . '.create', compact('page', 'roles'));
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
            'roles' => 'required'
        ]);
        if ($validator->passes()) {

            $input                  = $request->all();
            $input['parent_id']     = auth()->user()->id;   
            $user                   = User::create($input);
            
            $user->assignRole($request->input('roles'));

            $user->shop_id  = SHOP_ID;
            $user->password_create_token    = Str::random(64);
            $user->save();

            $profile                = new StaffProfile();
            $profile->user_id       = $user->id;
            $profile->save();

            // Password create link
            // Mail::send('email.passwordCreate', ['token' => $token], function($message) use($request) {
            //     $message->to($request->email);
            //     $message->subject('Create New Password Email');
            // });
            
            return ['flagError' => false, 'message' => "User created successfully"];
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
        // return view('users.show', compact('user'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user               = User::find($id);
        $page               = collect();
        $page->title        = $this->title;
        $page->route        = $this->route;
        $page->link         = url($this->route);
        $roles              = Role::where('shop_id', SHOP_ID)->pluck('name','name')->all();
        $userRole           = $user->roles->pluck('name','name')->where('name', '!=' , 'Super Admin')->where('name', '!=' , 'Store')->all();
        return view($this->viewPath . '.create',compact('user','roles', 'userRole', 'page'));
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
            'roles' => 'required'
        ]);
        if ($validator->passes()) {
            $input                  = $request->all();
            $user                   = User::find($id);
            $input['gender']        = $request->gender;     
            $user->update($input);

            DB::table('model_has_roles')->where('model_id',$id)->delete();
            $user->assignRole($request->input('roles'));
            return ['flagError' => false, 'message' => "User details updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !",  'error'=> $validator->errors()->all()];
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return ['flagError' => false, 'message' => $this->title. " deactivated successfully"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $user   = User::where('id', $id)->withTrashed()->first();
        $user->restore();
        return ['flagError' => false, 'message' => " User activated successfully"];
    }

    public function updateStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        if ($user) {
            $status             = ($user->is_active == 0)?1:0;
            $user->is_active    = $status;
            $user->save();
            return ['flagError' => false, 'message' => $this->title. " status updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !",  'error'=>$validator->errors()->all()];
    }

    // public function isUnique(Request $request)
    // { 
    //     if ($request->user_id == 0) {
    //         $count = User::where('email', $request->email)->count();
    //         echo ($count > 0 ? 'false' : 'true');
    //     } else {
    //         $count = User::where('email', $request->email)->where('id', '!=' , $request->user_id)->count();
    //         echo ($count > 0 ? 'false' : 'true');
    //     }
    // }

}