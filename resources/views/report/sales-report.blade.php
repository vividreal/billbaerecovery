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
                href="{{ url(ROUTE_PREFIX . '/reports/sales-report') }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">List</li>
    </ol>
@endsection

@section('page-action')
@endsection

<div class="section section-data-tables">
    <div class="card">
        <div class="actions action-btns display-flex align-items-center">
            <div class="row">
                <div style="display: block; float:right; padding: 10px;">
                    <div class="col s4" style="width:135px;">
                        <select id="reportDaySelect" name="reportDaySelect">
                            <option value="" disabled >Select One</option>
                            <option value="today" selected>Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                    </div>
                    <div class="col s4 " style="width:150px;">
                        <select id="reportYearSelect" name="reportYearSelect">
                            <option value="" disabled selected>Select a year</option>
                        </select>
                    </div>
                    <div class="col s4" style="width:224px;">
                        <input type="text" class="daterange" name="report_date_range" id="report_date_range"
                            value="" placeholder="Select Date Range">
                    </div>
                </div>
            </div>
        </div>
        <div class="pt-0">

            <div class="row">
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-150 white-text animate fadeLeft">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s4 m4">
                                    <i class="material-icons background-round mt-5">add_shopping_cart</i>
                                    <p>Total Sale Amount</p>
                                </div>
                                <div class="col s8 m8 right-align">
                                    <h5 class="mb-0 white-text">₹ <span id="total_cash"></span></h5>
                                    <!-- <p class="no-margin">New</p> -->
                                    <!-- <p></p> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-red-pink gradient-shadow min-height-150 white-text animate fadeLeft">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s4 m4">
                                    <i class="material-icons background-round mt-5">perm_identity</i>
                                    <p>Customer</p>
                                </div>
                                <div class="col s8 m8 right-align">
                                    <h5 class="mb-0 white-text" id="no_of_customers"></h5>
                                    <!-- <p class="no-margin">New</p>  -->
                                    <!-- <p id="no_of_customers"></p> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-amber-amber gradient-shadow min-height-150 white-text animate fadeRight">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s4 m4">
                                    <i class="material-icons background-round mt-5">timeline</i>
                                    <p>Invoice</p>
                                </div>
                                <div class="col s8 m8 right-align">
                                    <h5 class="mb-0 white-text" id="no_of_invoice"></h5>
                                    <!-- <p class="no-margin">Growth</p> -->
                                    <!-- <p id="no_of_invoice"></p> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div
                        class="card gradient-45deg-green-teal gradient-shadow min-height-150 white-text animate fadeRight">
                        <div class="padding-4">
                            <div class="row">
                                <div class="col s6 m6">
                                    <i class="material-icons background-round mt-5">attach_money</i>
                                    <p>Payment Status</p>
                                </div>
                                <div class="col s6 m6 right-align">
                                    <h5 class="mb-0 white-text">Paid: <span id="completed_status"></span></h5>
                                    <p class="no-margin"></p>
                                    <p>Pending: <span id="pending_status"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div id="sales-chart-data">
        <div class="row">
            <div class="col s12 m12 l12">
                <div id="revenue-chart" class="card animate fadeUp">
                    <div class="card-content">

                        <h4 class="header mt-0">
                            SALES DATA
                            <!-- <span class="purple-text small text-darken-1 ml-1">
                      <i class="material-icons">keyboard_arrow_up</i> 25.58 %
                    </span> -->
                        </h4>
                        <div class="row">
                            <div class="col s12">
                                <div class="yearly-revenue-chart">
                                    <canvas id="salesReportchart" height="250"></canvas>
                                </div>
                            </div>
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
                    <div class="actions action-btns display-flex align-items-center">
                        <div class="row">
                            <div style="display: block; float:right; padding: 10px;">
                                <div class="col s4" style="width:135px;">
                                    <select id="daySelect" name="daySelect">
                                        <option value="" disabled >Select One</option>
                                        <option value="today" selected>Today</option>
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
                                    <input type="text" class="daterange" name="dashboard_date_range"
                                        id="dashboard_date_range" value="" placeholder="Select Date Range">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <table id="data-table-reports" class="display data-tables"
                                data-url="{{ $page->link . '/lists' }}" data-form="page" data-length="20">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Date</th>
                                        <th>Bill ID</th>
                                        <th>Customer Name</th>
                                        <th>In - Out Times</th>
                                        <th>Amount</th>
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
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row">
    <div class="col s12 m12 l12">
      <div id="button-trigger" class="card card card-default scrollspy">
        <div class="card-content">
          <h4 class="card-title">Sales Report Table</h4>
          <div class="row">
            <div class="col s12">
              <table id="daily_reports" class="display data-tables"  data-form="page" data-length="20">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Date</th>
                    <th>Bill ID</th>
                    <th>Customer Name</th>
                    <th>In - Out Times</th>
                    <th>Amount</th>
                    <th>Payment Methods</th>
                    <th>Payment Status</th>
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
  </div> --}}
