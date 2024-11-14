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
            width: 80%;
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
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Service History</li>
    </ol>
@endsection



<div class="section section-data-tables">
    
{{-- @if($customerComments->count() > 0) --}}
    <div class="card card card-default scrollspy">
        <div class="card-content">
            <div class="col s12 m5">
                <h6>Service History &nbsp; <strong>{{$variants->customer->name}}</strong></h6>

            </div>
            <div class="main-container">
                <section id="timeline" class="timeline-outer">
                    <div class="container" id="content">
                        <div class="row">
                            <div class="col s12 m12 l12">
                                <ul class="timeline">
                                    @foreach ($variants->getHistory as $history)
                                    <li class="event" data-date="{{ $history->created_at->format('Y-m-d') }}">
                                        {{-- <h3>{{$history->title}}</h3> --}}
                                        <p>
                                            {{$history->comment}}
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
                </section>
            </div>
        </div>
    </div>
    {{-- @endif --}}
</div>

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


</script>
@endpush
