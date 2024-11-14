@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')

@endsection

{{-- page style --}}
@section('page-style')
@endsection

@section('content')

@section('breadcrumb')
<h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
<ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('page-action')
<a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create {{ Str::singular($page->title) ?? ''}}<i class="material-icons right">add</i></a>
<a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List {{ Str::plural($page->title) ?? ''}}<i class="material-icons right">list</i></a>
@endsection


<div class="section">
  

    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>

                    <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                        <div class="card-content red-text">
                            <ul></ul>
                        </div>
                    </div>
                    {!! Form::open(['class'=>'ajax-submit','id'=> Str::camel($page->title).'Form']) !!}
                    {{ csrf_field() }}
                    {!! Form::hidden('package_id', $package->id ?? '' , ['id' => 'package_id'] ); !!}
                      {!! Form::hidden('currency', CURRENCY , ['id' => 'currency'] ); !!}
                           {!! Form::hidden('timePicker', '', ['id' => 'timePicker'] ); !!}
               {!! Form::hidden('timeFormat', '', ['id' => 'timeFormat'] ); !!}
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('name', $package->name ?? '', ['id' => 'name']) !!}
                            <label for="name" class="label-placeholder">Package Name <span class="red-text">*</span></label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('services[]', $variants->services, $service_ids, ['id' => 'services', 'multiple' => 'multiple', 'class' => 'select2 browser-default']) !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m12 s12">
                            <div class="form-group" id="usedServicesDiv" style="display:none;">
                                <h5 class="card-title">Services </h5>
                                <table class="table table-hover text-nowrap" id="servicesTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Hours</th>
                                            <th>price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input class="form-control check_numeric" type="text" name="price" id="price" value="{{ $package->price ?? ''}}" />
                            <input class="form-control" type="hidden" name="totalPrice" id="totalPrice" value="" />
                            <input class="form-control" type="hidden" name="discount" id="discount" value="" />
                            <label for="price" class="label-placeholder">Package Price <span class="red-text">*</span></label>
                        </div>
                         {{-- <div class="input-field col m6 s12">
                            {!! Form::text('hsn_code', $package->hsn_code ?? '', ['id' => 'hsn_code']) !!}
                            <label for="hsn_code" class="label-placeholder">SAC Code </label>
                        </div> --}}
                        {{-- <div class="input-field col m6 s12">
                            {!! Form::text('instore_credit_amount', $package->instore_credit_amount ?? '', ['id' => 'instore_credit_amount']) !!}
                            <label for="instore_credit_amount" class="label-placeholder">In-Store Credit Amount</label>
                        </div> --}}
                        {{-- <div class="input-field col m6 s12">
                            <select id="validity_mode" class="form-control" name="validity_mode">
                                <option @if($package->validity_mode == 1) selected="selected" @endif value="1">Day</option>
                                <option @if($package->validity_mode == 2) selected="selected" @endif value="2">Month</option>
                                <option @if($package->validity_mode == 3) selected="selected" @endif value="3">Year</option>
                            </select>
                            <label for="validity_mode" class="label-placeholder">Validity Type</label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::text('validity', $package->validity ?? '' , array( 'id' => 'validity','class' => 'check_numeric')) !!}
                            <label for="validity" class="label-placeholder">Validity No.</label>
                        </div> --}}
                    </div>

                    {{-- <div class="row">
                        <div class="input-field col m6 s12">
                            <div class="col s12">
                                @php
                                $checked = '';
                                if(isset($package)){
                                $checked = ($package->tax_included == 1) ? 'checked' : '' ;
                                }
                                @endphp
                                <label for="tax_included">Check if tax is included with price !</label>
                                <p><label><input class="custom-control-input" type="checkbox" name="tax_included" id="tax_included" value="1" {{ $checked }}>
                                        <span>Tax Included</span>
                                    </label> </p>
                                <div class="input-field">
                                </div>
                            </div>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('gst_tax', $variants->tax_percentage , $package->gst_tax ?? '' , ['id' => 'gst_tax', 'class' => 'select2 browser-default', 'placeholder'=>'Select GST Tax %']) !!}
                        </div>
                    </div> --}}
                    <div class="row">
                          {{-- <div class="input-field col m6 s12">
                <input type="text" name="validity_from" id="validity_from" class="form-control" onkeydown="return false" autocomplete="off" value="{{$package->validity_from}}" />
                <label for="validity_from" class="label-placeholder active">Validity Starting Date </label> 
              </div> --}}
                 {{-- <div class="input-field col m6 s12">
                <input type="text" name="validity_to" id="validity_to" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                <label for="validity_to" class="label-placeholder active">Validity Expiring Date </label> 
              </div> --}}
                {{-- <div class="input-field col m6 s12">
                            <select id="validity" class="form-control" name="validity">
                                <option @if($package->validity==2) selected @endif value="2">2 Days</option>
                                <option @if($package->validity==5) selected @endif value="5">5 Days</option>
                                <option @if($package->validity==7) selected @endif value="7">7 Days</option>
                                <option @if($package->validity==10) selected @endif value="10">10 Days</option>
                                <option @if($package->validity==15) selected @endif value="15">15 Days</option>
                                <option @if($package->validity==30) selected @endif value="30">30 Days</option>
                                <option @if($package->validity==60) selected @endif value="60">60 Days</option>
                                <option @if($package->validity==90) selected @endif value="90">90 Days</option>
                            </select>
                            <label for="validity" class="label-placeholder">Validity Period</label>
                        </div> --}}
                        {{-- <div class="input-field col m6 s12">
                            {!! Form::select('additional_tax[]', $variants->additional_tax, $variants->additional_tax_ids ?? [] , ['id' => 'additional_tax', 'multiple' => 'multiple' ,'class' => 'select2 browser-default']) !!}
                        </div> --}}
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <button class="btn waves-effect waves-light" type="button" id="reset-btn" name="reset">Reset <i class="material-icons right">refresh</i></button>
                            <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
                        </div>
                    </div>
                    </form>
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

        var service_ids = <?php echo json_encode($service_ids); ?>; 
        $('#additional_tax').select2({ placeholder: "Please choose Additional Tax", allowClear: false });
        $('#gst_tax').select2({ placeholder: "Please select GST Tax %", allowClear: true });
        $("#services").select2({ placeholder: "Please choose Services", allowClear: false })
        .on('select2:select select2:unselect', function (e) { loadData() });
        // getServices(service_ids);
        loadData()
    });

