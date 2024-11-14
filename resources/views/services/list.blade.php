@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')
  <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/data-tables.css')}}">
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
  @can('service-create')
  <a href="javascript:" class="btn waves-effect waves-light orange darken-4 breadcrumbs-btn" onclick="importBrowseModal()" >Bulk Upload<i class="material-icons right">attach_file</i></a>
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create {{ Str::singular($page->title) ?? ''}} <i class="material-icons right">add</i></a>
  @endcan
  @can('service-list')
  <a class="btn dropdown-settings waves-effect waves-light  light-blue darken-4 breadcrumbs-btn" href="#!" data-target="dropdown1"><i class="material-icons hide-on-med-and-up">settings</i><span class="hide-on-small-onl">List {{ Str::plural($page->title) ?? ''}}</span><i class="material-icons right">arrow_drop_down</i></a>
    <ul class="dropdown-content" id="dropdown1" tabindex="0">
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" href="javascript:" data-type="active">Active </a></li>
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" data-type="deleted" href="javascript:">Inactive</a></li>
    </ul>
  @endcan
@endsection

<div class="section section-data-tables">
  
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
                      <table id="data-table-services" class="display data-tables" data-url="{{ $page->link }}" data-form="dt-filter-form" data-length="10">
                        <thead>
                          <tr>
                            <th width="20px" data-orderable="false" data-column="DT_RowIndex"> No </th>
                            <th width="" data-orderable="false" data-column="name"> Name </th>
                            <th width="" data-orderable="false" data-column="service_category">Service Category </th>
                            <th width="" data-orderable="false" data-column="price"> Price </th>
                            <th width="100px" data-orderable="false" data-column="hours"> Hours </th>
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
@include('services.import-browse-modal')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script src="{{asset('admin/js/custom/service/service.js')}}"></script>
<script>
</script>
@endpush

