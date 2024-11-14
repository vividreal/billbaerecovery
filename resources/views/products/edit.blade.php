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
                    <form action="{{ route('products.update',$product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Name Field -->
                            <div class="input-field col s12">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" value="{{$product->name}}" class="validate" required>
                                @error('name')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>

                             <!-- Category Select Box -->
                             <div class="input-field col s12">
                                <label>Category</label>
                                <select name="category_id" id="category_id" class="browser-default" required>
                                    <option value="" disabled selected>Select a category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                              <!-- Price Field -->
                              <div class="input-field col s12">
                                <label for="price">Price</label>
                                <input type="number" name="price" id="price" value="{{$product->price}}" class="validate" required
                                    min="0" step="0.01">
                                @error('price')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Description Field -->
                            <div class="input-field col s12">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="materialize-textarea">{{$product->description}}</textarea>
                                @error('description')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>

                          
                            <!-- Product Type Field -->
                            <div class="input-field col s12">
                                <label>Product Type</label>
                                <select name="product_type" id="product_type" class="browser-default" required>
                                   
                                    <option value="" disabled>Choose your option</option>
                                    <option value="reuse" {{ $product->product_type == 'reuse' ? 'selected' : '' }}>Reusable</option>
                                    <option value="no_reuse" {{ $product->product_type == 'no_reuse' ? 'selected' : '' }}>Not Reusable</option>
                                </select>
                                </select>
                                @error('product_type')
                                    <span class="helper-text" data-error="wrong"
                                        data-success="right">{{ $message }}</span>
                                @enderror
                            </div>

                           

                            <div class="file-field input-field col s12">
                                <div class="btn">
                                    <span>Upload Image</span>
                                    <input type="file" name="image" id="image" accept="image/*">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Upload product image">
                                </div>
                                @error('image')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- Bill Upload Field -->
                            <div class="file-field input-field col s12">
                                <div class="btn">
                                    <span>Upload Bill</span>
                                    <input type="file" name="bill_image" id="bill_image" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Upload bill document">
                                </div>
                                @error('bill_image')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn waves-effect waves-light">Create Product</button>
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
