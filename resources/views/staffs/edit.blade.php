@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection


{{-- vendor styles --}}
@section('vendor-style')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>

<!-- Dropzone -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/min/dropzone.min.css">


@endsection

{{-- page style --}}
@section('page-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-account-settings.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-users.css')}}">
@endsection
@section('page-css')
<style type="text/css">
.preview {
  overflow: hidden;
  width: 160px; 
  height: 160px;
  border: 1px solid red;
}
</style>
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
 
  
  <!--Basic Form-->
  <div class="row">
    <!-- Form Advance -->
    <div class="col s12 m12 l12">
      <div id="Form-advance" class="card card card-default scrollspy">
        <div class="card-content">
            <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none"><div class="card-content red-text"><ul></ul></div></div>
            <div class="media display-flex align-items-center mb-2">
              @php
                $staff_profile = ($staff->profile != null) ? asset('storage/store/users/' . $staff->profile) : asset('admin/images/user-icon.png');
              @endphp
              <a class="mr-2" href="#">
                <img src="{{$staff_profile}}" class="border-radius-4" alt="profile image" id="user_profile" 
                  height="64" width="64">
              </a>
              <div class="media-body">
                <h5 class="media-heading mt-0">Photo</h5>
                <div class="user-edit-btns display-flex">
                  <button id="select-files" class="btn indigo mr-2">
                    <span>Browse</span>
                  </button>
                  <a href="#" class="btn-small btn-light-pink">Remove</a>
                </div>
                <small>Allowed JPG, JPEG or PNG. Max size of 800kB</small>
                <div class="upfilewrapper" style="display:none;">
                  <input id="profile" type="file" accept="image/png, image/gif, image/jpeg" name="image" class="image" />
                </div>
              </div>              
            </div>



            <form id="{{$page->entity}}Form" name="{{$page->entity}}Form" role="form" method="" action="" class="ajax-submit" >
              {{ csrf_field() }}
              {!! Form::hidden('user_id', $staff->id ?? '' , ['id' => 'user_id'] ); !!}
              <div class="row">
                <div class="input-field col m6 s12">
                    {!! Form::select('designation', $page->designations , $staff->staffProfile->designation ?? '' , ['id' => 'designation' ,'class' => 'select2 browser-default','placeholder'=>'Please select designation']) !!}
                </div>
                <div class="input-field col m6 s12">
                    {!! Form::text('name', $staff->name ?? '',  ['id' => 'name']) !!}  
                    <label for="name" class="label-placeholder">Name <span class="red-text">*</span></label>
                   
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                    {!! Form::text('email', $staff->email ?? '', array('id' => 'email', 'autocomplete' => 'off')) !!}  
                    <label for="email" class="label-placeholder">Email <span class="red-text">*</span></label>
                </div>              
                <div class="input-field col m6 s12">                
                    {!! Form::text('mobile', $staff->mobile ?? '', array('id' => 'mobile', 'class' => 'check_numeric')) !!}
                  <label for="mobile" class="label-placeholder active">Mobile <span class="red-text">*</span></label>   
                </div>             
              </div>

              <div class="row">
                <div class="input-field col m6 s12">                
                    {!! Form::select('roles[]', $roles , $userRole ?? [] , ['id' => 'roles' ,'class' => 'select2 browser-default', 'multiple' => 'multiple' ]) !!}
                    <label for="roles" class="label-placeholder active">Role <span class="red-text">*</span></label>
                </div>              
               
                <div class="input-field col m6 s12">
                    <input type='text' name="dob" id="dob" onkeydown="return false" class="" autocomplete="off" />
                    <label for="dob" class="label-placeholder active">DOB </label> 
                </div> 

              </div>

              
              <div class="row">
                <div class="input-field col m6 s12">
                  <input type='text' name="joining_date" id="joining_date" onkeydown="return false" class="" autocomplete="off" />
                  <label for="joining_date" class="label-placeholder active">Joining Date </label>  
                </div>

                <div class="input-field col m6 s12">
                  <input type='text' name="contract_end_date" id="contract_end_date" onkeydown="return false" class="" autocomplete="off" />
                  <label for="contract_end_date" class="label-placeholder active">Contract End Date </label>  
                </div>
              </div>

              <div class="row">  
                  
                <div class="input-field col m6 s12">    
                    <p>  
                    <label>
                      <input value="1" id="male" name="gender" type="radio" @if($staff->gender == 1) checked @endif/>
                      <span> Male </span>
                    </label>             
                    <label>
                      <input value="2" id="female" name="gender" type="radio" @if($staff->gender == 2) checked @endif/>
                      <span> Female </span>
                    </label>     
                    <label>
                      <input value="3" id="others" name="gender" type="radio" @if($staff->gender == 3) checked @endif/>
                      <span> Others </span>
                    </label>                  
                  </p>
                  <!-- <label for="gender" class="label-placeholder">Gender </label> -->
                </div>               
              </div>

              <!-- <div class="row">
                <div class="input-field col m6 s12">
                    {!! Form::select('schedule_color', $page->schedule_colors , $staff->staffProfile->schedule_color ?? '' , ['id' => 'schedule_color' ,'class' => 'select2 browser-default','placeholder'=>'Please select color']) !!}
                </div>
                <div class="input-field col m6 s12">                   
                </div>
              </div> -->


              <div class="row">
                <div class="input-field col s12">
                  <button class="btn waves-effect waves-light" type="reset" name="reset">Reset <i class="material-icons right">refresh</i></button>
                  <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
                </div>
              </div>
            </form>
            
            


            <!-- <div class="row">
                <div class="input-field col s12">
                    <form action="{{ url('dropzone.store') }}" method="post" enctype="multipart/form-data" id="id-proof-upload" class="dropzone">
                        @csrf
                        <h5 class="card-title"><span>Upload id proof(s)</span></h5>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <form action="{{ url('dropzone.store') }}" method="post" enctype="multipart/form-data" id="certificates-upload" class="dropzone">
                        @csrf
                        <h5 class="card-title"><span>Upload certificates/documents:</span></h5>
                    </form>
                </div>
            </div> -->
        </div>
      </div>
    </div>
  </div>
