@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')
    <style>
        #manage-instore-modal {
            padding: 20px;
            overflow-x: hidden;
        }

        .validity {
            display: block !important
        }

        .strike-through {
            text-decoration: line-through;
        }
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0">
        <span>{{ Str::plural($page->title) ?? '' }}</span>
    </h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection

@section('page-action')
    <a href="javascript:" class="btn waves-effect waves-light orange darken-4 breadcrumbs-btn"
        onclick="importBrowseModal()">Bulk Upload<i class="material-icons right">attach_file</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
        class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create
        {{ Str::singular($page->title) ?? '' }}<i class="material-icons right">add</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route) }}"
        class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List
        {{ Str::plural($page->title) ?? '' }}<i class="material-icons right">list</i></a>
@endsection

<!-- users view start users-view section-data-tables invoice-list-wrapper -->
<div class="section section-data-tables">
    <!-- users view media object start -->
    <div class="card-panel">
        <!-- users view card details start -->
        <div class="row">
            <div class="col s12 m6 l6 xl3">
                <div
                    class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> Total Bills</h6>
                                <h5 class="center-align dashboard">
                                    {{ count($customer->billings) }}
                                </h5>
                            </div>

                        </div>
                    </div>
                    <div class="col s12 m12 ">

                        <p class=" dashboard right mr-0 ">Amount Paid:
                            {{ CURRENCY . ' ' . number_format($variants->completedBillTotal, 2) }}</p>

                    </div>
                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div
                    class="card gradient-45deg-amber-amber gradient-shadow min-height-100 white-text animate fadeRight">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> Pending Bills</h6>
                                <h5 class="center-align dashboard">
                                    {{ count($pending_bills) }}
                                </h5>
                            </div>

                        </div>
                    </div>
                    <div class="col s12 m12 text-center">


                        <p class="  dashboard right mr-0  ">Amount:
                            {{ CURRENCY . ' ' . number_format($variants->pendingBillTotal, 2) }}</p>

                    </div>
                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-red-pink gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> Incomplete Bills</h6>
                                <h5 class="center-align dashboard">{{ count($partial_bills) }} </h5>
                            </div>

                        </div>
                    </div>
                    <div class="col s12 m12 right-align">

                        <p class=" dashboard right mr-0 ">Due:
                            {{ CURRENCY . ' ' . number_format($variants->partialBillTotal, 2) }} </p>

                    </div>

                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeRight">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">
                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> Instore & Membership Credit
                                    Balance</h6>
                                @php
                                    $customerOverPaid = [];
                                    $customerOverPaidTotal = [];
                                    $totalOverPaid = 0;
                                    foreach ($customer->pendingDues as $key => $value) {
                                        if ($value->removed == 0 && $value->is_membership == 0) {
                                            $customerOverPaid[$key] = intval($value->over_paid);
                                        }
                                        if ($value->removed == 0) {
                                            $customerOverPaidTotal[$key] = intval($value->over_paid);
                                        }
                                    }
                                    $totalOverPaid = array_sum($customerOverPaid);
                                    $totalInstoreCreditAmount = array_sum($customerOverPaidTotal);
                                @endphp
                                <h5 class="center-align dashboard">
                                    @if (!empty($customer->pendingDues))
                                        {{ CURRENCY . ' ' . number_format(floatval($totalInstoreCreditAmount), 2) ?? '' }}
                                    @endif
                                </h5>
                            </div>

                        </div>
                    </div>
                    <div class="col s12 m12 right-align">

                        {{-- <p class="dashboard right">Credit Balance</p> --}}

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col s12 m6 l6 xl3">
                <div
                    class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> Total Discount </h6>
                                <h5 class="center-align dashboard">
                                    {{ CURRENCY . ' ' . number_format($variants->totalDiscountAmount, 2) }}</h5>

                            </div>
                            <div class="col s12 m12 text-center">
                                <p class="no-margin"></p>
                                <p></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <div class="row">
                <div class="col s12 m7">
                    <div class="display-flex media">
                        <div class="media-body">
                            <h6 class="media-heading">
                                <span class="users-view-name">{{ $customer->name }}</span>
                                @if ($customer->email != '')
                                    <span class="grey-text">@</span>
                                    <span class="users-view-username grey-text">{{ $customer->email }}</span>
                                @endif
                                <span>
                                    <a class="btn btn-light-indigo"
                                        href="{{ route('customers.reviewAboutCustomer', ['customerId' => $customer->id]) }}">Comments</a>
                                </span>
                            </h6>
                            <span>CODE:</span>
                            <span class="users-view-id">{{ $customer->customer_code }}</span>
                        </div>
                    </div>
                </div>
                <div class="col s12 m5 ">


                </div>

                <div class="col s12 m5 quick-action-btns display-flex justify-content-end align-items-center pt-2">
                    {{-- <a href="{{ url(ROUTE_PREFIX . '/history/' . $customer->id) }}"
                        class="btn-small indigo m2">History</a> --}}
                    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/' . $customer->id . '/edit') }}"
                        class="btn-small indigo">Edit</a>
                </div>
                <div>

                </div>
            </div>
        </div>
    </div>


    {{-- card generating on purchase bill --}}
    {{-- <div class="card" id="btnpurchase">
    <div class="card-content">
      <div>
        <h5>Membership Purchase</h5>
      </div>
      <div class="row">
      <input type="hidden" id="customerId" value="{{$customer->id}}">
      <lable>Select Membership</lable>
      <select class="" name="membership" id="membership">
      <option value=""> Select One</option>
      @foreach ($memberships as $key => $membership)
        <option value="{{$membership->id}}"> {{$membership->name}}</option>
      @endforeach
      </select>
  
      </div>
    </div>
  </div> --}}

    <!-- users view media object ends -->
    <!-- users view card data start -->
    <div class="card">
        <div class="card-content">
            <div class="row">
                <div class="col s12 m6">
                    <table class="striped">
                        <tbody>
                            <tr>
                                <td>Registered:</td>
                                <td>{{ $customer->created_at }}</td>
                            </tr>
                            <tr>
                                <td>Latest Activity on:</td>
                                <td class="users-view-latest-activity">{{ $last_activity->billed_date ?? '' }}</td>
                            </tr>

                            <tr>
                                <td>DOB:</td>
                                @php
                                    $dob = $customer->dob != '' ? $customer->dob->format('d-m-Y') : '';
                                @endphp
                                <td class="users-view-role">{{ $dob }}</td>
                            </tr>
                            <tr>
                                <td>Gender:</td>
                                <td>
                                    <span class=" users-view-status chip green lighten-5">
                                        @if ($customer->gender == 1)
                                            Male
                                        @endif
                                        @if ($customer->gender == 2)
                                            Female
                                        @endif
                                        @if ($customer->gender == 3)
                                            Others
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td class="users-view-verified">
                                    @if ($customer->deleted_at == null)
                                        <span class="chip lighten-5 green green-text">Active</span>
                                    @else
                                        <span class="chip lighten-5 red red-text">Banned</span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>In-Store Credit</td>
                                <td>{{ $totalOverPaid ?? '' }} </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col s12 m6">
                    <table class="striped">
                        <tbody>
                            <tr>
                                <td>Email:</td>
                                <td>{{ $customer->email ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Mobile:</td>
                                <td>{{ $customer->mobile ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Country:</td>
                                <td>{{ $customer->country->name ?? '' }}
                                    @if (!empty($customer->state->name))
                                        , {{ $customer->state->name ?? '' }}
                                    @endif
                                    @if (!empty($customer->district->name))
                                        , {{ $customer->district->name ?? '' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>GST:</td>
                                <td class="users-view-role">{{ $customer->gst ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Address:</td>
                                <td>{{ $customer->address ?? '' }} @if (!empty($customer->pincode))
                                        , {{ $customer->pincode ?? '' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><a href="{{ route('customers.edit', $customer->id) }}" class="btn indigo">Add
                                        In-Store Credit</a></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- users view card data ends -->

    @if ($customer_membership->count() > 0)
        <div class="card">
            <div class="card-content">
                <div>
                    <h5>Membership History</h5>
                </div>

                @foreach ($customer_membership as $membership)
                    <div class="row">
                        <div class="col s12 m6">
                            <table class="striped">
                                <tbody>
                                    <tr>
                                        <td>Membership:</td>
                                        <td>{{ $membership->membership->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Selling Price</td>
                                        <td class="users-view-latest-activity">
                                            {{ $membership->membership->price ?? '' }}</td>
                                    </tr>

                                    <tr>
                                        <td>Membership Price:</td>
                                        <td class="users-view-role">
                                            {{ $membership->membership->membership_price ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Membership Count:</td>
                                        @php
                                            $itemCount = 0;
                                        @endphp
                                        @foreach ($variants->bill_items as $item)
                                            @if ($item->item_id == $membership->membership->id)
                                                @php
                                                    $itemCount = $item->item_count;
                                                @endphp
                                                <td class="users-view-role"> {{ $itemCount }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                    <hr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col s12 m6">
                            <table class="striped">
                                <tbody>
                                    <tr>
                                        <td>Description:</td>
                                        <td>{{ $membership->membership->description }}</td>
                                    </tr>
                                    <tr>
                                        <td>Start Date:</td>
                                        <td>{{ $membership->start_date }}</td>
                                    </tr>
                                    <tr>
                                        <td>End Date:</td>
                                        <td>{{ $membership->end_date ?? '' }} </td>
                                    </tr>
                                    <hr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
                <div class="s12 mt-2">
                    <h6>Membership Balance Amount:&nbsp;{{ number_format($variants->lastOverPaidAmount, 2) ?? '0.00' }}
                    </h6>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div>
                    <h5>Membership Instore Credit</h5>
                </div>
                <div class="row">
                    <div class="col s12 data-table-container">
                        <div class="card-content">
                            <div class="row">
                                <div class="col ml-auto s3">
                                    <select name="membership_type" id="membership_type">
                                        <option value="">Select</option>
                                        @foreach ($memberships as $membership)
                                            <option value="{{ $membership->id }}">{{ $membership->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col s12 mt-4">
                                    <table id="data-table" class="display data-tables membershipInstoreCreditTable"
                                        data-customerId="{{ $customer->id }}" data-length="10">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th></th>
                                                {{-- <th>Service Date</th> --}}
                                                {{-- <th>Validity End Date</th> --}}
                                                <th>Service</th>
                                                <th>Price</th>
                                                <th>Therapist</th>
                                                {{-- <th>Credit Amount</th> --}}
                                                <th>Credit Used</th>
                                                <th>Balance Credit</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                {{-- <th>Service Date</th> --}}
                                                {{-- <th>Validity End Date</th> --}}
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                {{-- <th>Credit Amount</th> --}}
                                                <th> </th>
                                                <th> </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- users view card details ends -->

    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">Instore Credit Table</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                    </div>
                                </div>
                            </div>
                            <table id="data-table" class="display data-tables instoreCreditTable"
                                data-customerId="{{ $customer->id }}" data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Credit Before GST</th>
                                        <th>GST %</th>
                                        <th>Credit Amount</th>
                                        <th>Credit Used</th>
                                        <th>Balance Credit</th>
                                        <th>Expiry Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">Customer Dues</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                    </div>
                                </div>
                            </div>
                            <table id="data-table " class="display data-tables customerDueTable"
                                data-customerId="{{ $customer->id }}"data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Start Date</th>
                                        <th>Due Amount</th>
                                        <th>Deducted Amount</th>
                                        <th>Invoice</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ ucfirst($customer->name) }} Billing Table</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                        <form id="dt-filter-form" name="dt-filter-form">
                                            {{ csrf_field() }}
                                            {!! Form::hidden('customer_id', $customer->id ?? '', ['id' => 'customer_id']) !!}
                                            <div class="row">
                                                <div class="input-field col m6 s12">
                                                    <input type="search" class="" name="billing_code"
                                                        id="billing_code" placeholder="Search Invoice"
                                                        aria-controls="DataTables_Table_0">
                                                </div>
                                                <div class="input-field col m6 s12">
                                                    {!! Form::select(
                                                        'payment_status',
                                                        [
                                                            6 => 'Show All',
                                                            1 => 'Completed Bills',
                                                            0 => 'Incomplete Bills',
                                                            3 => 'Due Payment Bills',
                                                            4 => 'Over Paid Bills',
                                                        ],
                                                        '',
                                                        ['id' => 'payment_status', 'class' => 'select2 browser-default', 'placeholder' => 'Search by status'],
                                                    ) !!}
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <table id="data-table-customer-data" class="display data-tables"
                                data-url="{{ $page->link }}" data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bill ID</th>
                                        <th>Date</th>
                                        <th>In - Out Times</th>
                                        <th>Customer Paid</th>
                                        <th>In-store Paid</th>
                                        <th>Discount</th>
                                        <th>Actual Amount</th>
                                        <th>Payment Methods</th>
                                        <th>Payment Status</th>
                                    </tr>
                                </thead>
                                <tfoot align="right">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title"> Cancelled Bill Lists</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <table id="customer_canceled_list" class="display data-tables"
                                data-url="{{ $page->link }}" data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bill ID</th>
                                        <th>Date</th>
                                        <th>Actual Amount</th>
                                        <th>Customer Paid</th>
                                        <th>Refund Amount</th>
                                        <th>Cancellation Fee</th>
                                        <th>Refund Methods</th>
                                        <th>Payment Status</th>
                                    </tr>
                                </thead>
                                <tfoot align="right">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        {{-- <th></th> --}}
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title"> Schedule Lists</h4>
                    <div class="row">
                        <div class="input-field col s3">
                            <select id="therapist" name="therapist">
                                <option value="" disabled selected>Select Therapist</option>
                                @foreach ($variants->therapists as $therapist)
                                    <option value="{{ $therapist->id }}">{{ $therapist->name }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-field col s3">
                            <select id="room" name="room">
                                <option value="" disabled selected>Select Room</option>
                                @foreach ($variants->rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-field col s3">
                            <select id="service" name="service">
                                <option value="" disabled selected>Select Service</option>
                                @foreach ($variants->services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-field col s3">
                            <select id="package" name="package">
                                <option value="" disabled selected>Select Package</option>
                                @foreach ($variants->packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }} </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" id="reset-filters" class="btn red">Reset Filters</button>
                        <div class="col s12 data-table-container">
                            <table id="customer_service_list" class="display data-tables"
                                data-url="{{ $page->link }}" data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Type </th>
                                        <th>Type Name</th>
                                        <th>Therapist</th>
                                        <th>Room</th>
                                        <th>Date & Time</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                    </tr>
                                </thead>
                                <tfoot align="right">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
<!-- users view ends -->
{{-- @include('customer.manage') --}}
@include('customer.import-browse-modal')
<div id="manage-instore-modal" class="modal">
    <form id="manageInstoreForm" name="manageInstoreForm" role="form" method="POST" action=""
        class="ajax-submit">
        <div class="modalcontent">
            <div class="modal-header">
                <a class="btn-floating mb-1 waves-effect waves-light right modal-close"><i
                        class="material-icons">clear</i></a>
                <h4 class="modal-title">Instore Credit Edit Form</h4>
            </div>
            <div class="card-body" id="instore-refund">

            </div>

            <div class="modal-footer">
                <button class="btn orange waves-effect waves-light modal-action" type="button" id="cancelRefund"
                    style="display:none;">Cancel </button>

                <button class="btn cyan waves-effect waves-light form-action-btn" type="submit" name="action"
                    id="refund-submit-btn">Submit<i class="material-icons right">send</i></button>
            </div>
        </div>
    </form>
</div>
@endsection

{{-- vendor scripts --}}
@section('vendor-script')

@endsection

@push('page-scripts')
<!-- date-time-picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    var table;
    var instore_table;
    var customer_id = "{{ $customer->id }}";
    $('#payment_status').select2({
        placeholder: "Please select Bill status",
        allowClear: true
    });

    $(function() {
        table = $('#data-table-customer-data').DataTable({
            bSearchable: true,
            pagination: true,
            pageLength: 10,
            // responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            language: {
                processing: '<div class="progress"><div class="indeterminate"></div></div>'
            },
            ajax: {
                url: "{{ url(ROUTE_PREFIX . '/customers/billing-report') }}/" + customer_id,
                data: search
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    'width': '2%'
                },
                {
                    data: 'billing_code',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'billed_date',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'in_out_time',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'amount',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'instore',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'discount',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actual_amount',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'payment_method',
                    name: 'name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'payment_status',
                    name: 'name',
                    orderable: false,
                    searchable: false
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                var api = this.api(),
                    data;
                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };
                actualAmount = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                // Total over all pages
                total = api
                    .column(4)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(4).footer()).html('Total Paid<strong> ₹ ' + total.toFixed(2) +
                    '</strong>');
                $(api.column(7).footer()).html('Total<strong> ₹ ' + actualAmount.toFixed(2) +
                    '</strong>');
            }
        });
    });
    $(function() {
        var table = $('#customer_canceled_list').DataTable({
            bSearchable: true,
            pagination: true,
            pageLength: 10,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            language: {
                processing: '<div class="progress"><div class="indeterminate"></div></div>'
            },
            ajax: {
                url: "{{ url(ROUTE_PREFIX . '/customers/cancelled-billing-report') }}/" + customer_id,
                data: search
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    width: '2%'
                },
                {
                    data: 'billing_code',
                    name: 'billing_code',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'start_date',
                    name: 'start_date',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actual_amount',
                    name: 'actual_amount',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'customer_paid',
                    name: 'customer_paid',
                    orderable: false,
                    searchable: false
                },

                {
                    data: 'refund_amount',
                    name: 'refund_amount',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'cancellation_fee',
                    name: 'cancellation_fee',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'payment_method',
                    name: 'payment_method',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    });

    function search(value) {
        value.customer_id = $("#customer_id").val();
        value.payment_status = $("#payment_status").val();
        value.billing_code = $("#billing_code").val();
    }

    // $('.billing-payment-status').click(function(event) {
    //   $(".billing-payment-status").removeClass("active");
    //   $(this).addClass("active");
    //   $("#payment_status").val($(this).attr("data-status"));
    //   table.ajax.reload();
    // });

    $("#payment_status").on("change", function() {
        table.ajax.reload();
    });

    $("#billing_code").keyup(function() {
        table.ajax.reload();
    });

    $("#exportToCSVBtn").click(function() {
        // var customerForm  = $('#customerBillingTableForm').serialize();
        // var filterParam   = new FormData(customerForm);
        $.ajax({
            url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/export-report') }}",
            type: "POST",
            data: $('#customerBillingTableForm').serialize(),
        }).done(function(a) {
            disableBtn("exportToCSVBtn");
            if (data.flagError == false) {
                // showSuccessToaster(data.message);

            } else {
                // showErrorToaster(data.message);
                // printErrorMsg(data.error);
            }
        });
    });




    $(document).ready(function() {
        $('input[name="dob"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            maxYear: parseInt(moment().format('YYYY'), 10)
        }, function(start, end, label) {
            var years = moment().diff(start, 'years');
        });
    });


    if ($("#Form").length > 0) {
        var validator = $("#Form").validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 200,
                    lettersonly: true,
                },
                mobile: {
                    required: true,
                    minlength: 10,
                    maxlength: 10
                },
            },
            messages: {
                name: {
                    required: "Please enter customer name",
                    maxlength: "Length cannot be more than 200 characters",
                },
                mobile: {
                    required: "Please enter mobile number",
                    maxlength: "Length cannot be more than 10 numbers",
                    minlength: "Length must be 10 numbers",
                },
            },
            submitHandler: function(form) {
                $('#submit-btn').html('Please Wait...');
                $("#submit-btn").attr("disabled", true);
                id = $("#customer_id").val();
                customer_id = "" == id ? "" : "/" + id;
                formMethod = "" == id ? "POST" : "PUT";
                var forms = $("#Form");
                $.ajax({
                    url: "{{ url(ROUTE_PREFIX . '/' . $page->route) }}" + customer_id,
                    type: formMethod,
                    processData: false,
                    data: forms.serialize(),
                    dataType: "html",
                }).done(function(a) {
                    $('#submit-btn').html('Submit');
                    $("#submit-btn").attr("disabled", false);
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        setTimeout(function() {
                            window.location.href =
                                "{{ url(ROUTE_PREFIX . '/' . $page->route) }}";
                        }, 2000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                });
            },
        })
    }

    jQuery.validator.addMethod("lettersonly", function(value, element) {
        return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
    }, "Letters only please");

    instore_table = $('.instoreCreditTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.listInstoreCredit') }}",
            type: "GET",
            data: {
                customerId: customer_id
            }
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'start_date',
                name: 'start_date'
            },
            {
                data: 'end_date',
                name: 'end_date'
            },
            {
                data: 'credit_before_gst',
                name: 'credit_before_gst'
            },
            {
                data: 'gst',
                name: 'gst'
            },
            {
                data: 'balance',
                name: 'balance'
            },
            {
                data: 'credit_used',
                name: 'credit_used'
            },
            {
                data: 'balance_credit',
                name: 'balance_credit'
            },
            {
                data: 'status',
                name: 'status'
            },

            {
                data: 'action',
                name: 'action'
            },
        ],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            var settings = api.settings()[0];
            if (settings && settings.json && settings.json.totalOverPaid !== undefined) {
                var totalOverPaid = settings.json.totalOverPaid;
                $(api.column(5).footer()).html('Credit Balance: ' + totalOverPaid.toLocaleString('en-IN', {
                    style: 'currency',
                    currency: 'INR'
                }));
            } else {
                console.log("DataTable settings or required data are undefined.instore");
            }
        }
    });

    $(document).ready(function() {
        var table = $('.membershipInstoreCreditTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('customers.listMembershipInstoreCredit') }}",
                type: "GET",
                data: function(d) {
                    d.customerId = customer_id;
                    d.membershipId = $('#membership_type').val(); // Get selected membership type
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'banner',
                    name: 'banner'
                },
                // { data: 'start_date', name: 'start_date' },
                // { data: 'end_date', name: 'end_date' },
                {
                    data: 'service',
                    name: 'service'
                },
                {
                    data: 'service_price',
                    name: 'service_price'
                },
                {
                    data: 'therapist',
                    name: 'therapist'
                },
                {
                    data: 'credit_used',
                    name: 'credit_used'
                },
                {
                    data: 'balance_credit',
                    name: 'balance_credit'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                var settings = api.settings()[0];
                if (settings && settings.json && settings.json.totalOverPaid !== undefined) {
                    var totalOverPaid = settings.json.totalOverPaid;
                    var totalServicePrice = settings.json.totalServicePrice;
                    var totalCreditPrice = settings.json.totalCreditPrice;
                    $(api.column(3).footer()).html('Total: ' + totalServicePrice.toLocaleString(
                        'en-IN', {
                            style: 'currency',
                            currency: 'INR'
                        }));
                    $(api.column(6).footer()).html('Balance: ' + totalOverPaid.toLocaleString(
                        'en-IN', {
                            style: 'currency',
                            currency: 'INR'
                        }));
                    $(api.column(5).footer()).html('Total: ' + totalCreditPrice.toLocaleString(
                        'en-IN', {
                            style: 'currency',
                            currency: 'INR'
                        }));
                } else {
                    console.log("DataTable settings or required data are undefined.instore-member");
                }
            }
        });

        // Change event handler for membership_type dropdown
        $('#membership_type').change(function() {
            table.draw(); // Redraw the DataTable on change
        });
    });

    $('.customerDueTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.listCustomerDues') }}",
            type: "GET",
            data: {
                customerId: customer_id
            }
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'start_date',
                name: 'start_date'
            },
            {
                data: 'due',
                name: 'due'
            },
            {
                data: 'deducted_due',
                name: 'deducted_due'
            },
            {
                data: 'invoice',
                name: 'invoice'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action'
            },
        ],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();

            // Total due calculation
            var totalDue = api.column(2, {
                page: 'current'
            }).data().reduce(function(acc, val) {
                return acc + parseFloat(val || 0);
            }, 0);

            // Total deducted due calculation
            var totalDeductedDue = api.column(3, {
                page: 'current'
            }).data().reduce(function(acc, val) {
                return acc + parseFloat(val || 0);
            }, 0);

            // Update footer
            $(api.column(2).footer()).html('Total Due: ' + totalDue.toFixed(2));
            $(api.column(3).footer()).html('Total Deducted Due: ' + totalDeductedDue.toFixed(2));
        }
    });


    // Initialize DataTable
    // var table = $('#customer_service_list').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     ajax: {
    //         url: "{{ route('customers.listCustomerServices') }}",
    //         type: "GET",
    //         data: function(d) {
    //             d.customerId = customer_id;
    //             d.therapist = $('#therapist').val() || ''; // Add therapist filter
    //             d.room = $('#room').val() || ''; // Add room filter
    //             d.service = $('#service').val() || ''; // Add service filter
    //             d.package = $('#package').val() || ''; // Add package filter

    //             console.log('Filter Values:', {
    //                 therapist: d.therapist,
    //                 room: d.room,
    //                 service: d.service,
    //                 package: d.package
    //             }); // Check the values passed to the server
    //         }
    //     },
    //     columns: [{
    //             data: 'DT_RowIndex',
    //             name: 'DT_RowIndex',
    //             orderable: false,
    //             searchable: false
    //         },
    //         {
    //             data: 'type',
    //             name: 'type'
    //         },
    //         {
    //             data: 'type_name',
    //             name: 'type_name'
    //         },
    //         {
    //             data: 'therapist',
    //             name: 'therapist'
    //         },
    //         {
    //             data: 'room',
    //             name: 'room'
    //         },
    //         {
    //             data: 'start_date',
    //             name: 'start_date'
    //         },
    //         {
    //             data: 'paid_amount',
    //             name: 'paid_amount'
    //         },
    //         {
    //             data: 'status',
    //             name: 'status'
    //         },
    //         {
    //             data: 'payment_status',
    //             name: 'payment_status'
    //         }
    //     ]
    // });
    // $('#therapist, #room, #service, #package').on('change', function() {
    //     table.ajax.reload(); // Reload the table with new filter values
    // });

    // // Reset filters and reload table
    // $('#reset-filters').on('click', function() {
    //     $('#therapist, #room, #service, #package').val(''); 
    //     $('select').formSelect(); 
    //     table.ajax.reload(); 
    // });

    function openOnstoreEditModal(id) {
        $("#instore_id").val(id);
        $.ajax({
            url: "{{ route('getInstoreData') }}", // Replace this with your Laravel route
            method: 'GEt',
            data: {
                instore_id: id,
            },
            success: function(response) {
                $("#instore-refund").html(response.view);

                $("#manage-instore-modal").modal('open');
                $('input[name="validity_from"]').daterangepicker({
                        singleDatePicker: true,
                        startDate: new Date(),
                        showDropdowns: true,
                        autoApply: true,
                        timePicker: true,
                        locale: {
                            format: "DD-MM-YYYY HH:mm:ss "
                        },
                    },
                    function(ev, picker) {
                        // console.log(picker.format('DD-MM-YYYY'));
                    }
                );
            },
            error: function(xhr, status, error) {
                // Handle error response
            }
        });

    }


    $('input[name="validity_from"]').daterangepicker({
            singleDatePicker: true,
            startDate: new Date(),
            showDropdowns: true,
            autoApply: true,
            timePicker: true,
            locale: {
                format: "DD-MM-YYYY HH:mm:ss "
            },
        },
        function(ev, picker) {
            // console.log(picker.format('DD-MM-YYYY'));
        }
    );
    $(document).ready(function() {
        $('#manageInstoreForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('editInstoreData') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.data);
                        setTimeout(function() {
                            location.reload(); // Reload the page
                        }, 2000);
                    } else {

                        showErrorToaster(response.data);
                        printErrorMsg(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr, status, error);
                    console.error(xhr.responseText);
                }
            });
        });


        $('#manage-instore-modal').on('loaded.bs.modal', function(e) {
            alert('Modal is about to be shown');
            // Add your custom logic here
        });
    });
    $(document).ready(function() {
    // Initialize DataTable
    var table = $('#customer_service_list').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.listCustomerServices') }}",
            type: "GET",
            data: function(d) {
                d.customerId = customer_id;
                d.therapist = $('#therapist').val() || ''; // Add therapist filter
                d.room = $('#room').val() || ''; // Add room filter
                d.service = $('#service').val() || ''; // Add service filter
                d.package = $('#package').val() || ''; // Add package filter

                console.log('Filter Values:', {
                    therapist: d.therapist,
                    room: d.room,
                    service: d.service,
                    package: d.package
                }); // Check the values passed to the server
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'type', name: 'type' },
            { data: 'type_name', name: 'type_name' },
            { data: 'therapist', name: 'therapist' },
            { data: 'room', name: 'room' },
            { data: 'start_date', name: 'start_date' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' }
        ]
    });

    // Event listener to reload DataTable on filter change
    $('#therapist, #room, #service, #package').on('change', function() {
        table.ajax.reload(); // Reload the table with new filter values
    });

    // Reset filters and reload table
    $('#reset-filters').on('click', function() {
        $('#therapist, #room, #service, #package').val(''); // Clear filter values
        $('select').formSelect(); // Re-initialize Materialize select dropdowns
        table.ajax.reload(); // Reload the table with default filter
    });
});

</script>
@endpush
