@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')
    <style>
      
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection



<div class="section section-data-tables">
    <div class="row">
        <div class="col s12 m12 l12">

            <div class="card card card-default scrollspy">
                <div class="card-content">
                    <div>
                        <h5>Edit Comment </h5>
                    </div>
                    <div>
                        <form id="customer_comment" method="PUT">
                            @csrf
                            <div>
                                <label for="">Title</label>
                                <input type="text" name="title" id="title" value="{{$customerComment->title}}">
                            </div>
                            <div>
                                <input type="hidden" name="commentId" id="commentId" value="{{ $customerComment->id }}">
                                <input type="hidden" name="customerId" id="customerId" value="{{ $customerComment->customer_id }}">
                                <label for="">Comment</label>
                                <textarea name="comment" id="comment" cols="30" rows="60">{{$customerComment->comment}}</textarea>
                            </div>
                            <div>
                                <input type="submit" name="customer_submit" class="btn btn-light-blue">
                            </div>
                        </form>
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
    $(document).ready(function() {
        var baseURL = window.location.origin;
        $('#customer_comment').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var customerId = $("#customerId").val();
            $.ajax({
                url: '{{ route('customers.updateComment') }}',
                type: 'PUT',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                        if (baseURL.includes("localhost")) {
                            // If it's localhost, remove "billbae"
                            baseURL = baseURL + "/customer-review-book?customerId=" + customerId;
                        } else {
                            // If it's not localhost, append "billbae"
                            baseURL = baseURL + "/billbae/customer-review-book?customerId=" + customerId;
                        }
                        var url = baseURL;

                        // Redirect to the constructed URL
                        window.location.href = url;

                    } else {
                        showErrorToaster(response.message);
                        printErrorMsg(response.error);
                    }
                },
                error: function(xhr, status, error) {

                    console.error(xhr.responseText);
                }
            });
        });
    });
   


</script>
@endpush
