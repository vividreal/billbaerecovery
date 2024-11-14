<table class="responsive-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Items</th>
            <th>Number of Items</th>
            <th>SAC Code #</th>
            <th class="right-align">Details</th>
        </tr>
    </thead>
    <tbody>
        @php
            $package_ids = [];
            $unique_package_ids = [];
            // if($billing_items['item_type']!=='dues'){
            foreach ($billing_items as $item) {
                $package_ids[] = $item->package_id ?? '';
            }
            $unique_package_ids = array_values(array_unique($package_ids));
            // }
        @endphp
        {{-- @if ($billing_items['item_type'] !== 'dues') --}}
        @if ($billing_items)
            @if (count($unique_package_ids) > 1)
                @foreach ($unique_package_ids as $id)
                    @php
                        $package_row_added = false;
                        $package_items = collect($billing_items->toArray())->where('package_id', $id);
                        $last_item_key = $package_items->keys()->last();
                        $package = \App\Models\Package::find($id);
                        $package_benefits = $package ? $package->benefits : []; // Assuming you have a 'benefits' relationship or method
                    @endphp
                    @foreach ($billing_items->toArray() as $key => $item)
                        @if ($item['package_id'] == $id)
                            @if (!$package_row_added)
                                <tr>
                                    <td colspan="5" style="text-align: center;">
                                        <strong>{{ $package->name ?? '' }}</strong>
                                    </td>
                                </tr>
                                {{-- Display package benefits --}}
                                @if ($package_benefits && count($package_benefits) > 0)
                                    <tr>
                                        <td colspan="5">
                                            <ul>
                                                @foreach ($package_benefits as $benefit)
                                                    <li>{{ $benefit->name }} - {{ $benefit->description }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                                @php
                                    $package_row_added = true;
                                @endphp
                            @endif
                            <tr id="{{ $item['id'] }}">
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $item['item_details'] }} ( {{ $item['tax_array']['tax_method'] }} ) <br>
                                    @php echo CURRENCY . ' ' . number_format($item['tax_array']['price'], 2) @endphp
                                </td>
                                <td>{{ $item['item_count'] }}</td>
                                <td>
                                    @if ($item['item_type'] == 'services')
                                        {{ App\Helpers\CustomHelper::serviceSAC($item['id'], $item['item_type']) }}
                                    @else
                                        {{ App\Helpers\CustomHelper::serviceSAC($item['item_id'], $item['item_type']) }}
                                    @endif
                                </td>
                                <td class="right-align">
                                    <ul>
                                        <li class="display-flex justify-content-between">
                                            <span class="invoice-subtotal-title">Service value</span>
                                            <h6 class="invoice-subtotal-value serviceValue_{{ $key }}">₹
                                                @php echo number_format(($item['tax_array']['amount']), 2) @endphp
                                            </h6>
                                        </li>
                                        @if ($item['tax_array']['cgst'] > 0)
                                            <li class="display-flex justify-content-between">
                                                <span class="invoice-subtotal-title">CGST
                                                    ({{ $item['tax_array']['cgst_percentage'] }}%)
                                                </span>
                                                <h6 class="invoice-subtotal-value cgst_{{ $key }}">₹
                                                    {{ $item['tax_array']['cgst'] }}</h6>
                                            </li>
                                        @endif
                                        @if ($item['tax_array']['sgst'] > 0)
                                            <li class="display-flex justify-content-between">
                                                <span class="invoice-subtotal-title">SGST
                                                    ({{ $item['tax_array']['sgst_percentage'] }}%)
                                                </span>
                                                <h6 class="invoice-subtotal-value sgst_{{ $key }}">₹
                                                    {{ $item['tax_array']['sgst'] }}</h6>
                                            </li>
                                        @endif
                                        @if (count($item['tax_array']['additiona_array']) > 0)
                                            <li class="divider mt-2 mb-2"></li>
                                            @foreach ($item['tax_array']['additiona_array'] as $addKey => $additional)
                                                <li class="display-flex justify-content-between">
                                                    <span class="invoice-subtotal-title">{{ $additional['name'] }}
                                                        ({{ $additional['percentage'] }}%)
                                                    </span>
                                                    <h6
                                                        class="invoice-subtotal-value additionalAmount_{{ $addKey }}">
                                                        ₹ @php echo number_format(($additional['amount'] * $item['item_count']), 2) @endphp
                                                    </h6>
                                                </li>
                                            @endforeach
                                        @endif
                                        @php
                                            $grand_total_amount = $item['tax_array']['total_amount'];
                                        @endphp
                                        <li class="divider mt-2 mb-2"></li>
                                        <li class="display-flex justify-content-between">
                                            <span class="invoice-subtotal-title">
                                                <h6>Total payable</h6>
                                            </span>
                                            <h6 class="invoice-subtotal-value totalPayable_{{ $key }}">₹
                                                @php echo number_format( ($grand_total_amount), 2) @endphp
                                            </h6>
                                        </li>
                                        @if ($key == $last_item_key)
                                            <li class="divider mt-2 mb-2"></li>
                                            <li class="display-flex justify-content-between">
                                                <span class="invoice-subtotal-title">
                                                    <h6>Package Benefit</h6>
                                                </span>
                                                <h6 class="invoice-subtotal-value  packageamount{{ $key }}">
                                                    ₹{{ number_format($item['tax_array']['packagePrice'], 2) }}
                                                </h6>
                                            </li>
                                            <li class="divider mt-2 mb-2"></li>
                                            <li class="display-flex justify-content-between">
                                                <span class="invoice-subtotal-title">
                                                    <h6>Package Price</h6>
                                                </span>
                                                <h6 class="invoice-subtotal-value packageamount{{ $key }}">
                                                    ₹{{ number_format($item['packagePrice'], 2) }}
                                                </h6>
                                            </li>
                                        @endif
                                        @if ($item['tax_array']['discount_applied'] == 1)
                                            <li class="divider mt-2 mb-2"></li>
                                            <li class="display-flex justify-content-between">
                                                <span class="invoice-subtotal-title bold">
                                                    <h6>Discount
                                                        @if ($item['tax_array']['discount_type'] == 'percentage')
                                                            ({{ $item['tax_array']['discount_value'] }}%)
                                                        @endif
                                                        @if ($item['tax_array']['discount_type'] == 'amount')
                                                            Amount
                                                        @endif
                                                    </h6>
                                                </span>
                                                <h6 class="invoice-subtotal-value discountAmount_{{ $key }}">
                                                    - ₹ @php echo number_format($item['tax_array']['discount_amount'], 2) @endphp
                                                </h6>
                                            </li>
                                        @endif
                                    </ul>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
               
            @else
                @foreach ($billing_items->toArray() as $key => $item)
                    <tr id="{{ $item['id'] }}">
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $item['item_details'] }} ( {{ $item['tax_array']['tax_method'] }} ) <br>
                            @php echo CURRENCY . ' ' . number_format($item['tax_array']['price'],2) @endphp </td>
                        <td>{{ $item['item_count'] }}</td>
                        <td>

                            @if ($item['item_type'] == 'services' && $customerMembership->is_membership_holder == 0)
                                {{ App\Helpers\CustomHelper::serviceSAC($item['id'], $item['item_type']) }}
                            @elseif($item['item_type'] == 'packages')
                                {{ App\Helpers\CustomHelper::serviceSAC($item['item_id'], $item['item_type']) }}
                            @else
                                <span>---</span>
                            @endif

                        </td>
                        <td class="right-align">
                            <ul>
                                <li class="display-flex justify-content-between">
                                    <span class="invoice-subtotal-title">Service value</span>
                                    <h6 class="invoice-subtotal-value serviceValue_{{ $key }}">₹
                                        @php echo number_format(($item['tax_array']['amount']),2)  @endphp</h6>
                                </li>
                                @if ($item['tax_array']['cgst'] > 0)
                                    <li class="display-flex justify-content-between">
                                        <span class="invoice-subtotal-title">CGST
                                            ({{ $item['tax_array']['cgst_percentage'] }}%)
                                        </span>
                                        {{-- <h6 class="invoice-subtotal-value cgst">₹ {{ ($item['tax_array']['cgst'] * $item['item_count'])  }}</h6> --}}
                                        <h6 class="invoice-subtotal-value cgst_{{ $key }}">₹
                                            {{ $item['tax_array']['cgst'] }}</h6>
                                    </li>
                                @endif
                                @if ($item['tax_array']['sgst'] > 0)
                                    <li class="display-flex justify-content-between">
                                        <span class="invoice-subtotal-title">SGST
                                            ({{ $item['tax_array']['sgst_percentage'] }}%) </span>
                                        {{-- <h6 class="invoice-subtotal-value sgst">₹ {{ ($item['tax_array']['sgst'] * $item['item_count']) }}</h6> --}}
                                        <h6 class="invoice-subtotal-value sgst_{{ $key }}">₹
                                            {{ $item['tax_array']['sgst'] }}</h6>
                                    </li>
                                @endif
                                @if (count($item['tax_array']['additiona_array']) > 0)
                                    <li class="divider mt-2 mb-2"></li>
                                    @foreach ($item['tax_array']['additiona_array'] as $key => $additional)
                                        <li class="display-flex justify-content-between">
                                            <span class="invoice-subtotal-title">{{ $additional['name'] }}
                                                ({{ $additional['percentage'] }}%)
                                            </span>
                                            <h6 class="invoice-subtotal-value additionalAmount_{{ $key }}">
                                                ₹ @php echo number_format(($additional['amount'] * $item['item_count']), 2) @endphp</h6>
                                        </li>
                                    @endforeach
                                @endif
                                @php
                                    $grand_total_amount = $item['tax_array']['total_amount'];

                                @endphp
                                <li class="divider mt-2 mb-2"></li>
                                <li class="display-flex justify-content-between">
                                    <span class="invoice-subtotal-title">
                                        <h6>Total payable</h6>
                                    </span>
                                    <h6 class="invoice-subtotal-value totalPayable_{{ $key }}">₹
                                        @php echo number_format( ($grand_total_amount), 2)  @endphp</h6>
                                </li>
                                @if ($item['item_type'] == 'packages')
                                    <li class="divider mt-2 mb-2"></li>
                                    <li class="display-flex justify-content-between">
                                        <span class="invoice-subtotal-title">
                                            <h6>Package Benefit</h6>
                                        </span>
                                        <h6 class="invoice-subtotal-value  packageamount{{ $key }}">
                                            <span>-</span>₹{{ number_format($item['tax_array']['packagePrice'], 2) }}
                                        </h6>
                                    </li>
                                @endif
                                @if ($item['item_type'] == 'packages')
                                    <li class="divider mt-2 mb-2"></li>
                                    <li class="display-flex justify-content-between">
                                        <span class="invoice-subtotal-title">
                                            <h6>Package Price</h6>
                                        </span>
                                        <h6 class="invoice-subtotal-value  packageamount{{ $key }}">
                                            ₹{{ number_format($item['packagePrice'], 2) }}
                                        </h6>
                                    </li>
                                @endif
                                @if ($item['tax_array']['discount_applied'] == 1)
                                    <li class="divider mt-2 mb-2"></li>
                                    <li class="display-flex justify-content-between">
                                        <span class="invoice-subtotal-title bold">
                                            <h6>Discount
                                                @if ($item['tax_array']['discount_type'] == 'percentage')
                                                    ({{ $item['tax_array']['discount_value'] }}%)
                                                @endif
                                                @if ($item['tax_array']['discount_type'] == 'amount')
                                                    Amount
                                                @endif
                                            </h6>
                                        </span>
                                        <h6 class="invoice-subtotal-value discountAmount_{{ $key }}">
                                            - ₹ @php echo number_format($item['tax_array']['discount_amount'],2) @endphp</h6>
                                    </li>
                                @endif
                                @if ($customerMembership->is_membership_holder == 0)
                                    @if ($item['item_type'] == 'services')
                                        <li>
                                            <div id="discountDiv">
                                                @if ($item['tax_array']['discount_applied'] == 0)
                                                    <span class="new badge gradient-45deg-light-blue-cyan"
                                                        style="cursor: pointer;" data-badge-caption="Add Discount"
                                                        data-id="@if ($item['item_type'] == 'services') {{ $item['billingItemsId'] }}@else {{ $item['billingItemsId'] }} @endif"
                                                        data-action="add" onClick="manageDiscount(this)"><i
                                                            class="material-icons right">add</i></span>
                                                @else
                                                    <span class="new badge" style="cursor: pointer;"
                                                        data-badge-caption="Remove Discount"
                                                        data-id="@if ($item['item_type'] == 'services') {{ $item['billingItemsId'] }}@else {{ $item['billingItemsId'] }} @endif"
                                                        data-action="remove" onClick="manageDiscount(this)"><i
                                                            class="material-icons right">undo</i></span>
                                                @endif
                                            </div>
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </td>
                    </tr>
                @endforeach
            @endif

        @endif

    </tbody>
</table>
