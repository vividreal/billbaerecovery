<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use DataTables;
use Validator;

class PaymentTypeController extends Controller
{
    protected $title    = 'Payment Types';
    protected $viewPath = 'payment-type';
    protected $link     = 'payment-types';
    protected $route    = 'payment-types';
    protected $entity   = 'paymentTypes';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
        $detail =  PaymentType::where('shop_id', SHOP_ID)->orderBy('id', 'desc');  
        return Datatables::of($detail)
            ->addIndexColumn()
            ->addColumn('action', function($detail){
                $action = ' <a  href="javascript:" onclick="managePaymentType(' . $detail->id . ')" class="btn mr-2 cyan" title="Edit details"><i class="material-icons">mode_edit</i></a>';
                $action .= '<a href="javascript:void(0);" id="' . $detail->id . '" onclick="deletePaymentTypes(this.id)"  class="btn btn-danger btn-sm btn-icon mr-2" title="Delete"><i class="material-icons">delete</i></a>';
                return $action;
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
        $validator = Validator::make($request->all(), [ 'payment_type' => 'required' ]);
            if ($validator->passes()) {
                $data               = new PaymentType();
                $data->shop_id      = SHOP_ID;

                if ($request->ajax()) {
                    $data->name         = $request->payment_type;
                    if($data->save()) {
                        $tableHtml = '';
                        $tableHtml.=    '<tr id="'.$request->row_id.'">';
                        $tableHtml.=    '<td><p class="mb-1"><label><input type="checkbox" class="payment-types" name="payment_types[]" data-type="'.$data->id.'" id="payment_types_'.$data->id.'"  value="'.$data->id.'"><span></span></label></th>';
                        $tableHtml.=    '<td>'.$data->name.'</td>';
                        $tableHtml.=    '<td><a href="#"><i class="material-icons yellow-text">edit</i></a> <a href="javascript:" data-shop_id="'.$data->shop_id.'"  id="'.$data->id.'" class="deletePaymentTypes"><i class="material-icons pink-text">clear</i></a></td>';
                        $tableHtml.=    '</tr>';
                        return response()->json(['flagError' => false, 'data'=> $data, 'html' => $tableHtml]);
                    } else {
                        return response()->json(['flagError' => true,'message'=>'Errors Occurred. Please check !']);
                    }    
                } else {
                    $data               = new PaymentType();
                    $data->shop_id      = SHOP_ID;
                    $data->name         = $request->name;
                    $data->save();
                    return ['flagError' => false, 'message' => $this->title. " added successfully"];
                }
            }
            return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];

        // if ($request->ajax()) {
        //     $data->name         = $request->payment_type;
        //     if($data->save()) {
        //         $tableHtml = '';
        //         $tableHtml.=    '<tr id="'.$request->row_id.'">';
        //         $tableHtml.=    '<th><p class="mb-1"><label><input type="checkbox" class="payment-types" name="payment_types[]" data-type="'.$data->id.'" id="payment_types_'.$data->id.'"  value="'.$data->id.'"><span></span></label></th>';
        //         $tableHtml.=    '<th>'.$data->name.'</th>';
        //         $tableHtml.=    '<th><a href="#"><i class="material-icons yellow-text">edit</i></a> <a href="#"><i class="material-icons pink-text">clear</i></a></th>';
        //         $tableHtml.=    '</tr>';
        //         return response()->json(['flagError' => false, 'data'=> $data, 'html' => $tableHtml]);
        //     } else {
        //         return response()->json(['flagError' => true,'message'=>'Errors Occurred. Please check !']);
        //     }
                    
        // } else {
        //     $validator = Validator::make($request->all(), [ 'name' => 'required' ]);
        //     if ($validator->passes()) {
        //         $data               = new PaymentType();
        //         $data->shop_id      = SHOP_ID;
        //         $data->name         = $request->name;
        //         $data->save();
        //         return ['flagError' => false, 'message' => $this->title. " added successfully"];
        //     }
        //     return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentType  $PaymentType
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentType $PaymentType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentType  $PaymentType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data       = PaymentType::findOrFail($id);
        if ($data) {
            return ['flagError' => false, 'data' => $data];
        }else{
            return ['flagError' => true, 'message' => "Data not found, Try again!"];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentType  $PaymentType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [ 'name' => 'required' ]);

        if ($validator->passes()) {

            if ($request->ajax()) {
                $data               = PaymentType::findOrFail($request->id);
            } else{
                $data               = PaymentType::findOrFail($id);
            }
            $data->name         = $request->name;
            $data->save();
            return ['flagError' => false, 'message' => $this->title. " updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentType  $PaymentType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data   = PaymentType::with('billings')->findOrFail($id);
        if(count($data->billings) > 0 ) {
            return ['flagError' => true, 'message' => "Error!, Payment type is used in billings. "];
        } else {
            $data->delete();
            return ['flagError' => false, 'message' => Str::singular($this->title). " deleted successfully"]; 
        }
        
    }
}
