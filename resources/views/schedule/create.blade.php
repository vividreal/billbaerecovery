@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/fullcalendar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/scheduler.min.css') }}">


    <style>
        .fc-popover.fc-more-popover {
            top: 50% !important;
            right: 50% !important;
            position: fixed;
            margin-top: -200px;
            margin-right: -300px;
            left: auto !important;
        }

        .fc-more-popover .fc-event-container {
            padding: 10px;
            overflow-y: scroll;
            max-height: 400px;
        }

        .fc-more-popover {
            z-index: 2;
            width: 400px !important;
        }

        /*.fc-month-view table .fc-day-grid-event .fc-content { overflow: scroll;*/
        /*    width: 150px;*/
        /*    height: 79px;*/
        /*    display: block;}*/


        .disabled-select {
            pointer-events: none;
            background-color: #f0f0f0;
            /* Optional: To indicate the field is disabled */
        }

        .service_checkbox {
            position: relative !important;
            opacity: unset !important;
            pointer-events: none !important;
            margin: inherit;
        }

        .package_checkbox {
            position: relative !important;
            opacity: unset !important;
            pointer-events: none !important;
            margin: inherit;
        }

        .checkbox-container {
            display: flex;
            flex-wrap: wrap;
        }

        .checkbox-container label {
            margin-right: 20px;
            /* Adjust spacing as needed */
            pointer-events: auto;
            /* Enable pointer events for labels */
        }

        .fc-agendaDay-view .fc-scroller {
            overflow: hidden scroll !important;
            height: 600px !important;
        }

        .fc-month-view .fc-scroller {
            overflow: hidden hidden !important;
        }


        .fc-view-container .fc-agendaDay-view {
            max-width: 2500px;
        }

        .fc.fc-agendaDay-view table {
            width: -webkit-fill-available;
            max-width: 100%;
        }

        .fc-resource-cell {
            min-width: 260px;
        }

        .fc-axis {
            min-width: 86px;
        }

        #select2-package_of_service-container+.select2-selection__arrow {
            display: none;
        }

        .fc-today-button {
            text-transform: capitalize;
        }

        .fc-button {
            text-transform: capitalize;
        }

        .fc-content {
            padding: 23px 1px;
            margin-top: 2px;
            color: rgb(255, 255, 255);
        }

        .fc-title {
            margin-top: 5px;
            padding: 10px !important;
            font-size: 11px
        }

        .fc-time-grid .fc-event {
            overflow-y: scroll;
            border: 0.5px solid #383838 !important;
            overflow-x: auto;
        }

        .fc-time-grid-event .fc-content {
            min-width: 140px;
        }

        /* Custom scrollbar styles */
        .fc-time-grid .fc-event::-webkit-scrollbar {
            width: 6px;
            /* width of the entire scrollbar */
            height: 6px;
            /* height of the horizontal scrollbar */
        }

        .fc-time-grid .fc-event::-webkit-scrollbar-track {
            background: #F5F5F5;
            /* color of the track */
        }

        .fc-time-grid .fc-event::-webkit-scrollbar-thumb {
            background: #c5c4c4;
            /* color of the scroll thumb */
            border-radius: 10px;
            /* rounded corners */
        }

        .fc-time-grid .fc-event::-webkit-scrollbar-thumb:hover {
            background: #555;
            /* color of the scroll thumb on hover */
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

        .package_class {
            background-color: #2143c7be;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            position: absolute;
            top: 0;
            right: 90px;
            margin: 5px;
        }

        .package_of_service .select2-selection__arrow {
            display: none !important;
        }
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a
                href="{{ $page->link }}/calendar/therapists">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection

@section('page-action')
    @can('schedule-create')
        {{-- {{ $page->link }} --}}
        {{-- <a href="{{ url('/schedules/calendar/therapists') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">List<i class="material-icons right">list</i></a> --}}
    @endcan
@endsection

<div class="section">
    <div id="card-stats" class="pt-0">

        <div class="card">
            <div class="row">
                <div style="display: block; float:right; padding: 10px;">
                    <div class="col s4" style="width:235px;">
                        <select id="daySelect" name="daySelect">
                            <option value="" disabled >Select One</option>
                            <option value="today"selected >Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                    </div>
                    <div class="col s4 " style="width:150px;">
                        <select id="yearSelect" name="yearSelect">
                            <option value="" disabled selected>Select a year</option>
                        </select>
                    </div>
                    <div class="col s4" style="width:224px;">
                        <input type="text" class="daterange" name="dashboard_date_range" id="dashboard_date_range"
                            value="" placeholder="Select Date Range">
                    </div>
                </div>
            </div>

            <div class="row dashboard-card-row" style="margin: 3%">
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s12 m12">
                                    <h6 class="mb-0 mt-9 center-align dashboard text-white">Total Bookings</h6>
                                </div>
                                <div class="col s12 m12 text-center">
                                    <h5 class="mb-0 white-text dashboard text-center"><span id="total_bookings"></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s12 m12">
                                    <h6 class="mb-0 mt-9 center-align dashboard text-white">Booking value</h6>
                                </div>
                                <div class="col s12 m12 text-center">
                                    <h5 class="mb-0 white-text dashboard text-center"><span id="booking_amount"></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s12 m12">
                                    <h6 class="mb-0 mt-9 center-align dashboard text-white"> Total Sales</h6>
                                </div>
                                <div class="col s12 m12 text-center">
                                    <h5 class="mb-0 white-text dashboard text-center"><span id="total_sales"></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s12 m12">
                                    <h6 class="mb-0 mt-9 center-align dashboard text-white">Total Sales value</h6>
                                </div>
                                <div class="col s12 m12 text-center">
                                    <h5 class="mb-0 white-text dashboard text-center"><span id="sales_amount"></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <a href="{{ route('scheduler.listCustomerSchedules') }}">
                        <div
                            class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                            <div class="padding-4">
                                <div class="row">
                                    <div class="col s12 m12">
                                        <h6 class="mb-0 mt-9 center-align dashboard text-white"> Total Canceled
                                            Schedules</h6>
                                    </div>
                                    <div class="col s12 m12 text-center">
                                        <h5 class="mb-0 white-text dashboard text-center"><span
                                                id="canceled_schedule"></span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <a href="{{ route('scheduler.listCustomerSchedules') }}">
                        <div
                            class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                            <div class="padding-4">
                                <div class="row">
                                    <div class="col s12 m12">
                                        <h6 class="mb-0 mt-9 center-align dashboard text-white"> Checked-in Customers
                                        </h6>
                                    </div>
                                    <div class="col s12 m12 text-center">
                                        <h5 class="mb-0 white-text dashboard text-center"><span
                                                id="checked_in_customer"></span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <a href="{{ route('scheduler.listCustomerSchedules') }}">
                        <div
                            class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                            <div class="padding-4 card-content">
                                <div class="row text-center">
                                    <div class="col s12 m12">
                                        <h6 class="mb-0 mt-9 center-align dashboard text-white"> Not Checked-in
                                            Customers</h6>
                                    </div>
                                    <div class="col s12 m12 text-center">
                                        <h5 class="mb-0 white-text dashboard text-center"><span
                                                id="not_checked_in_customer"></span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <!--Basic Form-->
        <div class="row">
            <!-- Form Advance -->
            <div class="col s12 m12 l12">
                <div id="Form-advance" class="card card card-default scrollspy">
                    <div class="card-content">

                        <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                            <div class="card-content red-text">
                                <ul></ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                        <form id="dt-filter-form" name="dt-filter-form">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="input-field col m3 s3 right">
                                                    {!! Form::select('calendar_mode', ['therapists' => 'Therapist', 'rooms' => 'Rooms'], $mode ?? '', [
                                                        'id' => 'calendar_mode',
                                                        'class' => 'select2 browser-default',
                                                        'placeholder' => 'View schedule as per:',
                                                    ]) !!}
                                                </div>
                                                <div class="input-field col m3 s3  text-start mt-2">
                                                    <h4 class="card-title">{{ $page->title ?? '' }} Form </h4>
                                                </div>
                                                <div class="input-field col m3 s3 right text-end mt-2">
                                                    <span style="display: inline;">
                                                        <h6> View schedule as per: </h6>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <form id="{{ Str::camel($page->title) }}Form" name="{{ Str::camel($page->title) }}Form"
                                role="form" method="post" action="{{ url(ROUTE_PREFIX . '/' . $page->route) }}">
                                {{ csrf_field() }}
                                {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle']) !!}
                                {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                                {!! Form::hidden('billingRoute', url('billings'), ['id' => 'billingRoute']) !!}
                                {!! Form::hidden('timePicker', $variants->time_picker, ['id' => 'timePicker']) !!}
                                {!! Form::hidden('timeFormat', $variants->time_format, ['id' => 'timeFormat']) !!}
                                {!! Form::hidden('timezone', $variants->timezone, ['id' => 'timezone']) !!}
                                {!! Form::hidden('currency', CURRENCY, ['id' => 'currency']) !!}
                                {!! Form::hidden('mode', $mode, ['id' => 'mode']) !!}
                                {!! Form::hidden('item_count', 1, ['id' => 'item_count']) !!}
                            </form>
                            <div class="col s12">
                                <div id="preCalendar"></div>
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('schedule.manage')
    @include('schedule.refund')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
    <!-- typeahead -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <!-- Full calendar -->
    @php
        if (config('app.env') === 'local') {
            $url = '/';
        } else {
            $url = '/billbae/';
        }
    @endphp
    <script>
        var new_url = "{{ $url }}";
        var get_report_by_date = "{{ route('get_report_by_date') }}";
        var rooms_get_all = "{{ route('rooms-get-all') }}";
        var therapists = "{{ route('therapists.index') }}";
        var get_customer_details = "{{ route('get_customer_details') }}";
        var get_all_services = "{{ route('get_all_services') }}";
        var get_details = "{{ route('get_details') }}";
        var get_packages_details = "{{ route('getPackageDetails') }}";
        var getPackageList = "{{ route('get_all_packages') }}";
        var path = "{{ route('customers.autocomplete') }}";
        var getScheduleList = "{{ route('scheduler.listCustomerSchedules') }}";
        var refundBillPayment = "{{ route('billings.refundBillPayment') }}";
        var scheduleServiceList = "{{ route('billings.scheduleServiceLists') }}";
    </script>
    <script src="{{ asset('admin/js/fullcalendar.js') }}"></script>
    <script src="{{ asset('admin/js/custom/fullcalendar.js') }}"></script>
    <script src="{{ asset('admin/js/custom/daypilot-all.min.js') }}"></script>
    <script src="{{ asset('admin/js/custom/schedule/schedule.js') }}"></script>
    <script>
        var yearSelect = document.getElementById('yearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            yearSelect.appendChild(option);
        }
    </script>
    <script>
        $('input.typeahead').typeahead({
            autoSelect: true,
            hint: true,
            highlight: true,
            minLength: 2,
            source: function(query, process) {
                return $.get(path, {
                    search: query,
                    classNames: {
                        input: 'Typeahead-input',
                        hint: 'Typeahead-hint',
                        selectable: 'Typeahead-selectable'
                    }
                }, function(data) {
                    return process(data);
                });
                $('.searchCustomerLabel').show();
            },
            updater: function(item) {
                $('#customer_id').val(item.id);
                $('#search_customer').prop('disabled', true);
                showSuccessToaster("Customer selected successfully.");
                getCustomerDetails(item.id);
                return item;
            }
        });
        $('.searchCustomerLabel').hide();



        $(document).ready(function() {

            // Initialize the date range picker
            $('#dashboard_date_range').daterangepicker({
                // Specify your date range picker options here
            });

            // Event listener for change in date range
            $('#dashboard_date_range').on('apply.daterangepicker', function(ev, picker) {
                var fromDate = picker.startDate.format('YYYY-MM-DD');
                var toDate = picker.endDate.format('YYYY-MM-DD');
                $.ajax({
                    url: '{{ route('scheduleFilter') }}',
                    method: 'GET',
                    data: {
                        toDate: toDate,
                        fromDate: fromDate
                    },
                    success: function(response) {
                        if (response.flagError == false) {
                            $("#total_bookings").html(response.total_bookings);
                            $("#booking_amount").html(response.booking_amount);
                            $("#total_sales").html(response.total_sales);
                            $("#sales_amount").html(response.sales_amount);
                            $("#canceled_schedule").html(response.total_canceled);
                            $("#checked_in_customer").html(response.checked_in_customer);
                            $("#not_checked_in_customer").html(response
                                .not_checked_in_customer);

                        }

                    },
                    error: function(err) {
                        console.error('Error fetching data: ' + err);
                    }
                });
            });
        });
        document.getElementById('yearSelect').addEventListener('change', function() {
            var selectedYear = this.value;
            $.ajax({
                url: '{{ route('scheduleFilter') }}',
                method: 'GET',
                data: {
                    year: selectedYear
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#total_bookings").html(response.total_bookings);
                        $("#booking_amount").html(response.booking_amount);
                        $("#total_sales").html(response.total_sales);
                        $("#sales_amount").html(response.sales_amount);
                        $("#canceled_schedule").html(response.total_canceled);
                        $("#checked_in_customer").html(response.checked_in_customer);
                        $("#not_checked_in_customer").html(response.not_checked_in_customer);

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
        document.getElementById('daySelect').addEventListener('change', function() {
            var selectedDay = this.value;
            $.ajax({
                url: '{{ route('scheduleFilter') }}',
                method: 'GET',
                data: {
                    day: selectedDay
                },
                success: function(response) {
                    if (response.flagError == false) {
                        $("#total_bookings").html(response.total_bookings);
                        $("#booking_amount").html(response.booking_amount);
                        $("#total_sales").html(response.total_sales);
                        $("#sales_amount").html(response.sales_amount);
                        $("#canceled_schedule").html(response.total_canceled);
                        $("#checked_in_customer").html(response.checked_in_customer);
                        $("#not_checked_in_customer").html(response.not_checked_in_customer);
                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
    </script>
@endpush
