@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/app-invoice.css') }}">
    <style>
        #manage-bill-refund-modal {
            padding: 20px;
            overflow-x: hidden;
        }

        .disabled-select {
            pointer-events: none;
            background-color: #f0f0f0;
            /* Optional: To indicate the field is disabled */
        }

        .service_checkbox {
            position: relative !important;
            opacity: unset !important;
            pointer-events: none !important;
            margin: inherit;
        }

        .package_checkbox {
            position: relative !important;
            opacity: unset !important;
            pointer-events: none !important;
            margin: inherit;
        }

        .checkbox-container {
            display: flex;
            flex-wrap: wrap;
        }

        .checkbox-container label {
            margin-right: 20px;
            /* Adjust spacing as needed */
            pointer-events: auto;
            /* Enable pointer events for labels */
        }
    </style>
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
    @can('billing-create')
        <a href="{{ url(ROUTE_PREFIX . '/' . $page->route . '/create/') }}"
            class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit" name="action">Create
            {{ Str::singular($page->title) ?? '' }}<i class="material-icons right">add</i></a>
    @endcan
    {{-- @can('refund-bill') --}}
    <a href="{{ route('billings.refundBill') }}" class="btn waves-effect waves-light cyan breadcrumbs-btn" type="submit"
        name="action">Refund {{ Str::singular($page->title) ?? '' }}<i class="material-icons right">add</i></a>
    {{-- @endcan --}}

    <!--@can('billing-list')
        -->
        <!--<a href="{{ url(ROUTE_PREFIX . '/' . $page->route) }}" class="btn waves-effect waves-light light-blue darken-4 breadcrumbs-btn" type="submit" name="action">List {{ Str::plural($page->title) ?? '' }}<i class="material-icons right">list</i></a>-->
        <!--
    @endcan-->
@endsection

<div class="section section-data-tables">

    <div class="row">
        <div class="col s12 m6 l12">
            <div id="button-trigger" class="card card card-default scrollspy"></div>
        </div>
    </div>
    <!-- DataTables example -->
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ Str::plural($page->title) ?? '' }} Table</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                        <form id="dt-filter-form" name="dt-filter-form">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="input-field col m6 s12">
                                                    {!! Form::text('invoice', '', ['id' => 'invoice']) !!}
                                                    <label for="invoice" class="label-placeholder active">Invoice
                                                        ID</label>
                                                </div>
                                                <div class="input-field col m6 s12" style="margin-top: 11px">
                                                    {!! Form::select(
                                                        'search_customer_id[]',
                                                        $variants->customers,
                                                        [],
                                                        ['id' => 'search_customer_id', 'class' => 'select2 browser-default form-control', 'multiple' => 'multiple'],
                                                    ) !!}
                                                    <!-- <label for="customer_id" class="label-placeholder active">Customers</label> -->
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="input-field col m6 s12">
                                                    {!! Form::select(
                                                        'payment_status',
                                                        [1 => 'Completed Bills', 0 => 'Incomplete Bills', 3 => 'Due Payment Bills', 4 => 'Over Paid Bills'],
                                                        '',
                                                        ['id' => 'payment_status', 'class' => 'select2 browser-default', 'placeholder' => 'Search by status'],
                                                    ) !!}
                                                    <!-- <label for="payment_status" class="label-placeholder active">Payment Status</label> -->
                                                </div>
                                                <div class="input-field col m6 s12">
                                                    <div style="margin-top: 10px;">
                                                        <button type="button" class="btn mr-2 cyan"
                                                            id="dt-filter-form-show-result-button">Show Result</button>
                                                        <button type="button" class="btn"
                                                            id="dt-filter-form-clear-button">Clear Filter </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <table id="data-table-billing" class="display data-tables" data-url="{{ $page->link }}"
                                data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        <th width="10px" data-orderable="false" data-column="DT_RowIndex">No <span
                                                style="font-family: DejaVu Sans; sans-serif;">&#8377;</span></th>
                                        <th width="180px" data-orderable="false" data-column="billing_code">Invoice ID
                                        </th>
                                        <th width="180px" data-orderable="false" data-column="billed_date">Billed Date
                                        </th>
                                        <th width="280px" data-orderable="false" data-column="customer_id">Customer
                                            Name</th>
                                        <th width="70px" data-orderable="false" data-column="payment_status">Payment
                                            Status</th>
                                        <th width="150px" data-orderable="false" data-column="actual_amount">Bill Value
                                        </th>
                                        <th width="200px" data-orderable="false" data-column="updated_date">Paid on
                                        </th>
                                        <th width="100px" data-orderable="false" data-column="action">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('billing.manage')

