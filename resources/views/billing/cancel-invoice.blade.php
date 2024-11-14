@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/app-invoice.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/page-users.css') }}">
    <style>
        td,
        th {
            padding: 15px 10px;
            display: table-cell;
            text-align: left;
            vertical-align: middle;
            border-radius: 2px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        .badge-custom {
            background-color: #ff5252;
            color: white;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 12px;
        }

        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /*==================================================
                                                =            Bootstrap 3 Media Queries             =
                                                ==================================================*/




        /*==========  Mobile First Method  ==========*/

        /* Custom, iPhone Retina */
        @media only screen and (min-width : 320px) {}

        /* Extra Small Devices, Phones */
        @media only screen and (min-width : 480px) {}

        /* Small Devices, Tablets */
        @media only screen and (min-width : 768px) {}

        /* Medium Devices, Desktops */
        @media only screen and (min-width : 992px) {}

        /* Large Devices, Wide Screens */
        @media only screen and (min-width : 1200px) {}



        /*==========  Non-Mobile First Method  ==========*/

        /* Large Devices, Wide Screens */
        @media only screen and (max-width : 1200px) {}

        /* Medium Devices, Desktops */
        @media only screen and (max-width : 992px) {}

        /* Small Devices, Tablets */
        @media only screen and (max-width : 768px) {}

        /* Extra Small Devices, Phones */
        @media only screen and (max-width : 480px) {}

        /* Custom, iPhone Retina */
        @media only screen and (max-width : 320px) {}



        /*=====================================================
                                                =            Bootstrap 2.3.2 Media Queries            =
                                                =====================================================*/
        @media only screen and (max-width : 1200px) {}

        @media only screen and (max-width : 979px) {}

        @media only screen and (max-width : 767px) {}

        @media only screen and (max-width : 480px) {}

        @media only screen and (max-width : 320px) {}

        <style type="text/css">
        /* default styles here for older browsers.
                                                       I tend to go for a 600px - 960px width max but using percentages
                                                    */
        @media only screen and (min-width:960px) {
            /* styles for browsers larger than 960px; */
        }

        @media only screen and (min-width:1440px) {
            /* styles for browsers larger than 1440px; */
        }

        @media only screen and (min-width:2000px) {
            /* for sumo sized (mac) screens */
        }

        @media only screen and (max-device-width:480px) {
            /* styles for mobile browsers smaller than 480px; (iPhone) */
        }

        @media only screen and (device-width:768px) {
            /* default iPad screens */
        }

        /* different techniques for iPad screening */
        @media only screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait) {
            /* For portrait layouts only */
        }

        @media only screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape) {
            /* For landscape layouts only */
        }
    </style>
