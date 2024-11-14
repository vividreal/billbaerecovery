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
    <style>
        .pt-error-label {
            display: none;
        }
    </style>
@endsection

@section('content')
@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ $page->title ?? '' }}</a></li>
        <li class="breadcrumb-item active">Update</li>
    </ol>
@endsection
<!-- users edit start -->
<div class="section users-edit section-data-tables">

    <div class="card">
        <div class="card-content">
            <ul class="tabs mb-2 row">
                <li class="tab">
                    <a class="display-flex align-items-center active" id="account-tab" href="#account">
                        <i class="material-icons mr-1">person_outline</i><span>Billing</span>
                    </a>
                </li>
                <li class="tab">
                    <a class="display-flex align-items-center" id="information-tab" href="#additionalTaxes">
                        <i class="material-icons mr-2">account_balance_wallet</i><span>Tax</span>
                    </a>
                </li>
                <li class="tab">
                    <a class="display-flex align-items-center" id="paymentTypes-tab" href="#paymentTypes">
                        <i class="material-icons mr-2">payment</i><span>Payment Types </span>
                    </a>
                </li>
            </ul>
            <div class="divider mb-3"></div>
            <div class="row">
                @if ($store)
                    <div class="col s12" id="account">
                        <!-- users edit account form start -->
                        <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                            <div class="card-content red-text">I am sorry, this service is currently not supported in
                                your selected country. In case you wish to use this service in any country other than
                                India, please leave a message in the contact us page, and we shall respond to you at the
                                earliest.</div>
                            <button type="button" class="close red-text" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        @if (Session::has('error'))
                            <div class="card-alert card red lighten-5 print-error-msg">
                                <div class="card-content red-text">Few mandatory store details are missing</div>
                                <div class="card-content red-text">{!! Session::get('error') !!}</div>
                                <button type="button" class="close red-text" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        @endif
                        <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                        <form id="billingForm" name="billingForm" role="form" method="" action=""
                            class="ajax-submit">
                            {{ csrf_field() }}
                            {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                            {!! Form::hidden('billing_id', $billing->id ?? '', ['id' => 'billing_id']) !!}
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::text('company_name', $billing->company_name ?? '', ['id' => 'company_name']) !!}
                                    <label for="company_name" class="label-placeholder active">Company Name <span
                                            class="red-text">*</span></label>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::text('pincode', $billing->pincode ?? '', ['id' => 'pincode']) !!}
                                    <label for="pincode" class="label-placeholder active">Pin code</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col m12 s12">
                                    {!! Form::textarea('address', $billing->address ?? '', [
                                        'id' => 'address',
                                        'class' => 'materialize-textarea',
                                        'rows' => 3,
                                    ]) !!}
                                    <label for="address" class="label-placeholder active">Address</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::select('billing_country_id', $variants->countries, $billing->country_id ?? '', [
                                        'id' => 'billing_country_id',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select country',
                                    ]) !!}
                                    <!-- <label for="billing_country_id" class="label-placeholder active">Country</label> -->
                                    <span class="helper-text" data-error="wrong" data-success="right">Currently service
                                        is supported in India only!</span>
                                </div>
                                <div class="input-field col m6 s12">
                                    @if (!empty($variants->states))
                                        {!! Form::select('billing_state_id', $variants->states, $billing->state_id ?? '', [
                                            'id' => 'billing_state_id',
                                            'class' => 'select2 browser-default',
                                            'placeholder' => 'Please select state',
                                        ]) !!}
                                    @else
                                        {!! Form::select('billing_state_id', [], '', [
                                            'id' => 'billing_state_id',
                                            'class' => 'select2 browser-default',
                                            'placeholder' => 'Please select state',
                                        ]) !!}
                                    @endif
                                    <!-- <label for="billing_state_id" class="label-placeholder active">State</label> -->
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col m6 s12">
                                    @if (!empty($variants->districts))
                                        {!! Form::select('billing_district_id', $variants->districts, $billing->district_id ?? '', [
                                            'id' => 'billing_district_id',
                                            'class' => 'select2 browser-default',
                                            'placeholder' => 'Please select district',
                                        ]) !!}
                                    @else
                                        {!! Form::select('billing_district_id', [], '', [
                                            'id' => 'billing_district_id',
                                            'class' => 'select2 browser-default',
                                            'placeholder' => 'Please select district',
                                        ]) !!}
                                    @endif
                                    <!-- <label for="billing_district_id" class="label-placeholder active">District</label> -->
                                </div>
                                <div class="input-field col m6 s12">
                                    @if (!empty($variants->currencies))
                                        {!! Form::select('currency', $variants->currencies, $billing->currency ?? '', [
                                            'id' => 'currency',
                                            'class' => 'select2 browser-default',
                                            'placeholder' => 'Please select currency ',
                                        ]) !!}
                                    @else
                                        {!! Form::select('currency', [], $billing->currency ?? '', [
                                            'id' => 'currency',
                                            'class' => 'select2 browser-default',
                                            'placeholder' => 'Please select currency ',
                                        ]) !!}
                                    @endif
                                    <!-- <label for="currency" class="label-placeholder active">Currency</label> -->
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light" type="button" name="reset"
                                        id="billing-reset-btn">Reset <i
                                            class="material-icons right">refresh</i></button>
                                    <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                        id="billing-submit-btn">Submit <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                        </form>
                        <!-- users edit account form ends -->
                    </div>
                    <div class="col s12" id="additionalTaxes">
                        <h4 class="card-title">GST Percentage</h4>
                        <form id="storeGSTForm" name="storeGSTForm" role="form" method="" action=""
                            class="ajax-submit">
                            {{ csrf_field() }}
                            {!! Form::hidden('gst_billing_id', $billing->id ?? '', ['id' => 'gst_billing_id']) !!}
                            {!! Form::hidden('GSTRoute', url($page->GSTRoute), ['id' => 'GSTRoute']) !!}
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    {!! Form::text('gst', $billing->gst ?? '', ['id' => 'gst', 'style' => 'text-transform:uppercase']) !!}
                                    <label for="gst" class="label-placeholder active">GST No</label>
                                </div>
                                <div class="input-field col m6 s12">
                                    {!! Form::select('gst_percentage', $variants->tax_percentage, $billing->gst_percentage ?? '', [
                                        'id' => 'gst_percentage',
                                        'class' => 'select2 browser-default',
                                        'placeholder' => 'Please select default GST percentage',
                                    ]) !!}
                                    <!-- <label for="gst_percentage" class="label-placeholder active"> GST percentage </label> -->
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col m5 s6">
                                    {!! Form::text('hsn_code', $billing->hsn_code ?? '', ['id' => 'hsn_code']) !!}
                                    <label for="hsn_code" class="label-placeholder active">Store SAC Code </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light" type="button" name="reset"
                                        id="gst-reset-btn">Reset <i class="material-icons right">refresh</i></button>
                                    <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                        id="gst-submit-btn">Submit <i class="material-icons right">send</i></button>
                                </div>
                            </div>
                        </form>
                        <!-- users edit Info form ends -->
                        <div class="divider mb-3"></div>
                        <div class="row">
                            <h4 class="card-title">Additional Taxes</h4>
                            <div class="input-field col m12 s12">
                                <a href="javascript:" onclick="manageAdditionalTax(null)"
                                    class="btn waves-effect waves-light cyan breadcrumbs-btn right tooltipped"
                                    data-position="bottom" data-tooltip="Add New Additional Tax" type="submit"
                                    name="action">Add <i class="material-icons right">account_balance_wallet</i></a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <table id="data-table-taxes" class="display data-tables"
                                    data-url="{{ route('additional-tax.index') }}" data-form="page" data-length="10">
                                    <thead>
                                        <tr>
                                            <th width="20px" data-orderable="false" data-column="DT_RowIndex"> No
                                            </th>
                                            <th width="" data-orderable="false" data-column="name"> Name </th>
                                            <th width="" data-orderable="false" data-column="percentage">Tax
                                                Percentage </th>
                                            <th width="" data-orderable="false" data-column="information">
                                                Details</th>
                                            <th width="200px" data-orderable="false" data-column="action"> Action
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col s12" id="paymentTypes">
                        <h4 class="card-title">Payment Types </h4>
                        <!-- users edit Info form start -->
                        <!-- <a href="javascript:" onclick="managePaymentType(null)" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">Add<i class="material-icons right">payment</i></a> -->
                        <a href="javascript:" onclick="addPaymentTypesTableRows()"
                            class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit"
                            name="action">Add<i class="material-icons right">payment</i></a>
                        <div class="row">
                            <div class="col s12">
                                <table id="paymentTypesTable">
                                    <thead>
                                        <tr>
                                            <th>No. </th>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- users edit Info form ends -->
                    </div>
                @endif
            </div>
            <!-- </div> -->
        </div>
    </div>
