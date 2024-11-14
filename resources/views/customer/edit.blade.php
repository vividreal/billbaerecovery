@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('page-style')
<style>
.input-field input[type=text]:not(.browser-default):focus:not([readonly]) {border-bottom: 1px solid #9e9e9e;
    box-shadow: 0 1px 0 0 #9e9e9e; }
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


<div class="seaction">

    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                    {!! Form::open(['class' => 'ajax-submit', 'id' => Str::camel($page->title) . 'Form']) !!}
                    {{ csrf_field() }}
                    {!! Form::hidden('customer_id', $customer->id ?? '', ['id' => 'customer_id']) !!}
                    {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle']) !!}
                    {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('name', $customer->name ?? '', ['id' => 'name']) !!}
                            <label for="name" class="label-placeholder active">Customer Name <span
                                    class="red-text">*</span></label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::text('email', $customer->email ?? '', ['autocomplete' => 'off', 'id' => 'email']) !!}
                            <label for="email" class="label-placeholder active">Email </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s2">
                            <div class="input-field">
                                {!! Form::select('phone_code', $variants->phonecode, $customer->phone_code ?: $store->country_id ?? '', [
                                    'id' => 'phone_code',
                                    'class' => 'select2 browser-default',
                                    'placeholder' => 'Please select phone code',
                                ]) !!}
                                <label for="phone_code" class="label-placeholder active">Phone code </label>
                            </div>
                        </div>
                        <div class="input-field col m4 s12">
                            {!! Form::text('mobile', $customer->mobile ?? '', ['id' => 'mobile']) !!}
                            <label for="mobile" class="label-placeholder active">Mobile </label>
                        </div>
                        <div class="input-field col m6 s12">
                            <label for="gender" class="label-placeholder active"> Gender </label>
                            <p style="margin-top: 23px;">
                                <label>
                                    <input value="1" id="male" name="gender" type="radio"
                                        @if ($customer->gender == 1) checked @endif />
                                    <span> Male </span>
                                </label>
                                <label>
                                    <input value="2" id="female" name="gender" type="radio"
                                        @if ($customer->gender == 2) checked @endif />
                                    <span> Female </span>
                                </label>
                                <label>
                                    <input value="3" id="others" name="gender" type="radio"
                                        @if ($customer->gender == 3) checked @endif />
                                    <span> Others </span>
                                </label>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            @php
                                $dob =
                                    $customer->dob != ''
                                        ? $customer->dob->format('d-m-Y')
                                        : Carbon\Carbon::now()->format('d-m-Y');
                            @endphp
                            <input type='text' name="dob" id="dob" onkeydown="return false" class=""
                                value="{{ $dob }}" autocomplete="off" />
                            <label for="dob" class="label-placeholder active">DOB</label>
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::select('country_id', $variants->countries, $customer->country_id ?: $store->country_id ?? '', [
                                'id' => 'country_id',
                                'class' => 'select2 browser-default',
                                'placeholder' => 'Please select country',
                            ]) !!}
                            <label for="country_id" class="label-placeholder active">Country </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('pincode', $customer->pincode ?? '', ['class' => 'check_numeric']) !!}
                            <label for="pincode" class="label-placeholder active">Pincode </label>
                        </div>
                        <div class="input-field col m6 s12">
                            <div id="state_block">
                                @if (!empty($variants->states))
                                    {!! Form::select('state_id', $variants->states, $customer->state_id ?? '', [
                                        'id' => 'state_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select state',
                                    ]) !!}
                                @else
                                    {!! Form::select('state_id', [], '', [
                                        'id' => 'state_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select state',
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::text('gst', $customer->gst ?? '', ['id' => 'gst']) !!}
                            <label for="gst" class="label-placeholder active">GST No </label>
                        </div>
                        <div class="input-field col m6 s12">
                            <div id="state_block">
                                @if (!empty($variants->districts))
                                    {!! Form::select('district_id', $variants->districts, $customer->district_id ?? '', [
                                        'id' => 'district_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select district',
                                    ]) !!}
                                @else
                                    {!! Form::select('district_id', [], '', [
                                        'id' => 'district_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select district',
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m12 s12">
                            {!! Form::textarea('address', $customer->address ?? '', ['class' => 'materialize-textarea', 'rows' => 3]) !!}
                            <label for="address" class="label-placeholder active">Address</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            <label for="in-score-credit" class="label-placeholder active"> Is in-store credit required?
                            </label>
                            <p style="margin-top: 23px;">
                                <label>
                                    <input value="1" id="in_store_credit_yes" name="in_store_credit_radio"
                                        type="radio" @if ($customer->is_instore_credit == 1) checked @endif />
                                    <span> Yes </span>
                                </label>
                                <label>
                                    <input value="0" id="in_store_credit_no" name="in_store_credit_radio"
                                        type="radio" @if ($customer->is_instore_credit == 0) checked @endif />
                                    <span> No </span>
                                </label>
                            </p>
                        </div>
                        <div class="input-field col m6 s12">
                            {{-- @dd($customer->pendingDues) --}}
                            @php
                                use Carbon\Carbon;
                                $expiryDate = \Carbon\Carbon::now()->format('d-m-Y');
                                foreach ($customer->pendingDues as $key => $value) {
                                    $customerOverPaid = $value->over_paid;
                                    $expiryDate = \Carbon\Carbon::parse($value->validity_to)->format('d-m-Y');
                                }

                            @endphp

                            {!! Form::text('in_store_credit', $customerOverPaid ?? '', [
                                'class' => 'check_numeric',
                                'id' => 'in_store_credit',
                                'data-customer-id' => $customer->id,
                            ]) !!}
                            <label for="in-score-credit" class="label-placeholder active" id="credit_lable"> Credit
                                Amount </label>
                        </div>
                        {{-- <div class="input-field col m6 s12">
                            {!! Form::select('gst_tax', $variants->tax_percentage , 4 , ['id' => 'gst_tax', 'class' => 'select2 browser-default', 'placeholder'=>'Select GST Tax %']) !!}
                            <!-- <label for="gst_tax" class="label-placeholder active">Tax </label>                 -->
                        </div> --}}
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12 validity_class">
                            <input type="text" name="validity_from" id="validity_from" class="form-control"
                                onkeydown="return false" autocomplete="off" value="" />
                            <label for="validity_from" class="label-placeholder active instoreLable">Validity Starting
                                Date </label>
                        </div>
                        {{-- <div class="input-field col m6 s12">
                            <input type="text" name="validity_to" id="validity_to" class="form-control" onkeydown="return false" autocomplete="off" value="{{$expiryDate}}" />
                            <label for="validity_to" class="label-placeholder active instoreLable">Validity Expiring Date </label>
                        </div> --}}
                        <div class="input-field col m6 s12 validity_class">
                            <select id="validity" class="form-control validity" name="validity">
                                <option selected="selected" value="2">2 Days</option>
                                <option value="5">5 Days</option>
                                <option value="7">7 Days</option>
                                <option value="10">10 Days</option>
                                <option value="15">15 Days</option>
                                <option value="30">30 Days</option>
                                <option value="60">60 Days</option>
                                <option value="90">90 Days</option>
                                <option value="180">6 Month</option>
                                <option value="365">1 Year</option>
                            </select>
                            <label for="validity" class="label-placeholder">Validity Period </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class=" col s12">
                            <button class="btn waves-effect waves-light" type="button" id="reset-btn"
                                name="reset">Reset <i class="material-icons right">refresh</i></button>
                            <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                id="submit-btn">Submit <i class="material-icons right">send</i></button>
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
    var customerEmail = "{{ route('customer.uniqueEmail') }}";
    var get_states_by_country = "{{ route('getStatesByCountry') }}";
    var get_districts_by_state = "{{ route('get_districts_by_state') }}";
    var updateInstoreCredit = "{{ route('customers.updateInstoreCredit') }}";
    var timePicker = {!! json_encode($variants->time_picker) !!};
    var timeFormat = {!! json_encode($variants->time_format) !!};
</script>
<!-- date-time-picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('admin/js/custom/customer/customer.js') }}"></script>
<script></script>
@endpush
