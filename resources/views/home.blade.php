@extends('layouts.app')
@section('seo_title', 'Dashboard' ?? '')
@section('page-css')
<style>
.no_data {
    text-align: start;
    height: 67%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 100%;
    position: relative;
    left: 0px;
    float: none;
    width: 100%;
}
.dataTables_length{
    display: none;
}
.payment_history_table {
    width:700px !important;
}
.dataTables_wrapper{
    margin: 30px;
    padding: 10px;
}
.dashboard {
    font-family: system-ui;
    font-weight: 700;
    line-height: 1.1;
    color: white;
}
   h5.dashboard{ font-size:30px; } 

ul.navbar-list.billbae-list li:last-child {
     display: flex;
    align-items: center;
    height: 60px;}

    table, th, td {
    border: none;
    font-size: 12px;
}
</style>
@section('content')
    <div class="section">
        <!-- Current balance & total transactions cards-->
      <div class="card">
        <div class="row">
            <div  style="display: block; float:right; padding: 10px;">
                <div class="col s4" style="width:235px;">
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
        <div class="row dashboard-card-row " style="margin: 3%">
            <div class="col s6 m6 l6 xl3 animate fadeRight">
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Paid Amount </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard"><span id="totalSaleAmountPaid">{{ number_format($totalSaleAmountPaid,2)}}</span></h5>
                    </div>
                     <div class="">
                        <p class=" dashboard right mr-5" >Total Sale Amount &nbsp; <span id="totalSaleAmount">{{  number_format($totalSaleAmount,2) }}</span></p>
                    </div>
                </div>
            </div>
            <div class="col s6 m6 l6 xl3 ">
                <!-- Current Balance -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Service Amount </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard" id="serviceAmount">{{ number_format($serviceAmount,2)}}</h5>
                    </div>
                    <div class="">
                        <p class=" dashboard right mr-5" >Paid &nbsp;<span id="serviceAmountPaid">{{ number_format($serviceAmount_paid,2)}}</span></p>
                    </div>
                </div>
            </div>
         
            <div class="col s6 m6 l6 xl3">
                <!-- Current Balance -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Package Amount </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard" id="packageAmount">{{ number_format($packageAmount,2) }}</h5>
                    </div>
                     <div class="">
                        <p class=" dashboard right mr-5" >Paid &nbsp;<span id="packageAmountPaid">{{ number_format($packageAmount_paid,2)}}</span></p>
                    </div>
                </div>
            </div>
            <div class="col s6 m6 l6 xl3">
                <!-- Current Balance -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Discount Amount </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard" > <span id="totalDiscountAmount">{{ number_format($totalDiscountAmount,2) }}</span></h5>
                    </div>
                </div>
            </div>

            <div class="col s6 m6 l6 xl3 animate fadeRight">
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Dues </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard" id="total_dues">{{ number_format($total_dues, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col s6 m6 l6 xl3 animate fadeRight">
                <!-- Total Transaction -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Instore Credit Amount </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard" id="total_instore">{{ number_format($total_instore, 2) }}</h5>
               
                    </div>
                    <div class="col s12 m12">
                    <div class="d-flex align-items-center justify-content-between">
                        <p class=" dashboard right mr-0" >Balance &nbsp;<span id="total_instore_balance">{{ number_format($total_instore_balance,2)}}</span></p>
                        <p class=" dashboard right mr-0" >Used &nbsp;<span id="total_instore_used">{{ number_format($total_instore_used,2)}}</span></p>
                    </div>
