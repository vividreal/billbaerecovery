@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')

@endsection

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/page-users.css') }}">
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

@section('page-action')
    <a href="javascript:" class="btn waves-effect waves-light orange darken-4 breadcrumbs-btn"
        onclick="importBrowseModal()">Bulk Upload<i class="material-icons right">attach_file</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn"
        type="submit" name="action">Create {{ Str::singular($page->title) ?? '' }}<i
            class="material-icons right">add</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route) }}"
        class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List
        {{ Str::plural($page->title) ?? '' }}<i class="material-icons right">list</i></a>
@endsection

<div class="section">
    
    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                    <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                        <div class="card-content red-text">
                            <ul></ul>
                        </div>
                    </div>
                    {!! Form::open(['class' => 'ajax-submit', 'id' => Str::camel($page->title) . 'Form']) !!}
                    {{ csrf_field() }}
                    {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle']) !!}
                    {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                    {!! Form::hidden('typeAheadSearchPath', url('service-category/autocomplete'), ['id' => 'typeAheadSearchPath']) !!}
                    {!! Form::hidden('service_id', $service->id ?? '', ['id' => 'service_id']) !!}
                    {!! Form::hidden('service_category_id', '', ['id' => 'service_category_id']) !!}
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('name', $service->name ?? '', ['id' => 'name']) !!}
                            <label for="name" class="label-placeholder active">Service Name <span
                                    class="red-text">*</span></label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input type="text" name="search_service_category" id="search_service_category"
                                class="typeahead autocomplete" value="{{ $service->serviceCategory->name ?? '' }}"
                                autocomplete="off" value="">
                            <label for="search_service_category" class="typeahead label-placeholder active">Enter
                                service category</label>
                            <!-- {!! Form::select('service_category_id', $variants->service_category, $service->service_category_id ?? '', [
                                'id' => 'service_category_id',
                                'class' => 'select2 browser-default',
                                'placeholder' => 'Please select service category',
                            ]) !!} -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('price', $service->price ?? '', ['id' => 'price', 'class' => 'check_numeric']) !!}
                            <label for="price" class="label-placeholder active">Price <span
                                    class="red-text">*</span></label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('hours_id', $variants->hours, $service->hours_id ?? '', [
                                'class' => 'select2 browser-default',
                                'id' => 'hours_id',
                                'placeholder' => 'Please select service minutes',
                            ]) !!}
                            <!-- <label for="hours_id" class="label-placeholder active">Minutes </label> -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            @php
                                $checked = '';
                                if (isset($service)) {
                                    $checked = $service->tax_included == 1 ? 'checked' : '';
                                }
                            @endphp
                            <div class="col s12">
                                <label for="tax_included">Check if tax is included with price !</label>
                                <p><label><input class="validate" value="1" name="tax_included" id="tax_included"
                                            type="checkbox" {{ $checked }}><span>Tax Included</span></label></p>
                                <div class="input-field"></div>
                            </div>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('gst_tax', $variants->tax_percentage, $service->gst_tax ?? '', [
                                'id' => 'gst_tax',
                                'class' => 'select2 browser-default',
                                'placeholder' => 'Select GST Tax %',
                            ]) !!}
                            <!-- <label for="gst_tax" class="label-placeholder active">Tax </label>                 -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('hsn_code', $service->hsn_code ?? '', ['id' => 'hsn_code']) !!}
                            <label for="hsn_code" class="label-placeholder active">SAC Code </label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('additional_tax[]', $variants->additional_tax, $variants->additional_tax_ids ?? [], [
                                'id' => 'additional_tax',
                                'multiple' => 'multiple',
                                'class' => 'select2 browser-default',
                            ]) !!}
                            <!-- <label for="additional_tax" class="label-placeholder active">Additional Tax </label> -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::select('lead_before', $variants->hours, $service->lead_before ?? '', [
                                'id' => 'lead_before',
                                'placeholder' => 'Select Lead time before',
                            ]) !!}
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('lead_after', $variants->hours, $service->lead_after ?? '', [
                                'id' => 'lead_after',
                                'placeholder' => 'Select Lead time after',
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
@include('services.import-browse-modal')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')

@endsection

@push('page-scripts')
<!-- typeahead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{ asset('admin/js/custom/service/service.js') }}"></script>
<script>
    var typeAheadSearchPath = $("#typeAheadSearchPath").val();

    $(function() {
        $('input.typeahead').typeahead({
            autoSelect: true,
            hint: true,
            highlight: true,
            minLength: 1,
            source: function(query, process) {
                return $.get(typeAheadSearchPath, {
                    search: query,
                    classNames: {
                        input: 'Typeahead-input',
                        hint: 'Typeahead-hint',
                        selectable: 'Typeahead-selectable'
                    }
                }, function(data) {
                    return process(data);
                });
            },
            updater: function(item) {
                $('#service_category_id').val(item.id);
                console.log(item.id)
                return item;
            }
        })
    });
</script>
@endpush
