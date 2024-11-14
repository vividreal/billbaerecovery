<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Billbae</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 200px;
        }

        .content {
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }

        * {
            box-sizing: border-box;
        }

        .column {
            float: left;
            padding: 10px;
            height: 97px;
        }

        .left,
        .right {
            width: 30%;
        }

        .middle {
            width: 30%;
        }

        /* Clear floats after the columns */
        .row:after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body>
    <div style="margin:auto;">
        @php
            $shops = \App\Models\Shop::where('active', 1)->get();
        @endphp
        @foreach ($shops as $shop)
            @php
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
                $billings = \App\Models\Billing::where('shop_id', $shop->id)
                   ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                $totalSaleAmount = \App\Models\Billing::where('shop_id', $shop->id)
                   ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');
                $totalSale = \App\Models\Billing::where('shop_id', $shop->id)
                   ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('items')
                    ->count();
                $total_dues = \App\Models\CustomerPendingPayment::whereHas('customer', function ($query) use ($shop,$startDate, $endDate) {
                    $query->where('shop_id', $shop->id);
                })
                    ->where('expiry_status', 0)
                    ->where('removed', 0)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('current_due');
                    $totalInstoreUsed= \App\Models\CustomerPendingPayment::whereHas('customer', function ($query) use ($shop,$startDate, $endDate) {
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
    $totalCustomers = \App\Models\Billing::where('shop_id', $shop->id)->whereBetween('created_at', [$startDate, $endDate])->distinct('customer_id')->count('customer_id');

            @endphp
            <div style="border: 1px solid black;margin: auto;width: 100%;">
                <div>
                    <h1 style="text-align:center;">Your Daily Summary -{{ $shop->name }}</h1>
                    <p style="text-align: center;">{{ date('D, M d, Y') }}</p>
                    <hr>

                    <div style="margin:auto;padding:30px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="column left">
                                    <h2>Total Sale Amount</h2>
                                    <p>{{ number_format($totalSaleAmount, 2) }}</p>
                                </td>
                                <td class="column middle">
                                    <h2>Total Service</h2>
                                    <p>{{ $totalSale }}</p>
                                </td>
                                <td class="column right">
                                    <h2>Total Due</h2>
                                    <p>{{ $total_dues }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="column left">
                                    <h2>Instore Deducted</h2>
                                    <p>{{$totalInstoreUsed }}</p>
                                </td>
                                <td class="column middle">
                                    <h2>Total Discount</h2>
                                    <p>{{ $totalDiscount }}</p>
                                </td>
                                <td class="column right">
                                    <h2>Customers Served</h2>
                                    <p>{{ $totalCustomers }}</p></td> <!-- Empty cell for alignment -->
                            </tr>
                        </table>
                    </div>
                    
                </div>
                <hr>
               
                <div style="margin:auto; padding:30px;">
                    <h3 style="margin-top: 0px;">CUSTOMER BILLING</h3>
                    <table
                        style="width: 100%;border: 1px solid black; margin:auto;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ddd; border-right: 0px;">No</th>
                                {{-- <th style="border: 1px solid #ddd; border-right: 0px;">Date</th> --}}
                                <th style="border: 1px solid #ddd; border-right: 0px;">Bill ID</th>
                                <th style="border: 1px solid #ddd; border-right: 0px;">Customer Name</th>
                                <th style="border: 1px solid #ddd; border-right: 0px;">Amount</th>
                                <th style="border: 1px solid #ddd; border-right: 0px;">Due Amount</th>
                                <th style="border: 1px solid #ddd;">Payment Status</th>

                            </tr>
                        </thead>
                        <tbody>
                            @if ($billings->isNotEmpty())
                                @foreach ($billings as $key => $billing)
                                    <tr>
                                        <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                            {{ $key + 1 }}
                                        </td>
                                        {{-- <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;"> --}}
                                        {{-- {{ $billing->billed_date }}</td> --}}
                                        <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                            {{ $billing->billing_code }}</td>
                                        <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                            {{ $billing->customer->name }}</td>
                                        <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                            {{ $billing->actual_amount }}</td>
                                        <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                           @php
                                           $due=0.00;
                                           @endphp
                                            @foreach ($billing->customerPendingAmount as $dueamount)
                                                @if ($dueamount->bill_id == $billing->id)
                                               @php
                                                $due+=$dueamount->current_due;
                                               @endphp
                                                  
                                                @endif
                                            @endforeach
                                            {{ $due ?? '0.00' }}
                                        </td>
                                        <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                            @if ($billing->payment_status == 0)
                                                <span style="color:red;">UNPAID</span>
                                            @else
                                                <span style="color:green;">PAID</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6">No data Found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <hr>
                <div style="margin:auto; padding:30px;">
                    <h3 style="margin-top: 0px;">PAYMENT HISTORY</h3>
                    <div>
                        <table style="border: 1px solid black; width:100%; margin:auto;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; border-right: 0px;">No</th>
                                    <th style="border: 1px solid #ddd; border-right: 0px;">Payment Type</th>
                                    <th style="border: 1px solid #ddd; border-right: 0px;"> Amount</th>
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
                                    $key = 0;
                                @endphp
                                @if ($paymentSums)
                                    @foreach ($paymentSums as $paymentType => $sum)
                                        <tr>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $key + 1 }}</td>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $paymentType }}</td>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $sum }}</td>
                                        </tr>
                                        @php
                                            $key++;
                                        @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">No Data Found</td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div style="margin:auto; padding:30px;">
                    <h3 style="margin-top: 0px;">SERVICE HISTORY</h3>
                    <div>
                        <table style="border: 1px solid black; width:100%;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; border-right: 0px;">No</th>
                                    <th style="border: 1px solid #ddd; border-right: 0px;">Service Type</th>
                                    <th style="border: 1px solid #ddd; border-right: 0px;">Service Category</th>
                                    <th style="border: 1px solid #ddd; border-right: 0px;">Service</th>
                                    <th style="border: 1px solid #ddd; border-right: 0px;"> Count</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $serviceCounts = []; // Initialize array to store service counts

                                    foreach ($billings as $bill) {
                                        if ($bill->items) {
                                            foreach ($bill->items as $billingItem) {
                                                $serviceCategoryId = $billingItem->item->serviceCategory->id;
                                                $serviceCategoryType = $billingItem->item_type;
                                                $serviceName = $billingItem->item->name;
                                                $serviceCategoryName = $billingItem->item->serviceCategory->name;

                                                // Increment the count for each service category
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

                                    $rowNumber = 1; // Initialize row number
                                @endphp
                                @if ($serviceCounts)
                                    @foreach ($serviceCounts as $categoryId => $serviceData)
                                        <tr>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $rowNumber }}</td>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $categoryId }}</td>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $serviceData['service_category'] }}</td>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $serviceData['service_name'] }}</td>
                                            <td style="border: 1px solid #ddd; border-right: 0px;padding:5px;">
                                                {{ $serviceData['count'] }}</td>
                                        </tr>
                                        @php
                                            $rowNumber++;
                                        @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5">No Data Found</td>
                                    </tr>
                                @endif


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