</div>
                </div>
            </div>
            <div class="col s6 m6 l6 xl3 animate fadeRight">
                <!-- Total Transaction -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard"> Membership Credit Total </h6>      
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>                  
                        <h5 class="center-align dashboard" id="total_membership_instore">{{ number_format($total_membership_instore,2) }}</h5>
                    </div>
                    <div class="col s12 m12">
                    <div class="d-flex align-items-center justify-content-between">
                        <p class=" dashboard right mr-0" >Balance &nbsp;<span id="total_membership_instore_balance">{{ number_format($total_membership_instore_balance,2)}}</span></p>
                        <p class=" dashboard right mr-0" >Used &nbsp;<span id="total_membership_instore_used">{{ number_format($total_membership_instore_used,2)}}</span></p>
                    </div>
                    </div>

                </div>
            </div>
          
            <div class="col s6 m6 l6 xl3">
                <!-- Current Balance -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="card-content">
                        <h6 class="mb-0 mt-0 center-align dashboard">Total Customers </h6>
                        <div class="current-balance-container">
                            <div id="current-balance-donut-chart" class="current-balance-shadow"></div>
                        </div>
                        <h5 class="center-align dashboard" id="customerCount">{{ $customer }}</h5>
                    </div>
                </div>
            </div>

            <div class="col s6 m6 l6 xl3 animate fadeRight">
                <!-- Total Transaction -->
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                   <div class="card-content">
                        <!--<h6 class="mb-0 mt-0 center-align dashboard">Total Bill Count : <span id="total_bill_count">{{ $total_bill_count }}</span></h6>-->
                       <!--<div class="mb-2 mt-2 center-align dashboard">-->
                            <h6 class="mb-0 mt-0 center-align dashboard"> Total Schedules</h6>                        
                            <h5 class="center-align dashboard" ><span id="scheduleCount">{{ $schedulesCount }}</span></h5>
                       <!--</div>-->
                       <!--<div>-->
                       <!--     <h6 class="mb-0 mt-2 center-align dashboard"> Bill Count : <span id="billCount">{{ $billCount }}</span></h6>                        -->
                       <!--     <h5 class="center-align dashboard" ></h5>-->
                       <!--</div>-->
                      
                    </div>
                </div>
            </div>
           
            
        </div>
      </div>
        <!--/ Current balance & total transactions cards-->

        <!-- User statistics & appointment cards-->
        <div class="row">

            <div class="col s6 m6 6">
                <!-- Recent Buyers -->
                <div class="card recent-buyers-card animate fadeUp" style="height: 500px;">
                    <div class="card-content">
                        <h4 class="card-title mb-0">
                            Sales <i class="material-icons float-right">more_vert</i></h4>
                        <div class="col s12 l6 xl3 p-0">
                             <select id="saleSelect" name="saleSelect">
                            <option value="" disabled selected>Select One</option>
                            <option value="today"selected>Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                        </div>
                        <div class="col s12 l6 xl4 ">
                            <select id="salesYearSelect" name="salesYearSelect">
                                <option value="" disabled selected>Select a year</option>
                            </select>
                        </div>
                        <div class="col s12 l6 xl5 p-0">
                            <input type="text" class="daterange" name="sales_date_range"
                                id="sales_date_range" value="" placeholder="Select Date Range">
                        </div>
                        <canvas id="lineChart"></canvas>
                    </div>
                    
                </div>
            </div>
            <div class="col s6 m6 6">
                <!-- Recent Buyers -->
                <div class="card recent-buyers-card animate fadeUp" style="height: 500px;">
                    <div class="card-content" >
                        <h4 class="card-title mb-0">Customers <i class="material-icons float-right">more_vert</i></h4>
                         <div class="col s12 l6 xl3 p-0">
                             <select id="customerSelect" name="customerSelect">
                            <option value="" disabled selected>Select One</option>
                            <option value="today"selected>Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                        </div>
                        <div class="col s12 l6 xl4 ">
                            <select id="customerYearSelect" name="customerYearSelect">
                                <option value="" disabled selected>Select a year</option>
                            </select>
                        </div>
                        <div class="col s12 l6 xl5 p-0">
                            <input type="text" class="daterange" name="customer_date_range"
                                id="customer_date_range" value="" placeholder="Select Date Range">
                        </div>
                        <div>
                            <canvas id="myChart_customers"></canvas>
                        </div>
                    </div>
                    <div>
                        <p class="h4 text-center" id="customerCount_no_data_message"></p>
                    </div>
                </div>
            </div>

        </div>
        <!--/ Current balance & appointment cards-->

        <div class="row">
            <div class="col s6 m6 6">
                <div class="card recent-buyers-card animate fadeUp " style="height:500px;">
                    <div class="card-content" >
                        <h4 class="card-title mb-0">Payment History <i class="material-icons float-right">more_vert</i>
                        </h4>
                         <div class="col s12 l6 xl3 p-0">
                             <select id="paymentSelect" name="paymentSelect">
                            <option value="" disabled selected>Select One</option>
                            <option value="today" selected>Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                        </div>
                        <div class="col s12 l6 xl4">
                            <select id="paymentYearSelect" name="paymentYearSelect">
                                <option value="" disabled selected>Select a year</option>
                            </select>
                        </div>
                        <div class="col s12 l6 xl5">
                            <input type="text" class="daterange" name="payment_date_range"
                                id="payment_date_range" value="" placeholder="Select Date Range">
                        </div>
                      
                    </div>
                    <div>
                 
                        <table class="payment_history_table  data-table table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bill</th>
                                    <th>Payment Type</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot id="amountFoot">
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    
                                </tr>
                            </tfoot>
                        </table>
                        <div class="padding-5 pt-0 right-align"><a href="{{route('billOverview')}}">View More</a></div>
                       </div>
                   

                </div>
            </div>
            <div class="col s6 m6 6">
                <div class="card recent-buyers-card animate fadeUp">
                    <div class="card-content" style="height:500px;">
                        <h4 class="card-title mb-0">Service History <i class="material-icons float-right">more_vert</i>
                        </h4>
                         <div class="col s12 l6 xl3 p-0">
                             <select id="serviceSelect" name="serviceSelect">
                            <option value="" disabled >Select One</option>
                            <option value="today" selected>Today</option>
                            <option value="week">7 Days</option>
                            <option value="month">30 Days</option>
                        </select>
                        </div>
                        
                        <div class="col s12 l6 xl4 ">
                            <select id="serviceYearSelect" name="serviceYearSelect">
                                <option value="" disabled selected>Select a year</option>
                            </select>
                        </div>

                        <div class="col s12 l6 xl5 p-0">
                            <input type="text" class="daterange" name="service_date_range"
                                id="service_date_range" value="" placeholder="Select Date Range">
                        </div>
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div>
                        <p class="h4 text-center" id="customerCount_no_data_message"></p>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">

        </div>
    </div><!-- START RIGHT SIDEBAR NAV -->


