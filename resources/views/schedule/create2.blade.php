@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection


{{-- vendor styles --}}
@section('vendor-style')

<link rel="stylesheet" href="https://fullcalendar.io/js/fullcalendar-3.1.0/fullcalendar.css">
<link rel="stylesheet" href="https://fullcalendar.io/js/fullcalendar-scheduler-1.5.0/scheduler.min.css">
@endsection


@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Create</li>
  </ol>
@endsection

@section('page-action')
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">List<i class="material-icons right">list</i></a>
@endsection

<div class="section">
  <div class="card">
    <div class="card-content">
      <p class="caption mb-0">{{ Str::plural($page->title) ?? ''}}. Lorem ipsum is used for the ...</p>
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
                <div class="col s12">
                  <!-- <div id="calendar"></div> -->
                  <div id="full_calendar_events"></div>
                </div>
              </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div id="modal1" class="modal">
  <div class="modal-content">
    <form id="pop_form">
      <div class="row">
        <div class="pf_hd col m6 s12 l6">
          <h2>Please fill the form below</h2>
        </div>
        <div class="pf_con">
          <div class="pf_bar">
            <span class="confirm_bkg">Confirm booking</span>
            <span class="check_bkg">Check in </span>
            <span class="recieve_bkg">Received payment</span>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="input-field col m4 s4 l4">
          <input id="first_name01" type="text">
          <label for="first_name01" class="" id="user_name">Name</label>
        </div>
        <div class="input-field col m4 s4 l4">
          <input id="last_name" type="text">
          <label for="last_name">Phone Number</label>
        </div>
        <div class="input-field col m4 s4 l4">
          <input id="email5" type="email">
          <label for="email">Email</label>
        </div>
      </div>            
      <div class="row">
        <div class="input-field col m6 s6">
          <div class="select-wrapper">
              <select tabindex="-1">
                  <option value="" disabled="" selected="">Service/Package</option>
                  <option value="1">Manager</option>
                  <option value="2">Developer</option>
                  <option value="3">Business</option>
              </select>
          </div>
          <label>Select Profile</label>
        </div>  
        <div class="input-field col m6 s6">
          <div class="select-wrapper">
              <select tabindex="-1">
                  <option value="" disabled="" selected="">Duration</option>
                  <option value="1">10 min</option>
                  <option value="1">20 min</option>
                  <option value="1">30 min</option>
                  <option value="2">60 min</option>
                  <option value="3">90 min</option>
              </select>
          </div>
          <label>Select Profile</label>
        </div>              
      </div>        
    </form> 
  </div>
  <div class="modal-footer">
    <div class="row buttonRow">
      <button class="btn recv_payment" type="submit">Receive payment
      </button>   
      <button class="btn" type="submit">Submit
          <i class="material-icons right">send</i>
      </button>   
    </div>
  </div>
</div>



@endsection

{{-- vendor scripts --}}
@section('vendor-script')

@endsection


@push('page-scripts')
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.js"></script>
<script src="https://fullcalendar.io/js/fullcalendar-3.1.0/fullcalendar.js"></script>

<!-- Fullcalendar -->
<script src="{{asset('admin/js/custom/fullcalendar.js')}}"></script>
<script src="{{asset('admin/js/custom/daypilot-all.min.js')}}"></script>


<script>
var timePicker  = {!! json_encode($variants->time_picker) !!};
var timezone    = {!! json_encode($variants->timezone) !!};
var therapists  = '';
var today       = '';


$(document).ready(function () {

var SITEURL = "{{ url('/') }}";

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var calendar = $('#full_calendar_events').fullCalendar({
    editable: true,
    editable: true,
    events: "{{ url(ROUTE_PREFIX.'/schedules') }}",
    displayEventTime: true,
    eventRender: function (event, element, view) {
        if (event.allDay === 'true') {
            event.allDay = true;
        } else {
            event.allDay = false;
        }
    },
    selectable: true,
    selectHelper: true,
    select: function (event_start, event_end, allDay) {
        var event_name = prompt('Event Name:');
        if (event_name) {
            // var event_start = $.fullCalendar.formatDate(event_start, "Y-MM-DD HH:mm:ss");
            // var event_end = $.fullCalendar.formatDate(event_end, "Y-MM-DD HH:mm:ss");
            $.ajax({
                url: SITEURL + "/calendar-crud-ajax",
                data: {
                    event_name: event_name,
                    event_start: event_start,
                    event_end: event_end,
                    type: 'create'
                },
                type: "POST",
                success: function (data) {
                    displayMessage("Event created.");
                    calendar.fullCalendar('renderEvent', {
                        id: data.id,
                        title: event_name,
                        start: event_start,
                        end: event_end,
                        allDay: allDay
                    }, true);
                    calendar.fullCalendar('unselect');
                }
            });
        }
    },
    eventDrop: function (event, delta) {
        var event_start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
        var event_end = $.fullCalendar.formatDate(event.end, "Y-MM-DD");
        $.ajax({
            url: SITEURL + '/calendar-crud-ajax',
            data: {
                title: event.event_name,
                start: event_start,
                end: event_end,
                id: event.id,
                type: 'edit'
            },
            type: "POST",
            success: function (response) {
                displayMessage("Event updated");
            }
        });
    },
    eventClick: function (event) {
        var eventDelete = confirm("Are you sure?");
        if (eventDelete) {
            $.ajax({
                type: "POST",
                url: SITEURL + '/calendar-crud-ajax',
                data: {
                    id: event.id,
                    type: 'delete'
                },
                success: function (response) {
                    calendar.fullCalendar('removeEvents', event.id);
                    displayMessage("Event removed");
                }
            });
        }
    }
});
});

function displayMessage(message) {
  toastr.success(message, 'Event');            
}

 

</script>
@endpush

