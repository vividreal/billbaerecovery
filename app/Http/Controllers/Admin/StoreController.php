<?php
    
namespace App\Http\Controllers\Admin;
    
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\ThemeSetting;
use App\Events\StoreRegistered;
use App\Models\User;
use App\Models\ShopBilling;
use App\Models\BillingFormat;
use Spatie\Permission\Models\Role;
use App\Models\BusinessType;
use App\Models\ShopCountry;
use Mail;
use DB;
use Event;
use Validator;
use Auth;
use Hash;
use DataTables;
use Illuminate\Support\Arr;
use App\Models\Shop;
use Spatie\Permission\Models\Permission;
    
class StoreController extends Controller
{
    protected $title    = 'Stores';
    protected $viewPath = '/admin/users';
    protected $link     = 'admin/stores';
    protected $route    = 'stores';
    protected $entity   = 'stores';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {           
            if (!auth()->user()->can('role-list')) {
                return redirect()->route('/')->with('error', 'You do not have permission to access this resource.');
            }
         $this->middleware('permission:user-list|user-create', ['only' => ['index','store']]);
         $this->middleware('permission:user-create', ['only' => ['create','store']]);
         $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:role-delete', ['only' => ['destroy']]);
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
        $page           = collect();
        $page->title    = $this->title;
        $page->link     = url($this->link);
        $page->route    = $this->route;
        $page->entity   = $this->entity;
        if ($request->ajax()) {
            return $this->lists($request);
        }
        return view($this->viewPath . '.list', compact('page'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $detail = User::with(['shop', 'roles'])->select(['name', 'mobile', 'phone_code', 'email', 'is_active', 'id']);
            
            // Handle search filter
            if (isset($request->form)) {
                foreach ($request->form as $search) {
                    if ($search['value'] != NULL && $search['name'] == 'search_name') {
                        $names = strtolower($search['value']);
                        $detail->where('name', 'like', "%{$names}%");
                    }
                }
            }
        
            // Apply parent_id filter
            $detail->where('parent_id', $user_id)->orderBy('id', 'desc');  
            // dd($detail->get());      
            return Datatables::of($detail)
            ->addIndexColumn() // This automatically adds the row index (DT_RowIndex)
            ->addColumn('role', function($detail) {
                    $roles = $detail->roles;
                    $html = '';
                    foreach ($roles as $role) {
                        $html .= $role->name ?? '';
                    }
                    return $html;
                })
                ->addColumn('store', function($detail) {
                    return $detail->shop->name ?? ''; // Ensure shop is loaded properly
                })
                ->addColumn('name', function($detail) {
                    return $detail->name ?? ''; // Ensure shop is loaded properly
                })
                ->addColumn('businesstype', function($detail) {
                    return $detail->shop->business_types->name ?? ''; // Ensure business_types is loaded properly
                })
                ->editColumn('mobile', function($detail) {
                    $phone_code = (!empty($detail->phoneCode->phonecode) ? '+' . $detail->phoneCode->phonecode : '');
                    return $phone_code . ' ' . $detail->mobile ?? '';
                })
                ->editColumn('email', function($detail) {
                
                    return $detail->email;
                })
                ->addColumn('is_active', function($detail) {
                    $checked = ($detail->is_active == 1) ? 'checked' : '';
                    return '<div class="switch"><label><input type="checkbox" ' . $checked . ' id="' . $detail->id . '" class="activate-stores" data-id="' . $detail->id . '"><span class="lever"></span></label></div>';
                })
                ->addColumn('action', function($detail) {
                    return '<a href="' . url('admin/stores/' . $detail->id . '/edit') . '" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                })
                ->removeColumn('id')
                ->escapeColumns([])
                ->make(true);
        
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page                       = collect();
        $variants                   = collect();      
        $page->title                = $this->title;
        $page->link                 = url($this->link);
        $page->form_url             = url($this->link);
        $page->form_method          = 'POST';
        $page->route                = $this->route;
        $page->entity               = $this->entity;
        $variants->business_types   = BusinessType::pluck('name','id')->all();
        // $variants->roles            = Role::where('id', '=' , 2)->pluck('name','name')->all();     
        $variants->roles            = Role::pluck('name','name')->all();   
        $variants->phonecode        = ShopCountry::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->where('status',1)->pluck('phone_code', 'id');         
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
        
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'roles' => 'required'
        ]);
        if ($validator->passes()) {
            DB::beginTransaction();
            try {
            $input                      = $request->all();
            $user_id                    = Auth::user()->id;
            $input['parent_id']         = $user_id;
            $input['mobile']            = $input['mobile'];
            $password          = $this->generateRandomPassword(15);
            // $input['password']=Hash::make('123456');
            $input['password']=Hash::make($password);
            $user                       = User::create($input);
            $details = [
                'user' => $request->name,
                'username'=>$request->email,
                'password' =>$password,
                'link'=>url('/'),
            ];
           \Mail::to($request->email)->send(new \App\Mail\TestMail($details));
            $user->assignRole($request->input('roles'));
            if (Auth::user()->parent_id == null) {
                $shop                   = new Shop();
                $shop->name             = $input['shop_name'];
                $shop->business_type_id = $input['business_type'];
                $shop->user_id          = $user->id;
                $shop->save();  
                $user->shop_id = $shop->id;
            } else {
                $user->shop_id          = Auth::user()->shop_id;
                
            }

            $token                      = Str::random(64);
            $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
            $companyRole = Role::firstOrCreate(['name' => 'Company Admin']);
            $storeRole = Role::firstOrCreate(['name' => 'Store Manager']);
           if($request->roles[0]=='Company Admin'){
                $user->is_admin          = 2;
                $user->assignRole($companyRole);
                $permissions = [
                    'role-list','role-list', 'role-create', 'role-edit', 'role-delete',
                    'user-create', 'user-list', 'user-edit', 'user-delete',
                    'manage-store', 'manage-store-billing',
                    'service-create', 'service-list', 'service-edit', 'service-delete',
                    'package-create', 'package-list', 'package-edit', 'package-delete',
                    'staff-create', 'staff-list', 'staff-edit', 'staff-delete',
                    'staff-document-create', 'staff-document-download', 'staff-document-delete',
                    'schedule-create', 'schedule-list', 'schedule-edit', 'schedule-delete',
                    'billing-create', 'billing-list', 'billing-edit', 'billing-delete',
                    'billing-download', 'bill-overview', 'refund-bill',
                    'cashbook-view', 'cashbook-withdraw-cash', 'cashbook-add-cash',
                    'report-view','customer-list', 'customer-create', 'customer-edit', 'customer-delete',
                    'membership-list', 'membership-create', 'membership-edit', 'membership-delete',
                    'category-list', 'category-create', 'category-edit', 'category-delete',
                    'product-list', 'product-create', 'product-edit', 'product-delete',
                    'stock-list', 'stock-create', 'stock-edit', 'stock-delete',
                    'inventory-list', 'inventory-create', 'inventory-edit', 'inventory-delete',    
                    'attendence-list', 'attendence-create', 'attendence-edit', 'attendence-delete'
                      
                     ];
                      $user->syncPermissions($permissions);

            }
            else if($request->roles[0]=='Store Manager'){
                $user->is_admin          = 3;
                $user->assignRole($storeRole);
                $permissions = [
          
                    'schedule-list',
                    'schedule-create',
                    'schedule-edit',
                    'schedule-delete',
                    'billing-list',
                    'customer-list',
                    'customer-create',
                    'customer-edit',
                    'customer-delete',
                    // Add any other permissions as necessary
                ];
                $user->syncPermissions($permissions);
            }else{
                $user->is_admin          = NULL;
                $user->assignRole($adminRole);
                
                $permissions = Permission::all();
                $user->syncPermissions($permissions);

            }
           
           
            $user->verify_token         = $token;
            $user->save();

            // Store billing details created
            $billing                    = new ShopBilling();
            $billing->shop_id           = $shop->id;
            $billing->save();

            // Store billing format created with default details
            $billing_format             = new BillingFormat();
            $billing_format->shop_id    = $shop->id;
            $billing_format->prefix     = Str::upper(Str::substr(str_replace(' ', '', $shop->name), 0, 3)); 
            $billing_format->suffix     = 1000;
            $billing_format->save();

            // Store theme details created with default styles
            $theme_settings             = new ThemeSetting();
            $theme_settings->shop_id    = $shop->id;
            $theme_settings->save();
            DB::table('roles')
            ->where('name', $request->input('roles'))
            ->update(['shop_id' => $shop->id]);            //Store registration event  
        
                // StoreRegistered::dispatch($user->id);  commented for short term
                DB::commit();
                return ['flagError' => false, 'message' => "Account Added successfully"];
            }catch(Exception $e){
                DB::rollBack();
                return ['flagError' => true, 'message' =>$e->getMessage()];
            }
         

          
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=> $validator->errors()->all()];
    }
    function generateRandomPassword($length = 15) {
        $chars = [
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ', // uppercase letters
            'abcdefghijklmnopqrstuvwxyz', // lowercase letters
            '0123456789', // numbers
        ];
    
        $password = '';
    
        for ($i = 0; $i < $length; $i++) {
            $charsGroup = $chars[random_int(0, count($chars) - 1)];
            $password .= $charsGroup[random_int(0, strlen($charsGroup) - 1)];
        }
    
        return $password;
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // dd('2');
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
        $user                       = User::find($id);
        $page                       = collect();
        $variants                   = collect();
        $page->title                = $this->title;
        $page->link                 = url($this->link);
        $page->form_url             = url($this->link . '/' . $user->id);
        $page->form_method          = 'PUT';   
        $page->route                = $this->route;
        $page->entity               = $this->entity;     
        // $variants->roles            = Role::where('id', '=' , 2)->pluck('name','name')->all();
        $variants->roles            = Role::pluck('name','name')->all();
        $variants->business_types   = BusinessType::pluck('name','id')->all();
        $userRole                   = $user->roles->pluck('name','name')->all();
        $variants->phonecode        = ShopCountry::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->where('status',1)->pluck('phone_code', 'id');         
        return view($this->viewPath . '.create',compact('user','variants','userRole', 'page'));
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
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);
        if ($validator->passes()) {
            $input                  = $request->all();                       
            if (!empty($input['password'])) { 
                $input['password']  = Hash::make($input['password']);
            } else {
                $input              = Arr::except($input,array('password'));    
            }            
            $user                   = User::find($id);
            $input['updated_by']    = Auth::user()->id;
            $user->update($input);
            // $shop_role=new ShopRole();
            // foreach($request['roles'] as $role){
            //     $shop_role->shop_id=$user->shop_id;
            //     $shop_role->role_id=$role->id;
            //     $shop_role->save();
            // }
            
            
            DB::table('model_has_roles')->where('model_id',$id)->delete();
            $user->assignRole($request->input('roles'));
            $permissions = $user->getPermissionsViaRoles();
            $user->givePermissionTo($permissions);
            $shop                   = Shop::find($user->shop_id);
            if($shop==null){
                $shop =new Shop();
            }
            $shop->name             = $input['shop_name'];
            $shop->business_type_id = $input['business_type'];
            $shop->save();

            return ['flagError' => false, 'message' => "Account Updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$validator->errors()->all()];
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('model_has_roles')->where('model_id',$id)->delete();
        User::find($id)->delete();
        return redirect()->route('users.index')->with('success','User deleted successfully');
    }

    public function manageStatus(Request $request)
    {
        $user                   = User::findOrFail($request->user_id);
        if ($user) {
            $status             = ($user->is_active == 0)?1:0;
            $user->is_active    = $request->is_active;
            $user->save();
            return ['flagError' => false, 'message' => $this->title. " status updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors occurred Please check !",  'error'=>$validator->errors()->all()];
    }
}