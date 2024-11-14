<?php

namespace App\Http\Controllers;

use App\Models\Rebook;
use App\Models\RefundCash;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Billing;
use Illuminate\Support\Str;
use App\Helpers\FunctionHelper;
use DataTables;
use Validator;
use Auth;
use Carbon;

class RebookController extends Controller
{
    protected $title    = 'Cancellation Fee';
    protected $viewPath = 'rebook';
    protected $link     = 'Rebook';
    protected $route    = 'rebook';
    protected $entity   = 'Rebook';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page                       = collect();
        $variants                   = collect();
        $page->title                = $this->title;
        $page->link                 = url($this->link);
        $page->route                = $this->route;
        $page->entity               = $this->entity;
        $variants->cancellation_fee_total=Rebook::sum('amount');
        if ($request->ajax()) {
            $details = Rebook::with('parentBilling.customer', 'childBilling')->orderBy('id','DESC')->get();
    
            return Datatables::of($details)
                ->addIndexColumn()
                ->editColumn('billing_code', function ($detail) {
                    // Check if childBilling is set
                    if ($detail->childBilling) {
                        return '<a href="' . url('billings' . '/' . $detail->childBilling->id) . '" class="invoice-action-edit">' . $detail->childBilling->billing_code . '</a>';
                    }
                    return '';
                })
                ->editColumn('cancelled_billing_code', function ($detail) {
                    // Check if parentBilling is set
                    if ($detail->parentBilling) {
                        $refund=RefundCash::where('bill_id', $detail->parentBilling->id)->first();
                        if($refund){
                        return '<a href="' . route('cancelBillInvoice', $refund->id) . '" class="invoice-action-edit">' . $detail->parentBilling->billing_code . '</a>';
                        }
                        
                    }
                    return '';
                })
                ->addColumn('customer', function ($detail) {
                    // Check if childBilling and customer are set
                    if ($detail->childBilling && $detail->childBilling->customer) {
                        return $detail->childBilling->customer->name;
                    }
                    return '';
                })
                ->editColumn('billed_date', function ($detail) {
                    // Check if childBilling is set
                    if ($detail->childBilling) {
                        return FunctionHelper::dateToTimeZone($detail->childBilling->billed_date, 'd-M-Y');
                    }
                    return '';
                })
                ->addColumn('amount', function ($detail) {
                    return $detail->amount ?? '0.00';
                })
                ->removeColumn('id')
                ->escapeColumns([])
                ->make(true);
        }
        return view($this->viewPath . '.rebook', compact('page', 'variants'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
