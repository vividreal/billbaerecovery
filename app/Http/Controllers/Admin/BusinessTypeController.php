<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessType;
use Validator;
use DataTables;

class BusinessTypeController extends Controller
{
    protected $title    = 'Business Type';
    protected $viewPath = '/admin/business-type';
    protected $link     = 'business-types';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:manage-business-types', ['only' => ['index','create','store','update','destroy', 'lists']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page               = collect();
        $page->title        = $this->title;
        $page->link         = url($this->link); 
        return view($this->viewPath . '.list', compact('page'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  BusinessType::select(['name', 'id'])->orderBy('id', 'desc');
        // if (isset($request->form)) {
        //     foreach ($request->form as $search) {
        //         if ($search['value'] != NULL && $search['name'] == 'search_name') {
        //             $names = strtolower($search['value']);
        //             $detail->where('name', 'like', "%{$names}%");
        //         }
        //     }
        // }
        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('action', function($detail){
                $action = ' <a  onclick="manageBusinessTypes(' . $detail->id . ')" class="btn btn-primary btn-sm btn-icon mr-2" title="Edit details"> <i class="icon-1x fas fa-pencil-alt"></i></a>';
                $action .= '<button type="button" onclick="softDelete(' . $detail->id . ')" class="btn btn-danger btn-sm btn-icon mr-2"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                return $action;
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true);        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'name' => 'required',
        ];
    
        $messages = [
            'required' => 'Please enter Business type name'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

  
        if ($validator->passes()) {

                $business_type = new BusinessType();
                $business_type->name = $request->name;
                $business_type->save();

            return ['flagError' => false, 'message' => $this->title. " Added successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_type = BusinessType::findOrFail($id);
        if($business_type){
            return ['flagError' => false, 'data' => $business_type];
        }else{
            return ['flagError' => true, 'message' => "Data not found, Try again!"];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $rules = [
            'name' => 'required',
        ];
    
        $messages = [
            'required' => 'Please enter Business type name'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);


        if ($validator->passes()) {
            $business_type = BusinessType::findOrFail($id);
            if($business_type){
                $business_type->name = $request->name;
                $business_type->save();
                return ['flagError' => false, 'message' => $this->title. " Updated successfully"];
            }else{
                return ['flagError' => true, 'message' => "Data not found, Try again!"];
            }
        }
        return ['flagError' => true, 'error'=>$validator->errors()->all()];
            
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $business_type = BusinessType::findOrFail($id);
        $business_type->delete();
        return ['flagError' => false, 'message' => $this->title. " Deleted successfully"];
    }
}
