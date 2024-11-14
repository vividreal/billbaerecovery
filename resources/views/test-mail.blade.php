<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Daily Summary Report</title>
</head>

<body>
    <div style="margin: 45px 30px;">
        @php
            $shops = App\Models\Shop::where('active', 1)->get();
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        @endphp

        @foreach ($shops as $shop)
            @php
                $billings = App\Models\Billing::with('store')
                    ->where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                $totalSaleAmount = App\Models\Billing::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');
                $totalSale = App\Models\Billing::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                $totalDues = App\Models\CustomerPendingPayment::whereHas('customer', function ($query) use ($shop) {
                    $query->where('shop_id', $shop->id);
                })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('expiry_status', 0)
                    ->where('removed', 0)
                    ->sum('current_due');
                $totalInstoreUsed = App\Models\CustomerPendingPayment::whereHas('customer', function ($query) use (
                    $shop,
                ) {
                    $query->where('shop_id', $shop->id);
                })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('expiry_status', 0)
                    ->where('removed', 0)
                    ->sum('deducted_over_paid');
                $totalDiscount = App\Models\Billing::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas('items', function ($query) {
                        $query->where('is_discount_used', 1);
                    })
                    ->with('items')
                    ->get()
                    ->flatMap(function ($billing) {
                        return $billing->items;
                    })
                    ->where('is_discount_used', 1)
                    ->sum('discount_value');
                $totalCustomers = App\Models\Billing::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->distinct('customer_id')
                    ->count('customer_id');
            @endphp

            <!-- Start building HTML -->
            <div style="border: 1px solid black; margin: auto; width: 100%;">
                <div>
                    <h1 style="text-align:center;">Your Daily Summary - {{ $shop->name }}</h1>
                    <p style="text-align: center;">{{ now()->format('D, M d, Y') }}</p>
                    <hr>
                </div>

                <div style="margin: 0 33px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 33.33%; padding: 10px; vertical-align: top;">
                                <h2>Total Sale Amount</h2>
                                <p>{{ number_format($totalSaleAmount, 2) }}</p>
                            </td>
                            <td style="width: 33.33%; padding: 10px; vertical-align: top;">
                                <h2>Total Due</h2>
                                <p>{{ number_format($totalDues, 2) }}</p>
                            </td>
                            <td style="width: 33.33%; padding: 10px; vertical-align: top;">
                                <h2>Total Services</h2>
                                <p>{{ $totalSale }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 33.33%; padding: 10px; vertical-align: top;">
                                <h2>Total Discounts Used</h2>
                                <p>{{ number_format($totalDiscount, 2) }}</p>
                            </td>
                            <td style="width: 33.33%; padding: 10px; vertical-align: top;">
                                <h2>Total Instore Used</h2>
                                <p>{{ number_format($totalInstoreUsed, 2) }}</p>
                            </td>
                            <td style="width: 33.33%; padding: 10px; vertical-align: top;">
                                <h2>Total Customers Served</h2>
                                <p>{{ $totalCustomers }}</p>
                            </td>
                        </tr>
                    </table>
                </div>
                

                <!-- Customer Billing section -->
                <div>
                    <h3 style="padding-left:70px">CUSTOMER BILLING</h3>
                    <table style="width: 85%; border: 1px solid black; margin-bottom: 10px; margin: 0 auto;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ddd;">No</th>
                                <th style="border: 1px solid #ddd;">Bill ID</th>
                                <th style="border: 1px solid #ddd;">Customer Name</th>
                                <th style="border: 1px solid #ddd;">Amount</th>
                                <th style="border: 1px solid #ddd;">Due Amount</th>
                                <th style="border: 1px solid #ddd;">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($billings as $key => $billing)
                                @php
                                    $due =
                                        $billing->customerPendingAmount
                                            ->where('bill_id', $billing->id)
                                            ->sum('current_due') ?? '0.00';
                                    $status = $billing->payment_status == 0 ? 'UNPAID' : 'PAID';
                                @endphp
                                <tr>
                                    <td style="border: 1px solid #ddd; padding:5px;">{{ $key + 1 }}</td>
                                    <td style="border: 1px solid #ddd; padding:5px;">{{ $billing->billing_code }}</td>
                                    <td style="border: 1px solid #ddd; padding:5px;">{{ $billing->customer->name }}
                                    </td>
                                    <td style="border: 1px solid #ddd; padding:5px;">{{ $billing->actual_amount }}</td>
                                    <td style="border: 1px solid #ddd; padding:5px;">{{ $due }}</td>
                                    <td style="border: 1px solid #ddd; padding:5px;">{{ $status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <hr>
                <!-- Payment History Section -->
                <div>
                    <h4 style="padding-left: 70px">PAYMENT HISTORY</h4>
                    <div>
                        <table style="border: 1px solid black; width: 85%; margin: 0 auto; margin-bottom: 10px;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd;">No</th>
                                    <th style="border: 1px solid #ddd;">Payment Type</th>
                                    <th style="border: 1px solid #ddd;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
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
                                @endphp
                                @foreach ($paymentSums as $paymentType => $sum)
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding:5px;">{{ $loop->iteration }}</td>
                                        <td style="border: 1px solid #ddd; padding:5px;">{{ $paymentType }}</td>
                                        <td style="border: 1px solid #ddd; padding:5px;">{{ $sum }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Service History Section -->
                <div>
                    <h3 style="padding-left: 70px">Service History</h3>
                    <div>
                        <table style="border: 1px solid black; width: 85%; margin: 0 auto; margin-bottom: 10px;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd;">No</th>
                                    <th style="border: 1px solid #ddd;">Service Type</th>
                                    <th style="border: 1px solid #ddd;">Service Category</th>
                                    <th style="border: 1px solid #ddd;">Service</th>
                                    <th style="border: 1px solid #ddd;">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
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
                                                        'count' => 1,
                                                    ];
                                                } else {
                                                    $serviceCounts[$serviceCategoryId]['count']++;
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                @foreach ($serviceCounts as $categoryId => $serviceData)
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding:5px;">{{ $loop->iteration }}</td>
                                        <td style="border: 1px solid #ddd; padding:5px;">{{ $categoryId }}</td>
                                        <td style="border: 1px solid #ddd; padding:5px;">
                                            {{ $serviceData['service_category'] }}</td>
                                        <td style="border: 1px solid #ddd; padding:5px;">
                                            {{ $serviceData['service_name'] }}</td>
                                        <td style="border: 1px solid #ddd; padding:5px;">{{ $serviceData['count'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