@endsection
@section('content')
@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection
{{-- @section('page-action')
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create
{{ Str::singular($page->title) ?? '' }}<i class="material-icons right">add</i></a>
<!--<a href="{{ url(ROUTE_PREFIX . '/' . $page->route) }}"-->
<!--    class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List -->
<!--    {{ Str::plural($page->title) ?? '' }}<i class="material-icons right">list</i></a>-->
@endsection --}}
<section class="invoice-view-wrapper section">
    <div class="row">
        <!-- invoice view page -->
        <div class="col-12 xl9 m8 s12">
            <div class="card">
                <div class="">
                    <table cellpadding="0" cellspacing="0">
                        <tr class="top">
                            <td colspan="6">
                                <table>
                                    <tr style="border-bottom: none;">
                                        <td class="title">
                                            {{-- <img src="{{ $variants->store->show_image }}" style="width:100%; max-width:250px;"> --}}
                                            <h3 class="proton-logo"><span> {{ $variants->shop->name ?? '' }} </span> Day
                                                Spa </h3>
                                        </td>
                                        <td> <span class="badge badge-custom">Canceled</span>
                                        </td>
                                        <td style="text-align: right">
                                            Invoice Cancelled : {{ $billing->billing_code }}<br>                                            
                                            Created: {{ $refundBill->created_at }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr class="information">
                            <td colspan="6">
                                <table>
                                    <tr style="border-bottom: none;">
                                        <td width="65%">
                                            <div class="row invoice-info">
                                                <div class="col m6 s12">
                                                    <h6 class="invoice-from">Bill From</h6>
                                                    <div class="invoice-address">
                                                        <span>{{ $variants->store->billing->company_name ?? '' }}</span>
                                                    </div>
                                                    <div class="invoice-address">
                                                        <span>{{ $variants->store->email ?? '' }}</span>
                                                    </div>
                                                    <div class="invoice-address">
                                                        <span>{{ $variants->store->contact ?? '' }}</span>
                                                    </div>
                                                    <div class="invoice-address">
                                                        <span>{{ $variants->store->billing->address ?? '' }}</span>
                                                    </div>
                                                    <div class="invoice-address">
                                                        <span>
                                                            @if ($variants->store->billing->gst != '')
                                                                GST: {{ $variants->store->billing->gst ?? '' }}
                                                            @endif
                                                        </span>
                                                    </div>

                                                </div>
                                        </td>

                                        <td style="text-align: right">
                                            <div class="col m12 s12">
                                                <div class=" mb-3"></div>
                                                <h6 class="invoice-to">Bill To</h6>
                                                {!! $billing->customer_address ?? '' !!}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="heading">
                            <td colspan="6">
                                <table>
                                    <tr>
                                        <td>No</td>
                                        <td>Item </td>
                                        <td>Number of Items</td>
                                        <td>SAC Code #</td>
                                        <td style="text-align: right">Details</td>
                                    </tr>
                                    @if ($billing_items)
                                        @php
                                            $billing_items_count = $billing_items->count();
                                            $total_discount = 0;
                                            $additionalAmount = 0;
                                            $instoreCreditBalance = 0;
                                            $subtotal = 0;
                                            $refundAmount = 0;
                                            $cancellation_fee = 0;
                                            $total_item_price = 0;
                                            $currentPackageName = null;
                                            $deductedDueAmount = 0;


                                        @endphp
                                        @foreach ($billing_items as $key => $item)
                                            @php
                                                if ($item->item_type == 'packages') {
                                                    $subtotal += $item->grand_total;
                                                } else {
                                                    $subtotal += $item->grand_total;
                                                }
                                                $total_item_price += $item->price;
                                                $refundAmount = $item->totalRefundAmount;

                                            @endphp                                                  
                                        
                                            @if ($item->item_type == 'packages' && $item->name != $currentPackageName)
                                                @php
                                                    $currentPackageName = $item->name; // Update the current package name
                                                @endphp
                                                <tr style="border-top: 2px solid #e7e8eb;">
                                                    <td colspan="5" style="text-align: center;">
                                                        <strong>{{ $currentPackageName }}</strong>
                                                        @foreach ($cancelItems as $cancelItem)
                                                        @if ($item->package_id == $cancelItem && $item->package_id != 0)
                                                            <span class="badge badge-danger"> Cancelled </span>
                                                        @endif
                                                    @endforeach
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr id="{{ $item['id'] }}" class="item" style="border-bottom: none;">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->item_details }}
                                                    @foreach ($cancelItems as $cancelItem)
                                                        @if ($item->item_id == $cancelItem && $item->item_id != 0)
                                                            <span class="badge badge-danger"> Cancelled </span>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td style="width: 30px; text-align:center">{{ $item->item_count }}</td>
                                                <td>{{ $item->hsn_code ?? '---' }}</td>
                                                <td>
                                                    <table>
                                                        <tbody>
                                                            <tr>
                                                                <td>Service value</td>
                                                                <td>
                                                                    @if ($item->item_type != 'packages')
                                                                        {{ number_format($item->tax_amount, 2) }}
                                                                    @else
                                                                        {{ number_format($item->tax_amount, 2) }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            {{-- @if ($item->item_type != 'packages') --}}
                                                            @if ($item->cgst_amount > 0)
                                                                <tr>
                                                                    <td>CGST ({{ $item->cgst_percentage }}%)</td>
                                                                    <td>₹{{ $item->cgst_amount }}</td>
                                                                </tr>
                                                            @endif
                                                            @if ($item->sgst_amount > 0)
                                                                <tr>
                                                                    <td>SGST ({{ $item->sgst_percentage }}%)</td>
                                                                    <td>₹{{ $item->sgst_amount }}</td>
                                                                </tr>
                                                            @endif
                                                            {{-- @endif --}}
                                                            @if ($item->additionalTax && count($item->additionalTax) > 0)
                                                                @foreach ($item->additionalTax as $key => $additional)
                                                                    <tr>
                                                                        <td>{{ $additional->tax_name }}
                                                                            ({{ $additional->percentage }}%)
                                                                        </td>
                                                                        <td>₹{{ number_format($additional->amount, 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            @if ($item->is_discount_used == 1)
                                                                <tr style="border-bottom:none">
                                                                    <td>Discount @if ($item->discount_type == 'percentage')
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
                                                                           
                                                                            $total_discount =
                                                                                $total_discount + $discount_value;
                                                                        @endphp
                                                                        - ₹{{ number_format($discount_value, 2) }}
                                                                    </td>
                                                                </tr>
                                                                {{-- @else
                                                                @php
                                                                    $discount_value = 0;
                                                                @endphp
                                                                <tr>
                                                                    <td>Discount</td>
                                                                    <td>- ₹{{ number_format($discount_value, 2) }}</td>
                                                                </tr> --}}
                                                            @endif
                                                            <tr style="border-bottom: none;">
                                                                <td>Total payable</td>
                                                                <td>₹{{ number_format($item->grand_total, 2) }}
                                                                </td>
                                                            </tr>
                                                            @if ($item->item_type == 'packages' && $item->name == $currentPackageName)
                                                                <tr style="border-bottom: none;">
                                                                    <td>Package Benefit</td>
                                                                    <td>RS:{{ number_format($item->price, 2) }}</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">
                                <table>
                                    <tr>
                                        <td style="text-align: right"> <strong style="font-size: 20px">Payment
                                                Details:</strong> <br>
                                            <table>
                                                @if ($billing->paymentMethods)
                                                    @php
                                                        $instoreCreditAmount = 0;
                                                        $total_paid = $billing->paymentMethods
                                                            ->where('payment_type', '!=', 'In-store Credit')
                                                            ->sum('amount');
                                                        if ($billing->payment_status == 1) {
                                                            $paymentMethodSum = $billing->paymentMethods
                                                                ->where('payment_type', '=', 'In-store Credit')
                                                                ->sum('amount');
                                                            if ($paymentMethodSum == $billing->amount) {
                                                                $total_paid = $paymentMethodSum;
                                                            } else {
                                                                $total_paid = $paymentMethodSum + $total_paid;
                                                            }
                                                        }

                                                    @endphp
                                                    @foreach ($billing->paymentMethods as $row)
                                                        @php
                                                            if ($row->payment_type == 'In-store Credit') {
                                                                $instoreCreditAmount = $row->amount;
                                                            }
                                                        @endphp
                                                        <tr style="text-align: right">
                                                            <td style="text-align: right"> {{ $row->payment_type }}
                                                            </td>
                                                            <td style="text-align: right">
                                                                ₹{{ number_format($row->amount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    {{-- <tr>
                                                        @php
                                                            $dueAmount = 0;
                                                            $deductedDueAmount = 0;
                                                            foreach (
                                                                $billing->customerPendingAmount
                                                                as $pendingAmount
                                                            ) {
                                                                if ($billing->id == $pendingAmount->bill_id) {
                                                                    if ($pendingAmount->removed == 0) {
                                                                        $dueAmount += floatval(
                                                                            $pendingAmount->current_due,
                                                                        );
                                                                    }
                                                                }
                                                            }

                                                        @endphp
                                                         @if ($billing->payment_status == 3)                                                        <br>
                                                            <td style="text-align: right">Dues</td>
                                                            <td style="text-align: right">
                                                                ₹{{ number_format($dueAmount, 2) }}</td>
                                                        {{-- @endif 
                                                    </tr> --}}
                                                    <tr>
                                                        <td style="text-align: right">Total</td>
                                                        @php
                                                            $dueAmount = 0;
                                                            foreach ($billing->customerPendingAmount as $pendingAmount) {
                                                                if ($pendingAmount->removed == 0) {
                                                                    $dueAmount += floatVal($pendingAmount->current_due);
                                                                }
                                                            }
                                                            if (isset($item)) {
                                                                
                                                                if ($item->item_type == 'packages') {
                                                                    $grand_total = $grand_total;
                                                                } else {
                                                                    $grand_total = $grand_total+$total_discount;
                                                                }
                                                            }
                                                            $formatted_grand_total = number_format($grand_total, 2);
                                                        @endphp
                                                        <td style="text-align: right">₹{{ $formatted_grand_total }}
                                                        </td>
                                                    </tr>
                                                    @if ($total_discount)
                                                        <tr>
                                                            <td style="text-align: right">Discount</td>
                                                            <td style="text-align: right">
                                                                ₹{{ number_format($total_discount, 2) }}</td>

                                                        </tr>
                                                    @endif

                                                @endif
                                                @php
                                                    $instoreCreditBalance = 0;
                                                    $instoreCreditDeducted = 0;
                                                    $additionalPayment = 0;
                                                    foreach ($billing->customerPendingAmount as $pendingAmount) {
                                                        if ($pendingAmount->removed != 1) {
                                                            $instoreCreditBalance += $pendingAmount->over_paid;
                                                            if ($pendingAmount->bill_id == $billing->id) {
                                                                $instoreCreditDeducted +=
                                                                    $pendingAmount->deducted_over_paid;
                                                                $additionalPayment += $pendingAmount->over_paid;
                                                            }
                                                        }
                                                    }
                                                @endphp

                                                
                                                @php
                                                // dd($item);
                                                    if (isset($item)) {
                                                        if ($item->item_type == 'packages') {
                                                            $subtotal = $subtotal;
                                                        } else {
                                                            $additionalPayment = $total_paid + $deductedDueAmount;
                                                        }
                                                        $cancellation_fee = $item->totalActualAmount - $item->totalRefundAmount;
                                                    }
                                                @endphp
                                                @php
                                                    $dueAmount = 0;
                                                @endphp
                                                @foreach ($customerDue as $due)
                                                    @if (is_array($due) && isset($due['refundId']) && $due['refundId'] == $refundBill->id)
                                                        @php
                                                            $dueAmount = $due['dueAmount'];
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <tr>
                                                    <td style="text-align: right">Subtotal</td>
                                                    <td style="text-align: right">₹
                                                        {{ number_format($subtotal, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right">Due</td>
                                                    <td style="text-align: right">₹
                                                        {{ number_format($dueAmount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right">Refund</td>
                                                    <td style="text-align: right">₹
                                                        {{ number_format($refundAmount, 2) }}</td>
                                                </tr>
                                             
                                                <tr>
                                                    <td style="text-align: right">Cancellation Fee</td>
                                                    <td style="text-align: right">₹
                                                        {{ number_format($cancellation_fee - $dueAmount, 2) }}</td>
                                                </tr>


                                            </table>


                                        </td>
                                    </tr>
                                    <tr style="border-bottm:0px; ">
                                        <td colspan="2" style="text-align:center;bottm:0px;"> <span>Thanks
                                                for your business</span> </td>
                                    </tr>
                                </table>
                            </td>



                        </tr>
                    </table>
                </div>

            </div>
        </div>
        <!-- invoice action  -->

</section>
@endsection
{{-- vendor scripts --}}
@section('vendor-script')
@endsection
@push('page-scripts')
<script>
    var generatePdf = "{{ route('printPdf', $billing->id) }}";
</script>
<script src="{{ asset('admin/js/scripts/app-invoice.js') }}"></script>
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script></script>
@endpush
