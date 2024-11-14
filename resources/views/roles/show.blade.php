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
                <h4 class="card-title">{{ $role->name }} Permissions </h4>
                <div class="row">
                  <div class="col s12">
                    <ul class="collapsible" data-collapsible="accordion">
                      @if(!empty($rolePermissions))
                        @foreach($rolePermissions as $v)
                          <li><div class="collapsible-header"><i class="material-icons">vpn_key</i>{{ $v->name }}</div> </li>
                        @endforeach
                      @endif                      
                    </ul>
                  </div>
                </div>
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