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
        .card-panel .card {
            min-height: 130px !important;
        }

        .circle {
            width: 15px;
            height: 15px;
            border: 0px solid black;
            border-radius: 50%;
            margin: auto;
            display: inline-block;
            font-size: 11px;
            text-align: center;
            margin-right: 10px;
            line-height: 15px;
            text-transform: uppercase;
            color: #fff;
            font-weight: bold;
        }

        .behavioral_status {
            width: 10px;
            height: 10px;
            border: 0px solid black;
            border-radius: 50%;
            margin: auto;
            display: inline-block;
            margin-left: 8px;

        }

        .calm {
            background-color: green;
            /* Calm - green */
        }

        .neutral {
            background-color: #ff4081;
            /* Neutral - yellow */
        }

        .dangerous {
            background-color: red;
            /* Dangerous - red */
        }

        .warning {
            background-color: orange;
            /* Warning - orange */
        }

        .alert {
            background-color: blue;
            /* Alert - blue */
        }

        .critical {
            background-color: purple;
            /* Critical - purple */
        }

        .not_visited {
            background-color: gray;
            /* Not Visited - gray */
        }

        .rescheduled {
            background-color: orange;
            /* Rescheduled - orange */
        }

        .visited {
            background-color: green;
            /* Visited - green */
        }

        /* .open {
                background-color: blue;
               
            } */

        .finished {
            background-color: purple;
            /* Finished - purple */
        }

        .waiting {
            background-color: yellow;
            /* Waiting - yellow */
        }

        #select-options-bc3c4544-fa04-cd60-4266-60ed4879d400.dropdown-content li>a,
        .dropdown-content li:nth-child(0)>span {
            color: green !important;
        }

        #select-options-bc3c4544-fa04-cd60-4266-60ed4879d400.dropdown-content li>a,
        .dropdown-content li:nth-child(2)>span {
            color: rgb(24, 101, 17) !important;
        }

        #select-options-bc3c4544-fa04-cd60-4266-60ed4879d400.dropdown-content li>a,
        .dropdown-content li:nth-child(3)>span {
            color: blue !important;
        }
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">List</li>
    </ol>
@endsection

@section('page-action')
    <a href="{{ route('customers.createCallLog') }}"
        class="btn waves-effect waves-light green darken-2 breadcrumbs-btn">CallLog<i
            class="material-icons right">callogs</i></a>
    <a href="javascript:" class="btn waves-effect waves-light orange darken-4 breadcrumbs-btn"
        onclick="importBrowseModal()">Bulk Upload<i class="material-icons right">attach_file</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
        class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create
        {{ Str::singular($page->title) ?? '' }} <i class="material-icons right">add</i></a>
    <a class="btn dropdown-settings waves-effect waves-light  light-blue darken-4 breadcrumbs-btn" href="#!"
        data-target="dropdown1"><i class="material-icons hide-on-med-and-up">settings</i><span
            class="hide-on-small-onl">List {{ Str::plural($page->title) ?? '' }}</span><i
            class="material-icons right">arrow_drop_down</i></a>
    <ul class="dropdown-content" id="dropdown1" tabindex="0">
        <li tabindex="0"><a class="grey-text text-darken-2 listBtn" href="javascript:" data-type="active">Active </a></li>
        <li tabindex="0"><a class="grey-text text-darken-2 listBtn" data-type="deleted" href="javascript:">Inactive</a>
        </li>
    </ul>
