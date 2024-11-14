@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/select2/select2.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/select2/select2-materialize.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

{{-- page style --}}
@section('page-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-users.css')}}">
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/store/billing-series') }}">{{ $page->title ?? ''}}</a></li>
    <li class="breadcrumb-item active">Update</li>
  </ol>
@endsection

<!-- users edit start -->
<div class="section users-edit">
 
  <div class="card">
    <div class="card-content">
      <div class="row">
        @if($store)
          <div class="col s12" id="account">
            <!-- users edit account form start -->
            <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
              <form id="billFormatForm" name="billFormatForm" role="form" method="" action="" class="ajax-submit">
                {{ csrf_field() }}
                {!! Form::hidden('bill_format_id', $variants->billing_formats->id??'' , ['id' => 'bill_format_id'] ); !!}
                <div class="row">
                  <div class="input-field col m6 s12">                      
                    {!! Form::text('bill_prefix', $variants->billing_formats->prefix ?? '') !!} 
                    <label for="bill_prefix" class="label-placeholder active">Starts with: <span class="red-text">*</span></label>
                  </div>
                  <div class="input-field col m6 s12">                      
                    {!! Form::text('bill_suffix', $variants->billing_formats->suffix ?? '') !!} 
                    <label for="bill_suffix" class="label-placeholder active">Starts From: <span class="red-text">*</span></label>
                  </div>
                 
                </div>
                <div class="col m6 s12">
                  <a href="{{asset('/images/protone file.png')}}" target="_blank">Sample</a>
                </div>

                @isset($variants->billing_formats)
                <div class="col s12 m6">
                  <!-- <label for="applied_to_all">Use same format for all bills</label> -->
                  <p><label>
                      <input class="validate" name="applied_to_all" id="applied_to_all" type="checkbox" @if($variants->billing_formats->applied_to_all == 0) checked="checked" @endif>
                      <span>Use same format for all bills.</span>
                    </label> </p>
                  <div class="input-field">
                  </div>
                </div>
                <div class="col s12 payment-types-section" @if($variants->billing_formats->applied_to_all == 0) style="display:none;" @endif>
                  <table class="highlight">
                    <tbody>
                      @foreach($variants->payment_types as $type)
                        @php $checked='';  $prefix=''; $suffix='';   
                          if ($variants->billing_formats_all->contains('payment_type', $type->id)) {
                            $checked="checked";
                            $prefix= $variants->billing_formats_all->where('payment_type', $type->id)->pluck('prefix')->first();
                            $suffix= $variants->billing_formats_all->where('payment_type', $type->id)->pluck('suffix')->first();
                          }
                        @endphp 
                        <tr>
                          <td style="align:right">
                            <!-- <input class="payment-types" type="checkbox" name="payment_types[]" data-type="{{$type->id}}" id="payment_types_{{$type->id}}" {{$checked}} value="{{$type->id}}"> -->
                            <p class="mb-1"><label><input type="checkbox" class="payment-types" name="payment_types[]" data-type="{{$type->id}}" id="payment_types_{{$type->id}}" {{$checked}} value="{{$type->id}}"><span></span></label></p>
                          </td>
                          <td style="width:20%">                
                            {{$type->name}} </td>
                          <td style="align:right">
                            <input placeholder="{{$type->name}} Prefix starts with" id="bill_prefix_{{$type->id}}" class="form-control"  name="bill_prefix_type[{{$type->id}}]"  type="text" value="{{$prefix}}" >
                            <label id="bill_prefix-error_{{$type->id}}" class="red-text error"></label>
                          </td>
                          <td style="align:right"><input placeholder="{{$type->name}} Prefix Starts from" id="bill_suffix_{{$type->id}}" class="form-control check_numeric" name="bill_suffix_type[{{$type->id}}]" type="text" value="{{$suffix}}" ></td>
                        <tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                @endisset
              <div class="row">
                  <div class="input-field col s12">
                  <button class="btn waves-effect waves-light" type="reset" name="reset">Reset <i class="material-icons right">refresh</i></button>
                  <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
                </div>
              </div>
            </form>
            <!-- users edit account form ends -->
          </div>
        @endif
      </div>
      <!-- </div> -->
    </div>
  </div>
