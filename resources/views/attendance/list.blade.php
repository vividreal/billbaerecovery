@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">List</li>
  </ol>
@endsection

@section('page-action')
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Mark {{ Str::singular($page->title) ?? ''}} <i class="material-icons right">add</i></a>
  <a class="btn dropdown-settings waves-effect waves-light  light-blue darken-4 breadcrumbs-btn" href="#!" data-target="dropdown1"><i class="material-icons hide-on-med-and-up">settings</i><span class="hide-on-small-onl">List {{ Str::plural($page->title) ?? ''}}</span><i class="material-icons right">arrow_drop_down</i></a>
    <ul class="dropdown-content" id="dropdown1" tabindex="0">
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" data-type="active">List </a></li>
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" data-type="deleted" href="{{ url(ROUTE_PREFIX.'/'.$page->route. '/edit/markings') }}">Edit </a></li>
    </ul>


@endsection

<div class="section section-data-tables">
  
  <!-- DataTables example -->
  <div class="row">
    <div class="col s12 m6 l12">
      <div id="button-trigger" class="card card card-default scrollspy">
        <div class="card-content">
          <div class="row">
            <div class="col s3 right">
              <form id="dt-filter-form" name="dt-filter-form">
                {{ csrf_field() }}
                {!! Form::hidden('start_range', '', ['id' => 'start_range'] ); !!}
                {!! Form::hidden('end_range', '', ['id' => 'end_range'] ); !!}
                {!! Form::hidden('range_sort', '0', ['id' => 'range_sort'] ); !!}
                {!! Form::hidden('timePicker', $variants->time_picker, ['id' => 'timePicker'] ); !!}
                {!! Form::hidden('timeFormat', $variants->time_format, ['id' => 'timeFormat'] ); !!}
                <div class="row">
                  <div class="col-md-5 ml-auto mr-3">
                    <div class="form-group ">
                      <label for="billed_date" class="label-placeholder active">Please select Date </label>
                      <input type="text" name="marked_date" id="marked_date" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col s12 m12 l12">
      @include('layouts.success') 
      @include('layouts.error')
        <div id="button-trigger" class="card card card-default scrollspy">
          <div class="card-content">
            <h4 class="card-title">{{ Str::plural($page->title) ?? ''}} Table</h4>
            <div class="row">
              <div id="attendance-table-data"><div class="progress"><div class="indeterminate"></div></div> </div>
            </div>
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