</div>

@endsection
{{-- vendor scripts --}}
@section('vendor-script')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">
<script src="{{ asset('admin/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="{{ asset('admin/vendors/chartjs/chart.min.js') }}"></script>
@endsection

@push('page-scripts')
<script src="{{ asset('admin/js/scripts/data-tables.js') }}"></script>
<script src="{{ asset('admin/js/scripts/sales-chart.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#daily_reports').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            dom: "Blfrtip",
            buttons: ['excel', 'pdf'],
            ajax: {
                url: "{{ route('dailyReport') }}",
                type: 'GET', // or 'POST' depending on your API
                dataSrc: 'data' // adjust this according to your API response structure
            },
            columns: [{
                data: 'DT_RowIndex',
                orderable: false,
                searchable: false
            }, {
                data: 'billed_date',
                name: 'billed_date'
            }, {
                data: 'billing_code',
                name: 'billing_code'
            }, {
                data: 'customer_id',
                name: 'customer_id'
            }, {
                data: 'in_out_time',
                name: 'in_out_time'
            }, {
                data: 'amount',
                name: 'amount'
            }, {
                data: 'payment_method',
                name: 'payment_method'
            }, {
                data: 'payment_status',
                name: 'payment_status'
            }]

        });
    });
</script>
<script>
    var table;
    var load_count = 0;
    var chart_label = chart_label
    var chart_data = chart_data;
    var mode = 'index'
    var intersect = true
    var link = '{{ $page->link }}';
    // load_count++;

    $(function() {
        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $("#start_range").val(start.format('YYYY-MM-DD MM:MM:MM'));
            $("#end_range").val(end.format('YYYY-MM-DD MM:MM:MM'));
            $("#range_sort").val(1);
            getData();
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
            bSearchable: true,
            pagination: true,
            pageLength: 10,
            // responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            scrollX: true,
            ajax: {
                url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/get-sales-table-data') }}",
                data: function(d) {
                    d.daySelect = $('#daySelect').val();
                    d.yearSelect = $('#yearSelect').val();
                    d.fromDate = $('#dashboard_date_range').data('fromDate');
                    d.toDate = $('#dashboard_date_range').data('toDate');

                },
            },
            dom: "Blfrtip",
            buttons: ['excel', 'pdf'],
            select: true,
            columns: [{
                data: 'DT_RowIndex',
                orderable: false,
                searchable: false
            }, {
                data: 'billed_date',
                name: 'name',
                orderable: false,
                searchable: false
            }, {
                data: 'billing_code',
                name: 'name',
                orderable: false,
                searchable: false
            }, {
                data: 'customer_id',
                name: 'name',
                orderable: false,
                searchable: false
            }, {
                data: 'in_out_time',
                name: 'name',
                orderable: false,
                searchable: false
            }, {
                data: 'amount',
                name: 'name',
                orderable: false,
                searchable: false
            }, {
                data: 'payment_method',
                name: 'name',
                orderable: false,
                searchable: false
            }, {
                data: 'payment_status',
                name: 'name',
                orderable: false,
                searchable: false
            }],
            footerCallback: function(row, data, start, end, display) {

                var api = this.api(),
                    data;
                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i ===
                        'number' ? i : 0;
                };

                // Total over all pages
                total = api
                    .column(5)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);


                // Update footer
                $(api.column(5).footer()).html('<strong> ₹ ' + total + '</strong>');



            }
        });
        $("#daySelect").change(function() {
            table.ajax.reload();

        });
        $("#yearSelect").change(function() {
            table.ajax.reload();

        });
        $('#dashboard_date_range').on('apply.daterangepicker', function(ev, picker) {
            var fromDate = picker.startDate.format('YYYY-MM-DD');
            var toDate = picker.endDate.format('YYYY-MM-DD');

            // Store the selected dates
            $('#dashboard_date_range').data('fromDate', fromDate);
            $('#dashboard_date_range').data('toDate', toDate);

            // Reload the DataTable
            table.ajax.reload();
        });
    });

    function search(value) {
        value.name = $('input[type=search]').val();
        value.start_range = $("#start_range").val();
        value.end_range = $("#end_range").val();
    }

    // Chart start
    var salesReportchart = document.getElementById("salesReportchart").getContext("2d");

    var salesReportchartOption = {
        legend: {
            display: false,
            position: "bottom"
        },
        scales: {
            xAxes: [{
                display: true,
                gridLines: {
                    display: false,
                },
            }],
            yAxes: [{
                display: true,
                ticks: {
                    padding: 10,
                    // stepSize: 20,
                    // max: 100,
                    // min: 0,
                    fontColor: "#9e9e9e"
                },
                gridLines: {
                    display: true,
                    drawBorder: false,
                    lineWidth: 1,
                    zeroLineColor: "#e5e5e5"
                }
            }]
        },
        title: {
            display: false,
            fontColor: "#FFF",
            fullWidth: false,
            fontSize: 40,
            text: "82%"
        },
        responsive: true,
        maintainAspectRatio: true,
        datasetStrokeWidth: 3,
        pointDotStrokeWidth: 4,
        tooltipFillColor: "rgba(0,0,0,0.6)",
        hover: {
            mode: "label"
        },
    };

    var salesReportchart = new Chart(salesReportchart, {
        type: 'LineAlt',
        data: [],
        data: {
            labels: [],
            datasets: [{
                data: [],
                label: 'Sales :',
                pointRadius: 3,
                borderColor: "#9C2E9D",
                borderWidth: 2.5,
                pointBorderColor: "#9C2E9D",
                pointHighlightFill: "#9C2E9D",
                pointHoverBackgroundColor: "#9C2E9D",
                pointHoverBorderWidth: 2.5,
                fill: false,
            }, ]
        },
        options: salesReportchartOption
    })
    // Chart ends

    var getData = function() {
        var forms = $("#reportForm");
        $.ajax({
            url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/get-sales-chart-data') }}",
            type: 'post',
            processData: false,
            data: forms.serialize(),
            dataType: "html",
        }).done(function(a) {
            var data = JSON.parse(a);
            if (data.flagError == false) {

                $("#total_cash").text(data.total_cash);
                $("#no_of_invoice").text(data.invoice);
                $("#no_of_customers").text(data.customer);
                $("#completed_status").text(data.completed);
                $("#pending_status").text(data.pending);

                salesReportchart.data.labels = data.chart_label;
                salesReportchart.data.datasets[0].data = data.chart_data;

                salesReportchart.update();
                table.ajax.reload();

            }
        });

    };



    $(".exportReportBtn").click(function() {
        $("#export_format").val($(this).data("format"));
        $("#export_start_range").val($("#start_range").val());
        $("#export_end_range").val($("#end_range").val());
        $("#reportExportForm").submit();
    });
    var yearSelect = document.getElementById('yearSelect');
    var currentYear = new Date().getFullYear();
    for (var i = currentYear; i >= currentYear - 10; i--) {
        var option = document.createElement('option');
        option.value = i;
        option.text = i;
        yearSelect.appendChild(option);
    }

    var reportYearSelect = document.getElementById('reportYearSelect');
    var currentYear = new Date().getFullYear();
    for (var i = currentYear; i >= currentYear - 10; i--) {
        var option = document.createElement('option');
        option.value = i;
        option.text = i;
        reportYearSelect.appendChild(option);
    }
    $(document).ready(function() {
        // Initialize date range picker
        $('.daterange').daterangepicker({
            autoUpdateInput: false, // Prevent the input field from being automatically updated
            locale: {
                cancelLabel: 'Clear' // Set the label for clearing the selection
            }
        });

        // Add an event listener to update the input field when a date range is selected
        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            var selectedDateRange = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate
                .format('YYYY-MM-DD');
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                'YYYY-MM-DD'));
        });

        // Add an event listener to clear the input field
        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });


    $('#report_date_range').on('apply.daterangepicker', function(ev, picker) {
        var fromDate = picker.startDate.format('YYYY-MM-DD');
        var toDate = picker.endDate.format('YYYY-MM-DD');

        $.ajax({
            url: '{{ route('reportFilter') }}',
            method: 'GET',
            data: {
                toDate: toDate,
                fromDate: fromDate
            },
            success: function(response) {
                if (response.flagError == false) {
                    $("#total_cash").text(response.total_cash);
                    $("#no_of_invoice").text(response.invoice);
                    $("#no_of_customers").text(response.customer);
                    $("#completed_status").text(response.completed);
                    $("#pending_status").text(response.pending);

                } else {

                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
    document.getElementById('reportDaySelect').addEventListener('change', function() {
        var selectedDay = this.value;
        $.ajax({
            url: '{{ route('reportFilter') }}',
            method: 'GET',
            data: {
                day: selectedDay
            },
            success: function(response) {
                console.log(response);
                if (response.flagError == false) {
                    $("#total_cash").text(response.total_cash);
                    $("#no_of_invoice").text(response.invoice);
                    $("#no_of_customers").text(response.customer);
                    $("#completed_status").text(response.completed);
                    $("#pending_status").text(response.pending);

                } else {

                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
    document.getElementById('reportYearSelect').addEventListener('change', function() {
        var selectedYear = this.value;
        $.ajax({
            url: '{{ route('reportFilter') }}',
            method: 'GET',
            data: {
                year: selectedYear
            },
            success: function(response) {
                console.log(response.invoice);
                if (response.flagError == false) {
                    $("#total_cash").text(response.total_cash);
                    $("#no_of_invoice").text(response.invoice);
                    $("#no_of_customers").text(response.customer);
                    $("#completed_status").text(response.completed);
                    $("#pending_status").text(response.pending);
                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
</script>
@endpush