</div>

@include('layouts.crop-modal')

@endsection

{{-- vendor scripts --}}
@section('vendor-script')

@endsection


@push('page-scripts')
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<!-- date-time-picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
<script src="{{ asset('admin/js/cropper-script.js') }}"></script>

<!-- Dropzone -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/dropzone.js"></script>
<script>

Dropzone.options.dropzone =
{
    maxFilesize: 12,
    renameFile: function(file) {
        var dt = new Date();
        var time = dt.getTime();
        return time+file.name;
    },
    acceptedFiles: ".jpeg,.jpg,.png,.gif",
    addRemoveLinks: true,
    timeout: 5000,
    success: function(file, response) 
    {
        console.log(response);
    },
    error: function(file, response)
    {
        return false;
    }
};

$(document).ready(function(){
  $('input[name="dob"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    maxYear: parseInt(moment().format('YYYY'),10)
  }, function(start, end, label) {
    var years = moment().diff(start, 'years');
  });

  $('input[name="contract_end_date"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    // maxYear: parseInt(moment().format('YYYY'),10)
  }, function(start, end, label) {
    // var years = moment().diff(start, 'years');
  });

  $('input[name="joining_date"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    // maxYear: parseInt(moment().format('YYYY'),10)
  }, function(start, end, label) {
    // var years = moment().diff(start, 'years');
  });
});

$('#roles').select2({ placeholder: "Please select role", allowClear: true });
$('#designation').select2({ placeholder: "Please select designation", allowClear: true });
$('#schedule_color').select2({ placeholder: "Please select color", allowClear: true });

if ($("#{{$page->entity}}Form").length > 0) {
    var validator = $("#{{$page->entity}}Form").validate({ 
        rules: {
            name: {
              required: true,
              maxlength: 200,
              lettersonly: true,
            },
            // mobile:{
            //   required:true,
            //   minlength:10,
            //   maxlength:10
            // },
            "roles[]": {
                    required: true,
            },
            email: {
              required: true,
              email: true,
              remote: { url: "{{ url(ROUTE_PREFIX.'/common/is-unique-email') }}", type: "POST",
                  data: {
                      user_id: function () {
                          return $('#user_id').val();
                      }
                  }
              },
            },
        },
        messages: { 
            name: {
                required: "Please enter name",
                maxlength: "Length cannot be more than 200 characters",
                },
            mobile: {
                required: "Please enter mobile number",
                maxlength: "Length cannot be more than 10 numbers",
                minlength: "Length must be 10 numbers",
                },
            email: {
                required: "Please enter email",
                email: "Please enter a valid email address.",
                remote: "Email already existing"
            },
            "roles[]": {
                required: "Please choose role",
            },
        },
        submitHandler: function (form) {
            $('#submit-btn').html('Please Wait...');
            $("#submit-btn"). attr("disabled", true);
            id = $("#user_id").val();
            user_id      = "" == id ? "" : "/" + id;
            formMethod  = "" == id ? "POST" : "PUT";
            var forms = $("#{{$page->entity}}Form");
            $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}" + user_id, type: formMethod, processData: false, 
            data: forms.serialize(), dataType: "html",
            }).done(function (a) {
              $('#submit-btn').html('Submit <i class="material-icons right">send</i>');
              $("#submit-btn"). attr("disabled", false);
                var data = JSON.parse(a);
                if(data.flagError == false){
                    showSuccessToaster(data.message);
                    // setTimeout(function () { 
                    //   window.location.href = "{{ url(ROUTE_PREFIX.'/'.$page->route) }}";                
                    // }, 2000);

                }else{
                  showErrorToaster(data.message);
                  printErrorMsg(data.error);
                }
            });
        },
        errorPlacement: function(error, element) {
          if (element.is("select")) {
              error.insertAfter(element.next('.select2'));
          }else {
              error.insertAfter(element);
          }
        },
    })
}

jQuery.validator.addMethod("lettersonly", function (value, element) {
    return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
}, "Letters only please");

$("#profileImageSubmitBtn").click(function(){
    canvas = cropper.getCroppedCanvas({
    width: 60,
    height: 60,
  });

  canvas.toBlob(function(blob) {
    url = URL.createObjectURL(blob);
    var reader = new FileReader();
    reader.readAsDataURL(blob); 
    reader.onloadend = function() {
      var base64data = reader.result; 
      id = $("#user_id").val();
      $.ajax({
          type: "POST",
          dataType: "json",
          url: "{{ url(ROUTE_PREFIX.'/staffs/update/user-image') }}",
          data: {user_id : id , 'image': base64data},
          success: function(data){
            if(data.flagError == false){
                showSuccessToaster(data.message);                 
                $("#user_profile").attr("src", data.logo);
                $modal.modal('close');
            }else{
              showErrorToaster(data.message);
              printErrorMsg(data.error);
            }
        }
      });
    }
  });
})

</script>
@endpush

