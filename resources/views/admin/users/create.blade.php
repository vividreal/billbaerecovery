@extends('layouts.admin.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/flag-icon/css/flag-icon.min.css')}}">
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/toastr/toastr.min.css') }}"> -->
@endsection

@section('page-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-users.css')}}">
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/stores') }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Create</li>
  </ol>
@endsection

@section('page-action')
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Add<i class="material-icons right">business</i></a>
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List<i class="material-icons right">list</i></a>
@endsection

<div class="section">
 
  <!--Basic Form-->
  <div class="row">
    <!-- Form Advance -->
    <div class="col s12 m12 l12">
      <div id="Form-advance" class="card card card-default scrollspy">
        <div class="card-content">
          @include('layouts.success') 
          @include('layouts.error')
          <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
            <form id="{{$page->entity}}Form" name="{{$page->entity}}Form" role="form" method="" action="" class="ajax-submit">
                {{ csrf_field() }}
                {!! Form::hidden('user_id', $user->id ?? '' , ['id' => 'user_id'] ); !!}
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('shop_name', $user->shop->name ?? '', array('id' => 'shop_name')) !!}  
                  <label for="shop_name" class="label-placeholder active">Store Name <span class="red-text">*</span></label>
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::select('business_type', $variants->business_types , $user->shop->business_type_id ?? '' , ['id' => 'business_type' ,'class' => 'select2 browser-default', 'placeholder'=>'Please select business type']) !!}
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('name', $user->name ?? '', array('id' => 'name')) !!}  
                  <label for="name" class="label-placeholder active">Admin Name <span class="red-text">*</span></label>
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::text('email', $user->email ?? '', array('id' => 'email', 'autocomplete' => 'off')) !!}  
                  <label for="email" class="label-placeholder active">Email <span class="red-text">*</span></label>
                </div>
              </div>
              <div class="row">
                <div class="col s2">
                  <div class="input-field">  
                  @php $phonecode = (isset($user->phone_code) ? $user->phone_code : 101) @endphp    
                  {!! Form::select('phone_code', $variants->phonecode , $phonecode, ['id' => 'phone_code']) !!}
                  <label for="mobile" class="label-placeholder active">Phone code <span class="red-text">*</span></label>
                  </div>
                </div>
                <div class="input-field col m4 s12">
                  {!! Form::text('mobile', $user->mobile ?? '', array('id' => 'mobile', 'class' => 'check_numeric')) !!}
                  <label for="mobile" class="label-placeholder active">Mobile <span class="red-text">*</span></label>
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::select('roles[]', $variants->roles, $userRole ??  [] , array('id' =>'roles' , 'class' => 'select2 browser-default', 'multiple' => 'multiple', 'placeholder'=>'Please select roles')) !!}
                  <!-- <label for="roles" class="label-placeholder active">Role <span class="red-text">*</span></label> -->
                </div>
              </div>  
              <div class="row">
                <div class="input-field col s12">
                  <button class="btn waves-effect waves-light" type="button" name="reset" id="reset-btn">Reset <i class="material-icons right">refresh</i></button>
                  <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
                </div>
              </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@include('customer.import-browse-modal')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
<script src="{{asset('admin/vendors/toastr/toastr.min.js')}}"></script>
@endsection

@push('page-scripts')
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script>

$('#business_type').select2({ placeholder: "Please select business type", allowClear: false });
$('#roles').select2({ placeholder: "Please select roles", allowClear: true });

if ($("#{{$page->entity}}Form").length > 0) {
  var validator = $("#{{$page->entity}}Form").validate({ 
    rules: {
      shop_name: { 
        required: true, 
        maxlength: 200 
      }, 
      email: { 
        required: true,  
        email: true, 
        emailFormat:true,
        remote: { url: "{{route('isUniqueEmail')}}", type: "POST",
              data: {
                user_id: function () {
                  return $('#user_id').val();
                }
              }
        },
      },
      name: {
        required: true, 
        maxlength: 200
      },
      business_type: {
        required: true
      },
      mobile: {
          required: true,
          minlength: 3,
          maxlength: 15,
          digits:true
      },
      "roles[]": {
        required: true, 
      },
    },
    messages: { 
      shop_name: {
        required: "Please enter store name",
        maxlength: "Length cannot be more than 200 characters",
      },
      name: {
        required: "Please enter admin name",
        maxlength: "Length cannot be more than 200 characters",
      },
      business_type: {
        required: "Please select business type ",
      },
      email: {
        required: "Please enter email",
        email: "Please enter a valid email address.",
        remote: "Email already existing"
      },
      mobile: {
        required: "Please enter mobile number",
        maxlength: "Length cannot be more than 15 numbers",
        minlength: "Length must be 3 numbers",
        digits: "Please enter a valid mobile number",
      },
      "roles[]": {
        required: "Please choose role",
      },
      password: {
        required: "Please enter password",
        // minlength: "Passwords must be at least 6 characters in length",
        // maxlength: "Length cannot be more than 10 characters",
      },
      password_confirmation: {
        equalTo: "Passwords are not matching",
      },
      department_id: {
        required: "Please choose department"
      },
    },
    submitHandler: function (form) {
      $('#submit-btn').html('Please Wait...');
      $("#submit-btn"). attr("disabled", true);
      id = $("#user_id").val();
      userId      = "" == id ? "" : "/" + id;
      formMethod  = "" == id ? "POST" : "PUT";
      var forms   = $("#{{$page->entity}}Form");
      $.ajax({ url: "{{ url('admin/stores') }}" + userId, type: formMethod, processData: false, data: forms.serialize(), dataType: "html",
      }).done(function (a) {
        var data = JSON.parse(a);
        if (data.flagError == false) {
          showSuccessToaster(data.message);
          setTimeout(function () { 
            window.location.href = "{{ url('admin/stores')}}";                    
          }, 3000);
        } else {
        
          showErrorToaster(data.message);
          printErrorMsg(data.error);
        }
      });
    }
  })
} 

jQuery.validator.addMethod("lettersonly", function (value, element) {
  return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
}, "Letters only please");

jQuery.validator.addMethod("emailFormat", function (value, element) {
  return this.optional(element) || /[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/igm.test(value);
}, "Please enter a valid email address"); 
</script>
@endpush