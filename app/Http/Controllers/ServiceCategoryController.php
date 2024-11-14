<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use Illuminate\Validation\Rule;
use DataTables;
use Validator;

class ServiceCategoryController extends Controller
{
    protected $title    = 'Service Category';
    protected $viewPath = 'service-category';
    protected $link     = 'service-category';
    protected $route    = 'service-category';
    protected $entity   = 'serviceCategory';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:service-category-list|service-category-create|service-category-edit|service-category-delete', ['only' => ['index','show']]);
        // $this->middleware('permission:service-category-create', ['only' => ['create','store']]);
        // $this->middleware('permission:service-category-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:service-category-delete', ['only' => ['destroy']]);
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
        return view($this->viewPath . '.list', compact('page'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  ServiceCategory::select(['name', 'id'])->where('shop_id', SHOP_ID)->orderBy('id', 'desc');
        if (isset($request->form)) {
            foreach ($request->form as $search) {
                if ($search['value'] != NULL && $search['name'] == 'search_name') {
                    $names = strtolower($search['value']);
                    $detail->where('name', 'like', "%{$names}%");
                }
            }
        }
        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('action', function($detail){
                $action = ' <a  href="javascript:" onclick="manageserviceCategory(' . $detail->id . ')" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                $action .= '<a href="javascript:void(0);" id="' . $detail->id . '" onclick="softDelete(this.id)" onclick="softDelete(this.id)"  class="btn btn-danger btn-sm btn-icon mr-2" title="Delete"><i class="material-icons">delete</i></a>';
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
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',Rule::unique('service_categories')->where(function($query) {
                  $query->where('shop_id', '=', SHOP_ID);
              })
            ],
        ]);

        if ($validator->passes()) {
            $data           = new ServiceCategory();
            $data->name     = $request->name;
            $data->shop_id  = SHOP_ID;
            $data->save();
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
        $data = ServiceCategory::findOrFail($id);
        if ($data) {
            return ['flagError' => false, 'data' => $data];
        } else {
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
            'name' => [
                'required',Rule::unique('service_categories')->where(function($query) use($id) {
                  $query->where('shop_id', '=', SHOP_ID)->where('id', '!=', $id);
              })
            ],
        ];
        $messages   = [ 'required' => 'Please enter Service Category name' ];
        $validator  = Validator::make($request->all(), $rules, $messages);


        if ($validator->passes()) {
            $data           = ServiceCategory::findOrFail($id);
            if ($data) {
                $data->name = $request->name;
                $data->save();
                return ['flagError' => false, 'message' => $this->title. " updated successfully"];
            }else{
                return ['flagError' => true, 'message' => "Data not found, Try again!"];
            }
        }
        return ['flagError' => true, 'error'=>$validator->errors()->all()];
    }

    public function autocomplete(Request $request)
    {
        $data = array();
        $result   = ServiceCategory::select("name", "id")->where('shop_id', SHOP_ID)->where("name","LIKE","%{$request->search}%")->get();
        if ($result) {
            foreach($result as $row) {
                $data[] = array([ 'id' => $row->id, 'name' => $row->name]);
            }
        } else {
            $data = [];
        }
        return response()->json($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $data   = ServiceCategory::with('services')->findOrFail($id);
        if (count($data->services) > 0 ) {
            return ['flagError' => true, 'message' => "Please delete all services of item !"];
        }
        $data->delete();
        return ['flagError' => false, 'message' => $this->title. " deleted successfully"];
    }
}
