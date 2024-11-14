@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- page style --}}
@section('page-style')
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
@section('page-action')
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
        class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create
        {{ Str::singular($page->title) ?? '' }}<i class="material-icons right">add</i></a>
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
                    <h4 class="card-title">{{ $page->title ?? '' }} Form </h4>
                    <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                        <div class="card-content red-text">
                            <ul></ul>
                        </div>
                    </div>
                    <form id="{{ Str::camel($page->title) }}EditForm" name="{{ Str::camel($page->title) }}EditForm"
                        role="form" method="POST" action="{{ url(ROUTE_PREFIX . '/billings/' . $billing->id) }}">
                        {{ csrf_field() }}
                        @method('PUT')
                        {!! Form::hidden('billing_id', $billing->id ?? '', ['id' => 'billing_id']) !!}
                        {!! Form::hidden('customer_id', $billing->customer_id ?? '', ['id' => 'customer_id']) !!}

                        <div class="row">
                            <div class="col s12">
                                <div class="row">
                                    <div class="input-field col m12 s12" id="custom-templates">
                                        <i class="material-icons prefix">textsms</i>
                                        <input type="text" name="search_customer" id="search_customer"
                                            class="typeahead autocomplete" autocomplete="off" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col m6 s12">
                                {!! Form::text('customer_name', $billing->name ?? '', [
                                    'id' => 'customer_name',
                                    'placeholder' => 'Customer Name',
                                    'disabled' => 'disabled',
                                ]) !!}
                                <!-- <label for="customer_name" class="label-placeholder">Customer Name <span class="red-text">*</span></label> -->
                            </div>
                            <div class="input-field col m6 s12">
                                {!! Form::text('customer_mobile', $billing->mobile ?? '', [
                                    'id' => 'customer_mobile',
                                    'placeholder' => 'Customer Mobile',
                                    'disabled' => 'disabled',
                                ]) !!}
                                <!-- <label for="customer_mobile" class="label-placeholder">Customer Mobile <span class="red-text">*</span></label>  -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col m6 s12">
                                @php
                                    $billed_date =
                                        $billing->date_range_billed_date != ''
                                            ? $billing->date_range_billed_date
                                            : Carbon\Carbon::now()->format('d-m-Y');
                                @endphp
                                <input type="text" name="billed_date" id="billed_date" class="form-control"
                                    onkeydown="return false" autocomplete="off" value="{{ $billed_date }}" />
                            </div>
                            <div class="input-field col m6 s12">
                                {!! Form::text('customer_email', $billing->customer_email ?? '', [
                                    'id' => 'customer_email',
                                    'placeholder' => 'Customer Email',
                                    'disabled' => 'disabled',
                                ]) !!}
                                <!-- <label for="customer_email" class="label-placeholder">Customer Email <span class="red-text">*</span></label>  -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col m6 s12">
                                @php
                                    $checkin_time =
                                        $billing->date_range_checkin_time != ''
                                            ? $billing->date_range_checkin_time
                                            : Carbon\Carbon::now()->format('d-m-Y h:m A');
                                @endphp
                                <input type="text" name="checkin_time" id="checkin_time" class="form-control"
                                    onkeydown="return false" autocomplete="off" value="{{ $checkin_time }}" />
                            </div>
                            <div class="input-field col m6 s12">
                                @php
                                    $checkout_time =
                                        $billing->date_range_checkout_time != ''
                                            ? $billing->date_range_checkout_time
                                            : Carbon\Carbon::now()->format('d-m-Y h:m A');
                                @endphp
                                <input type="text" name="checkout_time" id="checkout_time" class="form-control"
                                    onkeydown="return false" autocomplete="off" value="{{ $checkout_time }}" />
                            </div>
                        </div>

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
                                {!! Form::text('customer_billing_name', $billing->billingaddress->billing_name ?? '', [
                                    'id' => 'customer_billing_name',
                                ]) !!}
                                <label for="customer_billing_name" class="label-placeholder">Billing Name/Company Name
                                    <span class="red-text">*</span></label>
                            </div>
                            <div class="input-field col m6 s12">
                                {!! Form::text('pincode', $billing->billingaddress->pincode ?? '', [
                                    'id' => 'pincode',
                                    'class' => 'check_numeric',
                                ]) !!}
                                <label for="pincode" class="label-placeholder">Pincode</label>
                            </div>
                        </div>
                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m6 s12">
                                {!! Form::text('customer_gst', $billing->billingaddress->gst ?? '', [
                                    'id' => 'customer_gst',
                                    'style' => 'text-transform:uppercase',
                                ]) !!}
                                <label for="customer_gst" class="label-placeholder">GST No.</label>
                            </div>
                            <div class="input-field col m6 s12">
                                @if (isset($billing->billingaddress->country_id))
                                    {!! Form::select('country_id', $variants->country, $billing->billingaddress->country_id ?? '', [
                                        'id' => 'country_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select country',
                                    ]) !!}
                                @else
                                    {!! Form::select('country_id', $variants->country, '', [
                                        'id' => 'country_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select country',
                                    ]) !!}
                                @endif

                            </div>
                        </div>

                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m6 s12">

                                @if (!empty($variants->states))
                                    {!! Form::select('state_id', $variants->states, $billing->billingaddress->state_id ?? '', [
                                        'id' => 'state_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select state',
                                    ]) !!}
                                @else
                                    {!! Form::select('state_id', [], '', [
                                        'id' => 'state_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select state',
                                    ]) !!}
                                @endif

                            </div>
                            <div class="input-field col m6 s12">

                                @if (!empty($variants->districts))
                                    {!! Form::select('district_id', $variants->districts, $billing->billingaddress->district_id ?? '', [
                                        'id' => 'district_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select district',
                                    ]) !!}
                                @else
                                    {!! Form::select('district_id', [], '', [
                                        'id' => 'district_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select district',
                                    ]) !!}
                                @endif

                            </div>
                        </div>

                        <div class="row billing-address-section" style="display:none;">
                            <div class="input-field col m12 s12">
                                {!! Form::textarea('address', $billing->billingaddress->address ?? '', [
                                    'class' => 'materialize-textarea',
                                    'placeholder' => 'Address',
                                    'rows' => 3,
                                ]) !!}
                            </div>
                            <div class="input-field col m6 s12">
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col m6 s12">
                                <select class="select2 browser-default" name="service_type" id="service_type"
                                    data-packageId="" onchange="$('#usedServicesDiv').hide();">
                                    <option selected="selected">Please select type</option>
                                    <option value="1" @if ($item_type == 'services') selected @endif>Services
                                    </option>
                                    <option value="2" @if ($item_type == 'packages') selected @endif>Packages
                                    </option>
                                    <option value="3" @if ($item_type == 'memberships') selected @endif>
                                        Memberships</option>
                                </select>
                            </div>
                            <div class="input-field col m6 s12">
                                <div id="services_block">
                                    <select class="select2 browser-default service-type" data-type="services"
                                        name="bill_item[]" id="services" multiple="multiple">
                                    </select>
                                </div>
                                <div id="packages_block" style="display:none;">
                                    <select class="select2 browser-default service-type" data-type="packages"
                                        name="bill_item[]" id="packages" multiple="multiple">
                                    </select>
                                </div>
                                <div id="membership_block" style="display:none;">
                                    <select class="select2 browser-default service-type" data-type="memberships"
                                        name="bill_item[]" id="memberships" multiple="multiple">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="usedServicesDiv" style="display:none">
                            <div class="input-field col s12">
                                <table class="responsive-table" id="servicesTable">
                                    <thead>
                                        <tr>
                                            {{-- <th>#</th> --}}
                                            <th>Name</th>
                                            <th class="packageCount">Number of Items</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row validityDiv">
                            <div class="input-field col m6 s12">
                                <input type="text" name="validity_from" id="validity_from" class="form-control"
                                    onkeydown="return false" autocomplete="off"
                                    value="{{ $billing->items[0]->validity_from }}" />
                                <label for="validity_from" class="label-placeholder active">Validity Starting Date
                                </label>
                            </div>
                            {{-- <div class="input-field col m6 s12">
                                <input type="text" name="validity_to" id="validity_to" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                                <label for="validity_to" class="label-placeholder active">Validity Expiring Date </label>
                            </div> --}}
                            <div class="input-field col m6 s12">
                                <select id="validity" class="form-control validity" name="validity">
                                    <option value="2" @if ($billing->items[0] == '2') selected @endif>2 Days
                                    </option>
                                    <option value="5" @if ($billing->items[0] == '5') selected @endif>5 Days
                                    </option>
                                    <option value="7" @if ($billing->items[0] == '7') selected @endif>7 Days
                                    </option>
                                    <option value="10" @if ($billing->items[0] == '10') selected @endif>10 Days
                                    </option>
                                    <option value="15" @if ($billing->items[0] == '15') selected @endif>15 Days
                                    </option>
                                    <option value="30" @if ($billing->items[0] == '30') selected @endif>30 Days
                                    </option>
                                    <option value="60" @if ($billing->items[0] == '60') selected @endif>60 Days
                                    </option>
                                    <option value="90"@if ($billing->items[0] == '90') selected @endif>90 Days
                                    </option>
                                </select>
                                <label for="validity" class="label-placeholder">Validity Period</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <a href="{{ url(ROUTE_PREFIX . '/' . $page->route) }}"
                                    class="btn waves-effect waves-light" type="reset" name="reset">Cancel <i
                                        class="material-icons right">refresh</i></a>
                                <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                    id="continue-btn">Update and Continue <i
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
<!-- typeahead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

<!-- date-time-picker -->
<script>
    $('#country_id').select2({
        placeholder: "Please select country",
        allowClear: true
    });
    $('#state_id').select2({
        placeholder: "Please select state",
        allowClear: true
    });
    $('#district_id').select2({
        placeholder: "Please select district",
        allowClear: true
    });
    $('#service_type').select2({
        placeholder: "Please select type"
    });
    $('#services').select2({
        placeholder: "Please select service",
        allowClear: true
    });
    $('#packages').select2({
        placeholder: "Please select package",
        allowClear: true
    });
    $("#memberships").select2({
        placeholder: "Please select Membership",
        allowClear: true,
    });

    var timePicker = {!! json_encode($variants->time_picker) !!};
    var timeFormat = {!! json_encode($variants->time_format) !!};
    let bill_id = {!! json_encode($billing->id) !!};
    var customer_id = {!! json_encode($billing->customer->id) !!};

    var service_type = {!! json_encode($service_type) !!};
    var item_type = {!! json_encode($item_type) !!};
    var item_ids = {!! json_encode($variants->item_ids) !!};
    var package_ids = {!! json_encode($variants->package_ids) !!};
    var billing_address_type = {!! json_encode($billing->address_type) !!};
    var pageRoute = location.pathname
    let last_selected;
    let last_unselected;
    let type;
    $(function() {
        $('input[name="billed_date"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: true,
            timePicker: true,
            autoUpdateInput: true,
            timePicker24Hour: timePicker,
            locale: {
                format: 'DD-MM-YYYY ' + timeFormat + ':mm A'
            },
        }, function(ev, picker) {
            // console.log(picker.format('DD-MM-YYYY'));
        });

        $('input[name="checkin_time"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: true,
            timePicker: true,
            autoUpdateInput: true,
            timePicker24Hour: timePicker,
            locale: {
                format: 'DD-MM-YYYY ' + timeFormat + ':mm A'
            },
        }, function(ev, picker) {
            // console.log(end.format('DD-MM-YYYY hh:mm A'));
        });

        $('input[name="checkout_time"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: true,
            timePicker: true,
            autoUpdateInput: true,
            timePicker24Hour: timePicker,
            locale: {
                format: 'DD-MM-YYYY ' + timeFormat + ':mm A'
            },
        }, function(start, end, label) {
            // console.log(end.format('DD-MM-YYYY hh:mm A'));
        });

        $('input[name="dob"]').daterangepicker({
            autoUpdateInput: false,
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: true,
            maxYear: parseInt(moment().format('YYYY'), 10),
            timePicker: false,
            maxDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            },
        });

        $('input[name="dob"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
    });

    // Load Customer details
    getCustomerDetails(customer_id);

    // set service_type value and list items
    $("#service_type").val(service_type);
    if (service_type == 1) {
        $("#services_block").show();
        $("#packages_block").hide();
        $("#membership_block").hide();
        $(".validityDiv").hide();
        getServices(item_ids);
    } else if (service_type == 3) {
        $("#services_block").hide();
        $("#membership_block").show();
        $("#packages_block").hide();
        $(".validityDiv").hide();
        getmemberships(item_ids);
    } else {
        // var packageId=$("#service_type").data('packageid');
        $("#services_block").hide();
        $("#packages_block").show();
        $("#membership_block").hide();
        $(".validityDiv").show();
        getPackages(package_ids);
    }


    var $eventSelect = $(".service-type");
    $eventSelect.select2({
        placeholder: "Please select ",
        allowClear: false
    });
    $eventSelect.on("select2:unselect", function(e) {
        type = $(this).data("type");
        last_unselected = e.params.data.id;
        listItemDetails(type, last_unselected, "unselect");
    });
    $eventSelect.on("select2:select", function(e) {
        $("#usedServicesDiv").hide();
        $("#servicesTable").find("tr:gt(0)").remove();
        type = $(this).data("type");
        last_selected = e.params.data.id;
        listItemDetails(type, last_selected, "select");
    });


    function listItemDetails(type, latest_value, selection) {
        var data_ids = $('#' + type).val();
        let billing_id = $('#billing_id').val();
        var billing = pageRoute;
        var parts = billing.split("/");
        var lastWord = parts[parts.length - 3];
        var action = parts[parts.length - 1]
        var url = '';
        if (type == 'packages') {
            url = "{{ route('getPackageDetails') }}";
            $(".packageCount").hide();
        } else if (type == 'services') {
            url = "{{ route('get_details') }}";
            $(".packageCount").show();
        } else {
            url = "{{ route('get_membership_details') }}";
            $(".packageCount").show();
        }
        if (selection == "select") {
            if (data_ids != "") {
                $.ajax({
                    type: "post",
                    url: url,
                    dataType: "json",
                    data: {
                        data_ids: data_ids,
                        type: type,
                        lastWord: lastWord,
                        action: action
                    },
                    delay: 250,
                    success: function(data) {
                        var html = "";

                        $("#servicesTable").append(data.html);
                        $("#usedServicesDiv").show();
                    },
                });
            } else {
                $("#usedServicesDiv").hide();
                $("#servicesTable").find("tr:gt(0)").remove();
            }
        } else {
            $("table#servicesTable tr#" + latest_value).remove();
        }

    }


    // Autocomplete customer details
    var path = "{{ route('customers.autocomplete') }}";
    $('input.typeahead').typeahead({
        autoSelect: true,
        source: function(query, process) {
            return $.get(path, {
                search: query
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

    function manageItemCount(action, rowID, price, tax_percentage, total_percentage, tax_included, additionaltax) {
        let newVal;
        let oldValue = $("#itemCount" + rowID).val();
        newVal = action == "inc" ?
            parseFloat(oldValue) + 1 :
            oldValue > 1 ?
            parseFloat(oldValue) - 1 :
            (newVal = 1);
        $("#itemCount" + rowID).val(newVal);
        if (action == 'inc' || action == 'dec') {
            serviceAmount = price * newVal;
            var totalServiceTax = 0;
            var gstAmount = (serviceAmount * tax_percentage) / 100;
            if (tax_included == 1) {
                totalServiceTax = (serviceAmount / (1 + (total_percentage / 100)));
                serviceValue = serviceAmount / (1 + (total_percentage / 100));

                totalPayable = serviceAmount;
            } else {
                totalServiceTax = serviceAmount;
                totalPayable = serviceAmount + gstAmount;
                serviceValue = serviceAmount;
            }

            var additionalTaxSum = 0;

            // Calculate and display additional tax amounts
            for (let i = 0; i < additionaltax.length; i++) {
                let additionalTaxAmount = (serviceAmount * additionaltax[i].percentage) / 100;
                additionalTaxSum += additionalTaxAmount;
            }
            totalPayable += additionalTaxSum;
            total_cgst_amount = (serviceValue * (tax_percentage / 2)) / 100;
            total_sgst_amount = (serviceValue * (tax_percentage / 2)) / 100;

            totalPayable = totalPayable.toFixed(2);
            total_cgst_amount = total_cgst_amount.toFixed(2);
            total_sgst_amount = total_sgst_amount.toFixed(2);
            serviceValue = serviceValue.toFixed(2);
            additionalTaxSum = additionalTaxSum.toFixed(2);
        }
        // totalPayable = serviceAmount +total_cgst_amount +total_sgst_amount;
        $("#serviceValue" + rowID).html("&#8377;" + serviceValue);
        $(".cgstAmount" + rowID).html("&#8377;" + total_cgst_amount);
        $(".sgstAmount" + rowID).html("&#8377; " + total_cgst_amount);
        $(".totalPayable" + rowID).html("&#8377; " + totalPayable);


    }


    if (billing_address_type == 'company') {
        $('#billing_address_checkbox').prop('checked', false);
        $('.billing-address-section').show();
    }


    $('#billing_address_checkbox').change(function() {
        if ($(this).is(":unchecked"))
            $('.billing-address-section').show();
        else
            $('.billing-address-section').hide();
    });


    function getCustomerDetails(customer_id) {
        $.ajax({
            type: 'get',
            url: "{{ route('get_customer_details') }}",
            dataType: 'json',
            data: {
                customer_id: customer_id
            },
            delay: 250,
            success: function(data) {
                $('#customer_id').val(data.data.id);
                var customerMobile = '';
                if (data.data.mobile != null) {
                    customerMobile = ' - ' + data.data.mobile;
                }
                $("#search_customer").val(data.data.name + customerMobile);
                $("#customer_name").val(data.data.name);
                $("#customer_mobile").val(data.data.mobile);
                $("#customer_email").val(data.data.email);
                $("#customer_details_div").show();
            }
        });
    }


    $(document).on('change', '#service_type', function() {
        $("#usedServicesDiv").hide();

        if (this.value == 1) {
            $("#services_block").show();
            $("#packages_block").hide();
            $("#membership_block").hide();
            getServices(null);
        } else if (this.value == 2) {
            $("#services_block").hide();
            $("#packages_block").show();
            $("#membership_block").hide();
            getPackages(null);
        } else {
            $("#services_block").hide();
            $("#membership_block").show();
            $("#packages_block").hide();
            $(".validityDiv").hide();
            getmemberships(null);
        }
    });

    function getServices(item_ids = null) {
        $.ajax({
            type: 'GET',
            url: "{{ url(ROUTE_PREFIX . '/common/get-all-services') }}",
            dataType: 'json',
            delay: 250,
            success: function(data) {

                var selectTerms = '<option value="">Please choose services</option>';
                $.each(data.data, function(key, value) {
                    selected = '';
                    if ((item_ids != null) && (item_ids.length != 0)) {

                        if (jQuery.inArray(value.id, item_ids) !== -1) {
                            selected = 'selected';
                        }
                    }

                    selectTerms += '<option value="' + value.id + '" ' + selected + '>' + value
                        .name + '</option>';
                });

                var select = $('#services');
                select.empty().append(selectTerms);

                listItemDetails(item_type, null, 'select')
            }
        });
    }

    function getPackages(package_ids = null) {
        $.ajax({
            type: 'GET',
            url: "{{ url(ROUTE_PREFIX . '/common/get-all-packages') }}",
            dataType: 'json',
            delay: 250,
            success: function(data) {
                // $('.service-type').attr('data-packageId',package_ids);
                var selectTerms = '<option value="">Please choose packages</option>';
                $.each(data.data, function(key, value) {
                    selected = '';
                    if ((package_ids != null) && (package_ids.length != 0)) {
                        if (jQuery.inArray(value.id, package_ids) !== -1) {
                            selected = 'selected';
                        }

                    }
                    selectTerms += '<option value="' + value.id + '" ' + selected + '>' + value
                        .name + '</option>';
                });
                var select = $('#packages');
                select.empty().append(selectTerms);
                listItemDetails(item_type, '', 'select')
            }
        });
    }

    function getmemberships() {
        $.ajax({
            type: "GET",
            url: "{{ route('get_all_memberships') }}",
            dataType: "json",
            delay: 250,
            success: function(data) {
                var selectTerms = '<option value="">Please choose Membership</option>';
                $.each(data.data, function(key, value) {
                    selected = '';
                    if ((item_ids != null) && (item_ids.length != 0)) {

                        if (jQuery.inArray(value.id, item_ids) !== -1) {
                            selected = 'selected';
                        }
                    }

                    selectTerms += '<option value="' + value.id + '" ' + selected + '>' + value
                        .name + '</option>';
                });

                var select = $('#memberships');
                select.empty().append(selectTerms);
                listItemDetails(item_type, '', 'select')

            },
        });
    }
    if ($("#{{ Str::camel($page->title) }}EditForm").length > 0) {
        var validator = $("#{{ Str::camel($page->title) }}EditForm").validate({
            rules: {
                customer_name: {
                    required: true,
                },
                search_customer: {
                    required: true,
                },
                "bill_item[]": {
                    required: true,
                },
            },
            messages: {
                customer_name: {
                    required: "Please select a Customer",
                },
                search_customer: {
                    required: "Please select a Customer",
                },
                "bill_item[]": {
                    required: "Please select an item",
                },
            },
            submitHandler: function(form) {
                // var forms = $("Form");
                // $.ajax({ url: "{{ url(ROUTE_PREFIX . '/' . $page->route) }}", type: "POST", processData: false, 
                // data: forms.serialize(), dataType: "html",
                // }).done(function (a) {
                //     var data = JSON.parse(a);
                //     if(data.flagError == false){
                //         showSuccessToaster(data.message);
                //         // setTimeout(function () { 
                //         //   window.location.href = "{{ url(ROUTE_PREFIX . '/' . $page->route) }}";                
                //         // }, 2000);
                //     }else{
                //       showErrorToaster(data.message);
                //       printErrorMsg(data.error);
                //     }
                // });

                $(".itemCount").each(function(index, element) {
                    $("#{{ Str::camel($page->title) }}EditForm").append(
                        '<input type="hidden" class="item-count-hidden" name="items[' +
                        $(this).data("id") +
                        " ][" +
                        $(this).val() +
                        ']"></input>'
                    );
                });


                form.submit();
            },
            errorPlacement: function(error, element) {
                if (element.is("select")) {
                    error.insertAfter(element.next('.select2'));
                } else {
                    error.insertAfter(element);
                }
            }
        })
    }

    jQuery.validator.addMethod("lettersonly", function(value, element) {
        return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
    }, "Letters only please");


    $(document).on('change', '#country_id', function() {
        $.ajax({
            type: 'POST',
            url: "{{ url(ROUTE_PREFIX . '/common/get-states-by-country') }}",
            data: {
                'country_id': this.value
            },
            dataType: 'json',
            success: function(data) {
                var selectTerms = '<option value="">Please select state</option>';
                $.each(data.data, function(key, value) {
                    selectTerms += '<option value="' + value.id + '" >' + value.name +
                        '</option>';
                });
                var select = $('#state_id');
                select.empty().append(selectTerms);
                $('#district_id').empty().trigger("change");
            }
        });
    });

    $(document).on('change', '#state_id', function() {
        $.ajax({
            type: 'POST',
            url: "{{ url(ROUTE_PREFIX . '/common/get-districts-by-state') }}",
            data: {
                'state_id': this.value
            },
            dataType: 'json',
            success: function(data) {
                var selectTerms = '<option value="">Please select district</option>';
                $.each(data.data, function(key, value) {
                    selectTerms += '<option value="' + value.id + '" >' + value.name +
                        '</option>';
                });
                var select = $('#district_id');
                select.empty().append(selectTerms);
            }
        });
    });

    $("#discount_btn").click(function() {
        $("#discount-modal").modal("show");
    });

    if ($("#discountForm").length > 0) {
        var validator = $("#discountForm").validate({
            rules: {
                discount_value: {
                    required: true,
                },
            },
            messages: {
                discount_value: {
                    required: "Please enter discount value",
                }
            },
            submitHandler: function(form) {
                var forms = $("#discountForm");
                var grand_total = $("#grand_total").val();
                $input = $('<input type="hidden" name="grand_total"/>').val(grand_total);
                forms.append($input);
                $.ajax({
                    url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/manage-discount') }}",
                    type: "POST",
                    processData: false,
                    data: forms.serialize(),
                    dataType: "html",
                }).done(function(a) {
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        $('#discountAmount').text('Discount Amount : ' + data.discount_value);
                        $('#afterdiscount').text('After discount : ' + data.amount);
                        $('#grand_total').val(data.amount);
                        $("#discount-modal").modal("hide");
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                });
            }
        })
    }
</script>
@endpush
