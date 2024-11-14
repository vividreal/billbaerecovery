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
<a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Mark {{ Str::singular($page->title) ?? ''}}<i class="material-icons right">add</i></a>
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
          {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!}
          {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
          {!! Form::hidden('editable', 0, ['id' => 'editable'] ); !!}
          {!! Form::close() !!}
          <ul class="collection">
            @forelse($variants->staffs as $staff)
            <li class="collection-item">
              <div class="col m4 s12">
                <span class="">{{$staff->user->name}}</span>
                @php
                  $status = 'Checked Out';
                  $checked = '';
                  $mark = 0;
                  $online = '';

                if (($staff->in_time != null) && ($staff->out_time == null)) {
                  $status = "Checked in ";
                  $checked = 'checked';
                  $mark = 1;
                  $online = 'avatar-online';
                }
                @endphp
                <span class="avatar-status {{$online}}"><img src="{{$staff->user->profile_url}}" alt="avatar"><i></i></span>

              </div>
              <div class="input-field col m4 s12">
                <div class="switch">
                  <label> Checked Out<input type="checkbox" class="checkin-checkout" id="mark_{{$staff->user_id}}" name="mark[]" {{$checked}} value="{{$mark}}"> <span class="lever"></span> Checked In </label>
                  <!-- <input disabled type="checkbox"> -->
                </div>
              </div>
              <div class="input-field col m4 s12">
                <button class="btn cyan waves-effect waves-light mark-attendance" data-userId="{{$staff->user_id}}" data-staffId="{{$staff->id}}" data-status="{{$mark}}" type="button" name="action" id="submit-btn_{{$staff->user_id}}">Submit <i class="material-icons right">send</i></button>
              </div>
            </li>
            @empty
            <li class="collection-item">
              <p>No Staffs found</p>
            </li>
            @endforelse
          </ul>
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{asset('admin/js/custom/attendance/attendance.js')}}"></script>
<script>
</script>
@endpush