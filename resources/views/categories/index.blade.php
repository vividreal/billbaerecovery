@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection


{{-- vendor styles --}}
@section('vendor-style')
  
@endsection

{{-- page style --}}
@section('page-style')
    
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
    <a href="{{ route('categories.create') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right"
        type="submit" name="action">Add<i class="material-icons right">person_add</i></a>
@endsection
<div class="section section-data-tables">
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ Str::plural($page->title) ?? '' }} Table</h4>
                    <div class="row">
                        <div class="col s12">
                            <table id="data-table-categories" class="display data-tables">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Description</th>
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

@endsection


@push('page-scripts')

<script>
    $(function() {
        $('#data-table-categories').DataTable({
            searching: true,
            pagination: true,
            pageLength: 10,
            responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('categories.index') }}",
                type: "GET"
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, width: 10 },
                { data: 'name', name: 'name', orderable: true },
                { data: 'description', name: 'description', orderable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: 25 }
            ]
        });
    });
</script>
@endpush
