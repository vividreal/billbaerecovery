@extends('layouts.app')

{{-- page style --}}
@section('page-style')
  <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/data-tables.css')}}">
@endsection


@section('content')

@section('breadcrumb')
<h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/membership') }}">{{ Str::plural($page->title) ?? ''}}</a></li>
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
                      <table id="data-table-membership" class="display" >
                        <thead>
                          <tr>
                            {{-- data-column="DT_RowIndex" --}}
                            <th width="10px" data-orderable="false" >No</th>
                            <th width="50px"data-orderable="false" >Membership</th>
                            <th width="90px" data-orderable="false" >Description</th>
                            <th width="70px" data-orderable="false" >Duration</th>                            
                            <th width="70px" data-orderable="false" >Sellinng Price</th>
                            <th width="75px" data-orderable="false" >Price</th>
                            <th width="50px" data-orderable="false" >Tax Status</th>
                            <th width="70px" data-orderable="false" >Action</th>


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
<!-- Include SweetAlert library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

@endsection
@push('page-scripts')
<script src="{{asset('admin/js/scripts/data-tables.js')}}"></script>
<script>

$(document).ready(function() {
    $('#data-table-membership').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('membership.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'membership', name: 'membership' },
            { data: 'description', name: 'description' },
            { data: 'duration', name: 'duration' },
            { data: 'sellingprice', name: 'sellingprice' },
            { data: 'membershipprice', name: 'membershipprice' },          
            { data: 'taxstatus', name: 'taxstatus' },
            { data: 'action', name: 'action' }
        ]
    });
});

$(document).on('click', '.delete-membership', function(e) {
    e.preventDefault();
    
    var membershipId = $(this).data('membership-id');
    
    // Show SweetAlert confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this membership!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed, send delete request
            $.ajax({
                url: "{{ route('membership.destroy', '') }}/" + membershipId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                    } else {
                        showErrorToaster(response.message);
                    }
                    $('#data-table-membership').DataTable().ajax.reload();

                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });
});




</script>
@endpush

