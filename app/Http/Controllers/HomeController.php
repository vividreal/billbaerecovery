<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Schedule;
use App\Models\CustomerPendingPayment;
use App\Models\Package;
use App\Models\Service;
use App\Models\Billing;
use App\Models\Customer;
use App\Models\User;
use App\Models\BillingItem;
use App\Models\BillAmount;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\DB;
use DataTables;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $currentYear = now()->year; 
        $today = Carbon::today();

        $customer=Customer::where('shop_id',SHOP_ID)->whereDate('created_at', $today)->count();
        $therapist=User::where('shop_id',SHOP_ID)->whereDate('created_at', $today)->whereHas('staffProfile',function($query){
            $query->where('is_staff',1);

        })->count();
        $services       = Service::where('shop_id',SHOP_ID)->whereDate('created_at', $today)->count();
        $packages       = Package::where('shop_id',SHOP_ID)->whereDate('created_at', $today)->count();
        $total_dues     = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })->whereDate('created_at', $today)->where('expiry_status',0)->where('removed',0)->sum('current_due');
        
        $total_instore = CustomerPendingPayment::whereHas('customer', function($query) {
                $query->where('shop_id',SHOP_ID); // Replace '2' with the actual SHOP_ID value
            })
            ->where('expiry_status',0)->where('is_membership',0)
            // ->whereDate('created_at', now())
            ->sum('over_paid');
        $total_instore_balance = CustomerPendingPayment::whereHas('customer', function($query) {
                $query->where('shop_id',SHOP_ID); 
            })
            ->where('expiry_status', 0)
            ->where('removed', 0)
            ->where('is_membership',0)
            // ->whereDate('created_at', now())
            ->sum('over_paid');

        $total_instore_used = CustomerPendingPayment::whereHas('customer', function($query) {
                $query->where('shop_id',SHOP_ID); 
            })
            ->where('expiry_status',0)->where('is_membership',0) ->where('removed', 1)
            // ->whereDate('created_at', now())
            ->sum('deducted_over_paid');


        $total_membership_instore  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })
        ->where('expiry_status',0)->where('is_membership',1)
        // ->whereDate('created_at',$today)
        ->sum('amount_before_gst');


        $total_membership_instore_balance= CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })
        ->where('expiry_status',0)->where('is_membership',1)
        // ->whereDate('created_at',$today)
        ->where('removed', 0)
        ->sum('over_paid');
        $total_membership_instore_used= CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })
        ->where('expiry_status',0)->where('is_membership',1)
        // ->whereDate('created_at',$today)
        ->sum('deducted_over_paid');
        $totalSaleAmount= Billing::where('shop_id',SHOP_ID)
       ->whereDate('created_at', $today)
        ->sum('amount');
        $totalSaleAmountPaid=Billing::where('shop_id',SHOP_ID)->withTrashed()
        ->whereDate('created_at', $today)->sum('actual_amount');
        $additionallyPaidAmount  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID)->where('is_membership',0);
        })->sum('amount_before_gst');
        $totalSaleAmountPaid-=$additionallyPaidAmount;
        $serviceAmount =Billing::where('shop_id',SHOP_ID)->whereDate('created_at', $today)
        ->whereHas('items',function($query){
            $query->where('item_type','services');
        })->sum('amount');
        $packageAmount=Billing::where('shop_id',SHOP_ID)->whereDate('created_at', $today)->whereHas('items',function($query){
            $query->where('item_type','packages');
        })->sum('amount');
        
        // $totalSaleAmount_paid= Billing::where('shop_id',SHOP_ID)->where('payment_status',1)->whereDate('created_at', $today)->sum('actual_amount');
        $serviceAmount_paid =Billing::where('shop_id',SHOP_ID)->whereIn('payment_status',[1,3,4,5,6])->whereDate('created_at', $today)
        ->whereHas('items',function($query){
            $query->where('item_type','services');
        })->sum('actual_amount');
        $packageAmount_paid=Billing::where('shop_id',SHOP_ID)->whereIn('payment_status',[1,3,4,5,6])->whereDate('created_at', $today)->whereHas('items',function($query){
            $query->where('item_type','packages');
        })->sum('actual_amount');
        
        $schedulesCount=Schedule::where('shop_id',SHOP_ID)->whereDate('created_at', $today)->count();
        $billCount = Billing::where('shop_id', SHOP_ID)
        ->whereDate('created_at', $today)
        // ->whereDoesntHave('schedule')
        ->count();
        
        //customer Registeration chart
        $mychart        = Customer::where('shop_id', SHOP_ID)
                         ->whereDate('created_at', $today)
                        ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as aggregate")
                        // ->whereYear('created_at', $currentYear)
                        ->groupBy('date')
                        ->get();
        //total sales chart
        $sales_line_chart = Billing::where('shop_id', SHOP_ID)
        ->selectRaw("SUM(actual_amount) as total_amount, MONTH(created_at) as month")
        ->whereDate('created_at', $today)
        // ->whereYear('created_at', $currentYear)
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy(DB::raw('MONTH(created_at)'))
        ->pluck('total_amount', 'month');
        $sales_data = [];
        // Loop through each month and fill the sales data array
        for ($i = 1; $i <= 12; $i++) {
            $sales_data[] = $sales_line_chart->get($i, 0);
        }
        
        $line_chart = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => $sales_data,
        ];
        
    
        $serviceCategory=ServiceCategory::where('shop_id', SHOP_ID)->get();

        // $scheduler_list =BillingItem::join('billings', 'billing_items.billing_id', '=', 'billings.id')
        // ->join('services', 'services.id', '=', 'billing_items.item_id')
        // ->join('service_categories', 'services.service_category_id', '=', 'service_categories.id')
        // ->where('billings.shop_id', SHOP_ID)
        // ->where('services.shop_id', SHOP_ID)
        // ->where('service_categories.shop_id', SHOP_ID)
        // ->whereDate('billing_items.created_at', $today)
        // // ->whereYear('billing_items.created_at', $currentYear)
        // ->select('service_categories.id','service_categories.name', DB::raw('count(*) as category_count'))
        // ->groupBy('services.service_category_id')
        // ->get();
        $scheduler_list = BillingItem::join('billings', 'billing_items.billing_id', '=', 'billings.id')
        ->join('services', 'services.id', '=', 'billing_items.item_id')
        ->join('service_categories', 'services.service_category_id', '=', 'service_categories.id')
        ->where('billings.shop_id', SHOP_ID)
        ->where('services.shop_id', SHOP_ID)
        ->where('service_categories.shop_id', SHOP_ID)
        ->whereDate('billing_items.created_at', $today)
        // ->whereYear('billing_items.created_at', $currentYear)
        ->select('service_categories.id', 'service_categories.name', DB::raw('count(*) as category_count'))
        ->groupBy('service_categories.id', 'service_categories.name') // Include service_categories.id and service_categories.name in the GROUP BY clause
        ->get();


    // Prepare the data for the chart
    $categoryCounts = [];

    // Initialize counts for each service category
    foreach ($serviceCategory as $category) {
        $categoryCounts[$category->id] = 0;
    }
    
    // Update counts from the scheduler list
    foreach ($scheduler_list as $schedule) {
        $categoryCounts[$schedule->id] = $schedule->category_count;
    }
    // dd( $categoryCounts);
    // Prepare the data for the chart
    $categoryList = [];
    foreach ($serviceCategory as $category) {
        $serviceCategoryName = $category->name;
        $categoryId = $category->id;
        $count = isset($categoryCounts[$categoryId]) ? $categoryCounts[$categoryId] : 0;
        $label = $serviceCategoryName . ' (' . $count . ')';
        $categoryList[] = [
            'label' => $label,
            'data' => $count
        ];
    }
     $TotalDiscountAmount = Billing::whereDate('created_at',  $today)->where('shop_id',SHOP_ID)
            ->with(['items' => function ($query) {
                $query->where('is_discount_used', 1);
            }])
            ->get()
            ->sum(function ($billing) {
                return $billing->items->sum('discount_value');
            });
    $total_bill_count=$schedulesCount+$billCount;
    $data = [
        'customer' => is_numeric($customer) ? number_format($customer, 2, '.', '') : $customer,  // Format if numeric
        'therapist' => is_numeric($therapist) ? number_format($therapist, 2, '.', '') : $therapist,  // Format if numeric
        'services' => is_numeric($services) ? number_format($services, 2, '.', '') : $services,  // Format if numeric
        'packages' => is_numeric($packages) ? number_format($packages, 2, '.', '') : $packages,  // Format if numeric
        'total_dues' => is_numeric($total_dues) ? number_format($total_dues, 2, '.', '') : null,
        'total_instore' => is_numeric($total_instore) ? number_format($total_instore, 2, '.', '') : null,
        'serviceAmount' => is_numeric($serviceAmount) ? number_format($serviceAmount, 2, '.', '') : null,
        'packageAmount' => is_numeric($packageAmount) ? number_format($packageAmount, 2, '.', '') : null,
        'totalSaleAmount' => is_numeric($totalSaleAmount) ? number_format($totalSaleAmount, 2, '.', '') : null,
        'mychart' => $mychart,  // Keep this unchanged
        'line_chart' => $line_chart,  // Keep this unchanged
        'categoryList' => $categoryList,  // Keep this unchanged
        'schedulesCount' => is_numeric($schedulesCount) ? number_format($schedulesCount, 2, '.', '') : null,
        'billCount' => is_numeric($billCount) ? number_format($billCount, 2, '.', '') : null,
        'total_membership_instore' => is_numeric($total_membership_instore) ? number_format($total_membership_instore, 2, '.', '') : null,
        'total_bill_count' => is_numeric($total_bill_count) ? number_format($total_bill_count, 2, '.', '') : null,
        'totalDiscountAmount' => is_numeric($TotalDiscountAmount) ? number_format($TotalDiscountAmount, 2, '.', '') : null,
        'totalSaleAmountPaid' => (is_numeric($totalSaleAmountPaid) && $totalSaleAmountPaid >= 0) 
            ? number_format($totalSaleAmountPaid, 2, '.', '') 
            : null,  // Format if numeric and non-negative
        'serviceAmount_paid' => is_numeric($serviceAmount_paid) ? number_format($serviceAmount_paid, 2, '.', '') : null,
        'packageAmount_paid' => is_numeric($packageAmount_paid) ? number_format($packageAmount_paid, 2, '.', '') : null,
        'total_membership_instore_balance' => is_numeric($total_membership_instore_balance) ? number_format($total_membership_instore_balance, 2, '.', '') : null,
        'total_membership_instore_used' => is_numeric($total_membership_instore_used) ? number_format($total_membership_instore_used, 2, '.', '') : null,
        'total_instore_used' => is_numeric($total_instore_used) ? number_format($total_instore_used, 2, '.', '') : null,
        'total_instore_balance' => is_numeric($total_instore_balance) ? number_format($total_instore_balance, 2, '.', '') : null,
    ];
    
    
    
        return view('home',$data);
    }

    public function paymentHistory(Request $request) {
        $currentYear = now()->year; 
        $today = Carbon::today();

        if ($request->ajax()) {
            $data = BillAmount::whereHas('bill')->whereDate('created_at', $today)->latest()->take(5)->get();
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('payment_type', function ($row) {              
                    return $row->payment_type;
            })
            ->addColumn('bill', function ($row) {                
                    return $row->bill->billing_code;                
            })
            ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2);                
            }) 
            ->removeColumn('id')
            ->escapeColumns([])
            ->make(true); 
        }
        return view('home');
    }
    public function customerBarChart(Request $request){
        $currentDate = now()->toDateString();   
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year; 
        $mychart = Customer::where('shop_id', SHOP_ID)
        ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as aggregate");
    
          // Apply filter for selected year
            if ($request->filled('year')) {
                $mychart->whereYear('created_at', $request->year);
            }
            
        
            // Apply filter for custom date range
            if ($request->filled(['fromDate', 'toDate'])) {
                $mychart->whereBetween('created_at', [
                    Carbon::parse($request->fromDate)->startOfDay(),
                    Carbon::parse($request->toDate)->endOfDay()
                ]);
            }
            if ($request->day == 'today') {
                $mychart->whereDate('created_at', $currentDate);
    
            } elseif ($request->day == 'week') {
                $mychart->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($request->day == 'month') {
                $mychart->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear);
            }
            $mychart = $mychart->groupBy('date')
                ->get();
                if ($mychart->count() > 0) {
                    $monthData = $mychart->groupBy(function ($date) {
                        return Carbon::parse($date->date)->format('M');
                    })
                    ->map(function ($data) {
                        return $data->sum('aggregate');
                    });
                    return response()->json(['flagError' => false, 'mychart' => $monthData]);
                } else {
                    return response()->json(['flagError' => true, 'message' => 'No Data Found']);
                }
    
    }
    public function salesLineChart(Request $request){
        $currentDate = now()->toDateString();   
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year; 
            $fromDate = $request->input('fromDate'); 
            $toDate = $request->input('toDate');
            $sales_line_chart = Billing::where('shop_id', SHOP_ID)
                                        ->selectRaw("SUM(actual_amount) as total_amount, MONTH(created_at) as month");
                                        

            if ($request->filled('year')) {
                $sales_line_chart->whereYear('created_at', $request->year);
            }
            
            if ($request->filled(['fromDate', 'toDate'])) {
                $sales_line_chart->whereBetween('created_at', [
                    Carbon::parse($request->fromDate)->startOfDay(),
                    Carbon::parse($request->toDate)->endOfDay()
                ]);
            }
            if ($request->day == 'today') {
                $sales_line_chart->whereDate('created_at', $currentDate);
    
            } elseif ($request->day == 'week') {
                $sales_line_chart->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($request->day == 'month') {
                $sales_line_chart->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear);
            }
            $sales_line_chart = $sales_line_chart->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('MONTH(created_at)'))
                ->pluck('total_amount', 'month');

            $sales_data = [];
          
            for ($i = 1; $i <= 12; $i++) {
                $sales_data[] = $sales_line_chart->get($i, 0);
            }

            $line_chart = [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => $sales_data,
            ];
            return response()->json(['flagError' => false, 'line_chart' => $line_chart]);
          

    }
    public function servicePieChart(Request $request){    
        $currentDate = now()->toDateString();   
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year;   
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceCategory = ServiceCategory::where('shop_id', SHOP_ID)->get();
        
        $scheduler_list = BillingItem::join('billings', 'billing_items.billing_id', '=', 'billings.id')
            ->join('services', 'services.id', '=', 'billing_items.item_id')
            ->join('service_categories', 'services.service_category_id', '=', 'service_categories.id')
            ->where('billings.shop_id', SHOP_ID)
            ->where('services.shop_id', SHOP_ID)
            ->where('service_categories.shop_id', SHOP_ID);
        if ($request->filled('year')) {
            $scheduler_list->whereYear('billing_items.created_at', $request->year);
        }
        if ($fromDate && $toDate) {
            $scheduler_list->whereBetween('billing_items.created_at', [$fromDate, $toDate]);
        }
        if ($request->day == 'today') {
            $scheduler_list->whereDate('billing_items.created_at', $currentDate);

        } elseif ($request->day == 'week') {
            $scheduler_list->whereBetween('billing_items.created_at', [$startDate, $endDate]);
        } elseif ($request->day == 'month') {
            $scheduler_list->whereMonth('billing_items.created_at', $currentMonth)
                ->whereYear('billing_items.created_at', $currentYear);
        }
        $scheduler_list = $scheduler_list->select('service_categories.id', 'service_categories.name', DB::raw('count(*) as category_count'))
            ->groupBy('services.service_category_id')
            ->get();

        // Initialize counts for each service category
        $categoryCounts = [];
        foreach ($serviceCategory as $category) {
            $categoryCounts[$category->id] = 0;
        }

        // Update counts from the scheduler list
        foreach ($scheduler_list as $schedule) {
            $categoryCounts[$schedule->id] = $schedule->category_count;
        }

        // Prepare the data for the chart
        $categoryList = [];
        foreach ($serviceCategory as $category) {
            $serviceCategoryName = $category->name;
            $categoryId = $category->id;
            $count = isset($categoryCounts[$categoryId]) ? $categoryCounts[$categoryId] : 0;
            $label = $serviceCategoryName . ' (' . $count . ')';
            $categoryList[] = [
                'label' => $label,
                'data' => $count
            ];
        }
        return response()->json(['flagError' => false, 'categoryList' => $categoryList]);

    }

    public function paymentDatatable(Request $request) {
        $currentDate = now()->toDateString();   
        $startDate = now()->startOfWeek()->toDateString();
        $endDate = now()->endOfWeek()->toDateString();     
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $billAmounts=BillAmount::with('bill');
        if ($request->filled('year')) {
            $billAmounts->whereYear('created_at', $request->year);
        }
        if ($fromDate && $toDate) {
            $billAmounts->whereBetween('created_at', [$fromDate, $toDate]);
        }
        if ($request->day == 'today') {
            $billAmounts->whereDate('created_at', $currentDate);

        } elseif ($request->day == 'week') {
            $billAmounts->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($request->day == 'month') {
            $billAmounts->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear);
        }
        $billAmounts = $billAmounts->get();
        $billTotal=0;
        foreach($billAmounts as $billAmount){
            if($billAmount->bill!=NULL){
                $billTotal +=$billAmount->amount;
            }
           
        }
        if ($billAmounts->count() > 0) {
            return response()->json(['flagError' => false, 'billAmounts' => $billAmounts,'billTotal'=>$billTotal]);
        } else {
            return response()->json(['flagError' => true, 'message' => 'No Data Found']);
        }
        
    }
    public function dashboardFilter(Request $request)
     {
            $currentDate = Carbon::today(); 
            $startDate = now()->startOfWeek()->toDateString();
            $endDate = now()->endOfWeek()->toDateString();     
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $customer = Customer::where('shop_id', SHOP_ID);
            $therapist = User::where('shop_id', SHOP_ID)->whereHas('staffProfile', function($query) {
                $query->where('is_staff', 1);
            });
            $services = Service::where('shop_id', SHOP_ID);
            $packages = Package::where('shop_id', SHOP_ID);
            $total_dues = CustomerPendingPayment::whereHas('customer', function($query) {
                $query->where('shop_id', SHOP_ID);
            })->where('expiry_status', 0)->where('removed', 0);
            $total_instore = CustomerPendingPayment::whereHas('customer', function($query) {
                $query->where('shop_id', SHOP_ID);
            })->where('expiry_status', 0)->where('is_membership',0)->where('removed', 0);
            $total_membership_instore = CustomerPendingPayment::whereHas('customer', function($query) {
                $query->where('shop_id', SHOP_ID);
            })->where('expiry_status', 0)->where('is_membership',1);
            $totalSaleAmount = Billing::where('shop_id', SHOP_ID)->withTrashed();

            $totalSaleAmountPaid = Billing::where('shop_id', SHOP_ID)->withTrashed();
            
            $serviceAmount = Billing::where('shop_id', SHOP_ID)
            ->whereHas('items', function ($query) {
                $query->where('payment_status', 1)
                    ->where('item_type', 'services');
            });
            
            $packageAmount = Billing::where('shop_id', SHOP_ID)->whereHas('items', function($query) {
                $query->where('item_type', 'packages');
            });
            $schedulesCount=Schedule::where('shop_id',SHOP_ID);
            $billCount = Billing::where('shop_id', SHOP_ID)       
            ->whereDoesntHave('schedule');
            $TotalDiscountAmount = Billing::where('shop_id', SHOP_ID) 
                ->with(['items' => function ($query) {
                    $query->where('is_discount_used', 1);
                }]);

            $total_instore_balance = CustomerPendingPayment::whereHas('customer', function($query) {
                    $query->where('shop_id',SHOP_ID); 
                })
                ->where('expiry_status', 0)
                ->where('removed', 0)
                ->where('is_membership',0);  
            $total_instore_used = CustomerPendingPayment::whereHas('customer', function($query) {
                    $query->where('shop_id',SHOP_ID); 
                })
                ->where('expiry_status', 0)
                ->where('removed', 0)
                ->where('is_membership',0);        

            $total_membership_instore_balance= CustomerPendingPayment::whereHas('customer',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                ->where('expiry_status',0)->where('is_membership',1)
                ->where('removed', 0);
            $total_membership_instore_used= CustomerPendingPayment::whereHas('customer',function($query){
                    $query->where('shop_id',SHOP_ID);
                })
                ->where('expiry_status',0)->where('is_membership',1);
            
            
                $models = [
                    $customer,
                    $therapist,
                    $services,
                    $packages,
                    $total_dues,
                    $total_instore,
                    $total_membership_instore,
                    $totalSaleAmount,
                    $serviceAmount,
                    $packageAmount,
                    $schedulesCount,
                    $billCount,
                    $TotalDiscountAmount,
                    $total_instore_balance,
                    $total_instore_used,
                    $total_membership_instore_balance,
                    $total_membership_instore_used,
                    $schedulesCount
                ];
            if ($request->filled('year')) {
                $year = $request->year;  
                foreach ($models as $model) {
                    $model->whereYear('created_at', $year);
                }
            }
        
            if ($request->filled('fromDate') && $request->filled('toDate')) {
                $fromDate = $request->input('fromDate');
                $toDate = $request->input('toDate');
                foreach ($models as $model) {
                    if ($fromDate === $toDate) {
                        $model->whereDate('created_at', $fromDate);
                    } else {
                        $model->whereBetween('created_at', [$fromDate, $toDate]);
                    }
                }            
            }
        
            if ($request->day == 'today') {            
                foreach ($models as $model) {
                    $model->whereDate('created_at', $currentDate);
                }
                

            } elseif ($request->day == 'week') {           
                foreach ($models as $model) {
                    $model->whereBetween('created_at', [$startDate, $endDate]);
                }
                
            } elseif ($request->day == 'month') {          
                foreach ($models as $model) {
                    $model->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                }
                
            }
        $totalSaleAmount= $totalSaleAmount->sum('amount');
        $totalSaleAmountPaid=$totalSaleAmountPaid->sum('actual_amount');
        $serviceAmount_paid= $serviceAmount;
        $packageAmount_paid= $packageAmount;
        $customerCount=$customer->count();
        $therapistCount = $therapist->count();
        $servicesCount = $services->count();
        $packagesCount = $packages->count();
        $totalDuesAmount = $total_dues->sum('current_due');
        $totalInStoreAmount = $total_instore->sum('over_paid');
        $total_membership_instore=$total_membership_instore->sum('amount_before_gst');
        $total_instore_balance=$total_instore_balance ->sum('over_paid');
        $total_instore_used=$total_instore_used ->sum('deducted_over_paid');
        $total_membership_instore_balance=$total_membership_instore_balance->sum('over_paid');
        $total_membership_instore_used=$total_membership_instore_used->sum('deducted_over_paid');
    
        $serviceAmount = $serviceAmount->sum('amount');
        $packageAmount = $packageAmount->sum('amount');
        
        $serviceAmount_paid = $serviceAmount_paid->where('payment_status',1)->sum('actual_amount');
        $packageAmount_paid = $packageAmount_paid->where('payment_status',1)->sum('actual_amount');
        
        $schedulesCount=$schedulesCount->count();
        $billCount=$billCount->count();
        $TotalDiscountAmount= $TotalDiscountAmount->get()
                ->sum(function ($billing) {
                    return $billing->items->sum('discount_value');
                });
        $additionallyPaidAmount  = CustomerPendingPayment::whereHas('customer',function($query){
            $query->where('shop_id',SHOP_ID);
        })->sum('amount_before_gst');
        $totalSaleAmountPaid -=$additionallyPaidAmount;    
        $total_bill_count=$schedulesCount+$billCount;
        $data = [
            'customer' => is_numeric($customerCount) ? $customerCount : $customerCount,
            'therapist' => is_numeric($therapistCount) ? $therapistCount : $therapistCount,
            'services' => is_numeric($servicesCount) ? $servicesCount : $servicesCount,
            'packages' => is_numeric($packagesCount) ? $packagesCount : $packagesCount,
            'total_dues' => is_numeric($totalDuesAmount) ? number_format($totalDuesAmount, 2, '.', '') : $totalDuesAmount,
            'total_instore' => is_numeric($totalInStoreAmount) ? number_format($totalInStoreAmount, 2, '.', '') : $totalInStoreAmount,
            'total_membership_instore' => is_numeric($total_membership_instore) ? number_format($total_membership_instore, 2, '.', '') : $total_membership_instore,
            'serviceAmount' => is_numeric($serviceAmount) ? number_format($serviceAmount, 2, '.', '') : $serviceAmount,
            'packageAmount' => is_numeric($packageAmount) ? number_format($packageAmount, 2, '.', '') : $packageAmount,
            'totalSaleAmount' => is_numeric($totalSaleAmount) ? number_format($totalSaleAmount, 2, '.', '') : $totalSaleAmount,
            'schedulesCount' => is_numeric($schedulesCount) ?  number_format($schedulesCount, 2, '.', '') : $schedulesCount,
            'billCount' => is_numeric($billCount) ? $billCount : $billCount,
            'total_bill_count' => is_numeric($total_bill_count) ? $total_bill_count : $total_bill_count,
            'totalDiscountAmount' => is_numeric($TotalDiscountAmount) ? number_format($TotalDiscountAmount, 2, '.', '') : $TotalDiscountAmount,
            'totalSaleAmountPaid' => is_numeric($totalSaleAmountPaid) ? number_format($totalSaleAmountPaid, 2, '.', '') : $totalSaleAmountPaid,
            'serviceAmount_paid' => is_numeric($serviceAmount_paid) ? number_format($serviceAmount_paid, 2, '.', '') : $serviceAmount_paid,
            'packageAmount_paid' => is_numeric($packageAmount_paid) ? number_format($packageAmount_paid, 2, '.', '') : $packageAmount_paid,
            'total_instore_used' => is_numeric($total_instore_used) ? number_format($total_instore_used, 2, '.', '') : $total_instore_used,
            'total_instore_balance' => is_numeric($total_instore_balance) ? number_format($total_instore_balance, 2, '.', '') : $total_instore_balance,
            'total_membership_instore_balance' => is_numeric($total_membership_instore_balance) ? number_format($total_membership_instore_balance, 2, '.', '') : $total_membership_instore_balance,
            'total_membership_instore_used' => is_numeric($total_membership_instore_used) ? number_format($total_membership_instore_used, 2, '.', '') : $total_membership_instore_used,
        ];
        
            return response()->json(['flagError' => false, 'data' => $data]);
    }
}