</div>
<!-- users edit ends -->
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
<script src="{{asset('admin/vendors/select2/select2.full.min.js')}}"></script>
@endsection

@push('page-scripts')
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<!-- date-time-picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{asset('admin/js/scripts/page-users.js')}}"></script>
<script>
  $('#applied_to_all').change(function() {
    if ($(this).is(":unchecked")) 
      $('.payment-types-section').show();
    else
      $('.payment-types-section').hide();         
  });

  if ($("#billFormatForm").length > 0) {
    var validator = $("#billFormatForm").validate({ 
      rules: {
        bill_prefix: {
          required: true,
          maxlength: 5,
          lettersonly:true,
        }, 
        bill_suffix: {
          required: true,
          maxlength: 5,
          number: true,
        },
        "bill_prefix_type[]": {
          maxlength: 3,
          lettersonly:true,
        },
        "bill_suffix_type[]": {
          maxlength: 3,
          number: true,
        },
      },
      messages: { 
        bill_prefix: {
          required: "Please enter bill prefix",
          maxlength: "Length cannot be more than 5 characters",
        },
        bill_suffix: {
          required: "Please enter bill suffix",
          maxlength: "Length cannot be more than 5 digits",
          number: "Accept only numeric values",
        },
        "bill_prefix_type[]": {
          maxlength: "Length cannot be more than 3 characters",
        },
        "bill_suffix_type[]": {
          maxlength: "Length cannot be more than 5 characters",
          number: "Accept only numeric values",
        },
      },
      submitHandler: function (form) {
        formMethod    = "POST";
        var forms     = $("#billFormatForm");
        var isSubmit  = true;
        var regex     = /^[A-Za-z]+$/;
        if($("#applied_to_all").is(":not(:checked)")){
          $('input:checkbox.payment-types').each(function () {
            if (this.checked) {
              typeId = $(this).data("type");
              if ($('#bill_prefix_'+typeId).val() == '') {
                $("#bill_prefix-error_"+typeId).text("Please enter bill prefix");
                $("#bill_prefix-error_"+typeId).addClass("error");
                $("#bill_prefix-error_"+typeId).attr("style", "display:block");
                isSubmit = false;
              } else if ($('#bill_prefix_'+typeId).val().length > 5) {
                $("#bill_prefix-error_"+typeId).text("Length cannot be more than 5 characters");
                $("#bill_prefix-error_"+typeId).addClass("error");
                $("#bill_prefix-error_"+typeId).attr("style", "display:block");
                isSubmit = false;
              } else if (!$('#bill_prefix_'+typeId).val().match(regex)) {
                $("#bill_prefix-error_"+typeId).text("Letters only please");
                $("#bill_prefix-error_"+typeId).addClass("error");
                $("#bill_prefix-error_"+typeId).attr("style", "display:block");
                isSubmit = false;
              }
            }
          });
        } 
        if (isSubmit === true) {
          $.ajax({ url: "{{ url('/store/update/bill-format') }}", type: formMethod, processData: false, 
            data: forms.serialize(), dataType: "html",
          }).done(function (a) {
            var data = JSON.parse(a);
            if(data.flagError == false) {
              showSuccessToaster(data.message);
              $("#mainFormatID").html(data.bill_format);
              // $("#bill_prefix").val('');
              // $("#bill_suffix").val('');
            } else {
              showErrorToaster(data.message);
              printErrorMsg(data.error);
            }
          });
        }
      }
    })
  }

  jQuery.validator.addMethod("lettersonly", function (value, element) {
    return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
  }, "Letters only please");

</script>
@endpush