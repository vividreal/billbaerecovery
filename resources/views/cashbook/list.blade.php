@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/data-tables/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/data-tables/css/select.dataTables.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/data-tables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/dashboard.css') }}">
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a
                href="{{ url(ROUTE_PREFIX . '/cashbook') }}">{{ Str::plural($page->title) ?? '' }}</a>
        </li>
        <li class="breadcrumb-item active">List</li>
    </ol>
@endsection

@section('page-action')
    <!-- <a href="javascript:" class="btn waves-effect waves-light orange darken-4 breadcrumbs-btn" onclick="importBrowseModal()" >Upload<i class="material-icons right">attach_file</i></a>
              <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Add<i class="material-icons right">person_add</i></a> -->

    <!-- <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">Add<i class="material-icons right">add</i></a> -->
    @can('cashbook-add-cash')
        <a href="javascript:" data-modalname="add-cash-modal" data-form="addCashForm"
            class="btn waves-effect waves-light cyan breadcrumbs-btn loadModal"><i class="material-icons right">add</i> Add Cash
        </a>
    @endcan
    @can('cashbook-withdraw-cash')
        <a href="javascript:" data-modalname="withdraw-cash-modal" data-form="withdrawCashForm"
            class="btn waves-effect waves-light cyan breadcrumbs-btn orange loadModal"><i
                class="material-icons right">account_balance_wallet</i> Withdraw Cash</a>
    @endcan
@endsection

<div class="section section-data-tables">
    <div id="card-stats" class="pt-0">
        <div class="row">
            <div class="col s12 m6 l6 xl4">
                <div
                    class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="padding-4">
                        <div class="row">
                            <div class="col s4 m4">
                                <i class="material-icons background-round mt-4">add_shopping_cart</i>
                                <p>Business Cash</p>
                            </div>
                            <div class="col s8 m8 right-align">
                                <h5 class="mb-0 white-text">₹ <span
                                        id="business_cash">{{ number_format($variants->business_cash, 2) ?? '' }}</span>
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
                                <i class="material-icons background-round mt-5">perm_identity</i>
                                <p>Petty Cash</p>
                            </div>
                            <div class="col s8 m8 right-align">
                                <h5 class="mb-0 white-text">₹ <span
                                        id="petty_cash">{{ number_format($variants->petty_cash, 2) ?? '' }}</span></h5>
                                <!-- <p class="no-margin">New</p>  -->
                                <!-- <p id="no_of_customers"></p> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m6 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <div class="row">
                        <div class="col s12">
                            <form id="reportForm" name="reportForm" role="form" method="" action=""
                                class="ajax-submit">
                                {{ csrf_field() }}
                                {!! Form::hidden('start_range', '', ['id' => 'start_range']) !!}
                                {!! Form::hidden('end_range', '', ['id' => 'end_range']) !!}
                                {!! Form::hidden('range_sort', '0', ['id' => 'range_sort']) !!}
                                <!-- <div class="row">
                        <div class="col-md-5 ml-auto mr-3">
                          <div class="form-group ">
                                {!! Form::label('day_range', 'Report Dates', ['class' => 'col-form-label text-alert']) !!}
                                <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                  <i class="fa fa-calendar"></i>&nbsp;
                                  <span></span> <i class="fa fa-caret-down"></i>
                              </div>
                          </div>
                        </div>
                      </div> -->
                                <div class="row">
                                    <div class="input-field col m3 s12">
                                        {!! Form::select('cash_book', [1 => 'Business Cash', 2 => 'Petty Cash'], '', [
                                            'id' => 'cash_book',
                                            'class' => 'select2 browser-default',
                                            'multiple' => 'multiple',
                                        ]) !!}
                                        <!-- <label for="cash_book" class="label-placeholder active">Cash book </label> -->
                                    </div>
                                    <div class="input-field col m3 s12">
                                        {!! Form::select('cash_from', [0 => 'Cash Deposit', 1 => 'From Sales'], '', [
                                            'id' => 'cash_from',
                                            'class' => 'select2 browser-default',
                                            'multiple' => 'multiple',
                                        ]) !!}
                                        <!-- <label for="cash_from" class="label-placeholder active">'Cash From</label> -->
                                    </div>
                                    <div class="input-field col m3 s12">
                                        {!! Form::select('transaction_type', [1 => 'Credit', 2 => 'Debit'], '', [
                                            'id' => 'transaction_type',
                                            'class' => 'select2 browser-default',
                                            'multiple' => 'multiple',
                                        ]) !!}
                                        <!-- <label for="transaction_type" class="label-placeholder active">Transaction Type </label> -->
                                    </div>
                                    <div class="input-field col m3 s12">
                                        <div id="reportrange"
                                            style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                        <!-- <label for="email" class="label-placeholder active">Transaction Date Range</label> -->
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <button class="btn waves-effect waves-light" type="reset" name="reset"
                                            id="resetSelection">Reset All <i
                                                class="material-icons right">refresh</i></button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables example -->
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ Str::plural($page->title) ?? '' }} Table</h4>
                    <div class="row">
                        <div class="col s12">
                            <table id="data-table-reports" class="display data-tables"
                                data-url="{{ $page->link . '/lists' }}" data-form="page" data-length="20">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Date</th>
                                        <th>Cash Book name</th>
                                        <th>Amount</th>
                                        <th>Transaction Type</th>
                                        <th>Transaction Done by</th>
                                        <th>Message</th>
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

@include('cashbook.add-cash')
@include('cashbook.withdraw-cash')
@include('cashbook.full-message')

@endsection

{{-- vendor scripts --}}
@section('vendor-script')
<script src="{{ asset('admin/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection

