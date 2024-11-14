@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

@section('page-style')

@endsection
@section('content')
@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection



<div class="section">

    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    @include('layouts.success')
                    @include('layouts.error')
                    <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                    <form action="{{ route('inventories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="input-field col s12">
                                <label>Product</label>
                                <select name="product_id" id="product_id" class="browser-default" required>
                                    <option value="" disabled selected>Select a product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                    
                            <div class="input-field col s12">
                                <label>Staff Name</label>
                                <select name="staffprofile_id" id="staffprofile_id" class="browser-default" required>
                                    <option value="" disabled selected>Select a Staff</option>
                                    @foreach ($staffs as $staff)
                                        <option value="{{ $staff->user->id }}">{{ $staff->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('staffprofile_id')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                    
                            <div class="input-field col s12" id="service-field">
                                <label>Service Name</label>
                                <select name="service_id" id="service_id" class="browser-default">
                                    <option value="" disabled selected>Select a Service</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_id')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                    
                            <div class="input-field col s12" id="package-field">
                                <label>Package Name</label>
                                <select name="package_id" id="package_id" class="browser-default">
                                    <option value="" disabled selected>Select a Package</option>
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                                    @endforeach
                                </select>
                                @error('package_id')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                    
                            <div class="input-field col s12">
                                <label for="taking_quantity">Required Quantity</label>
                                <input type="number" name="taking_quantity" id="taking_quantity" value="" class="validate" required min="0" step="0.01">
                                @error('taking_quantity')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    
                        <button type="submit" class="btn waves-effect waves-light">Create Inventory</button>
                    </form>
                    
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
  document.addEventListener('DOMContentLoaded', function () {
        const serviceField = document.getElementById('service-field');
        const packageField = document.getElementById('package-field');
        const serviceSelect = document.getElementById('service_id');
        const packageSelect = document.getElementById('package_id');

        // Add event listeners to handle the visibility of fields
        serviceSelect.addEventListener('change', function () {
            if (serviceSelect.value) {
                packageField.style.display = 'none'; // Hide package field
                packageSelect.value = ''; // Reset package selection
            } else {
                packageField.style.display = 'block'; // Show package field if no service selected
            }
        });

        packageSelect.addEventListener('change', function () {
            if (packageSelect.value) {
                serviceField.style.display = 'none'; // Hide service field
                serviceSelect.value = ''; // Reset service selection
            } else {
                serviceField.style.display = 'block'; // Show service field if no package selected
            }
        });
    });
</script>
@endpush
