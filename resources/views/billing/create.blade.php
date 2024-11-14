@extends('layouts.app')
{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- page style --}}
@section('page-style')

@endsection
@push('page-css')
    <style>
        .inp {
            border: none;
            border-bottom: 1px solid #1890ff;
            outline: none;
        }
    </style>
@endpush
@section('content')
@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection

@section('page-action')
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn"
        type="submit" name="action">Create {{ Str::singular($page->title) ?? '' }}<i
            class="material-icons right">add</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route) }}"
        class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List
        {{ Str::plural($page->title) ?? '' }}<i class="material-icons right">list</i></a>
@endsection
<div class="section">
 
    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                   
                             @if (session('error'))
     
                             <div class="card-alert card red lighten-5 print-error-msg" >
                        <div class="card-content red-text">
                                <div class="alert alert-danger">
                                   {{ session('error') }}
                                </div>
                                 </div>
                    </div>
                            @endif
                       
                    <form id="{{ Str::camel($page->title) }}Form" name="{{ Str::camel($page->title) }}Form"
                        role="form" method="post" action="{{ url(ROUTE_PREFIX . '/' . $page->route) }}">
                        {{ csrf_field() }}
                        
                        {!! Form::hidden('billing_id', $billing->id ?? '', ['id' => 'billing_id']) !!}
                        {!! Form::hidden('customer_id', $billing->customer_id ?? '', ['id' => 'customer_id']) !!}
                        {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle']) !!}
                        {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                        {!! Form::hidden('customerRoute', url('customers'), ['id' => 'customerRoute']) !!}
                        {!! Form::hidden('timePicker', $variants->time_picker, ['id' => 'timePicker']) !!}
                        {!! Form::hidden('timeFormat', $variants->time_format, ['id' => 'timeFormat']) !!}
                        <div class="row">
                            <div class="input-field col m6 s12" id="custom-templates">
                                <i class="material-icons prefix">search</i>
                                <input type="text" name="search_customer" id="search_customer"
                                    class="typeahead autocomplete" autocomplete="off" value="" placeholder="">
                                <label for="search_customer" class="typeahead label-placeholder active">Enter Name or
                                    Mobile for existing Customers...</label>
                            </div>
                            <div class="input-field col m2 s12" id="customerActionDiv" style="display:none;">
                                <a class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text amber darken-4 tooltipped"
                                    data-position="bottom" data-tooltip="View Customer" target="_blank"
                                    id="customerViewLink">
                                    <i class="material-icons">remove_red_eye</i>
                                </a>
                                <a class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text tooltipped"
                                    data-position="bottom" data-tooltip="Remove Customer" onclick="removeCustomer()">
                                    <i class="material-icons">cancel</i>
                                </a>
                            </div>
                            <div class="input-field col m5 s12" id="newCustomerBtn">
                                <a class="waves-effect waves-light btn-small cyan" onClick="addNewCustomer()"
                                    style="margin-top: 12px;"><i class="material-icons left">person_add</i>Create New
                                    Customer</a>
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="input-field col m6 s12 user-details">
                                {!! Form::text('customer_name', '', ['id' => 'customer_name', 'placeholder' => '', 'disabled' => 'disabled']) !!}
                                <label for="customer_name" class="label-placeholder active">Customer Name <span
                                        class="red-text">*</span></label>
                            </div>
                            <div class="input-field col m6 s12  user-details">
                                {!! Form::text('customer_mobile', '', [
                                    'id' => 'customer_mobile',
                                    'placeholder' => '',
                                    'disabled' => 'disabled',
                                ]) !!}
                                <label for="customer_mobile" class="label-placeholder active">Mobile </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <input type="text" name="billed_date" id="billed_date" class="form-control"
                                    onkeydown="return false" autocomplete="off" value="" />
                                <label for="billed_date" class="label-placeholder active">Billed Date </label>
                            </div>
                            <div class="input-field col m6 s12  user-details">
                                {!! Form::text('customer_email', '', ['id' => 'customer_email', 'placeholder' => '', 'disabled' => 'disabled']) !!}
                                <label for="customer_email" class="label-placeholder active">Email</label>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="input-field col m6 s12">
                                <input type="text" name="checkin_time" id="checkin_time" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                                <label for="checkin_time" class="label-placeholder active">Checkin time</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <input type="text" name="checkout_time" id="checkout_time" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                                <label for="checkout_time" class="label-placeholder active">Checkout time</label>
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <p><label for="billing_address_checkbox">
                                        <input type="checkbox" name="billing_address_checkbox"
                                            id="billing_address_checkbox" value="1" checked="checked" />
                                        <span>Billing address and customer address are same.</span>
                                    </label></p>
                                <!-- <label  class="custom-control-label"></label> -->
                                <small class="col-sm-2 ">Uncheck to add new billing address !</small>
                            </div>
                            <div class="input-field col m6 s12">
                            </div>
                        </div>
                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m6 s12">
                                {!! Form::text('customer_billing_name', '', ['id' => 'customer_billing_name']) !!}
                                <label for="customer_billing_name" class="label-placeholder">Billing Name/Company Name
                                    <span class="red-text">*</span></label>
                            </div>
                            <div class="input-field col m6 s12">
                                {!! Form::text('pincode', '', ['id' => 'pincode']) !!}
                                <label for="pincode" class="label-placeholder">Pin code</label>
                            </div>
                        </div>
                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m6 s12">
                                {!! Form::text('customer_gst', '', ['id' => 'customer_gst', 'style' => 'text-transform:uppercase']) !!}
                                <label for="customer_gst" class="label-placeholder">GST No.</label>
                            </div>
                            <div class="input-field col m6 s12">
                                {!! Form::select('country_id', $variants->countries, '', [
                                    'id' => 'country_id',
                                    'class' => 'select2 browser-default',
                                    'placeholder' => 'Please select country',
                                ]) !!}
                            </div>
                        </div>
                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m6 s12">
                                {!! Form::select('district_id', [], '', [
                                    'id' => 'district_id',
                                    'class' => 'select2 browser-default',
                                    'placeholder' => 'Please select district',
                                ]) !!}
                            </div>
                            <div class="input-field col m6 s12">
                                {!! Form::select('state_id', [], '', [
                                    'id' => 'state_id',
                                    'class' => 'select2 browser-default',
                                    'placeholder' => 'Please select state',
                                ]) !!}
                            </div>
                        </div>
                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m12 s12">
                                {!! Form::textarea('address', '', ['class' => 'materialize-textarea', 'placeholder' => 'Address', 'rows' => 3]) !!}
                            </div>
                            <div class="input-field col m6 s12">
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col m6 s12">
                                <select class="select2 browser-default" name="service_type" id="service_type"
                                    onchange="$('#usedServicesDiv').hide();">
                                    <option selected="selected">Please select type</option>
                                    <option value="1">Services</option>
                                    <option value="2" id="hide_for_membershipid">Packages</option>
                                    <option value="3">Membership</option>
                                </select>
                            </div>
                            <div class="input-field col m6 s12">
                                <div id="services_block">
                                    @include('layouts.loader')
                                    <select class="select2 browser-default service-type" data-type="services"
                                        name="bill_item[]" id="services" multiple="multiple"> </select>
                                </div>
                                <div id="packages_block" style="display:none;">
                                    @include('layouts.loader')
                                    <select class="select2 browser-default service-type" data-type="packages"
                                        name="bill_item[]" id="packages" multiple="multiple"> </select>
                                </div>
                                <div id="membership_block" style="display:none;">
                                    @include('layouts.loader')
                                    <select class="select2 browser-default service-type" data-type="memberships"
                                        name="bill_item[]" id="memberships" multiple="multiple"> </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="usedServicesDiv" style="display:none">
                            <div class="input-field col s12">
                                <table class="responsive-table" id="servicesTable">
                                    <thead>
                                        <tr>
                                            <!-- <th>#</th> -->
                                            <th>Name</th>
                                            <th class="packageCount">Number Of Items</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row validityDiv">
                            <div class="input-field col m6 s12">
                                <input type="text" name="validity_from" id="validity_from" class="form-control"
                                    onkeydown="return false" autocomplete="off" value="" />
                                <label for="validity_from" class="label-placeholder active">Validity Starting Date
                                </label>
                            </div>
                            {{-- <div class="input-field col m6 s12">
                                <input type="text" name="validity_to" id="validity_to" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                                <label for="validity_to" class="label-placeholder active">Validity Expiring Date </label>
                            </div> --}}
                            <div class="input-field col m6 s12">
                                <select id="validity" class="form-control validity" name="validity">
                                    <option selected="selected" value="2">2 Days</option>
                                    <option value="5">5 Days</option>
                                    <option value="7">7 Days</option>
                                    <option value="10">10 Days</option>
                                    <option value="15">15 Days</option>
                                    <option value="30">30 Days</option>
                                    <option value="60">60 Days</option>
                                    <option value="90">90 Days</option>
                                </select>
                                <label for="validity" class="label-placeholder">Validity Period</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field co5 s12">
                                <button class="btn waves-effect waves-light" type="reset" name="reset">Reset <i
                                        class="material-icons right">refresh</i></button>
                                <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                    id="continue-btn">Continue to Billing<i
                                        class="material-icons right">keyboard_arrow_right</i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('billing.new-customer-manage')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script>
    var path = "{{ route('customers.autocomplete') }}";
    var getService = "{{ route('get_all_services') }}";

    var get_customerDetails = "{{ route('get_customer_details') }}";
    var get_details = "{{ route('get_details') }}";
    var get_packages_details = "{{ route('getPackageDetails') }}";
    var getTaxPrint = "{{ route('list_service_with_tax') }}";
    var getPackageList = "{{ route('get_all_packages') }}";
    var getMembershipList = "{{ route('get_all_memberships') }}";
    var get_membership_details="{{route('get_membership_details')}}";
</script>
<!-- typeahead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<!-- date-time-picker -->
<script>
    $('input.typeahead').typeahead({
        autoSelect: true,
        hint: true,
        highlight: true,
        minLength: 2,
        source: function(query, process) {
            return $.get(path, {
                search: query,
                classNames: {
                    input: 'Typeahead-input',
                    hint: 'Typeahead-hint',
                    selectable: 'Typeahead-selectable'
                }
            }, function(data) {
                return process(data);
            });
        },
        updater: function(item) {
            $('#customer_id').val(item.id);
            getCustomerDetails(item.id);
            return item;
        }
    });
</script>
<script src="{{ asset('admin/js/custom/billing/billing.js') }}"></script>
<script>
    window.addEventListener('pageshow', function(event) {
        // Check if the page was redirected from another page
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            // Reload the page
            window.location.reload();
        }
    });
</script>


@endpush
