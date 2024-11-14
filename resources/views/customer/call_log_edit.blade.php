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
      
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection



<div class="section section-data-tables">
    <div class="row">
        <div class="col s12 m12 l12">

            <div class="card card card-default scrollspy">
                <div class="card-content">
                    <div>
                        <h5>Edit Log </h5>
                    </div>
                    <div>
                        <form id="customer_calllog" method="PUT">
                            @csrf
                            <div col s6 m6>
                                <label for="">Title</label>
                                <input type="text" id="call_log_text" name="call_log_text" value="{{ $customerLog->title }}" />

                            </div>
                            <div col s6 m6>
                                <label for="">Date</label>
                                <input type="date" id="call_log_date" name="call_log_date" value="{{ $customerLog->add_call_log_date ? \Carbon\Carbon::parse($customerLog->add_call_log_date)->format('Y-m-d') : '' }}">
                              
                                
                            </div>
                            <div col s6 m6>
                                <label for="">Time</label>
                                <input type="time" id="call_log_time" name="call_log_time" value="{{ $customerLog->call_time }}"
                                    min="09:00" max="22:00" required />

                                <small>Office hours are 9am to 10pm</small>

                            </div>
                            <div col s6 m6>
                                <label for="">Visiting Status</label>
                                <select name="visiting_status" id="visiting_status">
                                   
                                    <option value="">Select Status</option>
                                    <option value="0" @if($customer->visiting_status == '0') selected @endif>New Customer</option>
                                    <option value="1" @if($customer->visiting_status == '1') selected @endif>Regular Customer</option>
                                    <option value="2" @if($customer->visiting_status == '2') selected @endif>VIP Customer</option>
                                    <option value="3" @if($customer->visiting_status == '3') selected @endif>Occasional Visitor</option>
                                    <option value="4" @if($customer->visiting_status == '4') selected @endif>Former Customer</option>
                                    <option value="5" @if($customer->visiting_status == '5') selected @endif>WeekDays Customer</option>
                                    
                                </select>
                            </div>
                            <div col s6 m6>
                                <label for="">Behavioral Status</label>
                                <select name="behavioral_status" id="behavioral_status">
                                    <option value="">Select Behavioral Status</option>
                                    <option value="0" @if($customer->behavioral_status == '0') selected @endif>Calm</option>
                                    <option value="1" @if($customer->behavioral_status == '1') selected @endif>Neutral</option>
                                    <option value="2" @if($customer->behavioral_status == '2') selected @endif>Dangerous</option>
                                    {{-- <option value="3" @if($customer->behavioral_status == '3') selected @endif>Aggressive</option> --}}
                                    

                                </select>
                            </div>
                            <div>
                                <input type="hidden" name="callLogId" id="callLogId" value="{{ $customerLog->id }}">
                                <input type="hidden" name="customerId" id="customerId" value="{{ $customerLog->customer_id }}">
                                <label for="">Callog Comment</label>
                                <textarea name="log_comment" id="log_comment" cols="30" rows="60">{{ $customerLog->customer_logs }}</textarea>
                            </div>
                            <div>
                                <input type="submit" name="customer_log_submit"
                                    class="btn btn-light-green">
                            </div>
                        </form>
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
    $(document).ready(function() {
        var baseURL = window.location.origin;
        $('#customer_calllog').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var customerId = $("#customerId").val();
            $.ajax({
                url: '{{ route('customers.updateCallLog') }}',
                type: 'PUT',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                     
                        
                        if (baseURL.includes("localhost")) {
                            // If it's localhost, remove "billbae"
                            baseURL = baseURL + "/customer-review-book?customerId=" + customerId;
                        } else {
                            baseURL = baseURL + "/customer-review-book?customerId=" + customerId;
                        }
                        var url = baseURL;

                        // Redirect to the constructed URL
                        window.location.href = url;

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
   


</script>
@endpush
