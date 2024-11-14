@extends('layouts.app')
{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection
{{-- page style --}}
@section('page-style')
  <!-- daterange picker -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <!-- <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-users.css')}}"> -->
@endsection
@push('page-css')
<style>
</style>
@endpush
@section('content')
@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <!-- <li class="breadcrumb-item active">Create</li> -->
  </ol>
@endsection
@section('page-action')
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">List<i class="material-icons right">list</i></a>
@endsection
<div class="section">
 
  <!--Basic Form-->
  <div class="row">
    <!-- Form Advance -->
    <div class="col s12 m12 l12">
      <div id="Form-advance" class="card card card-default scrollspy">
        <div class="card-content">
            <h4 class="card-title">Billing Form</h4>
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none"><div class="card-content red-text"><ul></ul></div></div>
            <form id="customerBillingForm" name="customerBillingForm" role="form" method="post" action="{{ url(ROUTE_PREFIX.'/billings') }}">
                {{ csrf_field() }}
                {!! Form::hidden('billing_id', $billing->id ?? '' , ['id' => 'billing_id'] ); !!}
                {!! Form::hidden('customer_id', $customer->id, ['id' => 'customer_id'] ); !!}
                <div class="row">
                  <div class="input-field col m6 s12">
                    {!! Form::text('customer_name', $customer->name,  ['id' => 'customer_name', 'placeholder' => 'Customer Name', 'disabled' => 'disabled']) !!}  
                    <label for="customer_name" class="label-placeholder active">Name <span class="red-text">*</span></label>
                  </div>
                  <div class="input-field col m6 s12">
                    {!! Form::text('customer_mobile', $customer->mobile ?? '', array('id' => 'customer_mobile', 'placeholder' => 'Customer Mobile', 'disabled' => 'disabled')) !!}  
                    <label for="customer_mobile" class="label-placeholder active">Mobile </label> 
                  </div>
                </div>
                <div class="row">
                  <div class="input-field col m6 s12">
                    <input type="text" name="billed_date" id="billed_date" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                    <label for="billed_date" class="label-placeholder active">Billed Date </label>
                  </div>
                  <div class="input-field col m6 s12">
                    {!! Form::text('customer_email', $customer->email ?? '', array('id' => 'customer_email', 'placeholder' => 'Customer Email', 'disabled' => 'disabled')) !!}  
                    <label for="customer_email" class="label-placeholder active">Email</label>
                  </div>
                </div>
                <div class="row">
                  <div class="input-field col m6 s12">
                    <input type="text" name="checkin_time" id="checkin_time" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                    <label for="checkin_time" class="label-placeholder active">Checkin time</label> 
                  </div>
                  <div class="input-field col m6 s12">
                    <input type="text" name="checkout_time" id="checkout_time" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                    <label for="checkout_time" class="label-placeholder active">Checkout time</label>
                  </div>
                </div>
                <div class="row">
                  <div class="input-field col m6 s12">
                    <p><label for="billing_address_checkbox">
                      <input type="checkbox" name="billing_address_checkbox" id="billing_address_checkbox" value="1" checked="checked" />
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
                    {!! Form::text('customer_billing_name', '', array('id' => 'customer_billing_name')) !!}  
                    <label for="customer_billing_name" class="label-placeholder active">Billing Name/Company Name</label> 
                  </div>
                  <div class="input-field col m6 s12">
                    {!! Form::text('pincode', '', array('id' => 'pincode')) !!}  
                    <label for="pincode" class="label-placeholder">Pincode</label> 
                  </div>
                </div>    
                <div class="row billing-address-section" style="display:none;">
                  <div class="input-field col m6 s12">
                    {!! Form::text('customer_gst', '', array('id' => 'customer_gst', 'style' => "text-transform:uppercase")) !!}  
                    <label for="customer_gst" class="label-placeholder">GST No.</label> 
                  </div>
                  <div class="input-field col m6 s12">
                    {!! Form::select('country_id', $variants->countries , '' , ['id' => 'country_id', 'class' => 'select2 browser-default', 'placeholder'=>'Please select country']) !!}
                  </div>
                </div>
                <div class="row billing-address-section" style="display:none;">
                  <div class="input-field col m6 s12">
                    {!! Form::select('district_id', [] , '' , ['id' => 'district_id' ,'class' => 'select2 browser-default','placeholder'=>'Please select district']) !!}
                  </div>
                  <div class="input-field col m6 s12">
                    {!! Form::select('state_id', [] , '' , ['id' => 'state_id' ,'class' => 'select2 browser-default','placeholder'=>'Please select state']) !!} 
                  </div>
                </div>
                <div class="row billing-address-section" style="display:none;">
                  <div class="input-field col m12 s12">
                  {!! Form::textarea('address', '', ['class' => 'materialize-textarea', 'placeholder'=>'Address','rows'=>3]) !!}
                  </div>
                  <div class="input-field col m6 s12">
                  </div>
                </div> 
                <div class="row">
                  <div class="input-field col m6 s12">
                    <select class="select2 browser-default" name="service_type" id="service_type" onchange="$('#usedServicesDiv').hide();">
                      <option selected="selected">Please select type</option>
                      <option value="1">Services</option>
                      <option value="2">Packages</option>
                    </select> 
                  </div>
                  <div class="input-field col m6 s12">
                    <div id="services_block">
                      <select class="select2 browser-default service-type" data-type="services" name="bill_item[]" id="services" multiple="multiple"> </select>
                    </div>
                    <div id="packages_block" style="display:none;">
                      <select class="select2 browser-default service-type" data-type="packages" name="bill_item[]" id="packages" multiple="multiple"> </select>
                    </div>
                  </div>
                </div>
                <div class="row" id="usedServicesDiv" style="display:none">
                  <div class="input-field col s12">
                    <table class="responsive-table" id="servicesTable">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Name</th>
                          <th>SAC Code</th>
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>                         
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="row">
                  <div class="input-field col s12">
                    <button class="btn waves-effect waves-light" type="reset" name="reset">Reset <i class="material-icons right">refresh</i></button>
                    <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="continue-btn">Continue <i class="material-icons right">keyboard_arrow_right</i></button>
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
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<!-- typeahead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

<!-- date-time-picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>

var timePicker  = {!! json_encode($variants->time_picker) !!};
var timeFormat  = {!! json_encode($variants->time_format) !!};
var path        = "{{ route('customers.autocomplete') }}";
var timeout;

$('#country_id').select2({ placeholder: "Please select country", allowClear: true });
$('#state_id').select2({ placeholder: "Please select state", allowClear: true });
$('#district_id').select2({ placeholder: "Please select district", allowClear: true });
$('#service_type').select2({ placeholder: "Please select type"});
$('#services').select2({ placeholder: "Please select service", allowClear: true });
$('#packages').select2({ placeholder: "Please select package", allowClear: true });

$('input[name="billed_date"]').daterangepicker({
  singleDatePicker: true,
  startDate: new Date(),
  showDropdowns: true,
  autoApply: true,
  timePicker: true,
  timePicker24Hour: timePicker,
  locale: { format: 'DD-MM-YYYY '+timeFormat+':mm A' },
}, function(ev, picker) {
  // console.log(picker.format('DD-MM-YYYY'));
});

$('input[name="checkin_time"]').daterangepicker({
  singleDatePicker: true,
  startDate: new Date(),
  showDropdowns: true,
  autoApply: true,
  timePicker: true,
  timePicker24Hour: timePicker,
  locale: { format: 'DD-MM-YYYY '+timeFormat+':mm A' },
}, function(ev, picker) {
  // console.log(picker.format('DD-MM-YYYY'));
});

$('input[name="checkout_time"]').daterangepicker({
  singleDatePicker: true,
  startDate: new Date(),
  showDropdowns: true,
  autoApply: true,
  timePicker: true,
  timePicker24Hour: timePicker,
  locale: { format: 'DD-MM-YYYY '+timeFormat+':mm A' },
}, function(start, end, label) {
  // console.log(picker.format('DD-MM-YYYY'));
  // var years = moment().diff(start, 'years');
  var in_time   = $('input[name="checkin_time"]').val();
  var out_time  = $('input[name="checkout_time"]').val();
  var diff      = moment().diff(in_time, out_time);
  
});

$('input[name="dob"]').daterangepicker({
  singleDatePicker: true,
  showDropdowns: true,
  minYear: 1901,
  drops: "up",
  maxYear: parseInt(moment().format('YYYY'),10),
  autoApply: true,
}, function(ev, picker) {
    console.log(picker.format('DD-MM-YYYY'));
});

$('#billing_address_checkbox').change(function() {
  if($(this).is(":unchecked")) 
    $('.billing-address-section').show();
  else
    $('.billing-address-section').hide();         
});

// function getCustomerDetails(customer_id){
//   $.ajax({ type: 'POST', url: "{{ url(ROUTE_PREFIX.'/common/get-customer-details') }}", dataType: 'json', data: { customer_id:customer_id}, delay: 250,
//     success: function(data) {
//       var customerMobile = '';
//       if (data.data.mobile != null) {
//         customerMobile = ' - ' + data.data.mobile;
//       }
//       $("#search_customer").val(data.data.name + customerMobile );
//       $("#customer_name").val(data.data.name);
//       $("#customer_mobile").val(data.data.mobile);
//       $("#customer_email").val(data.data.email);
//       $("#customer_id").val(customer_id);
//       $("#newCustomerBtn").hide();
//       $("#customer_details_div").show();
//       var customerViewURL = "{{ url(ROUTE_PREFIX.'/customers/view-details/') }}/"+customer_id;
//       $("#customerViewLink").attr("href", customerViewURL);
//       $("#customerActionDiv").show();
//     }
//   });
// }

$('.service-type').select2({ placeholder: "Please select ", allowClear: false }).on('select2:select select2:unselect', function (e) { 
  var type = $(this).data("type");
  listItemDetails(type) 
  $(this).valid()
});

function listItemDetails(type){
  var data_ids = $('#'+type).val();
  if(data_ids != '') {
    $.ajax({ type: 'post', url: "{{ url(ROUTE_PREFIX.'/common/list-service-with-tax') }}", dataType: 'json',data: { data_ids:data_ids, type : type},delay: 250,
      success: function(data) {
        $("#servicesTable").find("tr:gt(0)").remove();
        $('#servicesTable').append(data.html);
        $('#grandTotal').text(data.grand_total);
        $('#grand_total').val(data.grand_total);
        $('#usedServicesDiv').show();
      }
    });
  } else {
    $('#usedServicesDiv').hide();
  }
}

function getServices(){
  //url(ROUTE_PREFIX.'/common/get-all-services')
  $.ajax({ type: 'GET', url: "{{ route('get_all_services') }}", dataType: 'json', delay: 250,
    success: function(data) {
      var selectTerms = '<option value="">Please select service</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });
      var select = $('#services');
      select.empty().append(selectTerms);
    }
  });
}

function getPackages(){
  $.ajax({ type: 'GET', url: "{{ url(ROUTE_PREFIX.'/common/get-all-packages') }}", dataType: 'json', delay: 250,
    success: function(data) {
      var selectTerms = '<option value="">Please choose packages</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });

      var select = $('#packages');
      select.empty().append(selectTerms);
    }
  });
}

$(document).on('change', '#service_type', function () {
  if ( this.value == 1 ) {
    $("#services_block").show();
    $("#packages_block").hide();
    getServices();
  } else {
    $("#services_block").hide();
    $("#packages_block").show();
    getPackages();
  }
});

$(document).on('change', '#country_id', function () {
  $.ajax({ type: 'POST', url: "{{ url(ROUTE_PREFIX.'/common/get-states-by-country') }}", data:{'country_id':this.value }, dataType: 'json',
    success: function(data) {
      var selectTerms = '<option value="">Please select state</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });
      var select = $('#state_id');
      select.empty().append(selectTerms);
      $('#district_id').empty().trigger("change");
    }
  });
});

$(document).on('change', '#state_id', function () {
  $.ajax({ type: 'POST', url: "{{ url(ROUTE_PREFIX.'/common/get-districts-by-state') }}", data:{'state_id':this.value }, dataType: 'json',
    success: function(data) {
      var selectTerms = '<option value="">Please select district</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });
      var select = $('#district_id');
      select.empty().append(selectTerms);
    }
  });
});

if ($("#customerBillingForm").length > 0) {
  var validator = $("#customerBillingForm").validate({ 
    rules: {
      "bill_item[]": {
        required: true,
      },
      "roles[]": {
        required: true,
      },
    },
    messages: { 
      customer_name: {
        required: "Please select a customer",
      },
      search_customer: {
          required: "Please select a customer",
      },
      "bill_item[]": {
        required: "Please select an item",
      },
      "roles[]": {
        required: "Please choose role",
      },
    },
    submitHandler: function (form) {
        // var forms = $("#{{$page->entity}}Form");
        // $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}", type: "POST", processData: false, 
        // data: forms.serialize(), dataType: "html",
        // }).done(function (a) {
        //     var data = JSON.parse(a);
        //     if(data.flagError == false){
        //         showSuccessToaster(data.message);
        //         // setTimeout(function () { 
        //         //   window.location.href = "{{ url(ROUTE_PREFIX.'/'.$page->route) }}";                
        //         // }, 2000);

        //     }else{
        //       showErrorToaster(data.message);
        //       printErrorMsg(data.error);
        //     }
        // });
        $('#continue-btn').html('Please Wait...');
        $("#continue-btn"). attr("disabled", true);
        form.submit();
    },
    errorPlacement: function(error, element) {
      if (element.is("select")) {
          error.insertAfter(element.next('.select2'));
      }else {
          error.insertAfter(element);
      }
    }
  })
} 

jQuery.validator.addMethod("lettersonly", function (value, element) {
  return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
}, "Letters only please");

</script>
@endpush

