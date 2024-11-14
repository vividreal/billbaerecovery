@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Create</li>
  </ol>
@endsection

@section('page-action')
  <a href="javascript:" class="btn waves-effect waves-light orange darken-4 breadcrumbs-btn" onclick="importBrowseModal()" >Bulk Upload<i class="material-icons right">attach_file</i></a>
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
          @include('layouts.success') 
          @include('layouts.error')
          <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
          {!! Form::open(['class'=>'ajax-submit','id'=> Str::camel($page->title).'Form']) !!} 
              {{ csrf_field() }}
              {!! Form::hidden('customer_id', $customer->id ?? '' , ['id' => 'customer_id'] ); !!}
              {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!} 
              {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('name', $customer->name ?? '', array('id' => 'name', 'autocomplete' => 'off')) !!}  
                  <label for="name" class="label-placeholder active">Customer Name <span class="red-text">*</span></label>
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::text('email', $customer->email ?? '', array('autocomplete' => 'off', 'id' => 'email')) !!}
                  <label for="email" class="label-placeholder active">Email</label>
                </div>
              </div>
              <div class="row">
                <div class="col s2">
                  <div class="input-field">                  
                  {!! Form::select('phone_code', $variants->phonecode ?? '' , $store->country_id ?? '' , ['id' => 'phone_code', 'class' => 'select2 browser-default', 'placeholder'=>'Please select phone code']) !!}
                  <!-- <label for="phone_code" class="label-placeholder active">Phone code </label> -->
                  </div>
                </div>
                <div class="input-field col m4 s12">
                  {!! Form::text('mobile', $customer->mobile ?? '', array('id' => 'mobile')) !!}  
                  <label for="mobile" class="label-placeholder active">Mobile </label>
                </div>              
                <div class="input-field col m6 s12">  
                <label for="gender" class="label-placeholder active">Gender </label>              
                  <p style="margin-top: 23px;">
                    <label>
                      <input value="1" id="male" name="gender" type="radio" checked/>
                      <span> Male </span>
                    </label>             
                    <label>
                      <input value="2" id="female" name="gender" type="radio" />
                      <span> Female </span>
                    </label>     
                    <label>
                      <input value="3" id="others" name="gender" type="radio" />
                      <span> Others </span>
                    </label>
                    
                  </p>
                </div>             
              </div>
              <div class="row">
                <div class="input-field col m6 s12">                  
                  <input type='text' name="dob" id="dob" onkeydown="return false" class="" autocomplete="off" placeholder="DOB" />
                  <label for="dob" class="label-placeholder ">DOB </label>
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
@endsection

@push('page-scripts')
<script>
  var customerEmail="{{route('customer.uniqueEmail')}}";

  
</script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{asset('admin/js/custom/customer/customer.js')}}"></script>

@endpush


