<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Billing;
use Illuminate\Support\Str;
use DataTables;
use Validator;
use Auth;
use Carbon;

class CashbookController extends Controller
{
    protected $title    = 'Cashbook';
    protected $viewPath = 'cashbook';
    protected $link     = 'cashbook';
    protected $route    = 'cashbook';
    protected $entity   = 'Cashbook';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page                       = collect();
        $variants                   = collect();
        $page->title                = $this->title;
        $page->link                 = url($this->link);
        $page->route                = $this->route;
        $page->entity               = $this->entity; 
        $variants->business_cash    = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 1)->orderBy('created_at', 'desc')->value('balance_amount');  
        $variants->petty_cash       = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 2)->orderBy('created_at', 'desc')->value('balance_amount');  
        return view($this->viewPath . '.list', compact('page', 'variants'));
    }

    /**
     * Display a listing of the resource in datatable.
     * @throws \Exception
     */
    public function lists(Request $request)
    {
        $from   = Carbon\Carbon::parse($request->start_range)->startOfDay();  
        $to     = Carbon\Carbon::parse($request->end_range)->endOfDay();
        $detail =  Cashbook::where('shop_id', SHOP_ID);

        if ( ($from != '') && ($to != '') ) {
            $detail->Where(function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            });
        }

        if ($request['transaction_type'] != '') {
            $transaction    = $request['transaction_type'];
            $detail         = $detail->where(function($query)use($transaction){
                    $query->whereIn('transaction', $transaction);
            }); 
        }
        
        if ($request['cash_from'] != '') {
            $cash_from  = $request['cash_from'];
            $detail     = $detail->where(function($query)use($cash_from){
                    $query->whereIn('cash_from', $cash_from);
            }); 
        }

        if ($request['cash_book'] != '') {
            $cash_book  = $request['cash_book'];
            $detail     = $detail->where(function($query)use($cash_book){
                    $query->whereIn('cash_book', $cash_book);
            }); 
        }

        $detail = $detail->orderBy('created_at', 'DESC')->get();
        return Datatables::of($detail)
            ->addIndexColumn()
            ->editColumn('created_at', function($detail){
                $created_at = new Carbon\Carbon($detail->billed_date);
                return $created_at->toFormattedDateString();
            })
            ->editColumn('billed_date', function($detail){
                $billed_date = new Carbon\Carbon($detail->billed_date);
                return $billed_date->toFormattedDateString();
            })
            ->editColumn('cash_book', function($detail){
                $cash_book = ($detail->cash_book == 1)?"Business Cash":"Petty Cash";
                return $cash_book;
            })
            ->editColumn('amount', function($detail){
                $amount = '₹ '. $detail->transaction_amount;
                return $amount;
            })
            ->addColumn('transaction_type', function($detail){
                $transaction_type   = '';
                $cash_from          = '';
                if ($detail->transaction == 1) {
                    $transaction_type .= '<span class="chip lighten-5 green green-text"> Credit</span>';                   
                } else {  
                    $transaction_type .= '<span class="chip lighten-5 orange orange-text">Debit</span>';                                
                }
                if ($detail->cash_from == 0 && $detail->transaction == 1) {
                    $cash_from .= '<span class="chip lighten-5 green green-text"> Cash Added</span>';
                } else if ($detail->cash_from == 3) {
                    $cash_from .= '<span class="chip lighten-5 red red-text"> Refunded</span>';
                }else if ($detail->cash_from == 0 && $detail->transaction == 2) {
                    $cash_from .= '<span class="chip lighten-5 green green-text"> Cash Debited</span>';
                }else {  
                    $cash_from .= '<span class="chip lighten-5 green green-text">From Sales</span>';                               
                }
                return $transaction_type. ' &nbsp; ' . $cash_from;
            })
            ->addColumn('transaction_from', function($detail){
                $cash_from = '';
                return $cash_from;
            })
            ->addColumn('transaction_by', function($detail){
                $transaction_by = $detail->user->name;
                return $transaction_by;
            })                                                        
            ->editColumn('message', function($detail){
                $message = '';
                $message = Str::limit(strip_tags($detail->message), 30);
                if (strlen(strip_tags($detail->message)) > 40) {
                    $message .= "<a href='javascript:void(0);' id=' . $detail->id . ' onclick='showFullName(\"".$detail->message."\")'>View</a>";
                }
                return $message ;
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
            'cash_book' => 'required',
            'amount' => 'required',
        ]);
        if ($validator->passes()) {
            if ($request->transaction == "add_cash") {
                $current_balance = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', $request->cash_book)->orderBy('created_at', 'desc')->value('balance_amount');
            } else {
                $withdraw_from          = ($request->cash_book == 1)?2:1;
                $status                 = $this->checkWithdrawStatus($withdraw_from, $request->amount);              
                if ($status) {
                    $withdraw           = $this->systemWithdraw($request);
                    $current_balance    = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', $request->cash_book)->orderBy('created_at', 'desc')->value('balance_amount');
                } else {
                    $error = array('message' => 'No sufficient balance in account.');
                    return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$error];
                }
            }
            $obj                        = new Cashbook();  
            $obj->shop_id               = SHOP_ID;	
            $obj->cash_book             = $request->cash_book;
            $obj->transaction_amount    = $request->amount;            
            $obj->balance_amount        = ($request->amount + $current_balance);
            $obj->transaction           = 1;
            $obj->message               = $request->details;
            $obj->done_by               = Auth::user()->id;            
            $obj->save();
            $business_cash              = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 1)->orderBy('created_at', 'desc')->value('balance_amount');  
            $petty_cash                 = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 2)->orderBy('created_at', 'desc')->value('balance_amount');  
            return ['flagError' => false, 'business_cash' => number_format($business_cash,2), 'petty_cash' => number_format($petty_cash,2), 'message' => "Transaction completed successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];
    }

    public function systemWithdraw(Request $request)
    {
        if ($request->cash_book == 2) {
            $cash_book                  = 1;
            $cash_transfer_from         = "Business cash";
            $cash_transfer_to           = "Petty cash";
        } else {
            $cash_book                  = 2;
            $cash_transfer_from         = "Petty cash";
            $cash_transfer_to           = "Business cash";
        }
            $current_balance            = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', $cash_book)->orderBy('created_at', 'desc')->value('balance_amount');
            $obj                        = new Cashbook();  
            $obj->shop_id               = SHOP_ID;	
            $obj->cash_book             = $cash_book;
            $obj->transaction_amount    = $request->amount;
            $obj->balance_amount        = ($current_balance - $request->amount);
            $obj->transaction           = 2;
            $obj->message               = "Auto withdrawal - Cash transferred to ". $cash_transfer_to. " from ". $cash_transfer_from;
            $obj->done_by               = Auth::user()->id;            
            $obj->save();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cash_book' => 'required',
            'amount' => 'required',
        ]);
        if ($validator->passes()) {
            $status                         = $this->checkWithdrawStatus($request->cash_book, $request->amount);
            if ($status) {
                $current_balance            = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', $request->cash_book)->orderBy('created_at', 'desc')->value('balance_amount');
                $obj                        = new Cashbook();  
                $obj->shop_id               = SHOP_ID;	
                $obj->cash_book             = $request->cash_book;
                $obj->transaction_amount    = $request->amount;
                $obj->balance_amount        = ($current_balance - $request->amount);
                $obj->transaction           = 2;
                $obj->message               = $request->withdraw_details;
                $obj->done_by               = Auth::user()->id;            
                $obj->save();
                $business_cash              = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 1)->orderBy('created_at', 'desc')->value('balance_amount');  
                $petty_cash                 = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 2)->orderBy('created_at', 'desc')->value('balance_amount');  
                return ['flagError' => false, 'business_cash' => number_format($business_cash,2), 'petty_cash' => number_format($petty_cash,2), 'message' => "Transaction completed successfully"];
            }
            $error                          = array('message' => 'No sufficient balance in account.');
            return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$error]; 
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check !",  'error'=>$validator->errors()->all()];   
    }

    public function checkWithdrawStatus($cash_book, $amount)
    {
        $business_cash    = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', $cash_book)->orderBy('created_at', 'desc')->value('balance_amount');
        if ($business_cash < $amount) {
            return false;
        }
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cashbook  $cashbook
     * @return \Illuminate\Http\Response
     */
    public function show(Cashbook $cashbook)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cashbook  $cashbook
     * @return \Illuminate\Http\Response
     */
    public function edit(Cashbook $cashbook)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cashbook  $cashbook
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cashbook $cashbook)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cashbook  $cashbook
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cashbook $cashbook)
    {
        //
    }

    public function getCashDetails(Request $request){
        $data=Cashbook::select('balance_amount')->where('shop_id', SHOP_ID)->orderBy('id','DESC')->first();
        if($data){
            return ['flagError' => false, 'business_cash' => number_format($data->balance_amount,2)];
        }
    }
}
