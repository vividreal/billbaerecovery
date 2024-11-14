@extends('layouts.app')

@section('content')
@push('page-css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush
@section('breadcrumb')
  <li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
  </li>
  <li class="nav-item d-none d-sm-inline-block">
    <a href="{{ url(ROUTE_PREFIX.'/home') }}" class="nav-link">Home</a>
  </li>
  <li class="nav-item d-none d-sm-inline-block">
    <a href="{{ url(ROUTE_PREFIX.'/users') }}" class="nav-link">Users</a>
  </li>
@endsection

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">{{ $page->title ?? ''}}</h1>
          </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <div class="text-right">
                  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> Add  {{ $page->title ?? ''}}
                  </a>
                </div>
              </ol>
            </div>
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

     <!-- Main content -->
     <section class="content">
      <div class="container-fluid">
        
        <div class="row">

        

          <div class="col-12">

            <div class="card">

                <div class="card-header">
                  <h3 class="card-title">{{ $page->title ?? ''}} Form</h3>
                </div>
                <!-- /.card-header -->
                <!-- Form Section -->
                <div class="card-body">
                  <form id="reportForm" name="reportForm" role="form" method="" action="" class="ajax-submit">
                    {{ csrf_field() }}

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group ">
                              {!! Form::label('day_range', 'Please select*', ['class' => 'col-form-label text-alert']) !!}
                              <select id="day_range" class="form-control" name="day_range">
                                <option value="1">Today</option>
                                <option value="2">Yesterday</option>
                                <option selected="selected"  value="3">Last 7 Days</option>
                                <option value="4">Last 30 Days</option>
                                <option value="5">This Month</option>
                                <option value="6">Last Month</option>
                                
                              </select>
                          </div> 
                        </div>

                        <div class="col-md-6">
                          <div class="form-group ">
                              {!! Form::label('daterange', 'Choose date range*', ['class' => 'col-form-label text-alert']) !!}
                              <input type="text" class="form-control" name="daterange" value="01/01/2018 - 01/15/2018" />
                          </div> 
                        </div>
                      </div>


                  </form>
                </div>
                <!-- /.card-body -->


                <!-- Values list Section -->
                <div class="row">
                  
                  <div class="col-sm-3 col-6">
                    <div class="description-block border-right">
                      <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> ₹</span>
                      <h5 class="description-header">₹ 35,210.43</h5>
                      <span class="description-text">TOTAL CASH</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                  <!-- /.col -->
                  <div class="col-sm-3 col-6">
                    <div class="description-block border-right">
                      <span class="description-percentage text-warning"><i class="fas fa-calendar"></i></span>
                      <h5 class="description-header">Start Date</h5>
                      <span class="description-text">20 May</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                  <!-- /.col -->
                  <div class="col-sm-3 col-6">
                    <div class="description-block border-right">
                      <span class="description-percentage text-success"><i class="fas fa-calendar"></i> </span>
                      <h5 class="description-header">End Date</h5>
                      <span class="description-text">15 June</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                  <!-- /.col -->
                  <div class="col-sm-3 col-6">
                    <div class="description-block">
                      <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 18%</span>
                      <h5 class="description-header">1200</h5>
                      <span class="description-text">GOAL COMPLETIONS</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                </div>
              
                <!-- Chart Section -->
                <div class="card-header">
                  <h3 class="card-title">{{ $page->title ?? ''}} Chart</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <div class="card">
                    <!-- <div id="linechart" style="height: 500px;"></div>     -->
                    <canvas id="linechart" height="250"></canvas>
                  </div>
                </div>
                <!-- /.card-body -->

                <!-- /.card-header -->
                <div class="card-header">
                  <h3 class="card-title">{{ $page->title ?? ''}} Table</h3>
                </div>
                <div class="card-body">
                          <table class="table table-hover table-striped table-bordered data-tables"
                                data-url="{{ $page->link.'/lists' }}" data-form="page" data-length="20">
                                <thead>
                                  <tr>
                                      <th>No</th>
                                      <th>Name</th>
                                      <th>Mobile</th>
                                      <th width="100px">Action</th>
                                  </tr>
                              </thead>
                          </table>

                          
                </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection
@push('page-scripts')
<!-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>  -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('admin/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('admin/js/pages/dashboard3.js') }}"></script>
<script>


$(document).ready(function(){
  loadChart()
});

$("#day_range").change(function(){
  loadChart();
});

function loadChart()
{
  var forms = $("#reportForm");
  $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route.'/get-sales-data') }}", type: 'post', processData: false, 
  data: forms.serialize(), dataType: "html",
  }).done(function (a) {
      var data = JSON.parse(a);
      if(data.flagError == false){
        chart(data.chart_label, data.chart_data);
        
      }
  });

}

