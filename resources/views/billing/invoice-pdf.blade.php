<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        @media print {
            @page {
                margin-top: -40;
            }

            body {
                margin-top: -40 !important;
                padding: 0 !important;
            }

            div.receipt-container {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container" style="margin-left:-35px;margin-right:-35px; margin-top:-70px">
        <table style="width: 100%">
            <tbody>
                <tr>
                    <td>
                        <h1>{{ $variants->shop->name ?? '' }}</h1>
                    </td>
                </tr>
                <tr>
                    <td> Invoice #: {{ $billing->billing_code }}</td>
                </tr>
                <tr>
                    <td> Created: {{ $billing->formatted_billed_date }}</td>
                </tr>
                <tr>
                    <td>
                        <hr style="width: 100%">
                    </td>
                </tr>
                <tr>
                    <td>
                        <h4>Bill From</h4>
                        {{ $store->billing->company_name ?? '' }} <br>
                        {{ $store->email ?? '' }}
                        {{ $store->store->contact ?? '' }}<br>
                        {{ $store->billing->address ?? '' }}<br>
                        @if ($store->billing->gst != '')
                            GST: {{ $store->billing->gst ?? '' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <h4>Bill To</h4>
                        {!! $billing->customer_address ?? '' !!}
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr style="width: 100%">
                    </td>
                </tr>
                <tr>
                    <td>
                        <table style="border:1px solid black; border-collapse: collapse;">
                            <tr>
                                <td style="border:1px solid black;border-collapse: collapse;">No</td>
                                <td style="border:1px solid black;border-collapse: collapse;">Item </td>
                                <td style="border:1px solid black;border-collapse: collapse;">Qty</td>
                                <td style="border:1px solid black;border-collapse: collapse;">Details</td>
                            </tr>
                            @if ($billing_items)
                                @php
                                    $totalDeductedDue = 0;
                                    $additionalAmount = 0;
                                    $total_discount = 0;
                                    $instoreCreditBalance = 0;
                                    $subtotal = 0;
                                    $grandTotal = 0;
                                    $currentPackageName = null;

                                @endphp

                                @foreach ($billing_items as $key => $item)
                                    @php
                                        // Calculating subtotal and grand total
                                        if ($item->item_type == 'packages') {
                                            $subtotal += $item->grand_total;
                                        } else {
                                            $subtotal += $item->grand_total - $total_discount;
                                            $grandTotal += $item->grand_total;
                                        }

                                        // Grouping package items and determining the last item key for each package

                                    @endphp

                                    {{-- Display package name if it's the start of a new package --}}
                                    @if ($item->item_type == 'packages' && $item->name != $currentPackageName)
                                        @php
                                            $currentPackageName = $item->name;
                                            $package_items = collect($billing_items->toArray())->where(
                                                'package_id',
                                                $item->package_id,
                                            );
                                        
                                            $last_item_key = $package_items->keys()->last();
                                        @endphp
                                        <tr style="border-top: 2px solid #e7e8eb;">
                                            <td colspan="5" style="text-align: center;">
                                                <strong>{{ $item->name }}</strong>
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- Display individual item details --}}
                                    <tr id="{{ $item['id'] }}" style="font-size: 12px">
                                        <td style="border:1px solid black;border-collapse: collapse;">
                                            {{ $loop->iteration }}</td>
                                        <td style="border:1px solid black;border-collapse: collapse;">
                                            {{ $item->item_details }}<br>SAC Code #:{{ $item->hsn_code }}</td>
                                        <td style="border:1px solid black;border-collapse: collapse;">
                                            {{ $item->item_count }}</td>
                                        <td style="border:1px solid black;border-collapse: collapse;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Service value:</td>
                                                        <td>RS: {{ number_format($item->tax_amount, 2) }}</td>
                                                    </tr>
                                                    {{-- Display CGST and SGST if applicable --}}
                                                    @if ($item->cgst_amount > 0)
                                                        <tr>
                                                            <td>CGST: ({{ $item->cgst_percentage }}%)</td>
                                                            <td>RS:{{ $item->cgst_amount }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($item->sgst_amount > 0)
                                                        <tr>
                                                            <td>SGST: ({{ $item->sgst_percentage }}%)</td>
                                                            <td>RS:{{ $item->sgst_amount }}</td>
                                                        </tr>
                                                    @endif
                                                    {{-- Additional taxes --}}
                                                    @if (count($item->additionalTax) > 0)
                                                        @foreach ($item->additionalTax as $key => $additional)
                                                            <tr>
                                                                <td>{{ $additional->tax_name }}
                                                                    ({{ $additional->percentage }}%)
                                                                </td>
                                                                <td>RS:{{ number_format($additional->amount, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    {{-- Discount if applicable --}}
                                                    @if ($item->is_discount_used == 1)
                                                        <tr>
                                                            <td>Discount: @if ($item->discount_type == 'percentage')
                                                                    ({{ $item->discount_value }}%)
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @php
                                                                    if ($item->discount_type == 'percentage') {
                                                                        $discount_value =
                                                                            $item->price *
                                                                            ($item->discount_value / 100);
                                                                    } else {
                                                                        $discount_value = $item->discount_value;
                                                                    }
                                                                    $grand_total -= $discount_value;
                                                                    $total_discount += $discount_value;
                                                                @endphp
                                                                RS:{{ number_format($discount_value, 2) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td>Total payable:</td>
                                                        <td>RS:{{ number_format($item->grand_total, 2) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>

                                    {{-- Display package price only once at the end of the package --}}
                                    @if ($item->item_type == 'packages' && $key == $last_item_key)
                                        <tr style="border-bottom: none;">
                                            <td colspan="5" style="text-align: right;"><strong>Package
                                                    Price:</strong> RS:{{ number_format($item->price, 2) }}</td>
                                        </tr>
                                    @endif
                                @endforeach

                            @endif
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr style="width: 100%">
                    </td>
                </tr>
                <tr>
                    <td style="align:center"> <strong>Payment Details:</strong> <br> </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table width="100%" style="align:right">
                            @if ($billing->paymentMethods)
                                @php
                                    $additionalAmount = 0;
                                    $dueAmount = 0;
                                    $deductedDueAmount = 0;
                                    $total_paid = $billing->paymentMethods
                                        ->where('payment_type', '!=', 'In-store Credit')
                                        ->sum('amount');
                                    // if ($billing->payment_status == 1) {
                                    //     $paymentMethodSum = $billing->paymentMethods
                                    //         ->where('payment_type', '=', 'In-store Credit')
                                    //         ->sum('amount');
                                    //     if ($paymentMethodSum == $billing->amount) {
                                    //         $total_paid = $paymentMethodSum;
                                    //     } else {
                                    //         $total_paid = $paymentMethodSum + $total_paid;
                                    //     }
                                    // }
                                    $instoreCreditAmount = 0;
                                @endphp
                                @foreach ($billing->paymentMethods as $row)
                                    @php
                                        if ($row->payment_type == 'In-store Credit') {
                                            $instoreCreditAmount = $row->amount;
                                        }
                                    @endphp
                                    <tr>
                                        <td style="text-align: right;">{{ $row->payment_type }}:</td>
                                        <td style="text-align: right;">RS:{{ number_format($row->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                @php
                                    // $additionalAmount = $billing->amount - $billing->actual_amount;
                                    foreach ($billing->customerPendingAmount as $pendingAmount) {
                                        // if($pendingAmount->bill_id==$billing->id)  {
                                        if ($pendingAmount->removed != 1) {
                                            $instoreCreditBalance += $pendingAmount->over_paid ?? 0;
                                        }
                                    }
                                @endphp
                                <tr>
                                    {{-- @if ($billing->payment_status == 3) --}}
                                    @php
                                        foreach ($billing->customerPendingAmount as $amount) {
                                            if ($billing->id == $amount->bill_id) {
                                                // if ($amount->removed == 0) {
                                                $dueAmount += floatVal($amount->current_due);
                                                // }
                                            }
                                        }

                                    @endphp
                                    @if ($dueAmount > 0)
                                        <td style="text-align: right;">Dues:</td>
                                        <td style="text-align: right;">RS:{{ number_format($dueAmount, 2) }}</td>
                                    @endif
                                    {{-- @endif --}}
                                </tr>

                                {{-- @if ($billing->payment_status == 4) --}}
                                {{-- <tr>
                                    @php
                                    $instoreCreditBalance=0;
                                    $instoreCreditDeducted=0;
                                    $additionalPayment=0;
                                    foreach($billing->customerPendingAmount as $pendingAmount){
                                        if($pendingAmount->removed!=1){
                                            $instoreCreditBalance += $pendingAmount->over_paid ?? 0;
                                            if($pendingAmount->bill_id==$billing->id)  {
                                                $instoreCreditDeducted+=$pendingAmount->deducted_over_paid;
                                                $additionalPayment+=$pendingAmount->over_paid;
                                            } 
                                        }
                                     }
                                    @endphp
                                    <td style="text-align: right;">Instore Credit Balance:</td>
                                    <td style="text-align: right;">RS:{{ number_format($instoreCreditBalance, 2) }}
                                    </td>
                                </tr> --}}
                                {{-- @endif --}}
                                @if ($billing->items[0]->is_discount_used == 1)
                                    <tr>
                                        <td style="text-align: right;">Discount:</td>
                                        <td style="text-align: right;">RS:{{ number_format($total_discount, 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            @foreach ($billing->customerPendingAmount as $duePaid)
                                @if ($billing->id == $duePaid->bill_id && $duePaid->child_id != null)
                                    @php
                                        $totalDeductedDue += $duePaid->deducted_over_paid;
                                    @endphp
                                @endif
                            @endforeach
                            @if ($totalDeductedDue > 0)
                                <tr>
                                    <td style="text-align: right;">Due Paid:</td>
                                    <td style="text-align: right;">RS:{{ number_format($totalDeductedDue, 2) }}
                                    </td>
                                </tr>
                            @endif
                            @php
                                $subTotal = 0;
                                if ($billing->items[0]->package_id != null) {
                                    $grand_total = $grand_total - $total_discount;
                                    $subtotal = $total_paid;
                                } else {
                                    $grand_total = $grandTotal - $total_discount;
                                    $subtotal = $total_paid;
                                }
                            @endphp
                            <tr>

                                <td style="text-align: right;"><strong>Total Paid:</strong></td>
                                <td style="text-align: right;"><strong>RS:{{ number_format($subtotal, 2) }}</strong>
                                </td>
                            </tr>
                            {{-- @if ($instoreCreditAmount) --}}
                            @php

                                // $grand_total = $total_paid + $dueAmount + $instoreCreditAmount + $total_discount;
                                if (isset($item)) {
                                    if ($item->item_type == 'packages') {
                                        $grand_total = $grand_total + $totalDeductedDue - $total_discount;
                                        $subtotal = $total_paid;
                                    } else {
                                        $grand_total =
                                            $grandTotal + $total_discount + $totalDeductedDue - $total_discount;
                                        $subtotal = $total_paid;
                                        // $subtotal=$billing->amount+$instoreCreditBalance;
                                        //+ $dueAmount + $instoreCreditAmount + $total_discount;
                                    }
                                }
                            @endphp
                            {{-- @endif --}}

                            <tr>
                                <td style="text-align: right;"><strong>Grand Total:</strong></td>
                                <td style="text-align: right;"><strong>RS:{{ number_format($grand_total, 2) }}</strong>
                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>
                <tr>
                    <td>
                        <hr style="width: 100%">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">Thanks for your business.</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
