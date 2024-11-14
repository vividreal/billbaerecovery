@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('page-style')
  <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/fullcalendar.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/scheduler.min.css')}}">
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Create</li>
  </ol>
@endsection

@section('page-action')
  @can('schedule-create')
    <a href="{{ $page->link }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">List<i class="material-icons right">list</i></a>
  @endcan
@endsection
<div class="section">
  <div id="card-stats" class="pt-0">
    <div class="row">              
      <div class="col s12 m6 l6 xl3">
        <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
          <div class="padding-4">
            <div class="row">
              <div class="col s7 m7"><i class="material-icons background-round mt-5">collections_bookmark</i><p>Total Bookings</p></div>
              <div class="col s5 m5 right-align"><h5 class="mb-0 white-text"><span id="total_bookings"></span></h5></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col s12 m6 l6 xl3">
        <div class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
          <div class="padding-4">
            <div class="row">
              <div class="col s7 m7"><i class="material-icons background-round mt-5">attach_money</i><p>Total Booking value</p></div>
              <div class="col s5 m5 right-align"><h5 class="mb-0 white-text">₹ <span id="booking_amount"></span></h5></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col s12 m6 l6 xl3">
        <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
          <div class="padding-4">
            <div class="row">
              <div class="col s7 m7"><i class="material-icons background-round mt-5">collections_bookmark</i><p> Total Sales</p></div>
              <div class="col s5 m5 right-align"><h5 class="mb-0 white-text"><span id="total_sales"></span></h5></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col s12 m6 l6 xl3">
        <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
          <div class="padding-4">
            <div class="row">
              <div class="col s7 m7"><i class="material-icons background-round mt-5">attach_money</i><p>Total Sales value</p></div>
              <div class="col s5 m5 right-align"><h5 class="mb-0 white-text">₹ <span id="sales_amount"></span></h5></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--Basic Form-->
  <div class="row">
    <!-- Form Advance -->
    <div class="col s12 m12 l12">
      <div id="Form-advance" class="card card card-default scrollspy">
        <div class="card-content">
          <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
          <div class="card-alert card red lighten-5 print-error-msg" style="display:none"><div class="card-content red-text"><ul></ul></div></div>
          <div class="row">
            <div class="card-content">
              <div class="row">
                <div class="col s12">
                  <form id="dt-filter-form" name="dt-filter-form">
                    {{ csrf_field() }}
                    <div class="row">
                      <div class="input-field col m6 s12">
                      {!! Form::select('calendar_mode', [1 => 'Therapist', 2 => 'Rooms'] , '' , ['id' => 'calendar_mode' ,'class' => 'select2 browser-default']) !!}
                        <label for="calendar_mode" class="label-placeholder active">Calendar View Mode</label>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <form id="{{Str::camel($page->title)}}Form" name="{{Str::camel($page->title)}}Form" role="form" method="post" action="{{ url(ROUTE_PREFIX.'/'.$page->route) }}">
              {{ csrf_field() }}
              {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!} 
              {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
              {!! Form::hidden('billingRoute', url('billings'), ['id' => 'billingRoute'] ); !!}
              {!! Form::hidden('timePicker', $variants->time_picker, ['id' => 'timePicker'] ); !!}
              {!! Form::hidden('timeFormat', $variants->time_format, ['id' => 'timeFormat'] ); !!}
              {!! Form::hidden('timezone', $variants->timezone, ['id' => 'timezone'] ); !!}
              {!! Form::hidden('currency', CURRENCY, ['id' => 'currency'] ); !!}
            </form>
            <div class="col s12">
              <div id="preCalendar"></div>
              <div id="calendar"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('schedule.manage')
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
<script src="{{asset('admin/js/fullcalendar.js')}}"></script>
<script src="{{asset('admin/js/custom/fullcalendar.js')}}"></script>
<script src="{{asset('admin/js/custom/daypilot-all.min.js')}}"></script>
<script src="{{asset('admin/js/custom/schedule/schedule.js')}}"></script>
<script>

$(function() {
  getTherapists();
  
});

var path        = "{{ route('customers.autocomplete') }}";
$('input.typeahead').typeahead({
    autoSelect: true,
    hint: true,
    highlight: true,
    minLength: 2,
    source:  function (query, process) {
    return $.get(path, 
        { 
          search: query,
          classNames: { input: 'Typeahead-input', hint: 'Typeahead-hint', selectable: 'Typeahead-selectable' }
        }, function (data) {
            return process(data);
        });
    },
    updater: function (item) {
      $('#customer_id').val(item.id);
      getCustomerDetails(item.id);
      return item;
    }
});



// Form script ends
      
// $("#cancelSchedule").click(function() {
//   swal({ title: "Are you sure ?", icon: 'warning', dangerMode: true,
//     buttons: { cancel: 'No, Please!',  delete: 'Yes, Cancel It' }
//   }).then(function (willDelete) {
//     if (willDelete) {
//       var schedule_id = $('#schedule_id').val();
//       $.ajax({url: "{{ url(ROUTE_PREFIX.'/schedules') }}/" + schedule_id, type: "DELETE", dataType: "html"})
//         .done(function (a) {
//           var data = JSON.parse(a);
//           if(data.flagError == false) {
//             showSuccessToaster(data.message); 
//             $('#manage-schedule-modal').modal('close');  
//             $('#calendar').fullCalendar( 'refetchEvents' );
//             getSalesData();
//           }else{
//             showErrorToaster(data.message);
//           }   
//         }).fail(function () {
//             showErrorToaster("Something went wrong!");
//         });
//     } 
//   });
// })
     
// function getSchedule(id){
//   $.ajax({ type: 'POST', url: "{{ url(ROUTE_PREFIX.'/schedules/') }}"+id, delay: 250,
//     success: function(data) { return data; }
//   });
// }

// $("#receivePaymentBtn").click(function() {
//   var form    = $('#manageScheduleForm');
//   $("#receive_payment").val(1);    
//   form.submit();
// })

</script>
@endpush