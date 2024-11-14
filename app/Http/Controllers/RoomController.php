<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Validator;
use DataTables;

class RoomController extends Controller
{
    protected $title        = 'Rooms';
    protected $viewPath     = 'rooms';
    protected $route        = 'rooms';

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
        $page->route            = $this->route;
        return view($this->viewPath . '.list', compact('page', 'variants'));
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
        $page->route                = $this->route;  
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
            'name' => 'required'
        ]);
        if ($validator->passes()) {

            $room               = new Room();
            $room->name         = $request->name;
            $room->description  = $request->description;
            $room->shop_id      = SHOP_ID;
            $room->save();
            return ['flagError' => false, 'message' => \Illuminate\Support\Str::singular($this->title). " created successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $detail =  Room::where('shop_id', SHOP_ID)->orderBy('id', 'desc');
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
                $action     = ' <a  href="' . url( $this->route .'/' . $detail->id . '/edit') . '" class="waves-effect waves-light  btn gradient-45deg-light-blue-cyan box-shadow-none border-round mr-1 mb-1" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                $action     .= '<a href="javascript:void(0);" data-id="' . $detail->id . '" data-url="'.url($this->route).'" class="waves-effect waves-light  btn gradient-45deg-red-pink box-shadow-none border-round mr-1 mb-1 delete-item" title="Delete"><i class="material-icons">delete</i></a>';
                return $action;
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Room $room)
    {
        if ($request->ajax()) {
            if($room)
                return response()->json(['flagError' => false, 'room' => $room]);
            else
                return response()->json(['flagError' => true, 'data' => null]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function edit(Room $room)
    {
        if ($room) { 
            $page                       = collect();
            $variants                   = collect();
            $page->title                = $this->title;
            $page->route                = $this->route;
            return view($this->viewPath . '.create', compact('page', 'room' ,'variants'));
        } else {
            return redirect($this->route)->with('error', $this->title.' not found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Room $room)
    {
        $validator      = Validator::make($request->all(), [ 'name' => 'required', ]);
        if ($validator->passes()) {
            $room->name         = $request->name;
            $room->description  = $request->description;
            $room->shop_id      = SHOP_ID;
            $room->save();
            return ['flagError' => false, 'message' => \Illuminate\Support\Str::singular($this->title). " updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=> $validator->errors()->all()];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return ['flagError' => false, 'message' => \Illuminate\Support\Str::singular($this->title). " deleted successfully"];
    }

    /**
     * Return all records
     * @throws \Exception
     */
    public function getAll(Request $request)
    {
        $room           = Room::where('shop_id', SHOP_ID)->get(['rooms.id', 'rooms.name as title']);

        if($room)
            return response()->json(['flagError' => false, 'data' => $room]);
        else
            return response()->json(['flagError' => true, 'data' => null]);
    }
}