</div>
<!-- users edit ends -->
@include('additional-tax.manage')
@include('payment-type.manage')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script>
    var getStatesByCountry = "{{ route('getStatesByCountry') }}";
    var getCurrencies = "{{ route('getCurrencies') }}";
    var get_districts_by_state = "{{ route('get_districts_by_state') }}";
</script>
<script src="{{ asset('admin/js/custom/store/billing.js') }}"></script>

<script>
    var table;
    var paymentTypeTable = '';
    var i = 1;
    var isEnableNewRow = 0;

    function addPaymentTypesTableRows() {
        let html = '';
        i++;
        html += '<tr id="row' + i + '">';
        html += '<th><input name="payment_types" id="payment_type_' + i +
            '" type="text" placeholder="Payment Types" class="" value=""><label id="pt-error_' + i +
            '" class="error red-text pt-error-label" for="name">Please enter payment types</label></th>';
        html += '<th>';
        // i button with Bootstrap tooltip
        html += '<a href="javascript:void(0);" id="info_' + i +
            '" class="info-icon" data-toggle="tooltip" title="Type the exact payment type name that is listed under the \'Name\' column">';
        html += '<i class="material-icons">info</i></a>';
        // Save button with Bootstrap tooltip
        html += '<a href="javascript:void(0);" id="' + i +
            '" class="btn mr-2 cyan btn_save"  title="Save"><i class="material-icons">save</i></a>';
        // Remove button with Bootstrap tooltip
        html += '<a href="javascript:void(0);" id="' + i +
            '" data-type="remove" class="btn btn-danger btn-sm btn-icon mr-2 btn_remove"  title="Remove"><i class="material-icons">clear</i></a>';
        html += '</th>';
        html += '</tr>';

        $('#paymentTypesTable').append(html);
        $('[data-toggle="tooltip"]').tooltip(); // Initialize Bootstrap tooltips
    }


    $(document).on('click', '.btn_remove', function() {
        $('#row' + this.id).remove();
    });

    $(document).on('click', '.btn_save', function() {
        $(".pt-error-label").hide();
        var row_id = this.id;
        var paymentTypeValue = $("#payment_type_" + this.id).val();
        if (paymentTypeValue == '') {
            // Replace below function with -  this.id.nearest.label
            $("#pt-error_" + this.id).show();
        } else {
            disableBtn(this.id);
            url = "{{ url(ROUTE_PREFIX . '/payment-types') }}";
            $.post(url, {
                payment_type: paymentTypeValue,
                row_id: row_id
            }, function(response) {
                if (response.flagError == false) {
                    $('#row' + row_id).remove();
                    loadPaymentTypes('reload');
                } else {
                    showErrorToaster(data.message);
                }

            });
        }
    });

    $("body").on("click", ".payment-types-btn-edit", function() {
        var shop_id = $(this).parents("tr").attr('data-shop-id');

        if (shop_id == 0) {
            showErrorToaster("You are not allowed to delete this Payment type !");
        } else {
            var name = $(this).parents("tr").attr('data-name');
            $(this).parents("tr").find("td:eq(1)").html('<input name="payment_types" value="' + name + '">');
            $(this).parents("tr").find("td:eq(2)").prepend('<a href="javascript:void(0);" id="' + i +
                '" class="btn mr-2 cyan btn-update" title="Save"><i class="material-icons">update</i></a><a href="javascript:void(0);" id="' +
                i +
                '" class="btn mr-2 red btn-cancel" title="Cancel"><i class="material-icons">cancel</i></a>')

            $(this).parents("tr").find(".deletePaymentTypes").hide();
            $(this).hide();
        }
    });

    $("body").on("click", ".btn-cancel", function() {
        var name = $(this).parents("tr").attr('data-name');
        $(this).parents("tr").find("td:eq(1)").text(name);

        $(this).parents("tr").find(".payment-types-btn-edit").show();
        $(this).parents("tr").find(".deletePaymentTypes").show();

        $(this).parents("tr").find(".btn-update").remove();
        $(this).parents("tr").find(".btn-cancel").remove();
    });


    $("body").on("click", ".btn-update", function() {
        var name = $(this).parents("tr").find("input[name='payment_types']").val();
        var id = $(this).parents("tr").attr('data-id');
        $.ajax({
            url: "{{ url(ROUTE_PREFIX . '/payment-types/') }}/" + id,
            type: "PUT",
            data: {
                name: name,
                id: id
            },
            dataType: "json",
        }).done(function(response) {
            if (response.flagError == false) {
                showSuccessToaster(response.message);
                loadPaymentTypes('reload');
            } else {
                showErrorToaster(data.message);
            }
        });
    });

    $(document).on('click', '.deletePaymentTypes', function() {
        var shop_id = $(this).attr('data-shop_id');
        var data_id = this.id;

        if (shop_id == 0) {
            showErrorToaster("You are not allowed to delete this Payment type !");
        } else {
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
                            url: "{{ url(ROUTE_PREFIX . '/payment-types') }}/" + data_id,
                            type: "DELETE",
                            dataType: "html"
                        })
                        .done(function(a) {
                            var data = JSON.parse(a);
                            if (data.flagError == false) {
                                showSuccessToaster(data.message);
                                $('table#paymentTypesTable tr#row' + data_id).remove();


                                // setTimeout(function () {
                                //   $("#paymentTypesTable").empty();
                                //   loadPaymentTypes();
                                // }, 1000);



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
    });

    $(function() {
        loadPaymentTypes();
        table = $('#data-table-payment-types').DataTable({
            bSearchable: true,
            pagination: true,
            pageLength: 10,
            responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            ajax: "{{ url(ROUTE_PREFIX . '/payment-types/lists') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: 20
                },
            ]
        });
        // taxTable = $('#data-table-taxes').DataTable({
        //   bSearchable: true,
        //   pagination: true,
        //   pageLength: 10,
        //   responsive: true,
        //   searchDelay: 500,
        //   processing: true,
        //   serverSide: true,
        //   ajax: "{{ url(ROUTE_PREFIX . '/additional-tax/lists') }}",
        //   columns: [
        //     { data: 'DT_RowIndex', orderable: false, searchable: false, width:10 },
        //     { data: 'name', name: 'name', orderable: false, },
        //     { data: 'percentage', name: 'name', orderable: false, searchable: false },
        //     { data: 'information', name: 'name', orderable: false, searchable: false },
        //     { data: 'action', name: 'action', orderable: false, searchable: false, width:20 },
        //   ]
        // });
    });

    function loadPaymentTypes(arg = null) {
        if (arg == 'reload') {
            $("#paymentTypesTable").find("tr:gt(0)").remove();
        }

        $.getJSON("{{ url('/common/get-payment-types') }}", function(results) {
            $("#paymentTypesTable tbody").append(results.html);
        });
    }

    function managePaymentType(paymentType_id) {
        validator.resetForm();
        $('input').removeClass('error');
        if (paymentType_id === null) {
            $("#paymentTypeForm")[0].reset();
            $('#paymentTypeForm').find("input[type=text]").val("");
            $("#paymentType_id").val('');
            $("#paymentTypeFields .label-placeholder").show();
            $('#paymentType-modal').modal('open');
        } else {
            $.ajax({
                    url: "{{ url(ROUTE_PREFIX . '/payment-types') }}/" + paymentType_id + "/edit",
                    type: "GET",
                    dataType: "html"
                })
                .done(function(a) {
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        $("#paymentType_id").val(data.data.id);
                        $("#paymentTypeForm input[name=name]").val(data.data.name);
                        $("#paymentTypeFields .label-placeholder").hide();
                        $("#paymentType-modal").modal("open");
                    }
                }).fail(function() {
                    printErrorMsg("Please try again...", "error");
                });
        }
    }

    if ($("#paymentTypeForm").length > 0) {
        var validator = $("#paymentTypeForm").validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 100,
                },
            },
            messages: {
                name: {
                    required: "Please enter payment type",
                    maxlength: "Length cannot be more than 100 characters",
                },
            },
            submitHandler: function(form) {
                $('#paymentTypename-submit-btn').html('Please Wait...');
                $("#paymentTypename-submit-btn").attr("disabled", true);
                id = $("#paymentType_id").val();
                paymentType_id = "" == id ? "" : "/" + id;
                formMethod = "" == id ? "POST" : "PUT";
                var forms = $("#paymentTypeForm");
                $.ajax({
                    url: "{{ url(ROUTE_PREFIX . '/payment-types') }}" + paymentType_id,
                    type: formMethod,
                    processData: false,
                    data: forms.serialize(),
                    dataType: "html",
                }).done(function(a) {
                    $('#paymentTypename-submit-btn').html(
                        'Submit <i class="material-icons right">send</i>');
                    $("#paymentTypename-submit-btn").attr("disabled", false);
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        $("#paymentType-modal").modal("close");
                        setTimeout(function() {
                            table.ajax.reload();
                        }, 1000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                });
            }
        })
    }

    function deletePaymentTypes(b) {
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
                        url: "{{ url(ROUTE_PREFIX . '/payment-types') }}/" + b,
                        type: "DELETE",
                        dataType: "html"
                    })
                    .done(function(a) {
                        var data = JSON.parse(a);
                        if (data.flagError == false) {
                            showSuccessToaster(data.message);
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 1000);
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
</script>
@endpush