@push('page-scripts')
<script>
    var table;
    var link = '{{ $page->link }}';

    $('.select2').select2({
        placeholder: "Please select ",
        allowClear: false
    }).on('select2:select select2:unselect', function(e) {
        table.ajax.reload();
    });

    $('#add_cash_book').select2({
        placeholder: "Add cash to",
        allowClear: true
    });

    $(".loadModal").on("click", function() {
        var modalname = $(this).data("modalname");
        var form = $(this).data("form");
        addvalidator.resetForm();
        validator.resetForm();
        $(".display-none").hide();
        $('input').removeClass('error');
        $('select').removeClass('error');
        $('#' + form).trigger("reset");
        $('#add_cash_book').select2({
            placeholder: "Add cash to",
            allowClear: true
        });
        $('#withdraw_cash_book').select2({
            placeholder: "Withdraw cash from",
            allowClear: true
        });
        $("#cashOptionDiv").hide()
        $("#" + modalname).modal("open");
    });

    $(function() {
        $("#add_cash_book").change(function() {
            (this.value != '') ? $("#cashOptionDiv").show(): $("#cashOptionDiv").hide();
            var other_option = (this.value == 1) ? 2 : 1;
            $("#move_from").text($("#add_cash_book option[value='" + other_option + "']").text());
            $.ajax({
                url: "{{ route('getCashDetails') }}",
                type: "GET",
                success: function(response) {
                    if (response.flagError == false) {
                        $("#business_lable").remove();
                        var businessCashLabel = $("<label id='business_lable'>").text(
                            "Business Cash: " + response.business_cash);
                        $("#cashOptionDiv").append(businessCashLabel);
                    } else {
                        showErrorToaster(response.message);
                        printErrorMsg(response.error);
                    }
                }
            });

        });
    });

    if ($("#addCashForm").length > 0) {
        var addvalidator = $("#addCashForm").validate({
            rules: {
                cash_book: {
                    required: true,
                },
                amount: {
                    required: true,
                }
            },
            messages: {
                cash_book: {
                    required: "Please select cash book",
                },
                amount: {
                    required: "Please enter amount",
                }
            },
            submitHandler: function(form) {
                var forms = $("#addCashForm");
                $('#submit').html('Please Wait...');
                $("#submit").attr("disabled", true);
                $.ajax({
                    url: "{{ url(ROUTE_PREFIX . '/' . $page->route) }}",
                    type: "POST",
                    data: forms.serialize(),
                    success: function(response) {
                        $('#submit').html('Submit');
                        $("#submit").attr("disabled", false);
                        if (response.flagError == false) {
                            showSuccessToaster(response.message);
                            $("#add-cash-modal").modal("close");
                            $("#business_cash").text(response.business_cash);
                            $("#petty_cash").text(response.petty_cash);
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 2000);
                        } else {
                            showErrorToaster(response.message);
                            printErrorMsg(response.error);
                        }
                    }
                });
            }
        })
    }

    if ($("#withdrawCashForm").length > 0) {
        var validator = $("#withdrawCashForm").validate({
            rules: {
                cash_book: {
                    required: true,
                },
                amount: {
                    required: true,
                }
            },
            messages: {
                cash_book: {
                    required: "Please select cash book",
                },
                amount: {
                    required: "Please enter amount",

                }
            },
            submitHandler: function(form) {
                var forms = $("#withdrawCashForm");
                $('#withdraw-submit-btn').html('Please Wait...');
                $("#withdraw-submit-btn").attr("disabled", true);
                $.ajax({
                    url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/withdraw') }}",
                    type: "POST",
                    data: forms.serialize(),
                    success: function(response) {
                        $('#withdraw-submit-btn').html('Submit');
                        $("#withdraw-submit-btn").attr("disabled", false);
                        if (response.flagError == false) {
                            showSuccessToaster(response.message);
                            $("#withdraw-cash-modal").modal("close");
                            $("#business_cash").text(response.business_cash);
                            $("#petty_cash").text(response.petty_cash);
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 2000);
                        } else {
                            showErrorToaster(response.message);
                            printErrorMsg(response.error);
                        }
                    }
                });
            }
        })
    }

    $(function() {
        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $("#start_range").val(start.format('YYYY-MM-DD MM:MM:MM'));
            $("#end_range").val(end.format('YYYY-MM-DD MM:MM:MM'));
            $("#range_sort").val(1);
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                    'month').endOf('month')]
            }
        }, cb);
        cb(start, end);
    });

    $(function() {
        table = $('#data-table-reports').DataTable({
            pagination: true,
            pageLength: 10,
            responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/lists') }}",
                data: search
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'name'
                },
                {
                    data: 'cash_book',
                    name: 'name'
                },
                {
                    data: 'amount',
                    name: 'name'
                },
                {
                    data: 'transaction_type',
                    name: 'name'
                },
                {
                    data: 'transaction_by',
                    name: 'name'
                },
                {
                    data: 'message',
                    name: 'name'
                },
            ]
        });
    });

    function search(value) {
        value.name = $('input[type=search]').val();
        value.start_range = $("#start_range").val();
        value.end_range = $("#end_range").val();
        value.transaction_type = $("#transaction_type").val();
        value.cash_from = $("#cash_from").val();
        value.cash_book = $("#cash_book").val();
    }

    $("#resetSelection").on("click", function() {
        $(".select2").val('').trigger('change');
        table.ajax.reload();
    });
    showMessage = function(message) {
        $("#fullMessage").text(message)
        $("#full-message-modal").modal("open");
    }
    function showFullName(message){
      $("#fullMessage").text(message)
        $("#full-message-modal").modal("open");
    }
</script>
@endpush
