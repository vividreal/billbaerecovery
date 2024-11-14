@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/fullcalendar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/scheduler.min.css') }}">
    <style>
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
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="">Home</a></li>
        <li class="breadcrumb-item"><a href="#">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Bill Lists</li>
    </ol>
@endsection


<div class="section">
    <div id="card-stats" class="pt-0">

    </div>
    <!--Basic Form-->
    <div class="row">
        <div id="button-trigger" class="card card card-default scrollspy">
            <div class="card-content">
                <h4 class="card-title">Canceled/Refund Lists</h4>
                <div class="row">
                    <div class="col s12 data-table-container">
                        <div class="card-content">
                            <div class="row">
                                <div class="col s12">

                                </div>
                            </div>
                        </div>
                        <table id="data-table-cancelBillings" class="display" data-length="10">
                            <thead>
                                <tr>
                                    <th width="5px" data-orderable="false" data-column="DT_RowIndex">No </th>
                                    <th width="100px" data-orderable="false" data-column="billing_code">Invoice ID</th>
                                    <th width="100px" data-orderable="false" data-column="billed_date">Billed Date</th>
                                    <th width="100px" data-orderable="false" data-column="customer_id">Customer Name
                                    </th>
                                    <th width="200px" data-orderable="false" data-column="payment_status">Payment Status</th>
                                    <th width="290px" data-orderable="false" data-column="refund_type">Refund Type</th>
                                    <th width="100px" data-orderable="false" data-column="amount">Bill Amount</th>
                                    <th width="100px" data-orderable="false" data-column="actual_amount">Service Amount
                                    </th>
                                    <th width="100px" data-orderable="false" data-column="refund_amount">Refund Amount
                                    </th>
                                    <th width="150px" data-orderable="false" data-column="cancellation">Cancellation Fee
                                    </th>
                                    <th width="200px" data-orderable="false" data-column="updated_date">Paid on</th>
                                    {{-- <th width="50px" data-orderable="false" data-column="action">Action</th> --}}
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@include('billing.manage')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<!-- typeahead -->
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
    $(document).ready(function() {
       $table= $('#data-table-cancelBillings').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false, // Disable the 'Show entries' dropdown
            "searching": false, 
            "ajax": {
                "url": "{{ route('billings.refundBill') }}",
                "data": function(d) {

                }
            },
            "columns": [{
                    "data": "DT_RowIndex",
                    "width": "10px",
                    "orderable": false
                },
                {
                    "data": "billing_code",
                    "width": "180px",
                    "orderable": false
                },
                {
                    "data": "billed_date",
                    "width": "180px",
                    "orderable": false
                },
                {
                    "data": "customer_id",
                    "width": "280px",
                    "orderable": false
                },
                {
                    "data": "payment_status",
                    "width": "70px",
                    "orderable": false
                },
                {
                    "data": "refund_type",
                    "width": "150px",
                    "orderable": false
                },
                {
                    "data": "amount",
                    "width": "150px",
                    "orderable": false
                },
               
                {
                    "data": "actual_amount",
                    "width": "150px",
                    "orderable": false
                },
              
                {
                    "data": "refund_amount",
                    "width": "150px",
                    "orderable": false
                },
                {
                    "data": "cancellation",
                    "width": "200px",
                    "orderable": false
                },
                {
                    "data": "updated_date",
                    "width": "200px",
                    "orderable": false
                }
                // {
                //     "data": "action",
                //     "width": "100px",
                //     "orderable": false
                // }
            ]
        });
    });

    function billrefund(b) {
        $("#bill_id").val(b);
        $("#manage-refund-modal").modal("open");
    }

    $(document).ready(function() {
        $('#manageRefundForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('billings.refundBillPayment') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                        setTimeout(function() {
                            // table.ajax.reload();
                            table.DataTable().draw();
                        }, 2000);
                    } else {
                      
                        showErrorToaster(response.message);
                        printErrorMsg(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr, status, error);
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush
