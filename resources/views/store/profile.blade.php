@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>
@endsection

{{-- page style --}}
@section('page-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-users.css')}}">
@endsection

@section('page-css')
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ $page->title ?? ''}}</a></li>
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
            <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
            <div class="media display-flex align-items-center mb-2">
              <a class="mr-2 storlogo" href="javascript:"><img src="{{ $store->show_image }}" alt="users avatar" class="z-depth-4 circle" id="store_logo"></a>
              <div class="media-body">
                <form id="storeLogoForm" name="storeLogoForm" action="" method="POST" enctype="multipart/form-data" class="ajax-submit">
                  {{ csrf_field() }}
                  {!! Form::hidden('log_url', $store->show_image, ['id' => 'log_url'] ); !!}
                  {!! Form::hidden('logoStoreRoute', url($page->route.'/update-logo'), ['id' => 'logoStoreRoute'] ); !!}
                  {!! Form::hidden('logoDeleteRoute', url($page->route.'/delete-logo'), ['id' => 'logoDeleteRoute'] ); !!}
                  <h5 class="media-heading mt-0">Logo</h5>
                  <div class="user-edit-btns display-flex ">
                    <a id="select-files" class="btn indigo mr-2 logo-onload-btn"><span>Browse</span></a>
                    
                    
                    <button class="btn-small btn-danger logo-onload-btn" id="deleteLogoBtn" style={{ ($store->image != '') ? 'display:block;' : 'display:none;'}}>Delete</button>
                    
                    <a href="" class="btn-small btn-light-pink logo-action-btn" id="removeLogoDisplayBtn" style="display:none;">Remove</a>
                    <button type="submit" class="btn btn-success logo-action-btn" id="uploadLogoBtn" style="display:none;">Upload</button>
                  </div>
                  <small>Allowed JPG, JPEG or PNG. Max size of 800kB</small>
                  <div class="upfilewrapper" style="display:none;">
                    <input id="profile" type="file" accept="image/png, image/gif, image/jpeg" name="image" class="image" />
                  </div>
                </form>
              </div>
            </div>
            <!-- users edit media object ends -->
            <!-- users edit account form start -->
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
              <div class="card-content red-text">I am sorry, this service is currently not supported in your selected country. In case you wish to use this service in any country other than India, please leave a message in the contact us page, and we shall respond to you at the earliest.</div>
              <button type="button" class="close red-text" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            @if (Session::has('error'))
            <div class="card-alert card red lighten-5 print-error-msg">
              <div class="card-content red-text">Few mandatory store details are missing</div>
              <div class="card-content red-text">{!! Session::get('error') !!}</div>
              <button type="button" class="close red-text" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            @endif
            <form id="profileForm" name="profileForm" class="ajax-submit">
              {{ csrf_field() }}
              {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
              {!! Form::hidden('store_id', $store->id ?? '' , ['id' => 'store_id'] ); !!}
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('name', $store->name ?? '', array('id' => 'name')) !!} 
                  <label for="name" class="label-placeholder active">Store Name <span class="red-text">*</span></label> 
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::text('email', $store->email ?? '', array('id' => 'email')) !!} 
                  <label for="email" class="label-placeholder active">Store Email</label> 
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::textarea('address', $store->address ?? '', ['class' => 'materialize-textarea', 'rows'=>3]) !!}
                  <label for="address" class="label-placeholder active">Address</label> 
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::select('country_id', $variants->countries , $store->country_id ?? '' , ['id' => 'country_id' ,'class' => 'select2 browser-default', 'placeholder'=>'Please select country']) !!}
                  <!-- <label for="country_id" class="label-placeholder active">Store country</label>  -->
                  <span class="helper-text" data-error="wrong" data-success="right">Currently service is supported in India only!</span>
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12" id="timezone_block">
                  @if(!empty($variants->timezone))
                    {!! Form::select('timezone', $variants->timezone ?? [], $store->timezone ?? '', ['id' => 'timezone' ,'class' => 'select2 browser-default','placeholder'=>'Please select timezone']) !!}
                  @else
                    {!! Form::select('timezone', [], '', ['id' => 'timezone' ,'class' => 'select2 browser-default', 'placeholder'=>'Please select timezone']) !!}
                  @endif
                  <!-- <label for="timezone" class="label-placeholder active">Store timezone <span class="red-text">*</span></label>  -->
                </div>
                <div class="input-field col m6 s12">
                  @if(!empty($variants->states))
                    {!! Form::select('state_id', $variants->states , $store->state_id ?? '' , ['id' => 'state_id' ,'class' => 'select2 browser-default','placeholder'=>'Please select state']) !!}
                  @else
                    {!! Form::select('state_id', [], '' , ['id' => 'state_id' ,'class' => 'select2 browser-default','placeholder'=>'Please select state']) !!}
                  @endif
                  <!-- <label for="state_id" class="label-placeholder active">Store state</label> -->
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::select('time_format', [1 => '12hr format ', 2 => '24hr format'] , $store->time_format ?? '' , ['id' => 'time_format']) !!}
                  <!-- <label for="time_format" class="label-placeholder active">Time Format</label>  -->
                </div>
                <div class="input-field col m6 s12">
                  @if(!empty($variants->districts))
                    {!! Form::select('district_id', $variants->districts , $store->district_id ?? '' , ['id' => 'district_id' ,'class' => 'select2 browser-default','placeholder'=>'Please select district']) !!}
                  @else
                    {!! Form::select('district_id', [] , '' , ['id' => 'district_id' ,'class' => 'select2 browser-default','placeholder'=>'Please select district']) !!}
                  @endif
                  <!-- <label for="district_id" class="label-placeholder active">Store district</label> -->
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('pincode', $store->pincode ?? '', array('id' => 'pincode', 'class' => 'check_numeric')) !!}
                  <label for="pincode" class="label-placeholder">Pin code</label> 
                </div>
                <div class="col s2">
                  <div class="input-field">                  
                  {!! Form::select('phone_code', $variants->phoneCode , $user->phone_code ?: $store->country_id ?? '', ['id' => 'phone_code']) !!}
                  <!-- <label for="mobile" class="label-placeholder active">Phone code <span class="red-text">*</span></label> -->
                  <span class="helper-text" data-error="wrong" data-success="right">Currently service is supported in India only!</span>
                  </div>
                </div>
                <div class="input-field col m4 s12">
                  {!! Form::text('contact', $store->contact ?? '', array('id' => 'contact', 'class' => 'form-control check_numeric')) !!}
                  <label for="contact" class="label-placeholder">Contact Mobile</label>
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                {!! Form::text('location', $store->location ?? '', array('id' => 'location')) !!}
                <label for="location" class="label-placeholder">Location</label> 
                </div>
                <div class="input-field col m6 s12">
                {!! Form::text('map_location', $store->map_location ?? '', array('id' => 'map_location')) !!}
                <label for="map_location" class="label-placeholder">Map location</label> 
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12">
                {!! Form::textarea('about', $store->about ?? '', ['id'=>'about', 'class' => 'materialize-textarea']) !!}
                <label for="about" class="label-placeholder">About</label> 
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12">
                  <button class="btn waves-effect waves-light" type="button" name="reset" id="profile-reset-btn">Reset <i class="material-icons right">refresh</i></button>
                  <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="profile-submit-btn">Submit <i class="material-icons right">send</i></button>
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
@include('layouts.crop-modal') 
@endsection

{{-- vendor scripts --}}
@section('vendor-script')

@endsection

@push('page-scripts')
<!-- date-time-picker -->
<script>
  var is_unique_store_email="{{route('isUniqueStoreEmail')}}";
  var getStatesByCountry="{{route('getStatesByCountry')}}";
  var getTimezone="{{route('getTimezone')}}";
  var get_districts_by_state="{{route('get_districts_by_state')}}";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
<script src="{{asset('admin/js/custom/store/profile.js')}}"></script>

@endpush