@endsection
<div class="card-panel">
    <div class="row">
        <div class="col s12 m6 l6 xl4">
            <input type="text" class="daterange" name="customer_status_date_range" id="customer_status_date_range"
                value="" placeholder="Select Date Range">
        </div>
    </div>
    <div class="row">
        <div class="col s12 m6 l6 xl4">
            <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col s4 m4">
                            <p>New Customer</p>
                        </div>
                        <div class="col s8 m8 right-align">
                            <h5 class="mb-0 white-text"> <span
                                    id="new_customer">{{ $variants->visitingStatusCounts['new'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl4">
            <div class="card gradient-45deg-red-pink gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col s4 m4">
                            <p>Regular Customer</p>
                        </div>
                        <div class="col s8 m8 right-align">
                            <h5 class="mb-0 white-text"><span
                                    id="regular_customer">{{ $variants->visitingStatusCounts['regular'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl4">
            <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col s4 m4">
                            <p>VIP Customer</p>
                        </div>
                        <div class="col s8 m8 right-align">
                            <h5 class="mb-0 white-text"> <span
                                    id="vip_customer">{{ $variants->visitingStatusCounts['vip'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl4">
            <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col s4 m4">
                            <p>Occasional Visitor</p>
                        </div>
                        <div class="col s8 m8 right-align">
                            <h5 class="mb-0 white-text"><span
                                    id="occasional_customer">{{ $variants->visitingStatusCounts['occasional'] }}</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl4">
            <div class="card gradient-45deg-red-pink gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col s4 m4">
                            <p>Former Customer</p>
                        </div>
                        <div class="col s8 m8 right-align">
                            <h5 class="mb-0 white-text"><span
                                    id="former_customer">{{ $variants->visitingStatusCounts['former'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6 l6 xl4">
            <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                <div class="padding-4">
                    <div class="row">
                        <div class="col s4 m6">
                            <p>Week Days Customer</p>
                        </div>
                        <div class="col s6 m6 right-align">
                            <h5 class="mb-0 white-text"><span
                                    id="weekday_customer">{{ $variants->visitingStatusCounts['weekdays'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="section section-data-tables">

    <!-- DataTables example -->
    <div class="row">
        <div class="col s12 m12 l12">
            @include('layouts.success')
            @include('layouts.error')
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ Str::plural($page->title) ?? '' }} Table</h4>
                    {{-- <div class="row">
                        <form id="dt-filter-form" name="dt-filter-form">
                            {!! Form::hidden('status', '', ['id' => 'status']) !!}
                        </form>
                    </div> --}}
                    <div class="row">
                        <div class="col s12">
                            <form id="dt-filter-form">
                                <div class="row">
                                    <div class="input-field col s6">
                                        <select id="behavioral_status" name="behavioral_status">
                                            <option value="" disabled selected>Select Behavioral Status</option>
                                            <option value="0">Calm <span class="behavioral_status calm"></span>
                                            </option>
                                            <option value="1">Neutral <span
                                                    class="behavioral_status neutral"></span></option>
                                            <option value="2">Dangerous <span
                                                    class="behavioral_status dangerous"></span></option>
                                            <!-- Add more options as needed -->
                                        </select>
                                    </div>
                                    <div class="input-field col s6">
                                        <select id="visiting_status" name="visiting_status">
                                            <option value="" disabled selected>Select Visiting Status</option>
                                            <option value="">Select Status</option>
                                            <option value="0">New Customer</option>
                                            <option value="1">Regular Customer</option>
                                            <option value="2">VIP Customer</option>
                                            <option value="3">Occasional Visitor</option>
                                            <option value="4">Former Customer</option>
                                            <option value="5">Week Days Customer</option>
                                            <!-- Add more options as needed -->
                                        </select>
                                    </div>
                                    <div class="col s12">
                                        <!-- Removed the submit button -->
                                        <button type="button" id="reset-filters" class="btn red">Reset
                                            Filters</button>
                                    </div>
                                </div>
                            </form>
                            <table id="data-table-customers" class="display data-tables"
                                data-url="{{ route('customers.index') }}" data-form="dt-filter-form"
                                data-length="10">
                                <thead>
                                    <tr>
                                        <th width="20px" data-orderable="false" data-column="DT_RowIndex"> No </th>
                                        <th width="" data-orderable="false" data-column="name"> Name </th>
                                        <th width="" data-orderable="false" data-column="gender"> Gender </th>
                                        <th width="" data-orderable="false" data-column="customer_code">
                                            Customer ID </th>
                                        <th width="" data-orderable="false" data-column="email"> Email </th>
                                        <th width="100px" data-orderable="false" data-column="mobile"> Mobile </th>
                                        <th width="100px" data-orderable="false" data-column="visiting_status">
                                            Visiting Status </th>
                                        <th width="100px" data-orderable="false" data-column="status"> Status </th>
                                        <th width="100px" data-orderable="false" data-column="create_bill"> Create
                                            Bill </th>
                                        <th width="200px" data-orderable="false" data-column="action"> Action </th>
                                    </tr>
                                </thead>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('customer.import-browse-modal')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('admin/js/custom/customer/customer.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize the select elements
        $('select').formSelect();

        // Initialize the DataTable if not already initialized
        if ($.fn.DataTable.isDataTable('#data-table-customers')) {
            $('#data-table-customers').DataTable().clear().destroy(); // Clear and destroy the existing instance
        }

        // Initialize the DataTable
        var table = $('#data-table-customers').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#data-table-customers').data('url'),
                type: 'GET',
                data: function(d) {
                    // Add filter data to request
                    d.behavioral_status = $('#behavioral_status').val();
                    d.visiting_status = $('#visiting_status').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'gender',
                    name: 'gender'
                },
                {
                    data: 'customer_code',
                    name: 'customer_code'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'mobile',
                    name: 'mobile'
                },
                {
                    data: 'visiting_status',
                    name: 'visiting_status'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'create_bill',
                    name: 'create_bill'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ]
        });

        // Handle change events on select elements to filter DataTable
        $('#behavioral_status, #visiting_status').on('change', function() {
            table.draw(); // Redraw the table with new filters when a selection changes
        });

        // Reset filters
        $('#reset-filters').on('click', function() {
            $('#dt-filter-form')[0].reset(); // Reset the form
            $('select').formSelect(); // Re-initialize select elements
            table.draw(); // Redraw the table without filters
        });
    });
    $(document).ready(function() {

        // Initialize the date range picker
        $('#customer_status_date_range').daterangepicker({
            // Specify your date range picker options here
        });

        // Event listener for change in date range
        $('#customer_status_date_range').on('apply.daterangepicker', function(ev, picker) {
            var fromDate = picker.startDate.format('YYYY-MM-DD');
            var toDate = picker.endDate.format('YYYY-MM-DD');
            $.ajax({
                url: '{{ route('customerStatusFilter') }}',
                method: 'GET',
                data: {
                    toDate: toDate,
                    fromDate: fromDate
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#new_customer").html(response.data['new']);
                        $("#regular_customer").html(response.data['regular']);
                        $("#vip_customer").html(response.data['vip']);
                        $("#occasional_customer").html(response.data['occasional']);
                        $("#former_customer").html(response.data['former']);
                        $("#weekday_customer").html(response.data['weekdays']);

                    }

                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
    });
</script>
@endpush
