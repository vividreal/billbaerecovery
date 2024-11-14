@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/fullcalendar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/scheduler.min.css') }}">

    <style>
        .d-flex {
            display: flex !important
        }

        .align-items-end {
            align-items: flex-end
        }

        #card-stats .card {
            min-height: 162px !important;
        }

        .payment_history_table thead tr th {
            min-width: 30px;
        }

        .dashboard {
            font-family: system-ui;
            font-weight: 700;
            line-height: 1.1;
            color: white;
        }

        .dataTables_length select {
            display: block !important;
        }

        h5.dashboard {
            font-size: 30px;
        }

        .fc-content {
            padding: 23px 1px;
            margin-top: 2px;
            color: rgb(255, 255, 255);
        }

        .fc-title {
            margin-top: 5px;
            padding: 10px !important
        }

        .fc-time-grid .fc-event {
            overflow-y: scroll;
        }


        /*
                                                                                                                                                                                                         *  STYLE 3
                                                                                                                                                                                                         */

        .fc-time-grid .fc-event::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
            background-color: #F5F5F5;
        }

        .fc-time-grid .fc-event::-webkit-scrollbar {
            width: 6px;
            background-color: #F5F5F5;
        }

        .fc-time-grid .fc-event::-webkit-scrollbar-thumb {
            background-color: #c5c4c4;
        }

        .fc-event .fc-bg {
            background: transparent;
        }

        .paid-tag {
            background-color: #e81321;
            /* Blue background */
            color: white;
            /* White text color */
            padding: 2px 8px;
            /* Padding around the text */
            border-radius: 10px;
            /* Rounded corners */
            font-size: 12px;
            /* Font size */
            position: absolute;
            /* Absolute positioning */
            top: 0;
            /* Align to the top */
            left: 0;
            /* Align to the right */
            margin: 5px;
            /* Add margin */
        }

        .checked_in-tag {
            background-color: #2196F3;
            /* Blue background */
            color: white;
            /* White text color */
            padding: 2px 8px;
            /* Padding around the text */
            border-radius: 10px;
            /* Rounded corners */
            font-size: 12px;
            /* Font size */
            position: absolute;
            /* Absolute positioning */
            top: 0;
            /* Align to the top */
            right: 0;
            /* Align to the right */
            margin: 5px;
            /* Add margin */
        }

        @media (max-width:1399px) {

            #card-stats .card {
                min-height: 180px !important;
            }

            .card-content h6 {
                font-size: 16px;
                font-weight: 500;
            }
        }

        #customer-select .select-dropdown.dropdown-trigger {
            height: 0px !important;
            opacity: 0;
        }

        #therapist-select .select-dropdown.dropdown-trigger {
            height: 0px !important;
            opacity: 0;
        }

        #select2-customer_list-container {
            display: block !important
        }

        #therapist-select .select-wrapper input.select-dropdown.dropdown-trigger {
            display: none !important
        }

        #room-select .select-wrapper input.select-dropdown.dropdown-trigger {
            display: none !important
        }

        .select2-selection__rendered {
            position: relative;
            cursor: pointer;
            background-color: transparent;
            border: none;
            border-bottom: 1px solid #9e9e9e;
            outline: none;
            height: 3rem;
            line-height: 3rem;
            width: 100%;
            font-size: 1rem;
            margin: 0 0 8px 0;
            padding: 0;
            display: block;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            z-index: 1;
        }

        .select-wrapper input.select-dropdown {
            border-bottom: 2px solid #9e9e9e;
        }

        #room-select {

            margin-top: 21px;
            display: block;
        }

        table.dataTable thead th,
        table.dataTable thead td {
            padding: 10px 10px;
            border-bottom: 1px solid #111;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: left;
            margin: 30px 0px 0px;
        }

        .payment_history_table td span.guest_name {
            position: relative;
        }

        .payment_history_table {
            font-size: 14px;
        }

        .payment_history_table td span.guest_name_badge {
            display: inline-block;
            display: inline-block;
            padding: 4px 10px !important;
            border-radius: 0.25em;
            font-size: 9px !important;
            color: #05c123 !important;
            background-color: transparent !important;
            border-radius: 17px;
            margin-left: 10px;
            position: absolute !important;
            top: -12px;
            margin-left: 0px;
            text-transform: uppercase !important;
            font-weight: 900 !important;
        }

        .dataTables_wrapper .dataTables_length {
            min-width: 150px
        }

        .dataTables_length label {
            display: flex;
            align-items: center;
        }

        .dataTables_length select {
            display: block !important;
            margin: 0px 15px;
            height: 30px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            border: 1px solid #f4eeee;
            border-radius: 6px;
            border: 1px solid transparent;
        }

        .dataTables_length {
            position: absolute !important;
            right: 0px !important;
            padding: 10px 10px 10px;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: right !important;

        }
    </style>