function loadData(){
 var service_ids = $('#services').val();
 var currency    = $("#currency").val();
 if(service_ids != ''){
   $.ajax({
       type: 'post',
       url: "{{ url(ROUTE_PREFIX.'/common/get-services') }}",
       dataType: 'json',
       data: { data_ids:service_ids},
       delay: 250,
       success: function(data) {
        if(data.data.length > 0){
           html = '';
           var cgst='';
          var sgst='';
          var serviceValue;
           $("#servicesTable").find("tr:gt(0)").remove();
           $.each(data.data, function(key, value) {
                if(value.tax_included==0){
                if(value.gsttax!==null){
                    var totalGstPercetage=value.gsttax.percentage;
                }else{
                    var totalGstPercetage=18;
                }
                serviceValue=value.price;
                cgst=sgst= (serviceValue *(totalGstPercetage/2)/100);
                serviceValue+=cgst+sgst;
                serviceValue=serviceValue.toFixed(2);
                }else{
                serviceValue =value.price;
                }
                html += '<tr><td>' + value.name + '(' + (value.tax_included=== 1? 'Tax Included:'+value.price : 'Tax Excluded:'+value.price) + ')' + '</td><td>' + value.hours.name + '</td><td>' + currency + ' ' + serviceValue + '</td></tr>';
            });
            $('#servicesTable').append('<tfoot><tr><td></td><td>Total</td><td>' + data.totalPrice + '</td></tr></tfoot>');

            $('#totalPrice').val(data.totalPrice);
            $("#price").prop("disabled", false);
            $('#servicesTable').append(html);
            $('#usedServicesDiv').show();
            calculateDiscount();
        }
        }
    });
    }
    else {
        $('#usedServicesDiv').hide();
        $('#totalPrice').val('');
        $('#price').val('');
        $('#discount').val('');
    }

}

    function calculateDiscount() {
        var total = $('#totalPrice').val();
        var price = $('#price').val();

        if (price != '') {
            var discount = parseFloat(total) - parseFloat(price);
            if (discount < 0) {
                showErrorToaster("Package price is greater than total price.");
            } else {
                $('#discount').val(discount);
            }
        }

    }

    function getServices(service_ids) {
        $.ajax({
            type: 'GET'
            , url: "{{ url(ROUTE_PREFIX.'/common/get-all-services') }}"
            , dataType: 'json',
            // data: { category_id:category_id},
            delay: 250
            , success: function(data) {
                var selectTerms = '<option value="">Please choose services</option>';
                $.each(data.data, function(key, value) {
                    selected = '';
                    if (jQuery.inArray(value.id, service_ids) != '-1') {
                        selected = 'selected';
                    }
                    selectTerms += '<option value="' + value.id + '" ' + selected + ' >' + value.name + '</option>';
                });

                var select = $('#services');
                select.empty().append(selectTerms);
                loadData();
            }
        });
    }

    $("#price").change(function() {
        calculateDiscount();
    });

    if ($("#{{Str::camel($page->title)}}Form").length > 0) {
        var validator = $("#{{Str::camel($page->title)}}Form").validate({
            rules: {
                name: {
                    required: true
                    , maxlength: 200
                , }
                , price: {
                    required: true
                , }
                , "services[]": {
                    required: true
                , }
            , }
            , messages: {
                name: {
                    required: "Please enter package name"
                    , maxlength: "Length cannot be more than 200 characters"
                , }
                , price: {
                    required: "Please enter package price"
                , }
                , "services[]": {
                    required: "Please choose services"
                , }
            , }
            , submitHandler: function(form) {
                disableBtn("submit-btn");
                id = $("#package_id").val();
                package_id = "" == id ? "" : "/" + id;
                formMethod = "" == id ? "POST" : "PUT";
                var forms = $("#{{Str::camel($page->title)}}Form");
                $.ajax({
                    url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}" + package_id
                    , type: formMethod
                    , processData: false
                    , data: forms.serialize()
                    , dataType: "html"
                , }).done(function(a) {
                    enableBtn("submit-btn");
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        setTimeout(function() {
                             window.location.href = "{{ url(ROUTE_PREFIX.'/'.$page->route) }}";                
                        }, 2000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                });
            }
        })
    }

    jQuery.validator.addMethod("lettersonly", function(value, element) {
        return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
    }, "Letters only please");

    $("#reset-btn").click(function(e) {
        validator.resetForm();
        $('#{{Str::camel($page->title)}}Form').find("input[type=text], textarea, radio").val("");
        $("#services").val('').trigger('change');
        $("#validity_mode").val('').trigger('change');
        $("#validity").val('').trigger('change');
        $("#gst_tax").val('').trigger('change');
        $("#additional_tax").val('').trigger('change');
        $('#tax_included').prop('checked', false);
        $('#usedServicesDiv').hide();
    });
var timePicker = $("#timePicker").val();
var timeFormat = $("#timeFormat").val();
$('input[name="validity_from"]').daterangepicker(
  {
      singleDatePicker: true,
      startDate: new Date(),
      showDropdowns: true,
      autoApply: true,
      timePicker: true,
      locale: { format: "DD-MM-YYYY " },
  },
  function (ev, picker) {
      // console.log(picker.format('DD-MM-YYYY'));
  }
);
$('input[name="validity_to"]').daterangepicker(
  {
      singleDatePicker: true,
      startDate: new Date(),
      showDropdowns: true,
      autoApply: true,
      timePicker: true,
      locale: { format: "DD-MM-YYYY " },
  },
  function (ev, picker) {
      // console.log(picker.format('DD-MM-YYYY'));
  }
);
</script>
@endpush
