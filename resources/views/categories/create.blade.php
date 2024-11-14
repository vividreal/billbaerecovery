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
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
        
                        <div class="row">
                            <!-- Name Field -->
                            <div class="input-field col s12">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="validate" required>
                                @error('name')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
        
                            <!-- Description Field -->
                            <div class="input-field col s12">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="materialize-textarea"></textarea>
                                @error('description')
                                    <span class="helper-text" data-error="wrong" data-success="right">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
        
                        <button type="submit" class="btn waves-effect waves-light">Create Category</button>
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
  
</script>
@endpush
