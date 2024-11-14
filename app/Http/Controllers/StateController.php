<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DataTables;
use Validator;

class StateController extends Controller
{
    protected $title    = 'State';
    protected $viewPath = 'state';
    protected $link     = 'states';
    protected $route    = 'states';
    protected $entity   = 'state';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:manage-location', ['only' => ['index','store', 'edit', 'destroy']]);
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
        $page->title            = $this->title;
        $page->link             = url($this->link);
        $page->route            = $this->route;
        $page->entity           = $this->entity;        
        $variants->countries    = Country::where('shop_id', SHOP_ID)->pluck('name', 'id');        
        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  State::where('shop_id', SHOP_ID)->orderBy('id', 'desc');
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
                    $action = ' <a  href="javascript:" onclick="manageState(' . $detail->id . ')" class="btn btn-primary btn-sm btn-icon mr-2" title="Edit details"> <i class="icon-1x fas fa-pencil-alt"></i></a>';
                    $action .= '<a href="javascript:void(0);" id="' . $detail->id . '" onclick="softDelete(this.id)"  class="btn btn-danger btn-sm btn-icon mr-2" title="Delete"> <i class="icon-1x fas fa-trash-alt"></i></a>';
                    return $action;
                })
                ->addColumn('country', function($detail){
                    $country = $detail->country->name;
                    return $country;
                })
                ->removeColumn('id')
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
        //
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
                'required',Rule::unique('states')->where(function($query) {
                  $query->where('shop_id', '=', SHOP_ID);
              })
            ],
            'country_id' => 'required',
        ]);

        if ($validator->passes()) {
            $data               = new State();
            $data->name         = $request->name;
            $data->country_id   = $request->country_id;
            $data->shop_id      = SHOP_ID;
            $data->save();
            return ['flagError' => false, 'message' => $this->title. " Added successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\State  $state
     * @return \Illuminate\Http\Response
     */
    public function show(State $state)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\State  $state
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data       = State::with('country')->findOrFail($id);
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
     * @param  \App\Models\State  $state
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',Rule::unique('states')->where(function($query) use($id) {
                  $query->where('shop_id', '=', SHOP_ID)->where('id', '!=', $id);
              })
            ],
            'country_id' => 'required',
        ]);


        if ($validator->passes()) {
            $data                   = State::findOrFail($id);
            if ($data) {
                $data->name         = $request->name;
                $data->country_id   = $request->country_id;
                $data->shop_id      = SHOP_ID;
                $data->save();
                return ['flagError' => false, 'message' => $this->title. " updated successfully"];
            } else {
                return ['flagError' => true, 'message' => "Data not found, Try again!"];
            }
        }
        return ['flagError' => true, 'error'=>$validator->errors()->all()];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(State $state)
    {
        //
    }
}
