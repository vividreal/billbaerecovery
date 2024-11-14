@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')


{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')

@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">List</li>
    </ol>
@endsection



<div class="section section-data-tables">

    <!-- DataTables example -->
    <div class="row">
        <div class="col s12 m12 l12">
            @include('layouts.success')
            @include('layouts.error')
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ Str::plural($page->title) ?? '' }} Table</h4>
                    <div class="row">
                        <form id="dt-filter-form" name="dt-filter-form">
                            {!! Form::hidden('status', '', ['id' => 'status']) !!}
                        </form>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <table id="data-table-schedulers" class="display data-tables" data-url="{{ $page->link }}"
                                data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        {{-- <th width="10px" data-orderable="false" data-column="DT_RowIndex"> No </th> --}}
                                        <th width="180px" data-orderable="false" data-column="schedule"> Schedule </th>
                                        <th width="100px" data-orderable="false" data-column="date"> Date & Time </th>
                                        <th width="70px" data-orderable="false" data-column="type"> Type </th>
                                        {{-- <th width="" data-orderable="false" data-column="type_name"> Type Name </th>                               --}}
                                        <th width="100px" data-orderable="false" data-column="customer"> Customer </th>
                                        <th width="150px" data-orderable="false" data-column="therapist"> Therapist
                                        </th>
                                        <th width="70px" data-orderable="false" data-column="room"> Room </th>
                                        <th width="280px" data-orderable="false" data-column="status"> Status </th>
                                        {{-- <th width="280px" data-orderable="false" data-column="action"> Action </th> --}}
                                        <th width="180px" data-orderable="false" data-column="payment_status"> Payment
                                            Status </th>

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
@endsection

@push('page-scripts')
<script>
    var getScheduleList = "{{ route('scheduler.listCustomerSchedules') }}";
</script>
<script src="{{ asset('admin/js/custom/schedule/schedule.js') }}"></script>
<script>
    function handleCheckIn(detailId,status) {        
      $.ajax({
        url: "{{route('scheduler.updateCheckInStatus')}}",
        method: 'POST',
        data: {
            detail_id: detailId,
            status:status
            // Add any additional data you need to send to the backend here
        },
        success: function(response) {
          if (response.flagError == false) {
            showSuccessToaster(response.message);
            setTimeout(function () {
                window.location.reload(); // Example: reload the current page
            }, 4000); 
          }else{
            showErrorToaster(response.message);
            setTimeout(function () {
                window.location.reload(); // Example: reload the current page
            }, 4000); 
          }
        },
        error: function(xhr, status, error) {
            // Handle any errors that occur during the AJAX call
            console.error('Error updating checkbox state:', error);
        }
    });
        
    }
</script>
@endpush