$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left'
  }, function(start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });
});




  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  function chart(chart_label, chart_data)
  {
    var chart_label = chart_label
    var chart_data  = chart_data;
    var mode = 'index'
    var intersect = true

    var $visitorsChart = $('#linechart')
    // eslint-disable-next-line no-unused-vars
    var visitorsChart = new Chart($visitorsChart, {
      data: {
        labels: chart_label,
        datasets: [{
          type: 'line',
          data: chart_data,
          backgroundColor: 'transparent',
          borderColor: '#007bff',
          pointBorderColor: '#007bff',
          pointBackgroundColor: '#007bff',
          fill: false
          // pointHoverBackgroundColor: '#007bff',
          // pointHoverBorderColor    : '#007bff'
        },
        // {
        //   type: 'line',
        //   data: [60, 80, 70, 67, 80, 77, 100],
        //   backgroundColor: 'tansparent',
        //   borderColor: '#ced4da',
        //   pointBorderColor: '#ced4da',
        //   pointBackgroundColor: '#ced4da',
        //   fill: false
        //   // pointHoverBackgroundColor: '#ced4da',
        //   // pointHoverBorderColor    : '#ced4da'
        // }
      ]
      },
      options: {
        // maintainAspectRatio: false,
        // tooltips: {
        //   mode: mode,
        //   intersect: intersect
        // },
        // hover: {
        //   mode: mode,
        //   intersect: intersect
        // },
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            // display: false,
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,
              suggestedMax: 200
            }, ticksStyle)
          }],
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            },
            ticks: ticksStyle
          }]
        }
      }
    })
  }
    
        






  var link = '{{ $page->link }}';
  $(function () {

    table = $('.data-tabless').DataTable({
        bSearchable: true,
        pagination: true,
        pageLength: 10,
        responsive: true,
        searchDelay: 500,
        processing: true,
        serverSide: true,
        ajax: {
                url: "{{ url(ROUTE_PREFIX.'/'.$page->route.'/lists') }}",
                data: search
            },
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},            
            {data: 'mobile', name: 'name'},               
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

  });

  function search(value) {
    value.name = $('input[type=search]').val();
  }



  function softDelete(b) {
           
           Swal.fire({
             title: 'Are you sure want to delete ?',
             text: "You won't be able to revert this!",
             type: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#3085d6',
             cancelButtonColor: '#d33',
             confirmButtonText: 'Yes, delete it!'
             }).then(function(result) {
                 if (result.value) {
                     $.ajax({url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}/" + b, type: "DELETE", dataType: "html"})
                         .done(function (a) {
                             var data = JSON.parse(a);
                             if(data.flagError == false){
                               showSuccessToaster(data.message);          
                               setTimeout(function () {
                                 table.ajax.reload();
                                 }, 2000);
       
                           }else{
                             showErrorToaster(data.message);
                             printErrorMsg(data.error);
                           }   
                         }).fail(function () {
                                 showErrorToaster("Somthing went wrong!");
                         });
                 }
             });
         }



</script>
@endpush

