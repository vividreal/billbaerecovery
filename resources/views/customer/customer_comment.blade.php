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
        textarea {
            width: 100%;
            height: 6rem;
            background-color: transparent;
        }

        body {
            font-family: helvetica;
        }

        /*——————————————
                                       TimeLine CSS
                                       ———————————————*/
        /* Base */
        #content {
            margin-top: 50px;
            text-align: center;
        }

        section.timeline-outer {
            width: calc(100% - 300px);
            margin: 0 auto;
        }

        h1.header {
            font-size: 50px;
            line-height: 70px;
        }

        /* Timeline */
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
            padding-left: 25px;
            margin-bottom: 20px;
            position: relative;
            display: flex;
            gap: 15px;
        }

        .timeline .event .log_content {
            width: 100%;
        }

        .timeline .event .log_content small {
            margin-top: 15px;
            display: block;
        }

        .timeline .event .edit_delete_outer {
            flex-shrink: 0 !important;
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
            left: -35px;
            background: #212121;
            border-radius: 50%;
            height: 11px;
            width: 11px;
            content: "";
            top: 2px;
        }

        /**/
        /*——————————————
                                       Responsive Stuff
                                       ———————————————*/
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

        /*——————————————
                                       others
                                       ———————————————*/
        a.portfolio-link {
            margin: 0 auto;
            display: block;
            text-align: center;
        }
    </style>
    <style>
        .timeline-1 {
            position: relative;
        }

        .timeline-1 .timeline-event {
            position: relative;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .timeline-1 .timeline-event .timeline-content {
            position: relative;
            width: calc(48% - 50px);
        }

        .timeline-1 .timeline-event::before {
            display: block;
            content: "";
            width: 2px;
            height: calc(50% - 30px);
            position: absolute;
            background: #42A5F5;
            left: calc(50% - 1px);
            top: 0;
        }

        .timeline-1 .timeline-event::after {
            display: block;
            content: "";
            width: 2px;
            height: calc(50% - 30px);
            position: absolute;
            background: #42a5f5;
            left: calc(50% - 1px);
            top: calc(50% + 50px);
        }

        .timeline-1 .timeline-event:first-child::before {
            display: none;
        }

        .timeline-1 .timeline-event:last-child::after {
            display: none;
        }

        .timeline-1 .timeline-event:nth-child(even) .timeline-content {
            margin-left: calc(48% + 110px);
        }

        .timeline-1 .timeline-event:nth-child(odd) .timeline-content {
            margin-left: 0;
        }

        .timeline-1 .timeline-badge {
            display: block;
            position: absolute;
            width: 100px;
            height: 100px;
            line-height: 100px;
            background: #42A5F5;
            top: calc(50% - 50px);
            right: calc(50% - 50px);
            border-radius: 50%;
            text-align: center;
            cursor: default;
        }

        .timeline-1 .timeline-badge i {
            font-size: 25px;
            line-height: 40px;
        }

        .card.timeline-content {
            border-radius: 10px;
        }

        .card.timeline-content .card-content {
            padding: 20px 20px;
        }

        @media (max-width: 600px) {
            .timeline-1 .timeline-event .timeline-content {
                width: calc(100% - 70px);
            }

            .timeline-1 .timeline-event::before {
                left: 19px;
            }

            .timeline-1 .timeline-event::after {
                left: 19px;
            }

            .timeline-1 .timeline-event:nth-child(even) .timeline-content {
                margin-left: 70px;
            }

            .timeline-1 .timeline-event:nth-child(odd) .timeline-content {
                margin-left: 70px;
            }

            .timeline-1 .timeline-badge {
                left: 0;
                width: 40px;
                height: 40px;
                line-height: 40px;
                top: calc(50% - 20px);
                right: calc(50% - 20px);
            }

            .timeline-1 .timeline-event::before {
                height: calc(50% - 20px);
            }

            .timeline-1 .timeline-event::after {
                height: calc(50% - 20px);
                top: calc(50% + 20px);
            }
        }
    </style>
@endsection
@section('content')
@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->sublink }}">{{ $page->subtitle ?? '' }}</a></li>
        <li class="breadcrumb-item active">Comments</li>
    </ol>
@endsection
<div class="card-panel">
    <!-- users view card details start -->
    <div class="row d-flex align-items-center">
       <div class="col s6 ">
        <h5>{{ $customer->name ?? '' }}</h5> 
        <h6>CODE:{{ $customer->customer_code ?? '' }}</h6>
       </div>

       <div class="col s6 text-end"> <a href="{{ $page->sublink }}" class="btn-small indigo">Back to Profile </a> </div>
    </div>
    
