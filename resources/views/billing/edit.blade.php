@extends('layouts.app')

@section('content')
@push('page-css')
  <!-- daterange picker -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" />
  <style>
    .row {
      margin:15px 0;
    }
  </style>
@endpush

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
      <div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">{{ $page->title ?? ''}}</h1></div></div>
    </div>
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
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
          </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <div class="alert alert-danger print-error-msg" style="display:none"><ul></ul></div>
          <form id="{{$page->entity}}Form" name="{{$page->entity}}Form" role="form" method="" action="" class="ajax-submit">
            {{ csrf_field() }}
            {!! Form::hidden('customer_id', $customer->id ?? '' , ['id' => 'customer_id'] ); !!}
            <div class=""> 
              <div class="form-group ">
                {!! Form::label('name', 'Customer Name*', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                {!! Form::text('name', $customer->name ?? '' , array('placeholder' => 'Customer Name','class' => 'col-sm-6 form-control')) !!}                        
              </div> 
              <div class="form-group">
                {!! Form::label('name', 'Gender', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
              </div>
              <div class="form-group">
                <input type="radio" value="1" id="male" name="gender" @if($customer->gender == 1) checked @endif>
                <label for="male">Male</label>
                <input type="radio" value="2" id="female" name="gender" @if($customer->gender == 2) checked @endif>
                <label for="female">Female</label>
                <input type="radio" value="3" id="others" name="gender" @if($customer->gender == 3) checked @endif>
                <label for="others">Others</label>
              </div> 
              <div class="form-group col-xl-6 col-lg-6">
                <label class="col-xl-4 col-lg-4 col-form-label font-weight-bolder">Start Date</label>
                <div class='col-lg-4 col-xl-6 input-group date' id='customerdob'>
                  <input type='text' name="dob" id="dob" onkeydown="return false" class="form-control" autocomplete="off" />
                  <div class="input-group-append">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
              </div>
              <!-- <div class="form-group ">
                  {!! Form::label('billing_name', 'Billing Name*', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                  {!! Form::text('billing_name', $customer->billing_name ?? '' , array('placeholder' => 'Billing Name','class' => 'col-sm-6 form-control')) !!}                        
              </div> -->
              <div class="form-group ">
                {!! Form::label('mobile', 'Mobile', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                {!! Form::text('mobile', $customer->mobile ?? '' , array('placeholder' => 'Mobile','class' => 'col-sm-6 form-control check_numeric')) !!}                        
              </div>
              <div class="form-group ">
                {!! Form::label('email', 'E mail ', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                {!! Form::text('email', $customer->email ?? '' , array('placeholder' => 'E mail','class' => 'col-sm-6 form-control')) !!}                        
              </div>
              <div class="row">
                <div class="form-group">
                  {!! Form::label('country_id', 'country*', ['class' => '']) !!} <br>
                  {!! Form::select('country_id', $variants->country , $variants->country_id ?? '' , ['id' => 'country_id' ,'class' => 'form-control','placeholder'=>'Select A Country']) !!}
                </div>
                <div class="form-group">
                  {!! Form::label('state_id', 'State*', ['class' => '']) !!} <br>
                  <div id="state_block">
                    @if($customer->district_id)
                      {!! Form::select('state_id', $variants->states , $variants->state_id ?? '' , ['id' => 'state_id' ,'class' => 'form-control','placeholder'=>'Select a state']) !!}
                    @else
                      {!! Form::select('state_id', [] , '' , ['id' => 'state_id' ,'class' => 'form-control','placeholder'=>'Select a state']) !!}
                    @endif
                  </div>
                </div>
                <div class="form-group">
                  {!! Form::label('state_id', 'District*', ['class' => '']) !!} <br>
                  <div id="district_block">
                  @if($customer->district_id)
                    {!! Form::select('district_id', $variants->districts , $customer->district_id ?? '' , ['id' => 'district_id' ,'class' => 'form-control','placeholder'=>'Select a district']) !!}
                  @else  
                    {!! Form::select('district_id', [] , '' , ['id' => 'district_id' ,'class' => 'form-control','placeholder'=>'Select a district']) !!}
                  @endif
                  </div>
                </div>
              </div>
              <div class="form-group ">
                {!! Form::label('pincode', 'Pincode ', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                {!! Form::text('pincode', $customer->pincode ?? '' , array('placeholder' => 'Pincode','class' => 'col-sm-6 form-control check_numeric')) !!}                        
              </div>
              <div class="form-group ">
                {!! Form::label('gst', 'GST No. ', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                {!! Form::text('gst', $customer->gst ?? '' , array('placeholder' => 'GST No.','class' => 'col-sm-6 form-control')) !!}                        
              </div>
              <div class="form-group ">
                {!! Form::label('address', 'Address. ', ['class' => 'col-sm-6 col-form-label text-alert']) !!}
                {!! Form::textarea('address', $customer->address ?? '', ['class' => 'form-control col-sm-6','placeholder'=>'Address','rows'=>3]) !!}                       
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <a href="#" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-success ajax-submit">Submit</button>
              </div>
            </div>
          </form>              
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<!-- date-time-picker -->
<!-- <script src="{{ asset('admin/plugins/datetimepicker/js/bootstrap-datetimepicker.js') }}"></script> -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">

$('#customerdob').datepicker({
  format: 'dd-mm-yyyy',
  todayHighlight: true,
  autoclose: true
});

var dob = "{{ date('d-m-yy', strtotime($customer->dob)) }}";
$('#customerdob').datepicker('setDate', dob).datepicker('fill');

if ($("#{{$page->entity}}Form").length > 0) {
  var validator = $("#{{$page->entity}}Form").validate({ 
    rules: {
      name: {
        required: true,
        maxlength: 200,
        lettersonly: true,
      },
      mobile:{
        required:true,
        minlength:10,
        maxlength:10
      },
    },
    messages: { 
      name: {
        required: "Please enter customer name",
        maxlength: "Length cannot be more than 200 characters",
      },
      mobile: {
        required: "Please enter mobile number",
        maxlength: "Length cannot be more than 10 numbers",
        minlength: "Length must be 10 numbers",
      },
    },
    submitHandler: function (form) {
      id = $("#customer_id").val();
      customer_id      = "" == id ? "" : "/" + id;
      formMethod  = "" == id ? "POST" : "PUT";
      var forms = $("#{{$page->entity}}Form");
      $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}" + customer_id, type: formMethod, processData: false, 
      data: forms.serialize(), dataType: "html",
      }).done(function (a) {
        var data = JSON.parse(a);
        if(data.flagError == false) {
          showSuccessToaster(data.message);
          setTimeout(function () { 
            window.location.href = "{{ url(ROUTE_PREFIX.'/'.$page->route) }}";                
          }, 2000);
        } else {
          showErrorToaster(data.message);
          printErrorMsg(data.error);
        }
      });
    },
  })
} 

jQuery.validator.addMethod("lettersonly", function (value, element) {
  return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
}, "Letters only please");

$("body").on("submit", ".ajax-submit", function (e) {
  e.preventDefault();         
});

$(document).on('change', '#country_id', functiserviceson () {
  $.ajax({ url: "{{ url(ROUTE_PREFIX.'/common/get-states') }}/", type: "GET", data:{'country_id':this.value }, dataType: "html"
  }).done(function (data) {
    console.log(data);
    $("#state_block").html(data);
  })
});

$(document).on('change', '#state_id', function () {
  $.ajax({ url: "{{ url(ROUTE_PREFIX.'/common/get-districts') }}/", type: "GET",  data:{'state_id':this.value }, dataType: "html"
  }).done(function (data) {
    console.log(data);
    $("#district_block").html(data);
  })
});

</script>
@endpush
