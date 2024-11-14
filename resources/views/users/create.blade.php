@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection


{{-- vendor styles --}}
@section('vendor-style')

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
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none"><div class="card-content red-text"><ul></ul></div></div>
            {!! Form::open(['class'=>'ajax-submit','id'=> Str::camel($page->title).'Form']) !!} 
              {{ csrf_field() }}
              {!! Form::hidden('user_id', $user->id ?? '' , ['id' => 'user_id'] ); !!}
              {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!} 
              {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('name', $user->name ?? '', ['id' => 'name']) !!}  
                  <label for="name" class="label-placeholder active">Name <span class="red-text">*</span></label>
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::text('email', $user->email ?? '', array('id' => 'email', 'autocomplete' => 'off')) !!}  
                  <label for="email" class="label-placeholder active">Email <span class="red-text">*</span></label> 
                </div>
              </div>
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('mobile', $user->mobile ?? '', array('id' => 'mobile', 'class' => 'check_numeric')) !!}
                  <label for="mobile" class="label-placeholder active">Mobile <span class="red-text">*</span></label>   
                </div>              
                <div class="input-field col m6 s12">                
                  {!! Form::select('roles[]', $roles , $userRole ?? [] , ['id' => 'roles' ,'class' => 'select2 browser-default', 'multiple' => 'multiple' ]) !!}
                  <label for="roles" class="label-placeholder active">Role <span class="red-text">*</span></label>
                </div>             
              </div>
              <div class="row">
                <div class="input-field col m6 s12">    
                <label for="gender" class="label-placeholder active">Gender </label>              
                  <p style="margin-top: 23px;">
                  @if(isset($user))  
                    <label>
                      <input value="1" id="male" name="gender" type="radio" @if($user->gender == 1) checked @endif/>
                      <span> Male </span>
                    </label>             
                    <label>
                      <input value="2" id="female" name="gender" type="radio" @if($user->gender == 2) checked @endif/>
                      <span> Female </span>
                    </label>     
                    <label>
                      <input value="3" id="others" name="gender" type="radio" @if($user->gender == 3) checked @endif/>
                      <span> Others </span>
                    </label>
                  @else
                    <label>
                      <input value="1" id="male" name="gender" type="radio" checked />
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
                  @endif
                  </p>
                  <!-- <label for="gender" class="label-placeholder">Gender </label> -->
                </div>      
                <div class="input-field col m6 s12">
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
@endsection
{{-- vendor scripts --}}
@section('vendor-script')
@endsection
@push('page-scripts')
<script>
  var get_email_url="{{route('isUniqueEmail')}}";
</script>
<script src="{{asset('admin/js/custom/user/user.js')}}"></script>

@endpush

