@extends('layouts.app')

@section('content')

@section('breadcrumb')
  <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
  <li class="nav-item d-none d-sm-inline-block"><a href="{{ url(ROUTE_PREFIX.'/home') }}" class="nav-link">Home</a></li>
  <li class="nav-item d-none d-sm-inline-block"><a href="{{ url(ROUTE_PREFIX.'/users') }}" class="nav-link">{{ $page->title ?? ''}}</a></li>
@endsection
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">{{ $page->title ?? ''}}</h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- SELECT2 EXAMPLE -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">{{ $page->title ?? ''}} Form</h3>


          
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
          <!-- /.card-header -->
          <div class="card-body">

          <div class="alert alert-danger print-error-msg" style="display:none">

          <ul></ul>

          </div>
              {!! Form::open(['url' => $page->form_url, 'method' => $page->form_method, 'class'=>'ajax-submit','id'=>'changepasswordForm']) !!}
                  <div class="card-body">
                      <div class="form-group row">
                          {!! Form::label('old_password', 'Old Password*', ['class' => 'col-sm-2 col-form-label text-alert']) !!}
                          <div class="col-sm-4">
                              {!! Form::password('old_password',  ['class' => 'form-control', 'placeholder'=>'Old Password']) !!}
                              <div class="error" id="old_password_error"></div>
                          </div>
                      </div>
                      <div class="form-group row">
                          {!! Form::label('new_password', 'Old Password*', ['class' => 'col-sm-2 col-form-label text-alert']) !!}
                          <div class="col-sm-4">
                              {!! Form::password('new_password',  ['class' => 'form-control',  'id' => 'new_password',  'placeholder'=>'New Password']) !!}
                              <div class="error" id="new_password_error"></div>
                          </div>
                      </div>
                      <div class="form-group row">
                          {!! Form::label('new_password_confirmation', 'Confirm Password*', ['class' => 'col-sm-2 col-form-label text-alert']) !!}
                          <div class="col-sm-4">
                              {!! Form::password('new_password_confirmation', ['class' => 'form-control','placeholder'=>'Confirm Password']) !!}
                              <div class="error" id="new_password_confirmation_error"></div>
                          </div>
                      </div>

                  </div>
                  <div class="card-footer">
                      <button type="submit" class="btn btn-primary mr-2">Submit
                      </button>
                      <button type="reset" class="btn btn-secondary">Cancel</button>
                  </div>
              {!! Form::close() !!}
              <!--end::Form-->             

          </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
    <!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@endsection
@push('page-scripts')
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<script>

if ($("#changepasswordForm").length > 0) {
    var validator = $("#changepasswordForm").validate({ 
        rules: {
            old_password1: {
                required: true,
            },
            new_password1: {
                required: true,
                // minlength: 6,
                // maxlength: 10,
            },
            new_password_confirmation1: {
                equalTo: "#new_password"
            },
        },
        messages: { 
            old_password: {
                required: "Please enter password",
            },
            new_password: {
                required: "Please enter password",
                // minlength: "Passwords must be at least 6 characters in length",
                // maxlength: "Length cannot be more than 10 characters",
            },
            new_password_confirmation: {
                equalTo: "Passwords are not matching",
            }
        },
        submitHandler: function (form) {
            var forms   = $("#changepasswordForm");
            $.ajax({ url: "{{ url('change-password') }}", type: 'POST', processData: false, 
            data: forms.serialize(), dataType: "html",
            }).done(function (a) {
                // var data = JSON.parse(a);
                // if(data.flagError == false){
                //     showSuccessToaster(data.message);
                //     setTimeout(function () { 
                //         window.location.href = "{{ url('admin/stores')}}";                    
                //     }, 3000);

                // }else{
                //   showErrorToaster(data.message);
                //   printErrorMsg(data.error);
                // }
            });
        }
    })
}
</script>
@endpush

