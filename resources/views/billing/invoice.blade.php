@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- page style --}}
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

@section('page-action')
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
        class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create
        {{ Str::singular($page->title) ?? '' }}<i class="material-icons right">add</i></a>
    <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/' . $billing->id . '/edit/') }}"
        class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Edit Invoice<i
            class="material-icons right">mode_edit</i></a>
@endsection

<section class="invoice-view-wrapper section">
    <div class="row">
        <!-- invoice view page -->
        <div class="col xl12 m12 s12">
            <div class="card">
                <div class="card-content invoice-print-area">
                    @include('layouts.success')
                    <!-- header section -->
                    <div class="row invoice-date-number">
                    </div>
                    <!-- logo and title -->
                    <div class="row mt-3 ">
                        <div class="col m2 s9 ">
                            <h4 class="indigo-text">Invoice</h4>
                        </div>
                      
                        <div class="col m6 s9 " style="text-align:center;">
                            <h5 class="proton-logo "><span> {{ $variants->shop->name ?? '' }}</span></h5>

                        </div>
                        @php
                         
                            $serviceType = $billing->items[0]->item_typen ?? 'Due Payment';
                           
                        @endphp
                            @if (count($variants->customerMembership)> 0)
                                <div class="col m2 s9 float-right ">
                                    <lable>Membership Purchased:</lable>
                                    <h5 class="proton-logo" id="service_type" data-type="membership">
                                        @foreach ($variants->customerMembership as $membership)
                                            {{ $membership->membership->name }}
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </h5>
                                </div>
                            @endif

                    </div>
                    <div class="divider mb-3 mt-3"></div>
                    <div class="row invoice-info">
                        <div class="col m4 s9 align-right">
                            <h6 class="invoice-from">Bill From</h6>
                            <div class="invoice-address">
                                <span>{{ $billing->store->billing->company_name ?? '' }}</span>
                            </div>
                            <div class="invoice-address"><span>{{ $billing->store->email ?? '' }}</span></div>
                            <div class="invoice-address"><span>{{ $billing->store->contact ?? '' }}</span></div>
                            <div class="invoice-address"><span>{{ $billing->store->billing->address ?? '' }}</span>
                            </div>
                        </div>
                        <div class="col m4 s9 align-right">
                            <div class="divider show-on-small hide-on-med-and-up mb-3"></div>
                            <h6 class="invoice-to">Bill To</h6>
                            <div class="invoice-address"><span>{{ $billing->customer->name ?? '' }}.</span></div>
                            <div class="invoice-address"><span>{{ $billing->customer->mobile ?? '' }}</span></div>
                            <div class="invoice-address"><span>{{ $billing->customer->email ?? '' }}</span></div>
                            @if ($billing->address_type == 'customer')
                                <div class="invoice-address"><span>{{ $billing->customer->address ?? '' }}</span></div>
                            @else
                                <div class="invoice-address">
                                    <span>{{ $billing->customer->billingaddress->billing_name ?? '' }}</span>
                                </div>
                                <div class="invoice-address">
                                    <span>{{ $billing->customer->billingaddress->address ?? '' }}</span>
                                </div>
                                <div class="invoice-address">
                                    <span>
                                        @if (!empty($billing->customer->billingaddress->pincode))
                                            Pincode : {{ $billing->customer->billingaddress->pincode ?? '' }} ,
                                        @endif
                                        @if (!empty($billing->customer->billingaddress->gst))
                                            GST : {{ $billing->customer->billingaddress->gst ?? '' }}
                                        @endif
                                    </span>
                                </div>
                                <div class="invoice-address">
                                    <span>{{ $billing->customer->billingaddress->shopCountry->name ?? '' }},
                                        {{ $billing->customer->billingaddress->ShopState->name ?? '' }},
                                        {{ $billing->customer->billingaddress->ShopDistrict->name ?? '' }} </span>
                                </div>
                            @endif
                        </div>

                        @if ($serviceType != 'memberships')
                            <div class="col m4 s9 align-right">
                                <div class="divider show-on-small hide-on-med-and-up mb-3"></div>
                                <h6 class="invoice-to">Service Type</h6>
                                @if (!empty($package))
                                    <p>Package:
                                        @foreach ($package as $key => $value)
                                            <span>{{ $value->name ?? '' }},</span>
                                        @endforeach
                                    </p>
                                @elseif(!empty($service))
                                    <p>Service:
                                        @foreach ($service as $key => $value)
                                            <span>{{ $value->serviceCategory->name ?? '' }},</span>
                                        @endforeach
                                    </p>
                                @else
                                  <p>Due Payment: Due</p>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
                <div class="divider mb-2 mt-3 mr-2 ml-2"></div>
                <!-- product details table-->
                <div class="invoice-product-details ml-2 mr-2" id="invoiceTable">
                </div>
                <!-- invoice subtotal -->
                <div class="invoice-subtotal">
                    <div class="row">
                        <div class="col m7 s12">
                            <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                                <div class="card-content red-text">
                                    <ul></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="invoice-subtotal ml-2 mr-2">
                    <div class="row">
                        <div class="col m5 s12 ">
                            <h6 class="lead">Payment Methods:</h6>
                            <form id="paymentForm" name="paymentForm" role="form" method="POST" action=""
                                class="ajax-submit">
                                {{ csrf_field() }}
                                {!! Form::hidden('billing_id', $billing->id ?? '', ['id' => 'billing_id']) !!}
                                {!! Form::hidden('membership_id', '', ['id' => 'membership_id']) !!}
                                {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle']) !!}
                                {!! Form::hidden('sub_total', '', ['id' => 'sub_total', 'class' => 'subTotal']) !!}
                                {!! Form::hidden('grand_total', '', ['id' => 'grand_total', 'class' => 'grandTotal']) !!}
                                {!! Form::hidden('payment_types', $variants->payment_types, ['id' => 'payment_types']) !!}
                                {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                                {!! Form::hidden('item_ids', json_encode($variants->item_ids) ?? '', ['id' => 'item_ids']) !!}
                                {!! Form::hidden('pending_amount', '', ['id' => 'pending_amount']) !!}
                                {!! Form::hidden('currency', CURRENCY, ['id' => 'currency']) !!}
                                {!! Form::hidden('in_store_credit_amount', 0, ['id' => 'in_store_credit_amount']) !!}
                                {!! Form::hidden('discount', 0, ['id' => 'discount_amount']) !!}
                                <table class="table" id="dynamic_field">
                                    @php
                                        $over_paid = 0;
                                        if ($billing->customer) {
                                            foreach ($billing->customer->pendingDues as $key => $value) {
                                                if ($value->over_paid != 0 && $value->removed == 0) {
                                                    $over_paid += $value->over_paid;
                                                }
                                            }
                                        }

                                    @endphp
                                    @if ($serviceType != 'memberships')
                                        <tr>
                                            <td>
                                                <div class="input-field">
                                                    <h6>In-Store Credit</h6>
                                                </div>
                                            </td>
                                            <td>
                                                <input name="payment_value[]" type="number" id="in_store_credit"
                                                    data-name="In-storeCredit" data-id="3"
                                                    data-customerid="{{ $billing->customer->id }}"
                                                    data-billingid="{{ $billing->id }}" placeholder="Amount"
                                                    class="customer-payments in_store_credit" value="">
                                                <label
                                                    class="payment-value-error red-text in-store-error-label"></label>
                                                <button type="button"
                                                    class="remove-button new badge gradient-45deg-light-blue-cyan"
                                                    data-billingid="{{ $billing->id }}"
                                                    onclick="removeInputContainer(this)">Remove</button>
                                            </td>
                                            <td>
                                                <span class="invoice-subtotal-title" style="padding-right: 5%">Total
                                                    Credit </span>
                                                <span id="inStoreCredit"
                                                    class="invoice-subtotal-value green-text"></span>
                                            </td>
                                            <td>
                                                <span class="invoice-subtotal-title" style="padding-right: 5%">In-store
                                                    Credit </span>
                                                <span id="inStoreCredit_non_membership"
                                                    class="invoice-subtotal-value green-text"></span>
                                            </td>
                                            <td>
                                                <span class="invoice-subtotal-title"
                                                    style="padding-right: 5%">Membership Credit </span>
                                                <span id="membershipInStoreCredit"
                                                    class="invoice-subtotal-value green-text"></span>
                                            </td>
                                        </tr>
                                    @endif

                                    @foreach ($variants->payment_types as $payment_type)
                                        <tr>
                                            <td>
                                                <div class="input-field">
                                                    <h6>{{ $payment_type->name }}</h6>
                                                </div>
                                            </td>
                                            <td>
                                                <input name="payment_value[]" type="number"
                                                    data-name="{{ $payment_type->name }}"
                                                    data-id="{{ $payment_type->id }}" placeholder="Amount"
                                                    class="customer-payments" value="">
                                                <label class="payment-value-error red-text"></label>
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </form>
                        </div>

                        <div class="col xl4 m5 s12 offset-xl3">
                            <ul>
                                <li class="display-flex justify-content-between">
                                    <p><span class="invoice-subtotal-title">Total</span></p>
                                    <h6 class="invoice-subtotal-value grandTotal grandItemTotal"><span
                                            id="grandItemTotal"></span></h6>
                                </li>
                                <li class="divider mt-2 mb-2"></li>
                                <li class="display-flex justify-content-between">
                                    <span class="invoice-subtotal-title">In-store
                                        Credit</span>
                                    <h6 class="invoice-subtotal-value green-text inStoreCreditBalance "> <span
                                            id="inStoreCreditBalance">-</span></h6>
                                </li>

                                @if ($variants->customerMembership->count() == 0)
                                    <li class="divider mt-2 mb-2"></li>
                                    <li class="display-flex justify-content-between">
                                        <span class="invoice-subtotal-title">Discount </span>
                                        <h6 class="invoice-subtotal-value green-text discountAmount"><span
                                                id="discountAmount"></span>
                                        </h6>
                                    </li>
                                @endif
                                <li class="divider mt-2 mb-2 display_item"></li>
                                <li class="display-flex justify-content-between display_item">
                                    <span class="invoice-subtotal-title">Dues </span>
                                    <h6 class="invoice-subtotal-value red-text"><span id="customerDues"></span>
                                    </h6>
                                </li>
                                <li class="divider mt-2 mb-2"></li>
                                <li class="display-flex justify-content-between">
                                    <p><span class="invoice-subtotal-title">Subtotal</span></p>
                                    <h6 class="invoice-subtotal-value subTotalItem"><span id="subTotal"
                                            class="subTotal"></span></h6>
                                </li>
                                <li class="divider mt-2 mb-2"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row mr-2">
                    <div class="input-field col s12 right-align ">
                        <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                            id="submit-payment-btn">Submit Payment <i class="material-icons right">send</i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- invoice action  -->
    </div>
</section>
@include('billing.discount-manage')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script>
    var getInvoice = "{!! route('getInvoiceData', $billing->id) !!}";
</script>
<script src="{{ asset('admin/js/custom/billing/invoice.js') }}"></script>
@endpush
