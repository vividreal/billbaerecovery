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
                    <form action="{{ route('stocks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="input-field col s12">
                                <label>Product</label>
                                <select name="product_id" id="product_id" class="browser-default" required>
                                    <option value="" disabled selected>Select a product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ $product->id }}>
                                            {{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Price Field -->
                            <div class="input-field col s12">
                                <label for="quantity">Qunatity</label>
                                <input type="number" name="quantity" id="quantity" value=""
                                    class="validate" required min="0" step="0.01">
                                @error('quantity')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <button type="submit" class="btn waves-effect waves-light">Create Stock</button>
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
<script></script>
@endpush