@endsection
{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script src="{{ asset('admin/js/custom/billing/billing.js') }}"></script>
<script>
    function deleteBill(b) {
        swal({
            title: "Are you sure?",
            icon: 'warning',
            dangerMode: true,
            buttons: {
                cancel: 'No, Please!',
                delete: 'Yes, Delete It'
            }
        }).then(function(willDelete) {
            if (willDelete) {
                $.ajax({
                        url: "{{ url(ROUTE_PREFIX . '/' . $page->route) }}/" + b,
                        type: "DELETE",
                        dataType: "html"
                    })
                    .done(function(a) {
                        var data = JSON.parse(a);
                        if (data.flagError == false) {
                            showSuccessToaster(data.message);
                            setTimeout(function() {
                                // table.ajax.reload();
                                table.DataTable().draw();
                            }, 2000);
                        } else {
                            showErrorToaster(data.message);
                            printErrorMsg(data.error);
                        }
                    }).fail(function() {
                        showErrorToaster("Something went wrong!");
                    });
            }
        });
    }
    // function billCancel(b) {
    //   console.log(b);
    //   swal({
    //     title: "Are you sure?",
    //     icon: 'warning',
    //     dangerMode: true,
    //     buttons: {
    //       cancel: 'No, Please!',
    //       delete: 'Yes, Cancel It'
    //     }
    //   }).then(function(willDelete) {
    //     if (willDelete) {
    //       $.ajax({
    //           url: "{{ route('billings.cancelBillPayment') }}" ,
    //           type: "POST",
    //           data:{
    //             id:b
    //           },
    //           dataType: "html"
    //         })
    //         .done(function(a) {
    //           var data = JSON.parse(a);
    //           if (data.flagError == false) {
    //             showSuccessToaster(data.message);
    //             setTimeout(function() {
    //               // table.ajax.reload();
    //               table.DataTable().draw();
    //             }, 2000);
    //           } else {
    //             showErrorToaster(data.message);
    //             printErrorMsg(data.error);
    //           }
    //         }).fail(function() {
    //           showErrorToaster("Something went wrong!");
    //         });
    //     }
    //   });
    // }
    function billCancel(bill_id, element) {
        var paymentStatus = $(element).data('payment_status'); // Fetch the data-payment_status attribute
        if ([1, 3, 4, 5, 6].includes(paymentStatus)) {
            swal({
                title: "Are you sure?",
                icon: 'warning',
                dangerMode: true,
                buttons: {
                    cancel: 'No, Please!',
                    delete: 'Yes, Cancel It'
                }
            }).then(function(willDelete) {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('billings.getBillItemDetails') }}",
                        type: "GET",
                        data: {
                            bill_id: bill_id
                        },
                        success: function(response) {
                            if (response.flagError == false) {
                                populateServiceCheckboxes(response.data);
                            } else {
                                showErrorToaster(response.message);
                                printErrorMsg(response.error);
                                setTimeout(function() {
                                    window.location
                                        .reload(); // Example: reload the current page
                                }, 1000);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr, status, error);
                            console.error(xhr.responseText);
                        }
                    });
                    $("#billing_id").val(bill_id);

                    $("#manage-bill-refund-modal").modal("open");
                    enableBtn("refund-submit-btn");
                    $('#manageRefundForm').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        var refundAmounts = [];
                        disableBtn("refund-submit-btn");
                        $('input[name="refund_amount[]"]').each(function() {
                            var refundAmount = $(this).val();
                            var paymentName = $(this).data('name');
                            var paymentId = $(this).data('id');
                            refundAmounts.push({
                                name: paymentName,
                                id: paymentId,
                                amount: refundAmount
                            });
                        });

                        var refundBillPaymentUrl = "{{ route('billings.refundBillPayment') }}";
                        var refundAmountsJson = JSON.stringify(refundAmounts);

                        var formData = $(this).serialize() + '&refundAmounts=' + encodeURIComponent(
                            refundAmountsJson);

                        $.ajax({
                            url: refundBillPaymentUrl,
                            type: 'POST',
                            data: formData,
                            success: function(response) {
                                if (response.flagError == false) {
                                    showSuccessToaster(response.message);
                                    $("#manage-refund-modal").modal("close");
                                    setTimeout(function() {
                                        window.location
                                    .reload(); // Example: reload the current page
                                    }, 1000);
                                } else {
                                    enableBtn("refund-submit-btn");
                                    showErrorToaster(response.message);
                                    printErrorMsg(response.error);

                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr, status, error);
                                console.error(xhr.responseText);
                            }
                        });
                    });
                }
            });
        } else {
            
            swal({
                title: "Are you sure?",
                icon: 'warning',
                dangerMode: true,
                buttons: {
                    cancel: 'No, Please!',
                    delete: 'Yes, Cancel It'
                }
            }).then(function(willDelete) {
                if (willDelete) {
                  
                    $.ajax({
                        url: "{{ route('cancelBill', '') }}/" + bill_id,
                        type: "POST",
                        data: {
                            bill_id: bill_id
                        },
                        success: function(response) {
                            if (response.flagError == false) {
                                setTimeout(function() {
                                    window.location
                                        .reload(); // Example: reload the current page
                                }, 1000);
                            } 
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr, status, error);
                            console.error(xhr.responseText);
                        }
                    });
                   
                    
                }
            });
        }
    }

    function populateServiceCheckboxes(schedules) {

        var balanceInstore = 0;
        var instoreCredit = 0;
        var tableBody = $("#service_append");
        var packageTableBody = $("#package_append");
        var serviceIds = [];
        tableBody.empty(); // Clear any existing checkboxes
        packageTableBody.empty(); // Clear the package append section as well
        var itemList = $("#itemList");
        itemList.empty();
        var instoreCreditUsed = schedules.instoreCreditUsed;
        if (schedules.services.length > 0) {
            $("#service_append").show();
            var serviceRow = $(
                "<table><tr><td colspan=''><h6>Service Schedules</h6></td><td><h6 id='total_service_amount'>Total Price: </h6></td><td><h6 id='total_discount_amount'>Total Discount Amount: </h6></td><td><h6 id='total_due_amount'>Total Due Amount: </h6></td></tr></table>"
            );
            var serviceContainer = $(
                "<table><tr style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'><td colspan=''><div class='checkbox-container'></div></td></tr></table>"
            );
            var serviceDiv = serviceContainer.find('.checkbox-container');

            if (schedules.billItems.length > 0) {
                $("#total_discount_amount").show();
            } else {
                $("#total_discount_amount").hide();
            }

            schedules.services.forEach(function(schedule, index) {
                var isChecked = "checked";
                var discount = 0;
                var serviceId = schedule.item_id;
                serviceIds.push(serviceId);
                // Check for discounts in bill items
                schedules.billItems.forEach(function(billItem) {
                    if (billItem.item_id === schedule.item_id) {
                        discount = billItem.discount_value;
                    }
                });

                var checkbox = $(
                    "<label for='cancel_service_" + index + "'>" +
                    "<input type='checkbox' class='service_checkbox' id='cancel_service_" + index +
                    "' name='cancel_service[]' data-discount='" + discount + "' data-price='" + schedule
                    .item.price + "' value='" + schedule.item_id + "' " + isChecked + ">" +
                    schedule.item.name +
                    "</label>"
                );
                serviceDiv.append(checkbox);

            });
            listItemDetails('services', serviceIds, 'select')

            $("#service_append").append(serviceRow);
            $("#service_append").append(serviceContainer);
        } else {

            $("#package_append").hide();
        }

        if (schedules.packages.length > 0) {
            $("#service_append").hide();
            $("#package_append").show();
            var packageRow = $(
                "<table><tr><td ><h6>Package Schedules</h6></td><td><h6 id='total_package_amount'>Total Price: </h6></td><td><h6 id='total_due_amount'>Total Due Amount: </h6></td></tr></table>"
            );
            var packageContainer = $(
                "<table><tr style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'><td><div class='checkbox-container'></div></td></tr></table>"
            );
            var packageDiv = packageContainer.find('.checkbox-container');
            schedules.packages.forEach(function(schedule, index) {

                var isChecked = "checked";
                var checkbox = $(
                    "<label for='cancel_package_" + index + "'>" +
                    "<input type='checkbox' class='package_checkbox' id='cancel_package_" + index +
                    "' name='cancel_package[]' data-price='" + schedule.package.price + "' value='" +
                    schedule.package_id + "' " + isChecked + ">" +
                    schedule.package.name +
                    "</label>"
                );
                packageDiv.append(checkbox);
                listItemDetails('packages', schedule.package_id, 'select')

            });

            packageTableBody.append(packageRow);
            packageTableBody.append(packageContainer);
        }


        function updateTotalPrice() {
            var totalPrice = 0; // Initialize totalPrice inside the function;
            var totaldiscount = 0;
            var totalDiscountPrice = 0;
            var priceTotal = 0;
            var dueAmount = schedules.totalDueAmount;
            var serviceIds = [];
            var packageIds = [];
            var totalDueAmount = dueAmount;
            if (schedules.services.length > 0) {
                $('.service_checkbox:checked').each(function() {
                    var serviceId = $(this).val();
                    var price = parseFloat($(this).data('price'));
                    var discount = parseFloat($(this).data('discount')) || 0;
                    serviceIds.push(serviceId);

                    totalPrice += price;
                    totaldiscount += discount;
                    totalDiscountPrice = totalPrice - totaldiscount;

                    if (dueAmount > 0) {
                        if (totalDiscountPrice > 0 && dueAmount > totalDiscountPrice || dueAmount >
                            totalPrice) {
                            // totalDueAmount = totalDiscountPrice > 0 ? totalDiscountPrice : totalPrice;
                            var paymentType = $("#refund_amount").data('name');
                            if (paymentType == 'In-store Credit') {
                                $("input[name='refund_amount[]']").val('');
                            }
                            $(".paymentType").hide();
                        } else {
                            totalDueAmount = dueAmount;
                            $(".paymentType").show();
                        }
                    }


                });
                listItemDetails('services', serviceIds, 'select')

            } else if (schedules.packages.length > 0) {
                $('.package_checkbox:checked').each(function() {
                    var packageId = $(this).val();
                    packageIds.push(packageId);
                    var price = parseFloat($(this).data('price'));
                    totalPrice += price;
                    if (dueAmount > 0) {
                        if (dueAmount > totalPrice) {
                            // totalDueAmount = totalDiscountPrice > 0 ? totalDiscountPrice : totalPrice;
                            var paymentType = $("#refund_amount").data('name');
                            if (paymentType == 'In-store Credit') {
                                $("input[name='refund_amount[]']").val('');
                            }
                            $(".paymentType").hide();
                        } else {
                            totalDueAmount = dueAmount;
                            $(".paymentType").show();
                        }
                    }
                    listItemDetails('packages', packageIds, 'select')

                });
            } else {
                totalPrice = 0;
            }
            $('#total_package_amount').text('Total Price: ₹' + totalPrice.toFixed(2));
            $('#total_due_amount').text('Total Due: ₹' + totalDueAmount.toFixed(2));
            $('#total_discount_amount').text('Discount Price: ₹' + totalDiscountPrice.toFixed(2));
            $('#total_service_amount').text('Total Price: ₹' + totalPrice.toFixed(2));
            if (instoreCreditUsed > (totalPrice - totaldiscount)) {
                instoreCredit = totalPrice - totaldiscount;
                balanceInstore = instoreCreditUsed - (totalPrice - totaldiscount);
            } else {
                instoreCredit = instoreCreditUsed;
            }
            if (totaldiscount > 0) {
                totalPrice -= totaldiscount;
            }
            var cancelFee = totalPrice > instoreCredit ? totalPrice - instoreCredit : 0;
            if (dueAmount > 0 && totalPrice > dueAmount) {
                cancelFee = cancelFee - dueAmount;
            }
            if (cancelFee < 0) {
                cancelFee = 0;
            }

            $("#refund_amount").val(instoreCredit);
            $("#total_paid_refund").text('₹' + instoreCredit.toFixed(2));
            $("#total_cancellation_fee").text('₹' + cancelFee.toFixed(2));
            if (totalDueAmount > totalPrice) {
                $("#total_paid_refund").text('₹' + 0.00);
                $("#total_cancellation_fee").text('₹' + 0.00);
            }
            if (totalDiscountPrice > 0 && dueAmount > totalDiscountPrice || dueAmount > totalPrice) {
                // totalDueAmount = totalDiscountPrice > 0 ? totalDiscountPrice : totalPrice;
                var paymentType = $("#refund_amount").data('name');
                if (paymentType == 'In-store Credit') {
                    $("#refund_amount").val('');
                }
                $(".paymentType").hide();
            }

        }
        // Attach the click event handler to the checkboxes after they are added to the DOM
        $('.service_checkbox').on('click', updateTotalPrice);
        $('.package_checkbox').on('click', updateTotalPrice);
        // Initial calculation in case there are any pre-checked checkboxes
        updateTotalPrice();
    }

    function listItemDetails(type, latest_value, action) {
        var data_ids = [];
        scheduleId = $("#schedule_id").val();
        data_ids = latest_value
        if (Array.isArray(latest_value)) {
            if (latest_value.length > 0) {
                data_ids = [...latest_value];
                data_ids = [...new Set(data_ids)];
            }
        }

        var url = '';
        var itemCount = $("#item_count").val();
        if (type == 'packages') {
            url = "{{ route('getPackageDetails') }}";
        } else if (type == 'services') {
            url = "{{ route('get_details') }}";
        }
        if (action == "select") {
            if (data_ids != "") {
                $.ajax({
                    type: "post",
                    url: url,
                    dataType: "json",
                    data: {
                        data_ids: data_ids,
                        type: type,
                        scheduleId: scheduleId
                    },
                    delay: 250,
                    success: function(data) {
                        $("#grand_total").val(data.grand_total);
                        $("#total_minutes").val(data.total_minutes);
                        $("#itemDetails").empty().append(data.html);
                        $("#itemList").empty().append(data.html);
                        $("#itemDetailsDiv").show();
                        $("#itemDiv").show();

                    },
                });
            } else {
                $("#itemDetailsDiv").hide();
                $("#itemDiv").hide();
                $("#itemDetails").find("tr:gt(0)").remove();
                $("#itemList").find("tr:gt(0)").remove();
            }
        } else {
            $("table#itemDetails tr#" + latest_value).remove();
        }

    }


    $(document).ready(function() {
        function calculateTotalRefundAmount() {
            var product = $('#total_service_amount').text();
            var totalDiscount = $("#total_discount_amount").text();
            var packageAmount = $('#total_package_amount').text();
            var dueAmount = $("#total_due_amount").text();

            var totalDueAmount = parseFloat(dueAmount.replace(/[^\d.-]/g, ''));
            var totalDiscountPrice = parseFloat(totalDiscount.replace(/[^\d.-]/g, ''));
            var productPrice = 0;
            if (product) {
                productPrice = parseFloat(product.replace(/[^\d.-]/g, ''));
            } else {
                productPrice = parseFloat(packageAmount.replace(/[^\d.-]/g, ''));
            }
            if (totalDueAmount > 0 && totalDueAmount < productPrice) {
                productPrice -= totalDueAmount;
            }

            var totalCancellationFee = 0;
            var refundAmounts = [];
            $('input[name="refund_amount[]"]').each(function() {
                var refundAmount = parseFloat($(this).val()) ||
                0; // Ensure the value is treated as a float, defaulting to 0 if invalid
                var paymentName = $(this).data('name');
                var paymentId = $(this).data('id');
                refundAmounts.push({
                    name: paymentName,
                    id: paymentId,
                    amount: refundAmount
                });

            });
            var totalRefundAmount = refundAmounts.reduce(function(total, refund) {
                return total + refund.amount;
            }, 0);

            if (totalDiscountPrice > 0) {
                totalCancellationFee = totalDiscountPrice > totalRefundAmount ? totalDiscountPrice -
                    totalRefundAmount : 0;

            } else {
                totalCancellationFee = productPrice > totalRefundAmount ? productPrice - totalRefundAmount : 0;
            }

            totalCancellationFee = parseFloat(totalCancellationFee);

            // if(totalCancellationFee>0 && totalDueAmount>0){
            //     totalCancellationFee -=totalDueAmount;
            // }

            if (isNaN(totalCancellationFee)) {
                totalCancellationFee = 0.00; // Set default value to 0.00 if NaN
            }

            $('#total_paid_refund').text('₹' + totalRefundAmount.toFixed(2));
            $('#total_cancellation_fee').text('₹' + totalCancellationFee.toFixed(2));
        }

        // Attach the change event handler to the refund amount inputs
        $(document).on('keyup', 'input[name="refund_amount[]"]', function() {
            calculateTotalRefundAmount();
        });

        // Initial calculation in case there are any pre-filled values
        calculateTotalRefundAmount();
    });
</script>
@endpush
