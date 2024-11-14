@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('page-style')
    <style>
        .timeline {margin-left: 200px !important;}
        .timeline .event { padding-left: 19px !important;}
      .timeline .event p.float-right   {     position: absolute;right: 0px;top: 0px;}
        .p-5 {
            padding: 20px
        }

        .select_customer .select-wrapper input.select-dropdown {
            display: none;
        }

        #select2-customer_id-6n-container {
            display: block !important
        }

        @media (max-width: 768px) {
            .timeline .event::before {
                left: 0.5px;
                top: 20px;
                min-width: 0;
                font-size: 13px;
            }

            .timeline .event:nth-child(1)::before,
            .timeline .event:nth-child(3)::before,
            .timeline .event:nth-child(5)::before {
                top: 38px;
            }

            .timeline h3 {
                font-size: 16px;
            }

            .timeline p {
                padding-top: 20px;
            }
        }

        @media (max-width: 945px) {
            .timeline .event::before {
                left: 0.5px;
                top: 20px;
                min-width: 0;
                font-size: 13px;
            }

            .timeline h3 {
                font-size: 16px;
            }

            .timeline p {
                padding-top: 20px;
            }

            section.lab h3.card-title {
                padding: 5px;
                font-size: 16px
            }
        }

        section.timeline-outer {
            width: 100%;
            margin: 0 auto;
        }



        / Timeline /
        .timeline {
            border-left: 8px solid #42A5F5;
            border-bottom-right-radius: 2px;
            border-top-right-radius: 2px;
            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);
            color: #333;
            margin: 50px auto;
            letter-spacing: 0.5px;
            position: relative;
            line-height: 1.4em;
            padding: 20px;
            list-style: none;
            text-align: left;
        }

        .timeline h1,
        .timeline h2,
        .timeline h3 {
            font-size: 1.4em;
        }

        .timeline .event {
            border-bottom: 1px solid rgba(160, 160, 160, 0.2);
            padding-bottom: 15px;
            margin-bottom: 20px;
            position: relative;
        }

        .timeline .event:last-of-type {
            padding-bottom: 0;
            margin-bottom: 0;
            border: none;
        }

        .timeline .event:before,
        .timeline .event:after {
            position: absolute;
            display: block;
            top: 0;
        }

        .timeline .event:before {
            left: -177.5px;
            color: #212121;
            content: attr(data-date);
            text-align: right;
            /*  font-weight: 100;*/
            font-size: 16px;
            min-width: 120px;
        }

        .timeline .event:after {
            box-shadow: 0 0 0 8px #42A5F5;
            left: -30px;
            background: #212121;
            border-radius: 50%;
            height: 11px;
            width: 11px;
            content: "";
            top: 5px;
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



<div class="section">

    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    @include('layouts.success')
                    @include('layouts.error')
                    <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                    {!! Form::open(['class' => 'ajax-submit', 'id' => Str::camel($page->title) . 'Form']) !!}
                    {{ csrf_field() }}
                    {!! Form::hidden('customer_id', $customer->id ?? '', ['class' => 'customerId']) !!}
                    {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle']) !!}
                    {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}

                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('call_log_text', $customerLog->title ?? '', ['id' => 'call_log_text', 'autocomplete' => 'off']) !!}
                            <label for="call_log_text" class="label-placeholder active">Title <span
                                    class="red-text">*</span></label>
                        </div>
                        <div class="input-field col s6 m6">
                            {!! Form::input('date', 'call_log_date', $customerLog->call_log_time ?? '', [
                                'id' => 'call_log_date',
                                'required' => true,
                            ]) !!}
                            <label for="call_log_date" class="label-placeholder active">Date</label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::time('call_log_time', $customerLog->call_time ?? '', [
                                'id' => 'call_time',
                                'min' => '09:00',
                                'max' => '22:00',
                                'required' => true,
                            ]) !!}
                            <label for="call_time" class="label-placeholder active">Time</label>
                            <small>Office hours are 9am to 10pm</small>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('customerId', $customers->pluck('name', 'id')->toArray() ?? [], null, [
                                'id' => 'customer',
                                'class' => 'select2 browser-default',
                                'placeholder' => 'Please select Customer',
                            ]) !!}
                            <label for="customer" class="label-placeholder active">Customer</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::select(
                                'visiting_status',
                                [
                                    '0' => 'New Customer',
                                    '1' => 'Regular Customer',
                                    '2' => 'VIP Customer',
                                    '3' => 'Occasional Visitor',
                                    '4' => 'Former Customer',
                                    '5' => 'Week Days Customer',
                                ],
                                $customer->visiting_status ?? '',
                                ['id' => 'visiting_status', 'class' => 'select2 browser-default', 'placeholder' => 'Please select Status'],
                            ) !!}
                            <label for="visiting_status" class="label-placeholder active">Visiting Status</label>
                        </div>

                        <div class="input-field col m6 s12">
                            {!! Form::select(
                                'behavioral_status',
                                [
                                    '0' => 'Calm',
                                    '1' => 'Neutral',
                                    '2' => 'Dangerous',
                                ],
                                $customer->behavioral_status ?? '',
                                ['id' => 'behavioral_status', 'class' => 'select2 browser-default', 'placeholder' => 'Please select Status'],
                            ) !!}
                            <label for="behavioral_status" class="label-placeholder active">Behavioral Status</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m12 s12">
                            {!! Form::textarea('log_comment', $customerLog->customer_logs ?? '', [
                                'id' => 'call_notes',
                                'class' => 'materialize-textarea',
                                'placeholder' => 'Enter additional notes about behavior...',
                            ]) !!}
                            <label for="call_notes" class="label-placeholder active">Call Notes</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <button class="btn waves-effect waves-light" type="button" name="reset"
                                id="reset-btn">Reset
                                <i class="material-icons right">refresh</i>
                            </button>
                            <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                id="submit-btn">Submit
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>


    </div>

