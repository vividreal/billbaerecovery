<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use App\Models\Billing;
use App\Models\Shop;

class dailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily report send to owner';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       
        $html = '<div style="margin: 30px;">';
        $shops = Shop::where('active', 1)->get();
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

foreach ($shops as $shop) {
    $billings = Billing::with('store')->where('shop_id', $shop->id)->whereBetween('created_at', [$startDate, $endDate])->get();
    $totalSaleAmount = \App\Models\Billing::where('shop_id', $shop->id)->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
    $totalSale = \App\Models\Billing::where('shop_id', $shop->id)->whereBetween('created_at', [$startDate, $endDate])->with('items')->count();
    $total_dues = \App\Models\CustomerPendingPayment::whereHas('customer', function ($query) use ($shop, $startDate, $endDate) {
        $query->where('shop_id', $shop->id);
    })->whereBetween('created_at', [$startDate, $endDate])->where('expiry_status', 0)
    ->where('removed', 0)->sum('current_due');
    $totalInstoreUsed = \App\Models\CustomerPendingPayment::whereHas('customer', function ($query) use ($shop, $startDate, $endDate) {
        $query->where('shop_id', $shop->id);
    })->whereBetween('created_at', [$startDate, $endDate])->where('expiry_status', 0)
    ->where('removed', 0)->sum('deducted_over_paid');

    $totalDiscount = \App\Models\Billing::where('shop_id', $shop->id)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->whereHas('items', function($query) {
        $query->where('is_discount_used', 1);
    })
    ->with('items')
    ->get()
    ->flatMap(function($billing) {
        return $billing->items;
    })
    ->where('is_discount_used', 1)
    ->sum('discount_value');

    // Calculate the total customers served
    $totalCustomers = \App\Models\Billing::where('shop_id', $shop->id)->whereBetween('created_at', [$startDate, $endDate])->distinct('customer_id')->count('customer_id');

    // Start building HTML
    $html .= '<div style="border: 1px solid black; margin: auto; width: 100%;">';
    $html .= '<div>';
    $html .= '<h1 style="text-align:center;">Your Daily Summary - ' . $shop->name . '</h1>';
    $html .= '<p style="text-align: center;">' . date('D, M d, Y') . '</p>';
    $html .= '<hr>';
    $html .= '</div>';

      
    $html .= '<div style="margin: 0 33px;">';
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    $html .= '<tr>';
    $html .= '<td style="width: 33.33%; padding: 10px; vertical-align: top;">';
    $html .= '<h2>Total Sale Amount</h2>';
    $html .= '<p>' . number_format($totalSaleAmount, 2) . '</p>';
    $html .= '</td>';
    $html .= '<td style="width: 33.33%; padding: 10px; vertical-align: top;">';
    $html .= '<h2>Total Due</h2>';
    $html .= '<p>' . number_format($total_dues, 2) . '</p>';
    $html .= '</td>';
    $html .= '<td style="width: 33.33%; padding: 10px; vertical-align: top;">';
    $html .= '<h2>Total Services</h2>';
    $html .= '<p>' . $totalSale . '</p>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td style="width: 33.33%; padding: 10px; vertical-align: top;">';
    $html .= '<h2>Total Discounts Used</h2>';
    $html .= '<p>' . number_format($totalDiscount, 2) . '</p>';
    $html .= '</td>';
    $html .= '<td style="width: 33.33%; padding: 10px; vertical-align: top;">';
    $html .= '<h2>Total Instore Used</h2>';
    $html .= '<p>' . number_format($totalInstoreUsed, 2) . '</p>';
    $html .= '</td>';
    $html .= '<td style="width: 33.33%; padding: 10px; vertical-align: top;">';
    $html .= '<h2>Total Customers Served</h2>';
    $html .= '<p>' . $totalCustomers . '</p>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</div>';
    $html .= '<hr>';
    // Continue with Customer Billing section
    $html .= '<div>';
    $html .= '<h3 style="padding-left:70px">CUSTOMER BILLING</h3>';
    $html .= '<table style="width: 85%; border: 1px solid black;margin-bottom:10px; margin-left: 30px; margin-right: 10px;">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">No</th>';
    $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Bill ID</th>';
    $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Customer Name</th>';
    $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Amount</th>';
    $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Due Amount</th>';
    $html .= '<th style="border: 1px solid #ddd;">Payment Status</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    foreach ($billings as $key => $billing) {
        $status = ($billing->payment_status == 0) ? '<span style="color:red;">UNPAID</span>' : '<span style="color:green;">PAID</span>';
        $html .= '<tr>';
        $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . ($key + 1) . '</td>';
        $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $billing->billing_code . '</td>';
        $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $billing->customer->name . '</td>';
        $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $billing->actual_amount . '</td>';
        $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">';
        $due = 0;
        foreach ($billing->customerPendingAmount as $dueamount) {
            if ($dueamount->bill_id == $billing->id) {
                $due += $dueamount->current_due ?? '0.00';
            }
        }
        $html .= $due;
        $html .= '</td>';
        $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $status . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '<hr>';
            // Payment History Section
            $html .= '<div>';
            $html .= '<h4 style="padding-left: 70px">PAYMENT HISTORY</h4>';
            $html .= '<div>';
            $html .= '<table style="border: 1px solid black; width: 85%; margin-left: 10px; margin-bottom: 10px">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">No</th>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Payment Type</th>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;"> Amount</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
        
            $paymentSums = [];
            foreach ($billings as $billing) {
                foreach ($billing->paymentMethods as $paymentMethod) {
                    $paymentType = $paymentMethod->payment_type;
                    $paymentAmount = $paymentMethod->amount;
                    if (!isset($paymentSums[$paymentType])) {
                        $paymentSums[$paymentType] = $paymentAmount;
                    } else {
                        $paymentSums[$paymentType] += $paymentAmount;
                    }
                }
            }
        
            $key = 0; // Initialize key for payment history rows
            foreach ($paymentSums as $paymentType => $sum) {
                $key++;
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $key . '</td>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $paymentType . '</td>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $sum . '</td>';
                $html .= '</tr>';
            }
        
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '</div>';
        
            // Service History Section
            $html .= '<div>';
            $html .= '<h3 style="padding-left: 70px">Service History</h3>';
            $html .= '<div>';
            $html .= '<table style="border: 1px solid black;width: 85%;margin-left: 10px; margin-bottom: 10px">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">No</th>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Service Type</th>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Service Category</th>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;">Service</th>';
            $html .= '<th style="border: 1px solid #ddd; border-right: 0px;"> Count</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
        
            $serviceCounts = [];
        
            foreach ($billings as $bill) {
                if ($bill->items) {
                    foreach ($bill->items as $billingItem) {
                        $serviceCategoryId = $billingItem->item->serviceCategory->id;
                        $serviceCategoryType = $billingItem->item_type;
                        $serviceName = $billingItem->item->name;
                        $serviceCategoryName = $billingItem->item->serviceCategory->name;
        
                        if (!isset($serviceCounts[$serviceCategoryId])) {
                            $serviceCounts[$serviceCategoryId] = [
                                'service_category_id' => $serviceCategoryId,
                                'service_category' => $serviceCategoryName,
                                'service_name' => $serviceName,
                                'count' => 1
                            ];
                        } else {
                            $serviceCounts[$serviceCategoryId]['count']++;
                        }
                    }
                }
            }
        
            $rowNumber = 1;
        
            foreach ($serviceCounts as $categoryId => $serviceData) {
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $rowNumber . '</td>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $categoryId . '</td>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $serviceData['service_category'] . '</td>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $serviceData['service_name'] . '</td>';
                $html .= '<td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">' . $serviceData['count'] . '</td>';
                $html .= '</tr>';
                $rowNumber++;
            }
        
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        
        
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portait');
            $dompdf->render();
        
            $pdfContent = $dompdf->output();
            //$shop->email
            Mail::to($shop->email)->send(new DailyReportMail($pdfContent));
        }
        
    
        
    
    
}