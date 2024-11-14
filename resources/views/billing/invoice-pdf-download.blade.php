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

        * {
            box-sizing: border-box;
        }

        .column {
            float: left;
            width: 50%;
            padding: 10px;
            height: 100px;
        }


        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        #itemTable table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <div>
        <div class="row">
            <div class="column">
                <h2>{{ $variants->shop->name ?? '' }}</h2>

            </div>
            <div class="column" style="align-items: flex-end">
                <p> Invoice #: {{ $billing->billing_code }}</p>
                <p>
                    Created: {{ $billing->formatted_billed_date }}</p>
            </div>
        </div>
        <div>
            <hr style="width: 100%">
        </div>
        <div class="row">
            <div class="column">
                <h4>Bill From</h4>
                {{ $store->billing->company_name ?? '' }}<br>
                {{ $store->email ?? '' }}<br>
                {{ $store->contact ?? '' }}<br>
                {{ $store->billing->address ?? '' }}<br>
                @if ($store->billing->gst != '')
                    GST: {{ $store->billing->gst ?? '' }}
                @endif
            </div>
            <div class="column" style="align-items: flex-end">
                <h4>Bill To</h4>
                {!! $billing->customer_address ?? '' !!}
            </div>
        </div>
        <div style="margin-top: 80px">
            <hr style="width: 100%">
        </div>
        <div style="margin-top: 30px">
            <table id="itemTable">
                <tr>
                    <td style="width:30px">No</td>
                    <td style="width:150px">Item </td>
                    <td style="width:100px">Number of Items</td>
                    <td style="width:80px">SAC Code #</td>
                    <td style="width:250px">Details</td>
                </tr>
                @php
                    $totalDeductedDue = 0;
                    $total_discount = 0;
                    $additionalAmount = 0;
                    $instoreCreditBalance = 0;
                    $subtotal = 0;
                    $grandTotal = 0;
                    $currentPackageName = null;

                @endphp
                @if ($billing_items)
                    @foreach ($billing_items as $key => $item)
                        @php
                            if ($item->item_type == 'packages') {
                                $subtotal = $subtotal + $item->grand_total;
                            } else {
                                $subtotal = $subtotal + $item->grand_total - $total_discount;
                                $grandTotal += $item->grand_total;
                            }

                        @endphp
                        @if ($item->item_type == 'packages' && $item->name != $currentPackageName)
                            @php
                                $currentPackageName = $item->name; // Update the current package name
                                $package_items = collect($billing_items->toArray())->where(
                                    'package_id',
                                    $item->package_id,
                                );
                                $last_item_key = $package_items->keys()->last();
                            @endphp
                            <tr style="border-top: 2px solid #e7e8eb;">
                                <td colspan="5" style="text-align: center;">
                                    <strong>{{ $item->name }}</strong>
                                    <!-- Display the package name -->
                                </td>
                            </tr>
                        @endif
                        <tr id="{{ $item['id'] }}" class="item">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->item_details }}</td>
                            <td>{{ $item->item_count }}</td>
                            <td>{{ $item->hsn_code }}</td>
                            <td>
                                <table style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    <tbody
                                        style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                        <tr
                                            style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                            <td
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none; padding-right:15px;">
                                                Service value</td>
                                            <td
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                RS:@if ($item->item_type != 'packages')
                                                    {{ number_format($item->tax_amount, 2) }}
                                                @else
                                                    {{ number_format($item->tax_amount, 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- @if ($item->item_type != 'packages') --}}
                                        @if ($item->cgst_amount > 0)
                                            <tr
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    CGST ({{ $item->cgst_percentage }}%)</td>
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    RS:{{ $item->cgst_amount }}</td>
                                            </tr>
                                        @endif
                                        @if ($item->sgst_amount > 0)
                                            <tr
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    SGST ({{ $item->sgst_percentage }}%)</td>
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    RS:{{ $item->sgst_amount }}</td>
                                            </tr>
                                        @endif
                                        {{-- @endif --}}
                                        @if ($item->additionalTax !== null && count($item->additionalTax) > 0)
                                            @foreach ($item->additionalTax as $key => $additional)
                                                <tr
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    <td
                                                        style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                        {{ $additional->tax_name }}
                                                        ({{ $additional->percentage }}%)
                                                    </td>
                                                    <td
                                                        style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                        RS:{{ number_format($additional->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @if ($item->is_discount_used == 1)
                                            <tr
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    Discount @if ($item->discount_type == 'percentage')
                                                        ({{ $item->discount_value }}%)
                                                    @endif
                                                </td>
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    @php
                                                        if ($item->discount_type == 'percentage') {
                                                            $discount_value =
                                                                $item->price * ($item->discount_value / 100);
                                                        } else {
                                                            $discount_value = $item->discount_value;
                                                        }
                                                        $grand_total = $grand_total;
                                                        $total_discount = $total_discount + $discount_value;
                                                    @endphp
                                                    RS:{{ number_format($discount_value, 2) }}
                                                </td>
                                            </tr>
                                        @else
                                            @php
                                                $discount_value = 0;
                                            @endphp
                                            <tr
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    Discount</td>
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    - RS:{{ number_format($discount_value, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr
                                            style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                            <td
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                Total payable</td>
                                            <td
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                RS:{{ number_format($item->grand_total, 2) }}
                                            </td>
                                        </tr>
                                        @if ($item->item_type == 'packages' && $key == $last_item_key)
                                            <tr
                                                style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    Package Price</td>
                                                <td
                                                    style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                                    RS:{{ number_format($item->price, 2) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </div>
        <div style="margin-top:0px;">
            <hr style="width: 100%">
        </div>
        <div class="row">
            <div class="column">

            </div>
            <div class="column">
                <p> <strong style="font-size: 20px;text-align:right">Payment Details:</strong></p>
                <table style="border-bottom: none; border-top:none;border-left:none;border-right:none;text-align:right">
                    @if ($billing->paymentMethods)
                        @php
                            $instoreCreditAmount = 0;
                            $dueAmount = 0;
                            // $total_paid = $billing->amount;
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
                        @endphp
                        @foreach ($billing->paymentMethods as $row)
                            @php

                                if ($row->payment_type == 'In-store Credit') {
                                    $instoreCreditAmount = $row->amount;
                                }
                            @endphp
                            <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    {{ $row->payment_type }} </td>
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    RS:{{ number_format($row->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @php
                            foreach ($billing->customerPendingAmount as $pendingAmount) {
                                $instoreCreditBalance += $pendingAmount->over_paid ?? 0;
                            }
                        @endphp

                        <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">

                            @php

                                $dueAmount = 0;
                                $deductedDueAmount = 0;
                                foreach ($billing->customerPendingAmount as $pendingAmount) {
                                    if ($billing->id == $pendingAmount->bill_id) {
                                        // if ($pendingAmount->removed == 0) {
                                        $dueAmount += floatVal($pendingAmount->current_due);
                                        // }
                                        // $deductedDueAmount+=floatval($pendingAmount->deducted_over_paid);
                                    }
                                }

                            @endphp
                            {{-- @if ($billing->payment_status == 3) --}}
                            @if ($dueAmount > 0)
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    Dues
                                </td>
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    RS:
                                    {{ number_format($dueAmount, 2) }}
                                </td>
                            @endif
                            {{-- @endif --}}

                        </tr>
                        {{-- <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">

                            @php
                             $balanceInstoreCredit=0;
                             $instoreCreditDeducted=0;
                             $additionalPayment=0;
                            foreach($billing->customerPendingAmount as $pendingAmount){
                                // if($pendingAmount->bill_id==$billing->id)  {
                                    if($pendingAmount->removed!=1){
                                        $balanceInstoreCredit += $pendingAmount->over_paid ?? 0;   
                                        if($pendingAmount->bill_id==$billing->id)  {
                                            $instoreCreditDeducted+=$pendingAmount->deducted_over_paid;
                                            $additionalPayment+=$pendingAmount->over_paid;
                                        }
                                    }  
                                // }   
                            }
                                
                            @endphp
                            <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                               In-store Credit Balance</td>
                            <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">RS
                                {{ number_format($balanceInstoreCredit, 2) }}</td>

                        </tr> --}}
                        {{-- @endif --}}
                        @if ($billing->items[0]->is_discount_used == 1)
                            <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    Discount</td>
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">RS
                                    {{ number_format($total_discount, 2) }}</td>

                            </tr>
                        @endif
                        @foreach ($billing->customerPendingAmount as $duePaid)
                            @if ($billing->id == $duePaid->bill_id && $duePaid->child_id != null)
                                @php
                                    $totalDeductedDue += $duePaid->deducted_over_paid;
                                @endphp
                            @endif
                        @endforeach
                        @if ($totalDeductedDue > 0)
                            <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                                    Due Paid</td>
                                <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;">RS
                                    {{ number_format($totalDeductedDue, 2) }}</td>

                            </tr>
                        @endif
                        @php
                            $subTotal = 0;

                            if ($billing->items[0]->package_id != null) {
                                $grand_total = $grand_total + $totalDeductedDue - $total_discount;
                                $subtotal = $total_paid;
                            } else {
                                $grand_total = $grandTotal + $total_discount + $totalDeductedDue - $total_discount;
                                $subtotal = $total_paid;
                            }

                        @endphp

                        <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                            <td
                                style="border-bottom: none; border-top:none;border-left:none;border-right:none; padding-right:25px;">
                                <strong style="font-size: 20px">Total Paid</strong>
                            </td>
                            <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;"><strong
                                    style="font-size: 20px">RS &nbsp;{{ number_format($subtotal, 2) }}</strong></td>
                        </tr>


                        <tr style="border-bottom: none; border-top:none;border-left:none;border-right:none;">
                            <td
                                style="border-bottom: none; border-top:none;border-left:none;border-right:none; padding-right:25px;">
                                <strong style="font-size: 20px">Grand Total</strong>
                            </td>
                            <td style="border-bottom: none; border-top:none;border-left:none;border-right:none;"><strong
                                    style="font-size: 20px">RS &nbsp;{{ number_format($grand_total, 2) }}</strong></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        <div style="margin-top:90px;">
            <hr style="width: 100%">
        </div>
        <div class="row">
            <div style="text-align: center;">
                <p>Thanks for your business.</p>
            </div>
        </div>

    </div>
</body>

</html>
