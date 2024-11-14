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
    <a href="{{ route('products.create') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn right"
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
                            <table id="data-table-products" class="display data-tables">
                                <thead>
                                    <tr>
                                        <th>No</th>                                       
                                        <th>Product Code</th>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Product Type</th>
                                        <th>Price</th>
                                        <th>Bill</th>
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
        $('#data-table-products').DataTable({
            searching: true,
            pagination: true,
            pageLength: 10,
            responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('products.index') }}",
                type: "GET"
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, width: 10 },
                { data: 'product_code', name: 'product_code' }, 
                { data: 'image', name: 'image' }, 
                { data: 'name', name: 'name' },
                { data: 'category.name', name: 'category.name' }, // Access category name
                { data: 'product_type', name: 'product_type' },
                { data: 'price', name: 'price' },
                { data: 'bill_image', name: 'bill_image' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
