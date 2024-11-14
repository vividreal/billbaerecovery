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
    <li class="breadcrumb-item"><a href="{{ url($page->route) }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">List</li>
  </ol>
@endsection

@section('page-action')
  <a href="{{ url($page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create {{ Str::singular($page->title) ?? ''}} <i class="material-icons right">add</i></a>
  <a class="btn dropdown-settings waves-effect waves-light  light-blue darken-4 breadcrumbs-btn" href="#!" data-target="dropdown1"><i class="material-icons hide-on-med-and-up">settings</i><span class="hide-on-small-onl">List {{ Str::plural($page->title) ?? ''}}</span><i class="material-icons right">arrow_drop_down</i></a>
    <ul class="dropdown-content" id="dropdown1" tabindex="0">
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" href="javascript:" data-type="active">Active </a></li>
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" data-type="deleted" href="javascript:">Inactive</a></li>
    </ul>
@endsection


<div class="section section-data-tables">
  <div class="card">
    <div class="card-content">
      <p class="caption mb-0">{{ Str::plural($page->title) ?? ''}}. Lorem ipsume is used for the ...</p>
    </div>
  </div>
    <!-- DataTables example -->
    <div class="row">
      <div class="col s12 m12 l12">
          <div id="button-trigger" class="card card card-default scrollspy">
            <div class="card-content">
                <h4 class="card-title">{{ Str::plural($page->title) ?? ''}} Table</h4>
                <div class="row">
                  <form id="dt-filter-form" name="dt-filter-form">
                    {!! Form::hidden('status', '', ['id' => 'status'] ); !!}
                  </form>
                </div>
                <div class="row">
                  <div class="col s12">
                    <table id="data-table-rooms" class="display data-tables" data-url="{{ url($page->route) }}" data-form="dt-filter-form" data-length="10">
                      <thead>
                        <tr>
                          <th width="20px" data-orderable="false" data-column="DT_RowIndex"> No </th>
                          <th width="" data-orderable="false" data-column="name"> Name </th>
                          <th width="200px" data-orderable="false" data-column="action"> Action </th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
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

