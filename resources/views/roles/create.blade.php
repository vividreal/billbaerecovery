@extends('layouts.app')

@section('content')

@section('breadcrumb')
<h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/roles') }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">View</li>
  </ol>
@endsection
@section('page-action')
  
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
                <h4 class="card-title">Roles </h4>
                <form id="roleForm" name="roleForm" role="form" method="POST" action="{{ url(ROUTE_PREFIX.'/roles') }}">
                {{ csrf_field() }}
                  <div class="row">
                    @include('layouts.success') 
                    @include('layouts.error')
                    <div class="col m6 s12">
                      <label for="name" class="label-placeholder">Role name <span class="red-text">*</span></label>
                      {!! Form::text('name', '') !!} 
                    </div>
                    </div>
                    <div class="row">
                    <div class="col s12">
                      <button class="btn waves-effect waves-light" type="reset" name="reset">Reset <i class="material-icons right">refresh</i></button>
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

@push('page-scripts')
<script>

</script>
@endpush