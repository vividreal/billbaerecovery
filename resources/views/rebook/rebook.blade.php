@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/data-tables/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css') }}">
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
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/rebook') }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">List</li>
    </ol>
@endsection

@section('page-action')

@endsection

<div class="section section-data-tables">
    <div id="card-stats" class="pt-0">
        <div class="row">
            <div class="col s12 m6 l6 xl4">
                <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class="padding-4">
                        <div class="row">
                            <div class="col s4 m4">
                                <i class="material-icons background-round mt-4">add_shopping_cart</i>
                                <p>Cancellation Fee </p>
                            </div>
                            <div class="col s8 m8 right-align">
                                <h5 class="mb-0 white-text">₹ <span id="cancellation_fee "> {{number_format($variants->cancellation_fee_total,2)}}</span></h5>
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
                  <h4 class="card-title">{{ Str::plural($page->title) ?? ''}} Table</h4>
                  <div class="row">
                    <div class="col s12">
                        <table id="data-table-reports" class="display data-tables" data-url="{{ $page->link.'/lists' }}" data-form="page" data-length="20">
                            <thead>
                              <tr>
                                <th>No</th>
                                <th>Bill</th> 
                                <th>Cancelled Bill</th> 
                                <th>Date</th>             
                                <th>Customer</th> 
                                <th>Amount</th>   
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
    
    @endsection
    
    {{-- vendor scripts --}}
    @section('vendor-script')
    <script src="{{asset('admin/vendors/data-tables/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @endsection
    
    @push('page-scripts')
    <script>
    var table;
    $(function () {
      table = $('#data-table-reports').DataTable({
        pagination: true,
        pageLength: 10,
        responsive: true,
        searchDelay: 500,
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{ route('rebook.index') }}",
          data: search
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'billing_code', name: 'billing_code' },
                { data: 'cancelled_billing_code', name: 'cancelled_billing_code' },
                { data: 'billed_date', name: 'billed_date' },
                { data: 'customer', name: 'customer' },
                { data: 'amount', name: 'amount' },
         
        ]
      });
    });
    
    function search(value) {
      value.name              = $('input[type=search]').val();
      value.start_range       = $("#start_range").val();
      value.end_range         = $("#end_range").val();
      value.transaction_type  = $("#transaction_type").val();
      value.cash_from         = $("#cash_from").val();
      value.cash_book         = $("#cash_book").val();
    }
    
    $("#resetSelection").on("click", function(){
      $(".select2").val('').trigger('change');
      table.ajax.reload();
    });
    
    showMessage = function(message) {
      $("#fullMessage").text(message)
      $("#full-message-modal").modal("open");
    }
    
    </script>
    @endpush
    
    