@extends('layouts.admin.app')

@section('content')

@section('breadcrumb')
<h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/stores') }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">List</li>
  </ol>
@endsection
@section('page-action')
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Add<i class="material-icons right">vpn_key</i></a>
  <!-- <a class="btn dropdown-settings waves-effect waves-light  light-blue darken-4 breadcrumbs-btn" href="#!" data-target="dropdown1" id="customerListBtn"><i class="material-icons hide-on-med-and-up">settings</i><span class="hide-on-small-onl">List</span><i class="material-icons right">arrow_drop_down</i></a>
    <ul class="dropdown-content" id="dropdown1" tabindex="0">
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" href="javascript:" data-type="active">Active </a></li>
      <li tabindex="0"><a class="grey-text text-darken-2 listBtn" data-type="deleted" href="javascript:">De active</a></li>
    </ul> -->
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
        @include('layouts.success') 
        @include('layouts.error')
          <div id="button-trigger" class="card card card-default scrollspy">
            <div class="card-content">
                <h4 class="card-title">{{ Str::plural($page->title) ?? ''}} Table</h4>
                <div class="row">
                  <div class="col s12">
                      <table id="data-table-simple-2" class="display data-tables">
                        <thead>
                          <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th width="380px">Action</th>
                          </tr>
                          @foreach ($roles as $key => $role)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $role->name }}</td>
                                <td> 
                                    <a href="{{ url(ROUTE_PREFIX.'/roles/'.$role->id) }}" class="btn mr-2 blue tooltipped" data-tooltip="View details"><i class="material-icons">visibility</i></a>
                                  @can('role-edit')
                                    <a href="{{ url(ROUTE_PREFIX.'/roles/'.$role->id.'/edit') }}" class="btn mr-2 orange tooltipped" data-tooltip="Edit details"><i class="material-icons">mode_edit</i></a>
                                  @endcan
                                  
                                  @can('role-delete')
                                  {!! Form::open(['method' => 'DELETE', 'route' => ['admin.roles.destroy', $role->id], 'name' => 'roleForm', 'style' => 'display:inline']) !!}
                                  <a href="javascript:void(0);" id="{{$role->id}}" class="btn btn-sm btn-icon mr-2 role-delete-btn" title="Delete Role"><i class="material-icons">cancel</i> </a>
                                    {!! Form::close() !!}
                                  @endcan
                                </td>
                            </tr>
                          @endforeach
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
@push('page-scripts')
<script>

  $(".role-delete-btn").click( function() {
    var form  = $(this).closest('form');
    swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons:{cancel: 'No, Please!', delete: 'Yes, Delete It'}
    }).then(function (willDelete) {
      if (willDelete) {
        form.submit();
      } 
    });
  });

</script>
@endpush