@endsection
@push('page-scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">
    <script src="{{ asset('admin/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/js/scripts/data-tables.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
    <script src="{{ asset('admin/vendors/chartjs/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript">
        const categoryList = @php echo json_encode($categoryList); @endphp

        // Extract labels and data from categoryList
        const labels = categoryList.map(item => item.label);
        const piedata = categoryList.map(item => item.data);

        const piechart = {
            labels: labels,
            datasets: [{
                label: 'count',
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(0, 255, 255)',
                    'rgb(255, 0, 255)',
                    'rgb(210, 100, 56)',
                    'rgb(56, 100, 210)',
                    'rgb(100, 56, 210)',
                ],
                data: piedata,
            }]
        };

        const pieconfig = {
            type: 'pie',
            data: piechart,
            options: {
                plugins: {
                    legend: {
                        position: 'left',
                        labels: {
                            boxWidth: 20,
                            fontSize: 14,
                            padding: 20,
                        }
                    },
                },

            },
        };

        var mypieChart = new Chart(
            document.getElementById('myPieChart'),
            pieconfig
        );
    </script>
    <script>
        var barChart;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthData = @php  echo json_encode(
                $mychart
                    ->groupBy(function ($date) {
                        return Carbon\Carbon::parse($date->date)->format('M');
                    })
                    ->map(function ($data) {
                        return $data->sum('aggregate');
                    }),
        ); @endphp

        const data = months.map(month => monthData[month] || 0);

        const barConfig = {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Customer Count',
                    data: data,
                    backgroundColor: 'rgba(154, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 10)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'x',
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Customer Count'
                        },
                        ticks: {
                           beginAtZero: true,
                           stepSize: 2 
                        }
                    }
                }
            }
        };

         barChart = new Chart(
            document.getElementById('myChart_customers'),
            barConfig
        );
    </script>
    <script>
        var ctx = document.getElementById('lineChart').getContext('2d');
        var lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($line_chart['labels']),
                datasets: [{
                    label: 'Data',
                    data: @json($line_chart['data']),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <script>
        $(document).ready(function() {
           $('.payment_history_table').DataTable({
        paging: false,
        pageLength: 5,
        searching: false,
        ordering: false,
        info: false,
        dom: "Blfrtip",
        buttons: ['excel', 'pdf'],
        ajax: {
            url: "{{ route('paymentHistory') }}",
            type: 'GET', // or 'POST' depending on your API
            dataSrc: 'data' // adjust this according to your API response structure
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'bill', name: 'bill' },
            { data: 'payment_type', name: 'payment_type' },
            { data: 'amount', name: 'amount' }
        ],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            var total = api.column(3, { page: 'current' }).data().reduce(function (acc, curr) {
                // Remove commas and parse as float
                var amount = parseFloat(curr.replace(/,/g, ''));
                return acc + amount;
            }, 0);


            $(api.column(3).footer()).html('Total: ' + total.toFixed(2));
        }
    });
    });


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
    </script>
    <script>
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
    </script>
    <script>
        var yearSelect = document.getElementById('customerYearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            yearSelect.appendChild(option);
        }
        var yearSelect = document.getElementById('salesYearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            yearSelect.appendChild(option);
        }
        var yearSelect = document.getElementById('serviceYearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            yearSelect.appendChild(option);
        }
        var yearSelect = document.getElementById('paymentYearSelect');
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= currentYear - 10; i--) {
            var option = document.createElement('option');
            option.value = i;
            option.text = i;
            yearSelect.appendChild(option);
        }
        
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
 document.getElementById('yearSelect').addEventListener('change', function() {
    var selectedYear = this.value;
    $.ajax({
        url: '{{ route('dashboardFilter') }}',
        method: 'GET',
        data: {
            year: selectedYear
        },
        success: function(response) {
            console.log(response);
            if (response.flagError==false) {
                $("#packages").html(response.data.packages)
                $("#services").html(response.data.services)
                $("#therapist").html(response.data.therapist)
                $("#customerCount").html(response.data.customer)
                $("#total_instore").html(response.data.total_instore)
                $("#total_membership_instore").html(response.data.total_membership_instore)
                $("#total_dues").html(response.data.total_dues)
                $("#packageAmount").html(response.data.packageAmount)
              
               $("#serviceAmount").html(response.data.serviceAmount)
               $("#packageAmountPaid").html(response.data.packageAmount_paid)
               $("#serviceAmountPaid").html(response.data.serviceAmount_paid)
               $("#totalSaleAmount").html(response.data.totalSaleAmount)
               $("#totalSaleAmountPaid").html(response.data.totalSaleAmountPaid)
               $("#scheduleCount").html(response.data.schedulesCount)
               $("#billCount").html(response.data.billCount)
               $("#total_bill_count").html(response.data.total_bill_count)
               $("#totalDiscountAmount").html(response.data.totalDiscountAmount)
               $("#total_instore_balance").html(response.data.total_instore_balance)
               $("#total_instore_used").html(response.data.total_instore_used)
               $("#total_membership_instore_balance").html(response.data.total_membership_instore_balance)
               $("#total_membership_instore_used").html(response.data.total_membership_instore_used)
               
            } else {                
               
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});



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
            url: '{{ route('dashboardFilter') }}',
            method: 'GET',
            data: {
                toDate: toDate,
                fromDate: fromDate
            },
            success: function(response) {
                if (response.flagError == false) {
                    console.log(response.data.total_bill_count);
                    $("#packages").html(response.data.packages)
                    $("#services").html(response.data.services)
                    $("#therapist").html(response.data.therapist)
                    $("#customerCount").html(response.data.customer)
                    $("#total_instore").html(response.data.total_instore)
                    $("#total_dues").html(response.data.total_dues)
                    $("#packageAmount").html(response.data.packageAmount)
                    $("#serviceAmount").html(response.data.serviceAmount)
                    $("#packageAmountPaid").html(response.data.packageAmount_paid)
               $("#serviceAmountPaid").html(response.data.serviceAmount_paid)
                    $("#totalSaleAmount").html(response.data.totalSaleAmount)
                    $("#totalSaleAmountPaid").html(response.data.totalSaleAmountPaid)
                    $("#scheduleCount").html(response.data.schedulesCount)
                    $("#billCount").html(response.data.billCount)
                    $("#total_bill_count").html(response.data.total_bill_count)
                     $("#totalDiscountAmount").html(response.data.totalDiscountAmount)
                     $("#total_instore_balance").html(response.data.total_instore_balance)
               $("#total_instore_used").html(response.data.total_instore_used)
               $("#total_membership_instore_balance").html(response.data.total_membership_instore_balance)
               $("#total_membership_instore_used").html(response.data.total_membership_instore_used)

                } else {
                  
                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
});
   document.getElementById('customerYearSelect').addEventListener('change', function() {
    var selectedYear = this.value;
    $.ajax({
        url: '{{ route('customerBarChart') }}',
        method: 'GET',
        data: {
            year: selectedYear
        },
        success: function(response) {
            if (response.flagError==false) {
               
                if (barChart) {
                    barChart.destroy();
                 }
                
                getCustomerBarChart(response);
            } else {
                $('#myChart_customers').hide();
                $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
$(document).ready(function() {
    // Initialize the date range picker
    $('#customer_date_range').daterangepicker({
        // Specify your date range picker options here
    });
    
    // Event listener for change in date range
    $('#customer_date_range').on('apply.daterangepicker', function(ev, picker) {
        var fromDate = picker.startDate.format('YYYY-MM-DD');
        var toDate = picker.endDate.format('YYYY-MM-DD');
        
        $.ajax({
            url: '{{ route('customerBarChart') }}',
            method: 'GET',
            data: {
                toDate: toDate,
                fromDate: fromDate
            },
            success: function(response) {
                if (response.flagError == false) {
                    if (barChart) {
                        barChart.destroy();
                    }
                    
                    getCustomerBarChart(response);
                } else {
                    $('#myChart_customers').hide();
                    $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
});

function getCustomerBarChart(response){
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const monthData = response.mychart;
                const data = months.map(month => monthData[month] || 0);

                const barConfig = {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Customer Count',
                            data: data,
                            backgroundColor: 'rgba(154, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'x',
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Customer Count'
                                },
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 5 
                                }
                            }
                        }
                    }
                };

                 barChart = new Chart(
                    document.getElementById('myChart_customers'),
                    barConfig
                );
}


//sales Data
document.getElementById('salesYearSelect').addEventListener('change', function() {
    var selectedYear = this.value;
    $.ajax({
        url: '{{ route('salesLineChart') }}',
        method: 'GET',
        data: {
            year: selectedYear
        },
        success: function(response) {
            if (response.flagError==false) {               
                if (lineChart) {
                    lineChart.destroy();
                 }                
                getSalesLineChart(response);
            } else {
                $('#lineChart').hide();
                $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
$(document).ready(function() {
    // Initialize the date range picker
    $('#sales_date_range').daterangepicker({
        // Specify your date range picker options here
    });
    
    // Event listener for change in date range
    $('#sales_date_range').on('apply.daterangepicker', function(ev, picker) {
        var fromDate = picker.startDate.format('YYYY-MM-DD');
        var toDate = picker.endDate.format('YYYY-MM-DD');        
        $.ajax({
            url: '{{ route('salesLineChart') }}',
            method: 'GET',
            data: {
                toDate: toDate,
                fromDate: fromDate
            },
            success: function(response) {
                if (response.flagError == false) {
                    if (lineChart) {
                    lineChart.destroy();
                 }
                getSalesLineChart(response);
                } else {
                    $('#lineChart').hide();
                    $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
});
function getSalesLineChart(response){
    var ctx = document.getElementById('lineChart').getContext('2d');
         lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: response.line_chart['labels'],
                datasets: [{
                    label: 'Data',
                    data: response.line_chart['data'],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
}
//service

document.getElementById('serviceYearSelect').addEventListener('change', function() {
    var selectedYear = this.value;
    $.ajax({
        url: '{{ route('servicePieChart') }}',
        method: 'GET',
        data: {
            year: selectedYear
        },
        success: function(response) {
            console.log(response);
            if (response.flagError==false) {               
                if (mypieChart) {
                    mypieChart.destroy();
                 }                
                 getServicePieChart(response);
            } else {
                console.log("sdfjgsdfd");
                $('#myPieChart').hide();
                $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
$(document).ready(function() {
    // Initialize the date range picker
    $('#service_date_range').daterangepicker({
        // Specify your date range picker options here
    });
    
    // Event listener for change in date range
    $('#service_date_range').on('apply.daterangepicker', function(ev, picker) {
        var fromDate = picker.startDate.format('YYYY-MM-DD');
        var toDate = picker.endDate.format('YYYY-MM-DD');        
        $.ajax({
            url: '{{ route('servicePieChart') }}',
            method: 'GET',
            data: {
                toDate: toDate,
                fromDate: fromDate
            },
            success: function(response) {
                if (response.flagError == false) {
                    if (mypieChart) {
                    mypieChart.destroy();
                 }   
                getServicePieChart(response);
                } else {
                    $('#myPieChart').hide();
                    $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
});
function getServicePieChart(response){
    const categoryList = response.categoryList;
        // Extract labels and data from categoryList
        const labels = categoryList.map(item => item.label);
        const piedata = categoryList.map(item => item.data);
        const piechart = {
            labels: labels,
            datasets: [{
                label: 'count',
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(0, 255, 255)',
                    'rgb(255, 0, 255)',
                    'rgb(210, 100, 56)',
                    'rgb(56, 100, 210)',
                    'rgb(100, 56, 210)',
                ],
                data: piedata,
            }]
        };

        const pieconfig = {
            type: 'pie',
            data: piechart,
            options: {
                plugins: {
                    legend: {
                        position: 'left',
                        labels: {
                            boxWidth: 20,
                            fontSize: 14,
                            padding: 20,
                        }
                    },
                },

            },
        };

         mypieChart = new Chart(
            document.getElementById('myPieChart'),
            pieconfig
        );
}

document.getElementById('paymentYearSelect').addEventListener('change', function() {
    var selectedYear = this.value;
    $.ajax({
        url: '{{ route('paymentDatatable') }}',
        method: 'GET',
        data: {
            year: selectedYear
        },
        success: function(response) {
            $("#amountFoot").hide();
            if (response.flagError==false) {               
                $('.payment_history_table tbody').html(generateTableRows(response));
               

            } else {                
                var tableRows = '';
                    tableRows += '<tr>';
                    tableRows += '<td colspan="4" style="text-align:center;">' + response.message + '</td>';
                    tableRows += '</tr>';
                    $('.payment_history_table tbody').html(tableRows);
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
$(document).ready(function() {
    // Initialize the date range picker
    $('#payment_date_range').daterangepicker({
        // Specify your date range picker options here
    });
    
    // Event listener for change in date range
    $('#payment_date_range').on('apply.daterangepicker', function(ev, picker) {
        var fromDate = picker.startDate.format('YYYY-MM-DD');
        var toDate = picker.endDate.format('YYYY-MM-DD');        
        $.ajax({
            url: '{{ route('paymentDatatable') }}',
            method: 'GET',
            data: {
                toDate: toDate,
                fromDate: fromDate
            },
            success: function(response) {
                $("#amountFoot").hide();
                if (response.flagError == false) {
                    $('.payment_history_table tbody').html(generateTableRows(response));
                } else {
                    var tableRows = '';
                    tableRows += '<tr>';
                    tableRows += '<td colspan="3" style="text-align:center;">' + response.message + '</td>';
                    tableRows += '</tr>';
                    $('.payment_history_table tbody').html(tableRows);
                }
            },
            error: function(err) {
                console.error('Error fetching data: ' + err);
            }
        });
    });
});
function generateTableRows(filterCriteria) {
        var tableRows = '';       
        $.each(filterCriteria.billAmounts, function(index, billAmount) {
            if(billAmount.bill!=null){
                if (index <= 4) {
                    tableRows += '<tr>';
                    tableRows += '<td>' + (index + 1) + '</td>';
                    tableRows += '<td>' + billAmount.bill.billing_code + '</td>';
                
                    tableRows += '<td>' + billAmount.payment_type + '</td>';
                    tableRows += '<td>' + billAmount.amount + '</td>';
                    tableRows += '</tr>';
                }
            }
        });
       
        tableRows += '<tr>';
        tableRows += '<td></td>';
        tableRows += '<td></td>';
        tableRows += '<td></td>';
        tableRows += '<td>Total Amount:' +filterCriteria.billTotal + '</td>';
        tableRows += '</tr>';
        return tableRows;
}

 document.getElementById('daySelect').addEventListener('change', function() {
    var selectedDay = this.value;
    $.ajax({
        url: '{{ route('dashboardFilter') }}',
        method: 'GET',
        data: {
            day: selectedDay
        },
        success: function(response) {
            console.log(response);
            if (response.flagError==false) {
                $("#packages").html(response.data.packages)
                $("#services").html(response.data.services)
                $("#therapist").html(response.data.therapist)
                $("#customerCount").html(response.data.customer)
                $("#total_instore").html(response.data.total_instore)
                $("#total_dues").html(response.data.total_dues)
               $("#packageAmount").html(response.data.packageAmount)
               $("#serviceAmount").html(response.data.serviceAmount)
               $("#packageAmountPaid").html(response.data.packageAmount_paid)
               $("#serviceAmountPaid").html(response.data.serviceAmount_paid)
               $("#totalSaleAmount").html(response.data.totalSaleAmount)
               $("#totalSaleAmountPaid").html(response.data.totalSaleAmountPaid)
               $("#scheduleCount").html(response.data.schedulesCount)
               $("#billCount").html(response.data.billCount)
               $("#total_bill_count").html(response.data.total_bill_count)
            $("#totalDiscountAmount").html(response.data.totalDiscountAmount)
            $("#total_instore_balance").html(response.data.total_instore_balance)
               $("#total_instore_used").html(response.data.total_instore_used)
               $("#total_membership_instore_balance").html(response.data.total_membership_instore_balance)
               $("#total_membership_instore_used").html(response.data.total_membership_instore_used)
            } else {                
               
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
 document.getElementById('saleSelect').addEventListener('change', function() {
    var selectedDay = this.value;
    $.ajax({
        url: '{{ route('salesLineChart') }}',
        method: 'GET',
        data: {
            day: selectedDay
        },
        success: function(response) {
            if (response.flagError==false) {               
                if (lineChart) {
                    lineChart.destroy();
                 }                
                getSalesLineChart(response);
            } else {
                $('#lineChart').hide();
                $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
 document.getElementById('customerSelect').addEventListener('change', function() {
    var selectedDay = this.value;
    $.ajax({
        url: '{{ route('customerBarChart') }}',
        method: 'GET',
        data: {
            day: selectedDay
        },
        success: function(response) {
            if (response.flagError==false) {
               
                if (barChart) {
                    barChart.destroy();
                 }
                
                getCustomerBarChart(response);
            } else {
                $('#myChart_customers').hide();
                $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
 document.getElementById('paymentSelect').addEventListener('change', function() {
    var selectedDay = this.value;
    $.ajax({
        url: '{{ route('paymentDatatable') }}',
        method: 'GET',
        data: {
            day: selectedDay
        },
        success: function(response) {
            $("#amountFoot").hide();
            if (response.flagError==false) {               
                $('.payment_history_table tbody').html(generateTableRows(response));
               

            } else {                
                var tableRows = '';
                    tableRows += '<tr>';
                    tableRows += '<td colspan="4" style="text-align:center;">' + response.message + '</td>';
                    tableRows += '</tr>';
                    $('.payment_history_table tbody').html(tableRows);
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});
 document.getElementById('serviceSelect').addEventListener('change', function() {
    var selectedDay = this.value;
    $.ajax({
        url: '{{ route('servicePieChart') }}',
        method: 'GET',
        data: {
            day: selectedDay
        },
        success: function(response) {
            console.log(response);
           if (response.flagError==false) {               
                if (mypieChart) {
                    mypieChart.destroy();
                 }                
                 getServicePieChart(response);
            } else {
                
                $('#customerCount_no_data_message').html('No Data Found').addClass('no_data');
            }
        },
        error: function(err) {
            console.error('Error fetching data: ' + err);
        }
    });
});



    </script>
@endpush
