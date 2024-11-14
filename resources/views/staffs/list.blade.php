@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection


{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/flag-icon/css/flag-icon.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/data-tables/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/data-tables/css/select.dataTables.min.css') }}">
@endsection

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/data-tables.css') }}">
    <style>
        #main .section-data-tables .dataTables_wrapper table.dataTable tbody td:nth-child(2) a {
            display: inline-flex;
            align-items: center;
        }
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/staffs') }}">{{ Str::plural($page->title) ?? '' }}</a>
        </li>
        <li class="breadcrumb-item active">List</li>
    </ol>
@endsection

@section('page-action')
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
        class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">Add<i
            class="material-icons right">person_add</i></a>
@endsection
<div class="section section-data-tables">

    <!-- DataTables example -->
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ Str::plural($page->title) ?? '' }} Table</h4>
                    <div class="row">
                        <div class="col s12">
                            <table id="data-table-users" class="display data-tables">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Designation</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
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

{{-- vendor scripts --}}
@section('vendor-script')
<script src="{{ asset('admin/vendors/data-tables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('admin/vendors/data-tables/js/dataTables.select.min.js') }}"></script>
@endsection


@push('page-scripts')
<script src="{{ asset('admin/js/scripts/data-tables.js') }}"></script>

<script>
    $(function() {

        table = $('#data-table-users').DataTable({
            bSearchable: true,
            pagination: true,
            pageLength: 10,
            responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url(ROUTE_PREFIX . '/' . $page->route . '/lists') }}",
                data: search
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    width: 10
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: false
                },
                {
                    data: 'role',
                    name: 'role',
                    orderable: false
                },
                {
                    data: 'designation',
                    name: 'designation',
                    orderable: false
                },
                {
                    data: 'email',
                    name: 'name',
                    orderable: false
                },
                {
                    data: 'mobile',
                    name: 'name',
                    orderable: false
                },
                {
                    data: 'activate',
                    name: 'name',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: 25
                },
            ]
        });

    });

    function manageUserStatus(id) {
        $.ajax({
                url: "{{ url(ROUTE_PREFIX . '/users/update-status') }}",
                data: {
                    'id': id
                },
                type: 'POST',
                dataType: "html"
            })
            .done(function(a) {
                var data = JSON.parse(a);
                if (data.flagError == false) {
                    showSuccessToaster(data.message);
                    setTimeout(function() {
                        table.ajax.reload();
                    }, 1000);
                } else {
                    showErrorToaster(data.message);
                    printErrorMsg(data.error);
                }
            }).fail(function() {
                showErrorToaster("Something went wrong!");
            });
    }

    function search(value) {
        value.name = $('input[type=search]').val();
    }

    function softDelete(b) {
        // Swal.fire({
        //   title: 'Are you sure want to deactivate user ?',
        //   type: 'warning',
        //   showCancelButton: true,
        //   confirmButtonColor: '#3085d6',
        //   cancelButtonColor: '#d33',
        //   confirmButtonText: 'Yes, deactivate!'
        //   }).then(function(result) {
        //       if (result.value) {
        //           $.ajax({url: "{{ url(ROUTE_PREFIX . '/' . $page->route) }}/" + b, type: "DELETE", dataType: "html"})
        //               .done(function (a) {
        //                   var data = JSON.parse(a);
        //                   if(data.flagError == false){
        //                     showSuccessToaster(data.message);          
        //                     setTimeout(function () {
        //                       table.ajax.reload();
        //                       }, 2000);

        //                 }else{
        //                   showErrorToaster(data.message);
        //                   printErrorMsg(data.error);
        //                 }   
        //               }).fail(function () {
        //                       showErrorToaster("Somthing went wrong!");
        //               });
        //       }
        //   });


        // swal({ title: "Are you sure want to deactivate user?",icon: 'warning', dangerMode: true,
        //   buttons: {
        //     cancel: 'No, Please!',
        //     delete: 'Yes, deactivate It'
        //   }
        // }).then(function (willDelete) {
        //   if (willDelete) {
        //     $.ajax({url: "{{ url(ROUTE_PREFIX . '/' . $page->route) }}/" + b, type: "DELETE", dataType: "html"})
        //         .done(function (a) {
        //           var data = JSON.parse(a);
        //           if(data.flagError == false){
        //             showSuccessToaster(data.message);          
        //             setTimeout(function () {
        //               table.ajax.reload();
        //               }, 1000);
        //           }else{
        //             showErrorToaster(data.message);
        //             printErrorMsg(data.error);
        //           }   
        //         }).fail(function () {
        //                 showErrorToaster("Something went wrong!");
        //         });
        //   } 
        // });


    }
</script>
@endpush