</div>
<div class="card">
    <div class="row">
        <div class="col s12 m9">
            <div class="p-5 select_customer">
                <label for="">Select a Customer</label>
                <select name="customer_id" class="customer_id">
                    <option value="">Choose One </option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <div>
                    <h6>Call Log History &nbsp; <strong id="customerName"></strong></h6>
                </div>
            </div>


            <section id="timeline" class="timeline-outer">
                <div class="container" id="content">
                    <div class="row">

                        <div class="col s12 m12 l12">
                            <ul class="timeline" id="callLogTimeline">
                                <!-- Call logs will be appended here -->
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script>
    var customerEmail = "{{ route('customer.uniqueEmail') }}";
</script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function() {
        var baseURL = "{{ url('/') }}";
      $('#callLogTimeline').css('display', 'none');


        $('.customer_id').select2({
            placeholder: "Choose One", // Placeholder text
            allowClear: true // Allow clearing the selection
        });
        $('.customer_id').on('change', function() {
           
            var customerId = $(this).val(); 
            $('#callLogTimeline').css('display', 'block')
            if (customerId) {
                $.ajax({
                    url: baseURL+'/list-calllog/' + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.data.length > 0) {
                            var editBaseUrl =
                            '{{ route('customers.editlogs', []) }}'; // Base route without parameters

                            $.each(data.data, function(index, callog) {
                                var actualEditUrl =
                                    `${editBaseUrl}?logId=${callog.id}`;
                                $('#callLogTimeline').append(`
                               <li class="event" data-date="${new Date(callog.created_at).toISOString().split('T')[0]}">

                                    <h3>${callog.title}</h3>
                                    <p>
                                        ${callog.customer_logs}
                                        <br>
                                        <small>${moment(callog.call_time, 'HH:mm:ss').format('h:mm A')}</small>
                                    </p>
                                    <p class="float-right m-3">
                                         <a href="${actualEditUrl}"> <i class="material-icons">mode_edit</i></a>
                                        <a class="delete-logs" data-log-id="${callog.id}" href="javascript:void(0);">
    <i class="material-icons">delete</i>
</a>

                                    </p>
                                    <br>
                                </li>
                            `);
                            });
                        } else {
                            // If no call logs found, display a message
                            $('#callLogTimeline').css('display', 'none');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error status:", status);
    console.error("Error message:", error);
    console.error("Response text:", xhr.responseText);
                    }
                });
            } else {
                // Handle the case where no customer is selected
                console.log("No customer selected");
            }
        });
    });
    $(document).ready(function() {

       
        $('.ajax-submit').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var customerId = $("#customerId").val();
            $.ajax({
                url: "{{ route('customers.storeCallLog') }}", // URL to send the request to
                type: 'POST', // POST method
                data: formData, // Form data
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                       
                            baseURL = baseURL + "/customers";
                       
                        var url = baseURL;
                        window.location.href = url;

                    } else {
                        showErrorToaster(response.message);
                        printErrorMsg(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Reset button functionality
        $('#reset-btn').on('click', function() {
            $('#call_log_text').val('');
            $('#call_time').val('');
            $('#customer').val('').trigger('change'); // Reset select2
            $('#visiting_status').val('').trigger('change'); // Reset select2
            $('#behavioral_status').val('').trigger('change'); // Reset select2
            $('#call_notes').val('');
        });

    });
    $(document).on('click', '.delete-logs', function(event) {
        const callogId = $(this).data('log-id');
        deleteCustomerLog(event, callogId);
    });

    function deleteCustomerLog(event, callogs) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Are you sure want to delete this Log!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('customers.deleteCallLog') }}",
                    type: 'DELETE',
                    data: {
                        callogs: callogs,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.flagError == false) {
                            showSuccessToaster(response.message);
                        } else {
                            showErrorToaster(response.message);
                        }
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    }
</script>
@endpush
