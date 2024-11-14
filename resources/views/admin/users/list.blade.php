@extends('layouts.admin.app')

{{-- page style --}}
@section('page-style')
  <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/data-tables.css')}}">
@endsection


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
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Add<i class="material-icons right">business</i></a>
  <a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List<i class="material-icons right">list</i></a>
@endsection

<div class="section section-data-tables">
 
    <!-- DataTables example -->
    <div class="row">
      <div class="col s12 m12 l12">
        @include('layouts.success') 
        @include('layouts.error')
          <div id="button-trigger" class="card card card-default scrollspy data-table-container">
            <div class="card-content">
                <h4 class="card-title">{{ Str::plural($page->title) ?? ''}} Table</h4>
                <div class="row">
                  <div class="col s12">
                    <table id="data-table-stores" class="display data-tables">
                      <thead>
                          <tr>
                              <th>No</th>
                              <th>Store</th>
                              <th>Admin Name</th>
                              <th>Business Type</th>
                              <th>Mobile</th>
                              <th>Email</th>
                              <th>Role</th>
                              <th>Status</th>
                              <th>Action</th>
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
@section('vendor-script')
<script src="{{asset('admin/vendors/data-tables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('admin/vendors/data-tables/js/dataTables.select.min.js')}}"></script>
@endsection
@push('page-scripts')
<script src="{{asset('admin/js/scripts/data-tables.js')}}"></script>
<script>
 $(document).ready(function() {
    if ($.fn.dataTable.isDataTable('#data-table-stores')) {
        $('#data-table-stores').DataTable().clear().destroy();
    }

    // Initialize the DataTable
   $('#data-table-stores').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.stores.index') }}", // Your route for fetching data
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
            { data: 'store', name: 'store' },
            { data: 'name', name: 'name' },
            { data: 'businesstype', name: 'businesstype' },
            { data: 'mobile', name: 'mobile' },
            { data: 'email', name: 'email' },
            { data: 'role', name: 'role' },
            { data: 'is_active', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
    $(document).on('change', '.activate-stores', function() {
        var userId = $(this).data('id'); // Get the user ID from data-id attribute
        var isChecked = $(this).prop('checked'); // Get the checkbox state (checked or unchecked)
        $.ajax({
            url: '/admin/stores/manage-status', // Your endpoint to handle status update
            type: 'POST',
            data: {
              _token: $('meta[name="csrf-token"]').attr('content'),
              user_id: userId,
                is_active: isChecked ? 1 : 0 // Send 1 for active, 0 for inactive
            },
            success: function(response) {   
                if (response.flagError==false) {                 
                  showSuccessToaster(response.message);         
                } else {
                  showErrorToaster(response.message);
                }
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error('Error updating status:', error);
            }
        });
    });
});
                

</script>
@endpush

