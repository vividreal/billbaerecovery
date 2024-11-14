<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\FunctionHelper;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use App\Models\Billing;
use App\Models\Shop;
use DataTables;
use Auth;
use Carbon;
use DB;
use PDF;

class ReportController extends Controller
{
    protected $title        = 'Report';
    protected $viewPath     = 'report';
    protected $link         = 'reports';
    protected $route        = 'reports';
    protected $timezone     = '';
    protected $time_format  = 'H';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:report-view', ['only' => ['salesReport','getSalesReportChartData','']]);
        $this->middleware(function ($request, $next) {
            $this->timezone     = Shop::where('user_id', Auth::user()->id)->value('timezone');
            $this->time_format  = (Shop::where('user_id', Auth::user()->id)->value('time_format') == 1)?'h':'H';
            return $next($request);
        });
    }

    public function salesReport(Request $request)
    {
        $page                   = collect();
        $variants               = collect();
        $page->title            = $this->title;
        $page->link             = url($this->link);
        $page->route            = $this->route;
        $variants->start_range  = Carbon\Carbon::now()->startOfMonth()->format('m-d-Y');
        $variants->end_range    = Carbon\Carbon::now()->format('m-d-Y');
        
        return view($this->viewPath . '.sales-report', compact('page', 'variants'));
    }

    public function getSalesReportChartData(Request $request)
    {
        $total_cash         = 0;
        $day_range          = $request->day_range;
        $today = now();
        $from               = Carbon\Carbon::parse($request->start_range)->startOfDay();  
        $to                 = Carbon\Carbon::parse($request->end_range)->endOfDay();
        $total_sales      = Billing::where('shop_id',SHOP_ID)->with('customerPendingMembership')
        ->withTrashed()->whereDate('created_at', $today) 
        ->get();     
        // $chart_reports      = Billing::select( DB::raw("DATE_FORMAT(created_at, '%d %M') as day"), DB::raw("SUM(amount) as amount"), 'id as row_id', 'customer_id', 'payment_status')
        //                         ->where('shop_id', SHOP_ID)
        //                         ->with('customerPendingMembership')->withTrashed()
        //                         ->groupBy(DB::raw("day(created_at)"))

        //                         ->orderBy('created_at', 'ASC')                               
        //                         ->get();                             
        $chart_data         = array();
        $chart_label        = array();
        $customer_array     = array();
        $pending            = 0;
        $completed          = 0;
        $totalSale=0;
        foreach ($total_sales as $key => $saleamount) {
         $total_cash+=$saleamount->amount;        
            if($saleamount->customerPendingMembership && $saleamount->customerPendingMembership->deducted_over_paid!==0){
             $total_cash-=$saleamount->customerPendingMembership->deducted_over_paid;           
            }
            
        }
        $chart_reports = Billing::select(
            DB::raw("DATE_FORMAT(created_at, '%d %M') as day"),
            DB::raw("SUM(amount) as amount"),
            'id as row_id',
            'customer_id',
            'payment_status'
        )
        ->where('shop_id', SHOP_ID)
        ->with('customerPendingMembership')
        ->withTrashed()
        ->whereBetween('created_at', [$from, $to])
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d %M')"))
        ->orderBy('created_at', 'ASC')
        ->get();

    // Process the chart reports and prepare chart data
    foreach ($chart_reports as $value) {
        $adjusted_amount = $value->amount;
        if ($value->customerPendingMembership && $value->customerPendingMembership->deducted_over_paid !== 0) {
            $adjusted_amount -= $value->customerPendingMembership->deducted_over_paid;
        }
        $chart_label[] = $value->day;
        $chart_data[] = (int)$adjusted_amount;
    }

        $report_data            = Billing::select( DB::raw("DATE_FORMAT(created_at, '%d %M') as day"), DB::raw("SUM(actual_amount) as amount"), 'id', 'payment_status', 'billing_code', 'billed_date', 'checkin_time', 'checkout_time', 'customer_id')->where('shop_id', SHOP_ID)->whereBetween('created_at', [$from,$to])->groupBy('billings.id')->orderBy('created_at', 'ASC')->get();
        foreach ($report_data as $data) {
            if (!array_key_exists($data->customer_id,$customer_array)) {
                $customer_array[$data->customer_id] = 1;
            } else {
                $customer_array[$data->customer_id] = $customer_array[$data->customer_id]+1;
            }
            if ($data->payment_status == 0) {
                $pending    = $pending+1;
            } else {  
                $completed  = $completed+1;                              
            }
        }
        return ['flagError' => false, 'chart_label' => $chart_label,  'chart_data'=> $chart_data, 'start_date' => $from, 'end_date' => $to, 'total_cash' => number_format($total_cash,2), 'invoice' => count($report_data), 'customer' => count($customer_array), 'completed' => $completed, 'pending' => $pending,];
    }

    public function getSalesReportTableData(Request $request)
    {
      
        $from       = Carbon\Carbon::parse($request->fromDate);  
        // $to         = Carbon\Carbon::parse($request->toDate)->endOfDay();
        $currentDate = now()->toDateString();   
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year; 
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $detail     =  Billing::where('shop_id', SHOP_ID);
        
        if (!empty($fromDate) && !empty($toDate)) {           
            $detail->whereBetween('created_at', [$fromDate, $toDate]);
        } 
        if (!empty($request->daySelect)=='today') {
                    $detail->whereDate('created_at', $currentDate);
        }elseif (!empty($request->daySelect)=='week') {
                    $detail->whereBetween('created_at', [$startDate, $endDate]);                  
        } elseif (!empty($request->daySelect)=='month') {
                    $detail->whereMonth('created_at', $currentMonth)
                           ->whereYear('created_at', $currentYear);            
        }
        if (!empty($request->yearSelect)) {
           
            $detail->whereYear('created_at', $request->yearSelect);
        }
        else{
            $detail->wheredate('created_at', $from);
        }
        $detail = $detail->orderBy('created_at', 'DESC')->get();
        return Datatables::of($detail)
            ->addIndexColumn()
            ->editColumn('billed_date', function($detail){
                return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-Y '.$this->time_format.':i a');
            })
            ->editColumn('billing_code', function($detail){
                $billing_code = '';
                $billing_code .=' <a href="' . url(ROUTE_PREFIX.'/billings/' . $detail->id) . '" target="_blank">'.$detail->billing_code.'</a>';
                return $billing_code;
            })
            ->editColumn('customer_id', function($detail){
                $customer = $detail->customer->name;
                return $customer;
            })
            ->editColumn('amount', function($detail){
                $amount = $detail->amount;
                return $amount;
            })
            ->editColumn('payment_status', function($detail){
                $status = '';                
                if ($detail->payment_status == 0) {
                    $status = '<span class="chip lighten-5 red red-text">UNPAID</span>';
                }
                elseif ($detail->payment_status == 1) {
                    $status = '<span class="chip lighten-5 green green-text">PAID</span>';
                }
                elseif ($detail->payment_status == 2) {
                    $status = '<span class="chip lighten-5 orange orange-text">CANCELLED</span>';
                }
                elseif ($detail->payment_status == 3) {
                    $status = '<span class="chip lighten-5 red red-text">PARTIALY PAID</span>';
                } else {
                    $status = '<span class="chip lighten-5 blue blue-text">ADDITIONALY PAID</span>';
                }
                return $status;
            })
            ->addColumn('in_out_time', function($detail){
                $checkin_time   =  FunctionHelper::dateToTimeZone($detail->checkin_time, 'd-M-Y '.$this->time_format.':i a');
                $checkout_time  =  FunctionHelper::dateToTimeZone($detail->checkout_time, 'd-M-Y '.$this->time_format.':i a');
                $in_out_time    = $checkin_time . ' - ' . $checkout_time;
                return $in_out_time;
            })
            ->addColumn('payment_method', function($detail){
                $methods         = '';
                foreach($detail->paymentMethods as $row){
                    $methods .= $row->payment_type. ', ';  
                }
                return rtrim($methods, ', ');
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true); 
            
    }

    public function exportReport(Request $request) 
    {
        return Excel::download(new ReportExport($request->start_range, $request->end_range ), 'invoices.csv', \Maatwebsite\Excel\Excel::CSV);
    }
    public function dailyReport(Request $request){
        $detail     =  Billing::select( DB::raw("DATE_FORMAT(created_at, '%d %M') as day"), DB::raw("SUM(amount) as amount"), 'id', 'payment_status', 'billing_code', 'billed_date', 'checkin_time', 'checkout_time', 'customer_id')
        ->where('payment_status', 1)
        ->where('shop_id', SHOP_ID)
        ->groupBy('billings.id')
        ->orderBy('created_at', 'DESC')->get();
        return Datatables::of($detail)
            ->addIndexColumn()
            ->editColumn('billed_date', function($detail){
                return FunctionHelper::dateToTimeZone($detail->billed_date, 'd-M-Y '.$this->time_format.':i a');
            })
            ->editColumn('billing_code', function($detail){
                $billing_code = '';
                $billing_code .=' <a href="' . url(ROUTE_PREFIX.'/billings/' . $detail->id) . '" target="_blank">'.$detail->billing_code.'</a>';
                return $billing_code;
            })
            ->editColumn('customer_id', function($detail){
                $customer = $detail->customer->name;
                return $customer;
            })
            ->editColumn('amount', function($detail){
                $amount = $detail->amount;
                return $amount;
            })
            ->editColumn('payment_status', function($detail){
                $status = '';
                if ($detail->payment_status == 0) {
                    $status = '<span class="chip lighten-5 red red-text">UNPAID</span>';
                } else {  
                    $status = '<span class="chip lighten-5 green green-text">PAID</span>';                                
                }
                return $status;
            })
            ->addColumn('in_out_time', function($detail){
                $checkin_time   =  FunctionHelper::dateToTimeZone($detail->checkin_time, 'd-M-Y '.$this->time_format.':i a');
                $checkout_time  =  FunctionHelper::dateToTimeZone($detail->checkout_time, 'd-M-Y '.$this->time_format.':i a');
                $in_out_time    = $checkin_time . ' - ' . $checkout_time;
                return $in_out_time;
            })
            ->addColumn('payment_method', function($detail){
                $methods         = '';
                foreach($detail->paymentMethods as $row){
                    $methods .= $row->payment_type. ', ';  
                }
                return rtrim($methods, ', ');
            })
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true); 
    }


    public function reportFilter(Request $request){
        $currentDate = now()->toDateString();
$startDate = now()->startOfWeek()->toDateString();
$endDate = now()->endOfWeek()->toDateString();
$currentMonth = now()->month;
$currentYear = now()->year;
$customer_array = [];
$pending = 0;
$completed = 0;
$totalSale = 0;
$total_cash=0;

$fromDate = $request->input('fromDate');
$toDate = $request->input('toDate');

$total_sales_query = Billing::where('shop_id', SHOP_ID)->with('customerPendingMembership')->withTrashed();
$report_data_query = Billing::select(
    DB::raw("DATE_FORMAT(created_at, '%d %M') as day"),
    DB::raw("SUM(actual_amount) as amount"),
    'id',
    'payment_status',
    'billing_code',
    'billed_date',
    'checkin_time',
    'checkout_time',
    'customer_id'
)->where('shop_id', SHOP_ID);

if ($request->filled('year')) {
    $total_sales_query->whereYear('created_at', $request->year);
    $report_data_query->whereYear('created_at', $request->year);
}

if ($request->filled(['fromDate', 'toDate'])) {
    $total_sales_query->whereBetween('created_at', [
        Carbon\Carbon::parse($request->fromDate)->startOfDay(),
        Carbon\Carbon::parse($request->toDate)->endOfDay()
    ]);
    $report_data_query->whereBetween('created_at', [
        Carbon\Carbon::parse($request->fromDate)->startOfDay(),
        Carbon\Carbon::parse($request->toDate)->endOfDay()
    ]);
}

if ($request->day == 'today') {
    $total_sales_query->whereDate('created_at', $currentDate);
    $report_data_query->whereDate('created_at', $currentDate);
} elseif ($request->day == 'week') {
    $total_sales_query->whereBetween('created_at', [$startDate, $endDate]);
    $report_data_query->whereBetween('created_at', [$startDate, $endDate]);
} elseif ($request->day == 'month') {
    $total_sales_query->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear);
    $report_data_query->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear);
}

$total_sales = $total_sales_query->get();
$report_data = $report_data_query->groupBy('billings.id')->orderBy('created_at', 'ASC')->get();

foreach ($total_sales as $key => $saleamount) {
    $total_cash += $saleamount->amount;
    if ($saleamount->customerPendingMembership && $saleamount->customerPendingMembership->deducted_over_paid !== 0) {
        $total_cash -= $saleamount->customerPendingMembership->deducted_over_paid;
    }
}

foreach ($report_data as $data) {
    if (!array_key_exists($data->customer_id, $customer_array)) {
        $customer_array[$data->customer_id] = 1;
    } else {
        $customer_array[$data->customer_id] = $customer_array[$data->customer_id] + 1;
    }
    if ($data->payment_status == 0) {
        $pending = $pending + 1;
    } else {
        $completed = $completed + 1;
    }
}
        return ['flagError' => false, 'total_cash' => number_format($total_cash,2), 'invoice' => count($report_data), 'customer' => count($customer_array), 'completed' => $completed, 'pending' => $pending,];

    }
   
   

}
