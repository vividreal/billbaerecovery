@extends('layouts.app')

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/data-tables.css') }}">
    <style>
        .pure-material-checkbox {
            position: relative !important;
            opacity: 1 !important;
            pointer-events: none;
        }

        .input-field label {
            margin-right: 10px
        }
    </style>
@endsection


@section('content')
@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a
                href="{{ url(ROUTE_PREFIX . '/membership') }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection
<div class="section section-data-tables">
    <div class="row">
        <div class="col s12 m12 l12">
            @include('layouts.success')
            @include('layouts.error')
            <div id="button-trigger" class="card card card-default scrollspy data-table-container">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                    <div class="row">
                        <div class="col s12">
                            {!! Form::open(['class' => 'ajax-submit', 'id' => Str::camel($page->title) . 'Form','method' => 'PUT']) !!}
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::text('membership', $membership->name, ['id' => 'membership']) !!}
                                    {!! Form::hidden('membershipId', $membership->id, ['id' => 'membershipId']) !!}
                                    <label for="" class="label-placeholder active">Membership Name <span class="red-text">*</span></label>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('description', $membership->description, ['id' => 'description']) !!}
                                    <label for="" class="label-placeholder active">Description <span class="red-text">*</span></label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::text('price', $membership->price, ['id' => 'price']) !!}
                                    <label for="" class="label-placeholder active">Selling Price <span class="red-text">*</span></label>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('membership_price', $membership->membership_price, ['id' => 'membership_price']) !!}
                                    <label for="" class="label-placeholder active">Membership Price <span
                                            class="red-text">*</span></label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <p>Duration Type</p>
                                    <p>
                                        {!! Form::radio('duration_type', 'days', $membership->duration_type === 'days', [
                                            'id' => 'duration_type_days',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                        {!! Form::label('duration_type_days', 'Days', ['style' => 'display: inline-block; vertical-align: middle;']) !!}
                                        {!! Form::radio('duration_type', 'months', $membership->duration_type === 'months', [
                                            'id' => 'duration_type_months',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                        {!! Form::label('duration_type_months', 'Months', ['style' => 'display: inline-block; vertical-align: middle;']) !!}
                                        {!! Form::radio('duration_type', 'years', $membership->duration_type === 'years', [
                                            'id' => 'duration_type_years',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                        {!! Form::label('duration_type_years', 'Years', ['style' => 'display: inline-block; vertical-align: middle;']) !!}
                                    </p>

                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('duration_in_days', $membership->duration_in_days, ['id' => 'duration_in_days']) !!}
                                    <label for="" class="label-placeholder active">Duration Counts <span
                                            class="red-text">*</span></label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <p>Tax Include/Exclude</p>
                                    <p>
                                        {!! Form::radio('tax_included', '1', $membership->is_tax_included === '1', [
                                            'id' => 'tax_included',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                    </p>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::select('gst_tax', $variants->gst, $membership->gst_id, [
                                        'id' => 'gst_tax',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Select GST Tax %',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light" type="button" name="reset"
                                        id="reset-btn">Reset <i class="material-icons right">refresh</i></button>
                                    <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                        id="submit-btn">Submit <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('vendor-script')
@endsection
@push('page-scripts')
<script>
    $(document).ready(function() {
        $('.ajax-submit').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('membership.update', ['membership' => $membership->id]) }}",
                type: 'PUT',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                        window.location.href = "{{ route('membership.index') }}";

                    } else {
                        showErrorToaster(response.message);
                        setTimeout(function() {
                            window.location
                        .reload(); // Example: reload the current page
                        }, 4000);
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