</div>
<section class="card">
  
    <div class="container">
        <div class="row">
            <div class="col s12 ">
                <ul class="tabs">
                    <li class="tab col s3"><a class="active" href="#test1">Comment </a></li>
                    <li class="tab col s3"><a href="#test2">Call log</a></li>
                    <li class="tab col s3"><a href="#test3">Service History</a></li>
                </ul>
            </div>
            <div id="test1" class="col s12">
                <div class="section section-data-tables">
                    <div class="row">
                        <div class="col s12 m12 l12">
                            <div class="card card card-default scrollspy">
                                <div class="card-content">
                                    <div>
                                        <h5>Add Comment Here</h5>
                                    </div>
                                    <div>
                                        <form id="customer_comment">
                                            @csrf
                                            <div>
                                                <label for="">Title</label>
                                                <input type="text" name="title" id="title">
                                            </div>
                                            <div>
                                                <input type="hidden" name="customerId"
                                                    value="{{ $customer->id ?? '' }}">
                                                <label for="">Comment</label>
                                                <textarea name="comment" id="comment" cols="30" rows="60"></textarea>
                                            </div>
                                            <div>
                                                <input type="submit" name="customer_submit"
                                                    class="btn btn-light-green">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($customerComments->count() > 0)
                        <div class="card card card-default scrollspy">
                            <div class="card-content">
                                <div class="main-container">
                                    <section id="timeline" class="timeline-outer">
                                        <div class="container" id="content">
                                            <div class="row">
                                                <div class="col s12 m12 l12">
                                                    <ul class="timeline">
                                                        @foreach ($customerComments as $comment)
                                                            <li class="event"
                                                                data-date="{{ $comment->created_at->format('Y-m-d') }}">
                                                                <h3>{{ $comment->title }}</h3>
                                                                <p>
                                                                    {{ $comment->comment }}
                                                                </p>
                                                                <p class="float-right m-3"><a class=""
                                                                        href="{{ route('customers.editComment', ['commentId' => $comment->id]) }}"><i
                                                                            class="material-icons">mode_edit</i></a>
                                                                    <a class="delete-comments"
                                                                        data-comment-id="{{ $comment->id }}"
                                                                        href="javascript:void(0);"
                                                                        onclick="deleteComment(event, {{ $comment->id }})"><i
                                                                            class="material-icons">delete</i></a>
                                                                </p>
                                                                <br>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div id="test2" class="col s12">
                <div class="section section-data-tables">
                    <div class="row">
                        <div class="col s12 m12 l12">
                            <div class="card card card-default scrollspy">
                                <div class="card-content">
                                    <div>
                                        <h5>Add </h5>
                                    </div>
                                    <div>
                                        <form id="customer_calllog">
                                            @csrf
                                            <div class=" col s6 m6 mb-2">
                                                <label for="">Title</label>
                                                <input type="text" id="call_log_text" name="call_log_text" />

                                            </div>
                                            <div class="col s6 m6 mb-2" >
                                                <label for="">Date</label>
                                                <input type="date" id="call_log_date" name="call_log_date"
                                                  required />

                                            </div>
                                            <div class=" col s6 m6 mb-2">
                                                <label for="">Time</label>
                                                <input type="time" id="call_log_time" name="call_log_time"
                                                    min="09:00" max="22:00" required />

                                                <small>Office hours are 9am to 6pm</small>

                                            </div>
                                            <div class="col s6 m6 mb-2">
                                                <label for="">Visiting Status</label>
                                                <select name="visiting_status" id="visiting_status">
                                                    <option value="">Select Status</option>
                                                    <option value="0">New Customer</option>
                                                    <option value="1">Regular Customer</option>
                                                    <option value="2">VIP Customer</option>
                                                    <option value="3">Occasional Visitor</option>
                                                    <option value="4">Former Customer</option>
                                                    <option value="5">Week Days Customer</option>
                                                </select>
                                            </div>
                                            <div class="col s12 m12 mb-2" >
                                                <label for="">Behavioral Status</label>
                                                <select name="behavioral_status" id="behavioral_status">
                                                    <option value="">Select Behavioral Status</option>
                                                    <option value="0">Calm</option>
                                                    <option value="1">Neutral</option>
                                                    <option value="2">Dangerous</option>
                                                    {{-- <option value="3">Aggressive</option> --}}

                                                </select>
                                            </div>
                                            <div class="col s12 m12 mb-2" >
                                                <input type="hidden" name="customerId"
                                                    value="{{ $customer->id ?? '' }}">
                                                <label for="">Callog Comment</label>
                                                <textarea name="log_comment" id="log_comment" cols="30" rows="60"></textarea>
                                            </div>
                                            <div>
                                                <input type="submit" name="customer_log_submit"
                                                    class="btn btn-light-green">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($variants->calllogs->count() > 0)
                        <div class="card card card-default scrollspy">
                            <div class="card-content">
                                <div class="main-container">
                                    <div>
                                        <h6>Callog History &nbsp; <strong>{{ $customer->name }}</strong></h6>
                                    </div>
                                    <section id="timeline" class="timeline-outer">
                                        <div class="container" id="content">
                                            <div class="row">
                                                <div class="col s12 m12 l12">
                                                    <ul class="timeline">
                                                        @foreach ($variants->calllogs as $callogs)
                                                            <li class="event"
                                                                data-date="{{ $callogs->created_at->format('Y-m-d') }}">
                                                                {{--  <h3>{{ $callogs->title }}</h3> --}}
                                                                <p class="log_content">
                                                                    {{ $callogs->customer_logs }}
                                                                    <br>
                                                                    <small>{{ \Carbon\Carbon::createFromFormat('H:i:s', $callogs->call_time)->format('h:i A') }}
                                                                    </small>
                                                                </p>
                                                                <p class="float-right m-3 edit_delete_outer"><a
                                                                        class=""
                                                                        href="{{ route('customers.editlogs', ['logId' => $callogs->id]) }}"><i
                                                                            class="material-icons">mode_edit</i></a>
                                                                    <a class="delete-logs"
                                                                        data-log-id="{{ $callogs->id }}"
                                                                        href="javascript:void(0);"
                                                                        onclick="deleteCustomerLog(event, {{ $callogs->id }})"><i
                                                                            class="material-icons">delete</i></a>
                                                                </p>
                                                                <br>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div id="test3" class="col s12">

                <div class="card card card-default scrollspy">
                    <div class="card-content">
                        <div class="main-container">
                            <div>
                                <h6>Service History &nbsp; <strong>{{ $customer->name }}</strong></h6>
                            </div>



                            <div class="container">
                                <div class="timeline-1">
                                    @php
                                        $previousDate = null;
                                    @endphp

                                    @foreach ($variants->getHistory as $history)
                                        <div class="timeline-event">
                                            {{-- Show timeline badge only if the date is different from the previous one --}}
                                            @if ($previousDate !== $history->created_at->format('Y-m-d'))
                                                <div class="timeline-badge">
                                                    <span class="badge-content blue white-text">
                                                        {{ $history->created_at->format('d M Y') }}
                                                    </span>
                                                </div>
                                            @endif

                                            <div class="card timeline-content">
                                                <div class="card-content">
                                                    <p> {{ $history->comment }}

                                                    </p>
                                                    <p>
                                                        @if ($history->billing)
                                                            @if ($history->billing->schedule)                                                                                                                              
                                                                @php
                                                                    $service = $history->billing->schedule->description;
                                                                    $serviceName = explode('(', $service)[0];
                                                                @endphp
                                                                <span>{{ trim($serviceName) }},</span>
                                                                <br>
                                                            @endif
                                                        @endif
                                                        <small class="text-muted">
                                                            {{ $history->created_at->format('d M Y, h:i A') }}
                                                        </small>
                                                    </p>
                                                </div>
                                                <div class="card-reveal">
                                                    <span class="card-title grey-text text-darken-4">Card Title<i
                                                            class="material-icons right">close</i></span>
                                                    <p>Here is some more information about this product that is only
                                                        revealed once clicked on.</p>
                                                </div>
                                            </div>

                                            @php
                                                $previousDate = $history->created_at->format('Y-m-d');
                                            @endphp
                                        </div>
                                    @endforeach


                                </div>
                            </div>


                            {{-- 
                            <section id="timeline" class="timeline-outer">
                                <div class="container" id="content">
                                    <div class="row">
                                        <div class="col s12 m12 l12">

                                            <ul class="timeline">
                                                @foreach ($variants->getHistory as $history)
                                                    <li class="event"
                                                        data-date="{{ $history->created_at->format('Y-m-d') }}">
                                                        <h3>{{$history->title}}</h3> 
                                                        <p>
                                                            {{ $history->comment }}
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $history->created_at->format('d M Y, h:i A') }}
                                                            </small>
                                                        </p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </section> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Content goes here -->
    </div>
</section>
@endsection
{{-- vendor scripts --}}
@section('vendor-script')
@endsection
@push('page-scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('admin/js/custom/customer/customer.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function() {
        $('#customer_comment').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: '{{ route('customers.storeComment') }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                        location.reload()

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
        $('#customer_calllog').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: '{{ route('customers.storeCallLog') }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                        location.reload()

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
    });

    function deleteComment(event, commentId) {
        event.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to recover this comment!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('customers.deleteComment') }}",
                    type: 'DELETE',
                    data: {
                        commentId: commentId,
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

    function deleteCustomerLog(event, callogs) {
        event.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to recover this Log!',
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
