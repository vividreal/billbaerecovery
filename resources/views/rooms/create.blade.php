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
    <li class="breadcrumb-item"><a href="{{ url($page->route) }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Create</li>
  </ol>
@endsection

@section('page-action')
  <a href="{{ url($page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create {{ Str::singular($page->title) ?? ''}}<i class="material-icons right">add</i></a>
  <a href="{{ url($page->route) }}" class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List {{ Str::plural($page->title) ?? ''}}<i class="material-icons right">list</i></a>
@endsection

<div class="section">
  <div class="card">
    <div class="card-content">
      <p class="caption mb-0">{{ Str::plural($page->title) ?? ''}}. Lorem ipsum is used for the ...</p>
    </div>
  </div>
  
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
              {!! Form::hidden('room_id', $room->id ?? '' , ['id' => 'room_id'] ); !!}
              {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!} 
              {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
              <div class="row">
                <div class="input-field col m6 s12">
                  {!! Form::text('name', $room->name ?? '', ['id' => 'name']) !!}  
                  <label for="name" class="label-placeholder active">Name <span class="red-text">*</span></label>
                </div>
                <div class="input-field col m6 s12">
                  {!! Form::textarea('description', $room->description ?? '', ['class' => 'materialize-textarea']) !!}
                  <label for="description" class="label-placeholder active"> Description </label> 
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
<script src="{{asset('admin/js/custom/rooms/rooms.js')}}"></script>
<script>
</script>
@endpush

