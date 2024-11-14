@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? ''}} @endsection


{{-- vendor styles --}}
@section('vendor-style')

@endsection

{{-- page style --}}
@section('page-style')
@endsection

@section('content')

@section('breadcrumb')
<h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
<ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('page-action')
<a href="{{ url(ROUTE_PREFIX.'/'.$page->route.'/create/') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create {{ Str::singular($page->title) ?? ''}}<i class="material-icons right">add</i></a>
<a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List {{ Str::plural($page->title) ?? ''}}<i class="material-icons right">list</i></a>
@endsection


<div class="section">

    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? ''}} Form</h4>
                    <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                        <div class="card-content red-text">
                            <ul></ul>
                        </div>
                    </div>
                    {!! Form::open(['class'=>'ajax-submit','id'=> Str::camel($page->title).'Form']) !!}
                    {{ csrf_field() }}
                    {!! Form::hidden('currency', CURRENCY , ['id' => 'currency'] ); !!}
                    {!! Form::hidden('package_id', $package->id ?? '' , ['id' => 'package_id'] ); !!}
                    {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!}
                    {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
                    {!! Form::hidden('timePicker', '', ['id' => 'timePicker'] ); !!}
                    {!! Form::hidden('timeFormat', '', ['id' => 'timeFormat'] ); !!}
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('name', $package->name ?? '', ['id' => 'name']) !!}
                            <label for="name" class="label-placeholder">Package Name <span class="red-text">*</span></label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('services[]', $variants->services, [], ['id' => 'services', 'multiple' => 'multiple', 'class' => 'select2 browser-default']) !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m12 s12">
                            <div class="form-group" id="usedServicesDiv" style="display:none;">
                                <h5 class="card-title">Services </h5>
                                <table class="table table-hover text-nowrap" id="servicesTable">
                                    <thead>
                                        <tr>
                                            <th> Name </th>
                                            <th> Time </th>
                                            <th> Price </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input class="form-control check_numeric" type="text" name="price" id="price" value="" />
                            <input class="form-control" type="hidden" name="totalPrice" id="totalPrice" value="" />
                            <input class="form-control" type="hidden" name="discount" id="discount" value="" />
                            <label for="price" class="label-placeholder">Package Price <span class="red-text">*</span></label>
                        </div>
                        {{-- <div class="input-field col m6 s12">
                            {!! Form::text('hsn_code', $service->hsn_code ?? '', ['id' => 'hsn_code']) !!}
                            <label for="hsn_code" class="label-placeholder">SAC Code</label>
                        </div> --}}
                        {{-- <div class="input-field col m6 s12">
                    {!! Form::text('instore_credit_amount', $package->instore_credit_amount ?? '', ['id' => 'instore_credit_amount']) !!}  
                    <label for="instore_credit_amount" class="label-placeholder">In-Store Credit Amount</label>
                </div> --}}

                        {{-- <div class="input-field col m6 s12">
                            <input type="text" name="validity_from" id="validity_from" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                            <label for="validity_from" class="label-placeholder active">Validity Starting Date </label>
                        </div> --}}
                        {{-- <div class="input-field col m6 s12">
                            <input type="text" name="validity_to" id="validity_to" class="form-control" onkeydown="return false" autocomplete="off" value="" />
                            <label for="validity_to" class="label-placeholder active">Validity Expiring Date </label>
                        </div> --}}
                        {{-- <div class="input-field col m6 s12">
                            {!! Form::text('validity', $package->validity ?? '' , array( 'id' => 'validity','class' => 'check_numeric')) !!}
                            <label for="validity" class="label-placeholder">Validity Period</label>
                            <span class="helper-text">Accepts digits Only. Enter Numbers </span>
                        </div> --}}
                       
                    </div>
                    {{-- <div class="row">
                <div class="input-field col m6 s12">
                  <div class="col s12">
                    @php 
                      $checked = '';
                        if(isset($service)){
                          $checked = ($service->tax_included == 1) ? 'checked' : '' ; 
                        }                      
                    @endphp
                    <!-- <label for="tax_included">Check if tax is included with price !</label> -->
                    <p><label><input class="custom-control-input" type="checkbox" name="tax_included" id="tax_included" value="1" {{ $checked }} ><span>Tax Included</span></label></p>
                    <span class="helper-text">Please Check if tax is included with Price !</span>
                    <div class="input-field">
                    </div>
                </div>
            </div>
            <div class="input-field col m6 s12">
                {!! Form::select('gst_tax', $variants->tax_percentage , $service->gst_tax ?? '' , ['id' => 'gst_tax', 'class' => 'select2 browser-default', 'placeholder'=>'Select GST Tax %']) !!}
            </div>
        </div> --}}
        <div class="row">

            {{-- <div class="input-field col m6 s12">
                {!! Form::select('additional_tax[]', $variants->additional_tax, $variants->additional_tax_ids ?? [] , ['id' => 'additional_tax', 'multiple' => 'multiple' ,'class' => 'select2 browser-default']) !!}
                </div> --}}
        </div>
        <div class="row">
            <div class="input-field col s12">
                <button class="btn waves-effect waves-light" type="button" name="reset" id="reset-btn">Reset <i class="material-icons right">refresh</i></button>
                <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
            </div>
        </div>
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
    var getServices = "{{route('getServices')}}";

</script>

<script src="{{asset('admin/js/custom/package/package.js')}}"></script>
<script>
</script>
@endpush
