@extends('layouts.app')

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/data-tables.css') }}">
    <style>
        [type="radio"]:not(:checked)+label:after {
            background-color: transparent !important;
        }

        /* Checked radio buttons */
        [type="radio"]:checked+label:after {
            background-color: #2196F3 !important;
        }


        .pure-material-checkbox span::before {
            content: '';
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #000;
            /* Change the border color as needed */
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }

        .pure-material-checkbox input[type=radio]:checked+span::before {
            background-color: #000;
            /* Change the background color when the radio button is checked */
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
        <li class="breadcrumb-item active">Create</li>
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
                            {!! Form::open(['class' => 'ajax-submit', 'id' => Str::camel($page->title) . 'Form']) !!}
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::text('membership', '', ['id' => 'membership']) !!}
                                    <label for="" class="label-placeholder active">Membership Name <span
                                            class="red-text">*</span></label>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('description', '', ['id' => 'description']) !!}
                                    <label for="" class="label-placeholder active">Description <span
                                            class="red-text">*</span></label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::text('price', '', ['id' => 'price']) !!}
                                    <label for="" class="label-placeholder active">Selling Price <span
                                            class="red-text">*</span></label>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('membership_price', '', ['id' => 'membership_price']) !!}
                                    <label for="" class="label-placeholder active">Membership Price <span
                                            class="red-text">*</span></label>
                                </div>
                            </div>
                            <div class="row">
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
                                <div class="input-field col m6 s12">
                                    <p>Duration Type</p>
                                    <p>
                                        {!! Form::radio('duration_type', 'days', false, [
                                            'id' => 'duration_type_days',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                        {!! Form::label('duration_type_days', 'Days', ['style' => 'display: inline-block; vertical-align: middle;']) !!}
                                        {!! Form::radio('duration_type', 'months', false, [
                                            'id' => 'duration_type_months',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                        {!! Form::label('duration_type_months', 'Months', ['style' => 'display: inline-block; vertical-align: middle;']) !!}
                                        {!! Form::radio('duration_type', 'years', false, [
                                            'id' => 'duration_type_years',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                        {!! Form::label('duration_type_years', 'Years', ['style' => 'display: inline-block; vertical-align: middle;']) !!}
                                    </p>

                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('duration_in_days', '', ['id' => 'duration_in_days']) !!}
                                    <label for="" class="label-placeholder active">Duration Counts <span class="red-text">*</span></label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <p>Tax Include/Exclude</p>
                                    <p>
                                        {!! Form::radio('tax_included', '1', false, [
                                            'id' => 'tax_included',
                                            'class' => 'pure-material-checkbox',
                                            'style' => 'display: inline-block;',
                                        ]) !!}
                                         <label for="tax_included" class="label-placeholder active">Tax <span  class="red-text">*</span></label>
                                    </p>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::select('gst_tax', $variants->gst, '', [
                                        'id' => 'gst_tax','class' => 'select2 browser-default', 'placeholder' => 'Select GST Tax %',
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
                url: "{{ route('membership.store') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.flagError == false) {
                        showSuccessToaster(response.message);
                        window.location.href = "{{ route('membership.index') }}";
                    } else {
                        showErrorToaster(response.message);
                        setTimeout(function() {
                            window.location.reload(); // Example: reload the current page
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