@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="">Home</a></li>
        <li class="breadcrumb-item"><a href="#">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Bill History</li>
    </ol>
@endsection


<div class="section">
    <div id="card-stats" class="pt-0">


        <div class="card" style=" padding: 13px 0px;">
            <div class="row">
                <div class="col s6 "> </div>
                <div class="col s6 m-2 text-lg-center d-flex align-items-end">
                    <div class="col s6" style="width:235px;">
                        <select id="sales_daySelect" name="sales_daySelect">
                            <option value="" disabled>Select One</option>
                            <option value="today" selected>Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                    </div>
                    <div class="col s6 " style="width:150px;">
                        <select id="sales_yearSelect" name="sales_yearSelect">
                            <option value="" disabled selected>Select a year</option>
                        </select>
                    </div>
                    <div class="col s6" style="width:224px;">
                        <input type="text" class="daterange" name="sales_dashboard_date_range"
                            id="sales_dashboard_date_range" value="" placeholder="Select Date Range">
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: -116px; padding:0px 30px;">
            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal  gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">

                        <div class="row">

                            <div class="col s12 m12 center-align">
                                <h6 class="mb-0 mt-0 center-align dashboard">Total Sales Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class=" white-text center-align dashboard"><span
                                        id="total_bookings">{{ number_format(floatval($totalSaleAmount), 2) }}</span>
                                </h5>
                            </div>

                        </div>
                        <!-- row end -->
                    </div>
                    <div class="col s12 m12 right-align p-0">
                        {{-- <p class=" dashboard right " id="serviceAmount">Paid &nbsp;3,000.00</p> --}}
                    </div>

                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Paid Amount<h6>
                            </div>

                        </div>
                    </div>
                    <div class="col s12 m12 center-align">
                        <h5 class="center-align dashboard"> <span
                                id="total_paid_amount">{{ number_format(floatval($paidAmount), 2) }}</span></h5>
                    </div>
                </div>
            </div>


            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Refund Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="refund_amount">{{ number_format(floatval($total_refund), 2) }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Canceled Bill Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="canceled_amount">{{ number_format(floatval($total_canceled_bill_amount), 2) }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Service Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="service_total_amount">{{ number_format(floatval($totalServiceAmount), 2) }}</span>
                                </h5>
                            </div>

                        </div>


                    </div>
                    <div class="">
                        <p class=" dashboard right mr-5 ">Paid &nbsp;
                            <span id="service_total_amount_paid">
                                {{ number_format(floatval($serviceAmount), 2) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard"> Total Package Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class=" center-align dashboard"> <span
                                        id="package_total_amount">{{ number_format(floatval($packageAmount), 2) }}</span>
                                </h5>

                            </div>

                        </div>

                    </div>
                    <div class="">
                        <p class=" dashboard right mr-5  ">Paid &nbsp;
                            <span id="service_total_amount_paid">
                                {{ number_format(floatval($totalPackageAmount), 2) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Unpaid Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="unpaid_amount_total">{{ number_format(floatval($unpaidAmount), 2) }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Due Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="total_due_amount">{{ number_format(floatval($total_dues), 2) }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Discount Amount</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="total_discount_amount">{{ number_format(floatval($TotalDiscountAmount), 2) }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Membership Instore </h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard "> <span
                                        id="total_membership_instore">{{ number_format(floatval($total_membership_instore_credited), 2) }}</span>
                                </h5>
                            </div>

                        </div>
                    </div>
                    <div class="col s12 m12">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class=" dashboard right mr-0">Used &nbsp; <span id="service_total_amount_paid"><span
                                        id="total_membership_used_instore">{{ number_format(floatval($total_membership_instore_credit_used), 2) }}<span>
                            </p>
                            <p class=" dashboard right mr-0">Balance <span
                                    id="total_membership_balance_instore">{{ number_format(floatval($total_membership_instore_credit_balance), 2) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard">Total Instore Credited</h6>
                            </div>
                            <div class="col s12 m12 center-align">
                                <h5 class="center-align dashboard"> <span
                                        id="total_instore">{{ number_format(floatval($total_instore_credited), 2) }}</span>
                                </h5>
                            </div>


                        </div>
                    </div>
                    <div class="col s12 m12 ">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class=" dashboard right mr-0">Used &nbsp;<span
                                    id="total_used_instore">{{ number_format(floatval($creditTotalAmount), 2) }}</span>
                            </p>
                            <p class=" dashboard right mr-0">Balance <span
                                    id="total_balance_instore">{{ number_format(floatval($total_instore_credit_balance), 2) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>



        </div>

        {{-- Payment Type Cards --}}
        <div class="card  ">
            <div class="card-content">
                <div class="row">
                    <div class="col s4">
                        <h6>Payment Type </h6>
                    </div>
                    <div class="col s8 m-2 text-lg-center" style="display:flex;align-items-center">
                        <div class="col s6" style="width:235px;">
                            <select id="daySelect" name="daySelect">
                                <option value="" disabled>Select One</option>
                                <option value="today" selected>Today</option>
                                <option value="week">7 Days</option>
                                <option value="month">30 Days</option>
                            </select>
                        </div>
                        <div class="col s6 " style="width:150px;">
                            <select id="yearSelect" name="yearSelect">
                                <option value="" disabled selected>Select a year</option>
                            </select>
                        </div>
                        <div class="col s6" style="width:224px;">
                            <input type="text" class="daterange" name="dashboard_date_range"
                                id="dashboard_date_range" value="" placeholder="Select Date Range">
                        </div>
                    </div>
                    <div class="col m12 m6 l6 xl4">
                        <a href="{{ route('scheduler.listCustomerSchedules') }}">
                            <div
                                class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                                <div class="padding-4">
                                    <div class="row">
                                        <div class="col s12 m12 text-center">
                                            <h6 class="text-white"> Total Amount Using UPI </h6>
                                        </div>
                                        <div class="col s12 m12 text-center ">
                                            <h5 class="mb-0 white-text"><span
                                                    id="upiTotalAmount">{{ number_format(floatval($upiTotalAmount), 2) }}</span>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- <div class="col m12 m6 l6 xl4">
                        <div
                            class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                            <div class="padding-4">
                                <div class="row">
                                    <div class="col s12 m12 text-center">
                                        <h6 class="text-white"> Total Card Amount</h6>
                                    </div>
                                    <div class="col s12 m12 text-center ">
                                        <h5 class="mb-0 white-text"> <span
                                                id="cardTotalAmount">{{ number_format(floatval($cardTotalAmount), 2) }}</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <div class="col m12 m6 l6 xl4">
                        <div
                            class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                            <div class="padding-4">
                                <div class="row">
                                    <div class="col s12 m12 text-center ">
                                        <h6 class="text-white"> Total Cash Amount</h6>
                                    </div>
                                    <div class="col s12 m12 text-center t">
                                        <h5 class="mb-0 white-text"> <span
                                                id="cashTotalAmount">{{ number_format(floatval($cashTotalAmount), 2) }}</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m6 l6 xl4">
                        <a href="{{ route('scheduler.listCustomerSchedules') }}">
                            <div
                                class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                                <div class="padding-4">
                                    <div class="row">
                                        <div class="col s12 m12 text-center ">
                                            <h6 class="text-white"> Total In-store Amount</h6>
                                        </div>
                                        <div class="col s12 m12 text-center">
                                            <h5 class="mb-0 white-text"><span
                                                    id="creditTotalAmount">{{ number_format(floatval($creditTotalAmount), 2) }}</span>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!--Basic Form-->
        <div class="card payment-history-card ">
            <div class="row">
                <div class="col m-6 s12">
                    <h5>Payment History </h5>
                    <div class="col  m-6 s6">
                        <div class="form-group">
                            <div>
                                <label for="paymentType">Payment Type:</label>
                                <select name="payment_type" id="payment_type">
                                    <option value="0">Choose One</option>
                                    @foreach ($paymentTypes as $paymentType)
                                        <option value="{{ $paymentType->id }}">{{ $paymentType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label id="customer-select" for="customer">Customer:
                                    <select name="customer_list" id="customer_list" class="form-control">
                                        <option value="0">Choose One</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select></label>
                            </div>
                        </div>
                    </div>
                    <div class="col  m-6 s6">
                        <div class="form-group">
                            <div>
                                <label id="therapist-select" for="therapist">Therapist:
                                    <select name="therapist_list" id="therapist_list">
                                        <option value="0">Choose One</option>
                                        @foreach ($therapists as $therapist)
                                            <option value="{{ $therapist->id }}">{{ $therapist->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div>
                                <label id="room-select" for="room">Room:
                                    <select name="room_list" id="room_list" class="form-control">
                                        <option value="0">Choose One</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select></label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <table class="payment_history_table  data-table table table-responsive display">
                            <thead>
                                <tr>
                                    <th style="min-width:20px !important">No</th>
                                    <th style="min-width:100px">Date</th>
                                    <th style="min-width:100px">Invoice</th>
                                    <th style="min-width:170px">Guest Name</th>
                                    <th style="min-width:30px">Gender</th>
                                    <th style="min-width:200px">Service</th>
                                    <th style="min-width:80px">Price</th>
                                    <th style="min-width:100px">Time In</th>
                                    <th style="min-width:120px!important">Time Out By</th>
                                    <th style="min-width:80px">Min</th>
                                    <th style="min-width:120px">Therapist</th>
                                    <th style="min-width:100px">Room</th>
                                    <th style="min-width:120px !important">Cancellation Fee</th>
                                    <th style="min-width:80px">Discount</th>
                                    <th style="min-width:120px !important">In-store Credit</th>
                                    <th style="min-width:80px">Cash</th>
                                    <th style="min-width:80px">Pay:Card/Online</th>
                                    <th style="min-width:120px">Total Per Client</th>
                                    <th style="min-width:120px">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="card payment-history-card ">
            <div class="row">
                <div class="col m-6 s12">
                    <h5>Business Cash List </h5>
                    {{-- <div class="col  m-6 s6">
                        <div class="form-group">
                            <div style="">

                                <select name="cashbook_transaction_type" id="cashbook_transaction_type">
                                    <option value="0">Choose One</option>
                                    <option value="1">Credit</option>
                                    <option value="2">Debit </option>
                                </select>
                            </div>
                        </div>
                    </div> --}}
                    <div class="col m-6 s8 ml-auto"> </div>
                    <div class="col m-6 s4 ml-auto">
                        <div class="form-group">
                            <div style="">

                                <input type="text" class="daterange cashbook_date_range"
                                    name="cashbook_date_range" id="cashbook_date_range" value=""
                                    placeholder="Select Date Range">
                            </div>
                        </div>
                    </div>
                    {{-- --}}
                    <div>
                        <table class="cashbook_history_table  data-table table table-responsive display">
                            <thead>
                                <tr>
                                    <th colspan="6"
                                        style="text-align: center; background-color: #f1f1f1; font-weight: bold;">
                                        Business Cash Opening Balance: <span id="businessCashBalance"
                                            style="color: green; font-weight: bold;"></span>
                                    </th>
                                    <th colspan="6"
                                        style="text-align: center; background-color: #f1f1f1;font-weight: bold;">
                                        Business Cash Closing Balance: <span id="businessCashClosingBalance"
                                            style="color: rgb(221, 10, 7); font-weight: bold;"></span>
                                    </th>
                                </tr>
                                <tr>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Cashbook Type</th>
                                    <th>Credit</th>
                                    <th>Debit</th>
                                    {{-- <th>Trasaction Amount</th> --}}
                                    <th>Balance Amount</th>
                                    <th>Narration</th>

                                </tr>
                            </thead>
                            <tbody>
                                {{-- @php
                                    $previousDate = null; // Variable to track the last processed date
                                    $openingBalance = 0.0; // Initialize an opening balance variable
                                    $closingBalance = 0.0; // Initialize a closing balance variable
                                @endphp

                                @foreach ($cashbookLists as $index => $cashbookList)
                                    @php
                                        $currentDate = \Carbon\Carbon::parse($cashbookList->created_at)->format('d-m-Y');

                                        // Check if we have a new date
                                        if ($previousDate !== $currentDate) {
                                            // If not the first date, display the closing balance of the previous date
                                            if ($previousDate !== null) {
                                                echo '<tr><td colspan="7" style="font-weight: bold; background-color: #f0f0f0;">Date: ' .
                                                    $previousDate .
                                                    ' | Closing Balance: ' .
                                                    number_format($closingBalance, 2) .
                                                    '</td></tr>';
                                            }

                                            // Reset opening balance for the new date
                                            $openingBalance = 0.0;
                                            $closingBalance = 0.0;

                                            // If cashbook has related entries, get the opening and closing balances
                                            if ($cashbookList->cashbook->isNotEmpty()) {
                                                foreach ($cashbookList->cashbook as $cashbalance) {
                                                    if ($cashbalance->opening_business_cash_balance !== null) {
                                                        $openingBalance = $cashbalance->opening_business_cash_balance;
                                                    }
                                                    
                                                    if ($cashbalance->closing_business_cash_balance !== null) {
                                                        $closingBalance = $cashbalance->closing_business_cash_balance;
                                                    }
                                                }
                                            }
                                            
                                            echo '<tr><td colspan="7" style="font-weight: bold; background-color: #f0f0f0;">Date: ' .
                                                $currentDate .
                                                ' | Opening Balance: ' .
                                                number_format($openingBalance, 2) .
                                                '</td></tr>';

                                            // Update previous date to the current date
                                            $previousDate = $currentDate;
                                        }

                                        // Update the closing balance after each transaction
                                        $closingBalance = $cashbookList->balance_amount; // Assuming you want to accumulate this amount
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $currentDate }}</td>
                                        <td>
                                            @if ($cashbookList->cash_book == 1)
                                               
                                                Business Cash
                                            @else
                                                Petty Cash
                                            @endif
                                        </td>
                                        <td>
                                            @if ($cashbookList->transaction == 1)
                                                Credit
                                            @else
                                                Debit
                                            @endif
                                        </td>
                                        <td>{{ number_format($cashbookList->transaction_amount, 2) }}</td>
                                        <td>{{ number_format($cashbookList->balance_amount, 2) }}</td>
                                        <td>{{ $cashbookList->message }}</td>
                                    </tr>
                                @endforeach

                                @if ($previousDate !== null)
                                    <tr>
                                        <td colspan="7" style="font-weight: bold; background-color: #f0f0f0;">Date:
                                            {{ $currentDate }} | Closing Balance:
                                            {{ number_format($closingBalance, 2) }}
                                        </td>
                                    </tr>
                                @endif
                                --}}


                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="card payment-history-card ">
            <div class="row">
                <div class="col m-6 s12">
                    <h5>Petty Cash List </h5>
                    {{-- <div class="col  m-6 s6">
                        <div class="form-group">
                            <div style="">

                                <select name="petty_transaction_type" id="petty_transaction_type">
                                    <option value="0">Choose One</option>
                                    <option value="1">Credit</option>
                                    <option value="2">Debit </option>
                                </select>
                            </div>
                        </div>
                    </div> --}}
                    <div class="col m-6 s8 ml-auto"> </div>
                    <div class="col m-6 s4 ml-auto">
                        <div class="form-group">
                            <div style="">

                                <input type="text" class="daterange petty_date_range" name="petty_date_range"
                                    id="petty_date_range" value="" placeholder="Select Date Range">
                            </div>
                        </div>
                    </div>
                    {{-- --}}
                    <div>
                        <table class="cashbook_petty_cash_history_table  data-table table table-responsive display">
                            <thead>
                                <tr>
                                    <th colspan="6"
                                        style="text-align: center;background-color: #f1f1f1; font-weight: bold;">
                                        Petty Cash Opening Balance: <span id="pettyCashBalance"
                                            style="color: green; font-weight: bold;"></span>
                                    </th>
                                    <th colspan="6"
                                        style="text-align: center; background-color: #f1f1f1;font-weight: bold;">
                                        Petty Cash Closing Balance: <span id="pettyCashCloseBalance"
                                            style="color: rgba(238, 15, 45, 0.777); font-weight: bold;"></span>
                                    </th>
                                </tr>
                                <tr>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Cashbook Type</th>
                                    <th>Credit</th>
                                    <th>Debit</th>
                                    {{-- <th>Trasaction Amount</th> --}}
                                    <th>Balance Amount</th>
                                    <th>Narration</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

@endsection

{{-- vendor scripts --}}
@section('vendor-script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.6/css/jquery.dataTables.min.css">

    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">
    <script src="{{ asset('admin/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/js/scripts/data-tables.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
@endsection

@push('page-scripts')
    <!-- typeahead -->

    <!-- Full calendar -->
    @php
        if (config('app.env') === 'local') {
            $url = '/';
        } else {
            $url = '/billbae/';
        }
    @endphp
    <script>
        $(document).ready(function() {
            $('#customer_list').select2({
                placeholder: "Choose One", // Placeholder for the select box
                allowClear: true // Allows clearing the selection
            });
            $('#therapist_list').select2({
                placeholder: "Choose One", // Placeholder for the select box
                allowClear: true // Allows clearing the selection
            });
            $('#room_list').select2({
                placeholder: "Choose One", // Placeholder for the select box
                allowClear: true // Allows clearing the selection
            });
        });
        $(document).ready(function() {
            var table = $('.payment_history_table').DataTable({
                paging: true,
                autoWidth: true,
                pageLength: 10,
                searching: false,
                ordering: false,
                lengthChange: true,
                lengthMenu: [10, 25, 50, 75, 100],
                info: false,
                dom: "Blfrtip",
                buttons: [{
                        extend: 'excel',
                        title: 'Daily Summary',
                        exportOptions: {
                            modifier: {
                                page: 'all', // Export all pages
                                search: 'none' // Ignore search filtering in export
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        title: 'Billbae|Daily Summary',
                        pageSize: 'A3', // Set the page size (e.g., A4, A3, letter)
                        orientation: 'landscape',
                        exportOptions: {
                            modifier: {
                                page: 'all', // Export all pages
                                search: 'none' // Ignore search filtering in export
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('searchPayment') }}",
                    data: function(d) {
                        d.paymentType = $('#payment_type').val();
                        d.customer_list = $('#customer_list').val();
                        d.therapist_list = $('#therapist_list').val();
                        d.room_list = $('#room_list').val();

                    },
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {

                        data: 'invoice',
                        name: 'invoice'
                    },
                    {
                        data: 'guest_name',
                        name: 'guest_name',

                    },
                    {
                        data: 'gender',
                        name: 'gender'
                    },
                    {
                        data: 'service',
                        name: 'service'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },

                    {
                        data: 'timeIn',
                        name: 'timeIn'
                    },
                    {
                        data: 'timeOutBy',
                        name: 'timeOutBy'
                    },
                    {
                        data: 'min',
                        name: 'min'
                    },
                    {
                        data: 'therapist',
                        name: 'therapist'
                    },

                    {
                        data: 'roomNo',
                        name: 'roomNo'
                    },
                    {
                        data: 'cancellationfee',
                        name: 'cancellationfee'
                    },
                    {
                        data: 'discount',
                        name: 'discount'
                    },
                    {
                        data: 'instoreCredit',
                        name: 'instoreCredit'
                    },
                    {
                        data: 'cash',
                        name: 'cash'
                    },
                    {
                        data: 'pay_cardOnline',
                        name: 'pay_cardOnline'
                    },

                    {
                        data: 'totalPerClient',
                        name: 'totalPerClient'
                    }, {
                        data: 'total',
                        name: 'total'
                    }



                ]
            });
            $("#payment_type, #customer_list, #therapist_list, #room_list").change(function() {

                table.ajax.reload();
            });
        });
        $(document).ready(function() {
            var table = $('.cashbook_history_table').DataTable({
                paging: true,
                pageLength: 10, // Shows all entries without limiting by page length
                lengthChange: true, // Hides the "Show entries" dropdown
                searching: false,
                ordering: false,
                lengthMenu: [10, 25, 50, 100], // Dropdown options
                info: false,
                dom: "Blfrtip",
                buttons: [{
                        extend: 'excel',
                        title: 'Business Cash List',
                        exportOptions: {
                            modifier: {
                                page: 'all', // Export all pages
                                search: 'none' // Ignore search filtering in export
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        title: 'Business Cash List',
                        exportOptions: {
                            modifier: {
                                page: 'all', // Export all pages
                                search: 'none' // Ignore search filtering in export
                            }
                        }
                    }
                ],

                ajax: {
                    url: "{{ route('cashbookList') }}",
                    data: function(d) {
                        d.cashbook_date_range = $("#cashbook_date_range").val();
                        d.cashbook_transaction_type = $("#cashbook_transaction_type").val();
                    },
                    dataSrc: function(json) {
                        $('#businessCashBalance').text(json.businessCashBalance ?? '0');
                        $('#businessCashClosingBalance').text(json.businessCashclosingBalance ?? '0');
                        return json.data;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'transaction_type',
                        name: 'transaction_type'
                    },
                    {
                        data: 'debit',
                        name: 'debit'
                    },
                    {
                        data: 'credit',
                        name: 'credit'
                    },
                    // {
                    //     data: 'transaction_amount',
                    //     name: 'transaction_amount'
                    // },
                    {
                        data: 'balance_amount',
                        name: 'balance_amount'
                    },

                    {
                        data: 'narration',
                        name: 'narration'
                    },


                ]
            });
            $("#cashbook_transaction_type,.cashbook_date_range").on('change', function() {
                table.ajax.reload();
            });
            var cashtable = $('.cashbook_petty_cash_history_table').DataTable({
                paging: true,
                pageLength: 10, // Shows all entries without limiting by page length
                lengthChange: true, // Hides the "Show entries" dropdown
                searching: false,
                ordering: false,
                lengthMenu: [10, 25, 50, 100], // Dropdown options
                info: false,
                dom: "Blfrtip",
                buttons: [{
                        extend: 'excel',
                        title: 'Petty Cash List',
                        exportOptions: {
                            modifier: {
                                page: 'all', // Export all pages
                                search: 'none' // Ignore search filtering in export
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        title: 'Petty Cash List',
                        exportOptions: {
                            modifier: {
                                page: 'all', // Export all pages
                                search: 'none' // Ignore search filtering in export
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('pettyCashbookList') }}",
                    data: function(d) {
                        d.petty_date_range = $("#petty_date_range").val();
                        d.petty_transaction_type = $("#petty_transaction_type").val();

                    },
                    dataSrc: function(json) {
                        $('#pettyCashBalance').text(json.pettyCashBalance ?? '0');
                        $('#pettyCashCloseBalance').text(json.pettyCashCloseBalance ?? '0');
                        return json.data;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'transaction_type',
                        name: 'transaction_type'
                    },
                    {
                        data: 'debit',
                        name: 'debit'
                    },
                    {
                        data: 'credit',
                        name: 'credit'
                    },
                    // {
                    //     data: 'transaction_amount',
                    //     name: 'transaction_amount'
                    // },
                    {
                        data: 'balance_amount',
                        name: 'balance_amount'
                    },

                    {
                        data: 'narration',
                        name: 'narration'
                    },


                ]
            });
            $("#petty_transaction_type,.petty_date_range").on('change', function() {
                cashtable.ajax.reload();
            });
        });
    </script>


    <script>
        var yearSelect = document.getElementById('yearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            yearSelect.appendChild(option);
        }
        $('#dashboard_date_range').daterangepicker({
            // Specify your date range picker options here
        });
        $('#cashbook_date_range').daterangepicker({
            autoUpdateInput: false, // Prevent auto-update
            locale: {
                cancelLabel: 'Clear'
            }
        });

        // Handle date selection
        $('#cashbook_date_range').on('apply.daterangepicker', function(ev, picker) {
            // Manually set the value in the input field
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));

            // Trigger the filter to reload the table with the selected date range
            $('.cashbook_history_table').DataTable().ajax.reload();
        });

        // Handle the "Clear" button (optional)
        $('#cashbook_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val(''); // Clear the input
            $('.cashbook_history_table').DataTable().ajax.reload(); // Reload table without the date range filter
        });

        $('#petty_date_range').daterangepicker({
            autoUpdateInput: false, // Prevent auto-update
            locale: {
                cancelLabel: 'Clear'
            }
        });

        // Handle date selection
        $('#petty_date_range').on('apply.daterangepicker', function(ev, picker) {
            // Manually set the value in the input field
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));

            // Trigger the filter to reload the table with the selected date range
            $('.cashbook_petty_cash_history_table').DataTable().ajax.reload();
        });

        // Handle the "Clear" button (optional)
        $('#petty_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val(''); // Clear the input
            $('.cashbook_petty_cash_history_table').DataTable().ajax
                .reload(); // Reload table without the date range filter
        });

        document.getElementById('daySelect').addEventListener('change', function() {
            var selectedDay = this.value;
            $.ajax({
                url: '{{ route('paymentTypeFilter') }}',
                method: 'GET',
                data: {
                    day: selectedDay
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#creditTotalAmount").text(response.data['In-store Credit'])
                        $("#upiTotalAmount").text(response.data['UPI'])
                        $("#cashTotalAmount").text(response.data['Cash'])
                        //$("#cardTotalAmount").text(response.data['card'])
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
        document.getElementById('yearSelect').addEventListener('change', function() {
            var selectedYear = this.value;
            $.ajax({
                url: '{{ route('paymentTypeFilter') }}',
                method: 'GET',
                data: {
                    year: selectedYear
                },
                success: function(response) {
                    if (response.flagError == false) {
                        console.log(response.data);
                        $("#creditTotalAmount").text(response.data['In-store Credit'])
                        $("#upiTotalAmount").text(response.data['UPI'])
                        $("#cashTotalAmount").text(response.data['Cash'])
                        // $("#cardTotalAmount").text(response.data['card'])
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
        $('#dashboard_date_range').on('apply.daterangepicker', function(ev, picker) {
            var fromDate = picker.startDate.format('YYYY-MM-DD');
            var toDate = picker.endDate.format('YYYY-MM-DD');
            $.ajax({
                url: '{{ route('paymentTypeFilter') }}',
                method: 'GET',
                data: {
                    toDate: toDate,
                    fromDate: fromDate
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#creditTotalAmount").text(response.data['In-store Credit'])
                        $("#upiTotalAmount").text(response.data['UPI'])
                        $("#cashTotalAmount").text(response.data['Cash'])
                        // $("#cardTotalAmount").text(response.data['card'])
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
    </script>
    <script>
        var sales_yearSelect = document.getElementById('sales_yearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            sales_yearSelect.appendChild(option);
        }
        $('#sales_dashboard_date_range').daterangepicker({
            // Specify your date range picker options here
        });
        document.getElementById('sales_daySelect').addEventListener('change', function() {
            var selectedDay = this.value;
            $.ajax({
                url: '{{ route('salesPaymentTypeFilter') }}',
                method: 'GET',
                data: {
                    day: selectedDay
                },
                success: function(response) {
                    if (response.flagError == false) {
                        console.log(response.data);
                        $("#total_bookings").text(response.data.totalSaleAmount)
                        $("#total_paid_amount").text(response.data.paidAmount)
                        $("#refund_amount").text(response.data.total_refund)
                        $("#canceled_amount").text(response.data.total_canceled_bill_amount)
                        $("#service_total_amount").text(response.data.totalServiceAmount)
                        $("#service_total_amount_paid").text(response.data.serviceAmount)
                        $("#package_total_amount").text(response.data.packageAmount)
                        $("#package_total_amount_paid").text(response.data.totalPackageAmount)
                        $("#unpaid_amount_total").text(response.data.unpaidAmount)
                        $("#total_due_amount").text(response.data.total_dues)
                        $("#total_discount_amount").text(response.data.TotalDiscountAmount)
                        $("#total_instore").text(response.data.total_instore_credited)
                        $("#total_membership_instore").text(response.data
                            .total_membership_instore_credited)
                        $("#total_membership_used_instore").text(response.data
                            .total_membership_instore_credit_used)
                        $("#total_membership_balance_instore").text(response.data
                            .total_membership_instore_credit_balance)
                        $("#total_used_instore").text(response.data.total_instore_credit_used)
                        $("#total_balance_instore").text(response.data.total_instore_credit_balance)
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
        document.getElementById('sales_yearSelect').addEventListener('change', function() {
            var selectedYear = this.value;
            $.ajax({
                url: '{{ route('salesPaymentTypeFilter') }}',
                method: 'GET',
                data: {
                    year: selectedYear
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#total_bookings").text(response.data.totalSaleAmount)
                        $("#total_paid_amount").text(response.data.paidAmount)
                        $("#refund_amount").text(response.data.total_refund)
                        $("#canceled_amount").text(response.data.total_canceled_bill_amount)
                        $("#service_total_amount").text(response.data.totalServiceAmount)
                        $("#service_total_amount_paid").text(response.data.serviceAmount)
                        $("#package_total_amount").text(response.data.packageAmount)
                        $("#package_total_amount_paid").text(response.data.totalPackageAmount)
                        $("#unpaid_amount_total").text(response.data.unpaidAmount)
                        $("#total_due_amount").text(response.data.total_dues)
                        $("#total_discount_amount").text(response.data.TotalDiscountAmount)
                        $("#total_instore").text(response.data.total_instore_credited)
                        $("#total_membership_instore").text(response.data
                            .total_membership_instore_credited)
                        $("#total_membership_used_instore").text(response.data
                            .total_membership_instore_credit_used)
                        $("#total_membership_balance_instore").text(response.data
                            .total_membership_instore_credit_balance)
                        $("#total_used_instore").text(response.data.total_instore_credit_used)
                        $("#total_balance_instore").text(response.data.total_instore_credit_balance)
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
        $('#sales_dashboard_date_range').on('apply.daterangepicker', function(ev, picker) {
            var fromDate = picker.startDate.format('YYYY-MM-DD');
            var toDate = picker.endDate.format('YYYY-MM-DD');
            $.ajax({
                url: '{{ route('salesPaymentTypeFilter') }}',
                method: 'GET',
                data: {
                    toDate: toDate,
                    fromDate: fromDate
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#total_bookings").text(response.data.totalSaleAmount)
                        $("#total_paid_amount").text(response.data.paidAmount)
                        $("#refund_amount").text(response.data.total_refund)
                        $("#canceled_amount").text(response.data.total_canceled_bill_amount)
                        $("#service_total_amount").text(response.data.totalServiceAmount)
                        $("#service_total_amount_paid").text(response.data.serviceAmount)
                        $("#package_total_amount").text(response.data.packageAmount)
                        $("#package_total_amount_paid").text(response.data.totalPackageAmount)
                        $("#unpaid_amount_total").text(response.data.unpaidAmount)
                        $("#total_due_amount").text(response.data.total_dues)
                        $("#total_discount_amount").text(response.data.TotalDiscountAmount)
                        $("#total_instore").text(response.data.total_instore_credited)
                        $("#total_membership_instore").text(response.data
                            .total_membership_instore_credited)
                        $("#total_membership_used_instore").text(response.data
                            .total_membership_instore_credit_used)
                        $("#total_membership_balance_instore").text(response.data
                            .total_membership_instore_credit_balance)
                        $("#total_used_instore").text(response.data.total_instore_credit_used)
                        $("#total_balance_instore").text(response.data.total_instore_credit_balance)
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
    </script>
@endpush
