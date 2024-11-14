"use strict";

var pageTitle = $("#pageTitle").val();
var pageRoute = $("#pageRoute").val();
var timePicker = $("#timePicker").val();
var timeFormat = $("#timeFormat").val();
var customerRoute = $("#customerRoute").val();
var timeout;
var table;
var customerId;
var post_id;
var formMethod;
var validator;

$("#country_id").select2({
    placeholder: "Please select Country",
    allowClear: true,
});
$("#state_id").select2({
    placeholder: "Please select State",
    allowClear: true,
});
$("#district_id").select2({
    placeholder: "Please select District",
    allowClear: true,
});
$("#service_type").select2({ placeholder: "Please Type" });
$("#services").select2({
    placeholder: "Please select Service",
    allowClear: true,
});
$("#packages").select2({
    placeholder: "Please select Package",
    allowClear: true,
});
$("#memberships").select2({
    placeholder: "Please select Membership",
    allowClear: true,
});
$("#phone_code").select2({
    placeholder: "Please select Phone code",
    dropdownParent: "#new-customer-modal",
});

$("#search_customer_id").select2({
    placeholder: "Please select Customers",
    allowClear: true,
});
$("#payment_status").select2({
    placeholder: "Please select Bill status",
    allowClear: true,
});

$(function () {
    $(".user-details").hide();
});

$('input[name="billed_date"]').daterangepicker(
    {
        singleDatePicker: true,
        startDate: new Date(),
        showDropdowns: true,
        autoApply: true,
        timePicker: true,
        timePicker24Hour: timePicker,
        locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
    },
    function (ev, picker) {
        // console.log(picker.format('DD-MM-YYYY'));
    }
);

$('input[name="checkin_time"]').daterangepicker(
    {
        singleDatePicker: true,
        startDate: new Date(),
        showDropdowns: true,
        autoApply: true,
        timePicker: true,
        timePicker24Hour: timePicker,
        locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
    },
    function (ev, picker) {
        // console.log(picker.format('DD-MM-YYYY'));
    }
);

$('input[name="checkout_time"]').daterangepicker(
    {
        singleDatePicker: true,
        startDate: new Date(),
        showDropdowns: true,
        autoApply: true,
        timePicker: true,
        timePicker24Hour: timePicker,
        locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
    },
    function (start, end, label) {
        // console.log(picker.format('DD-MM-YYYY'));
        // var years = moment().diff(start, 'years');
        var in_time = $('input[name="checkin_time"]').val();
        var out_time = $('input[name="checkout_time"]').val();
        var diff = moment().diff(in_time, out_time);
    }
);

$('input[name="dob"]').daterangepicker({
    autoUpdateInput: false,
    singleDatePicker: true,
    showDropdowns: true,
    autoApply: true,
    maxYear: parseInt(moment().format('YYYY'),10),
    timePicker: false,
    maxDate: moment(),
    locale: { format: 'YYYY-MM-DD'},
});

$('input[name="dob"]').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('YYYY-MM-DD') );
  });

$("#billing_address_checkbox").change(function () {
    $(".billing-address-section").toggle();
});

function getCustomerDetails(customer_id) {
    var get_customer_details = get_customerDetails;
    $.ajax({
        type: "GET",
        url: get_customer_details,
        dataType: "json",
        data: { customer_id: customer_id },
        delay: 250,
        success: function (data) {
            var customerMobile = "";
            if (data.data.mobile != null) {
                customerMobile = " - " + data.data.mobile;
            } 
            if(data.data.is_membership_holder ==1){
                $("#hide_for_membershipid").css('display', 'none');
            }         
            $("#search_customer").val(data.data.name + customerMobile);
            $("#customer_name").val(data.data.name);
            $("#customer_mobile").val(data.data.mobile);
            $("#customer_email").val(data.data.email);
            $("#customer_id").val(customer_id);
            $("#newCustomerBtn").hide();
            $(".user-details").show();
            var customerViewURL = "customers/" + customer_id;
            $("#customerViewLink").attr("href", customerViewURL);
            $("#customerActionDiv").show();
            $("#" + pageTitle + "Form label").removeClass("error");
        },
    });
}

function removeCustomer() {
    $("#search_customer").val("");
    $("#customer_name").val("");
    $("#customer_mobile").val("");
    $("#customer_email").val("");
    $("#customer_id").val();
    $("#newCustomerBtn").show();
    $("#customer_details_div").hide();
    $(".user-details").hide();
    $("#customerActionDiv").hide();
    showSuccessToaster("Customer removed successfully.");
}

let last_selected;
let last_unselected;
let type;

var $eventSelect = $(".service-type");
$eventSelect.select2({ placeholder: "Please select ", allowClear: false });
$eventSelect.on("select2:unselect", function (e) {
    type = $(this).data("type");
    last_unselected = e.params.data.id;
    listItemDetails(type, last_unselected, "unselect");
});
$eventSelect.on("select2:select", function (e) {
    type = $(this).data("type");
    last_selected = e.params.data.id;
    listItemDetails(type, last_selected, "select");
});
function listItemDetails(type, latest_value, action) {
    var data_ids = $("#" + type).val();
    var billing = pageRoute; // Assuming pageRoute contains the URL
    var parts = billing.split("/");

    var lastWord = parts[parts.length - 1]; 
    var url ='';
    if(type=='packages'){
        url =get_packages_details;
        $(".packageCount").hide();
   }else if(type=='services'){
    $(".packageCount").show();
        url=get_details;
   }else if(type=='memberships'){
    $(".packageCount").show();
     url=get_membership_details;
   }
    // var get_details = getTaxPrint;
    if (action == "select") {
        if (data_ids != "") {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                data: { data_ids: latest_value, type: type,lastWord:lastWord },
                delay: 250,
                success: function (data) {   
                    var html = "";                 
                    $("#servicesTable").append(data.html);
                    $("#usedServicesDiv").show();
                },
            });
        } else {
            
            $("#usedServicesDiv").hide();
            $("#servicesTable").find("tr:gt(0)").remove();
        }
    } else {
        $("table#servicesTable tr#" + latest_value).remove();
    }
}

function manageItemCount(action, rowID, price, tax_percentage, total_percentage, tax_included, additionaltax) {
    let newVal;
    let oldValue = $("#itemCount" + rowID).val();
    var serviceAmount = 0;
    var total_cgst_amount = 0;
    var total_sgst_amount = 0;
    var totalPayable = 0;
    var serviceValue = 0;
    newVal = action == "inc"
        ? parseFloat(oldValue) + 1
        : oldValue > 1
            ? parseFloat(oldValue) - 1
            : (newVal = 1);
    $("#itemCount" + rowID).val(newVal);
    if (action == 'inc' || action == 'dec') {
        serviceAmount = price * newVal;
        var totalServiceTax = 0;      
        var gstAmount = (serviceAmount * tax_percentage) / 100;       
        if (tax_included == 1) {
            totalServiceTax = (serviceAmount / (1 + (total_percentage / 100)));
            serviceValue = serviceAmount / (1 + (total_percentage / 100));
            totalPayable = serviceAmount;
        } else {
            totalServiceTax = serviceAmount;
            totalPayable = serviceAmount + gstAmount;
            serviceValue = serviceAmount;
        }
       
        var additionalTaxSum = 0;       
        // Calculate and display additional tax amounts
        for (let i = 0; i < additionaltax.length; i++) {
            let additionalTaxAmount = (serviceAmount * additionaltax[i].percentage) / 100;
            additionalTaxSum += additionalTaxAmount;
        }
        totalPayable += additionalTaxSum;
        total_cgst_amount = (serviceValue * (tax_percentage / 2)) / 100;
        total_sgst_amount = (serviceValue * (tax_percentage / 2)) / 100;

        totalPayable = totalPayable.toFixed(2);
        total_cgst_amount = total_cgst_amount.toFixed(2);
        total_sgst_amount = total_sgst_amount.toFixed(2);
        serviceValue = serviceValue.toFixed(2);
        additionalTaxSum = additionalTaxSum.toFixed(2);
    }
    // totalPayable = serviceAmount +total_cgst_amount +total_sgst_amount;
    $("#serviceValue" + rowID).html(serviceValue);
    $(".cgstAmount" + rowID).html("&#8377;" + total_cgst_amount);
    $(".sgstAmount" + rowID).html("&#8377; " + total_cgst_amount);
    $(".totalPayable" + rowID).html("&#8377; " + totalPayable);
}
$(".validityDiv").hide();
function getServices(itemIds = null) {
  $("#servicesTable").find("tr:gt(0)").remove();
    $("#pre-loader-div").show();
    var get_all_services = getService;
    // "/common/get-all-services",
    $.ajax({
        type: "GET",
        url: get_all_services,
        dataType: "json",
        delay: 250,
        success: function (data) {
            var selectTerms = '<option value="">Please Select Service</option>';
            $.each(data.data, function (key, value) {
                selectTerms +=
                    '<option value="' +
                    value.id +
                    '" >' +
                    value.name +
                    "</option>";
            });
            var select = $("#services");
            select.empty().append(selectTerms);
            $("#pre-loader-div").hide();
            var values = $("input[name='item_ids[]']")
                .map(function () {
                    return $(this).val();
                })
                .get();
            if (values != "") {
                select.val(values).trigger("change");
                listItemDetails('services', values, 'select')
            } 
        },
    });
}

function getPackages() {
    $("#servicesTable").find("tr:gt(0)").remove();
    $("#pre-loader-div").show();
    var getPackage = getPackageList;
    //  "/common/get-all-packages",
    $.ajax({
        type: "GET",
        url: getPackage,
        dataType: "json",
        delay: 250,
        success: function (data) {
            var selectTerms = '<option value="">Please Select Package</option>';
            $.each(data.data, function (key, value) {            
                selectTerms +=
                    '<option value="' +
                    value.id +
                    '" >' +
                    value.name +
                    "</option>";
            });
            var select = $("#packages");
            select.empty().append(selectTerms);
            $("#pre-loader-div").hide();
        },
    });
}

$(document).on("change", "#service_type", function () {
    if (this.value == 1) {
        $("#services_block").show();
        $("#packages_block").hide();
        $("#membership_block").hide();
        $(".validityDiv").hide();
        getServices();
    } else if(this.value==2) {
        $("#services_block").hide();
        $("#membership_block").hide();
        $("#packages_block").show();
        $(".validityDiv").show();
        getPackages();
    }else{
        $("#services_block").hide();
        $("#membership_block").show();
        $("#packages_block").hide();
        $(".validityDiv").hide();
        getmemberships();
    }
});
function getmemberships() {
    $("#servicesTable").find("tr:gt(0)").remove();
    $("#pre-loader-div").show();
    var getMembership = getMembershipList;
    $.ajax({
        type: "GET",
        url: getMembership,
        dataType: "json",
        delay: 250,
        success: function (data) {
            var html = '<option value="">Please Select Membership </option>';
            $.each(data.data, function (key, value) {
                html +=
                    '<option value="' +
                    value.id +
                    '" >' +
                    value.name +
                    "</option>";
            });
            var select1 = $("#memberships");
            select1.empty().append(html);
            $("#pre-loader-div").hide();
        },
    });
}
$(document).on("change", "#country_id", function () {
    $.ajax({
        type: "POST",
        url: "/common/get-states-by-country",
        data: { country_id: this.value },
        dataType: "json",
        success: function (data) {
            var selectTerms = '<option value="">Please select State</option>';
            $.each(data.data, function (key, value) {
                selectTerms +=
                    '<option value="' +
                    value.id +
                    '" >' +
                    value.name +
                    "</option>";
            });
            var select = $("#state_id");
            select.empty().append(selectTerms);
            $("#district_id").empty().trigger("change");
        },
    });
});

$(document).on("change", "#state_id", function () {
    $.ajax({
        type: "POST",
        url: "/common/get-districts-by-state",
        data: { state_id: this.value },
        dataType: "json",
        success: function (data) {
            var selectTerms =
                '<option value="">Please select District</option>';
            $.each(data.data, function (key, value) {
                selectTerms +=
                    '<option value="' +
                    value.id +
                    '" >' +
                    value.name +
                    "</option>";
            });
            var select = $("#district_id");
            select.empty().append(selectTerms);
        },
    });
});

// Form Validation with Ajax Submit
if ($("#" + pageTitle + "Form").length > 0) {
    validator = $("#" + pageTitle + "Form").validate({
        rules: {
            customer_name: {
                required: true,
            },
            search_customer: {
                required: true,
            },
            // "bill_item[]": {
            //     required: true,
            // },
            // "roles[]": {
            //   required: true,
            // },
        },
        messages: {
            customer_name: {
                required: "Please select a Customer",
            },
            search_customer: {
                required: "Please select a Customer",
            },
            // "bill_item[]": {
            //     required: "Please select an Item",
            // },
            // "roles[]": {
            //   required: "Please choose Role",
            // },
        },
        submitHandler: function (form) {
            $("#continue-btn").html("Please Wait...");
            $("#continue-btn").attr("disabled", true);
            $(".itemCount").each(function (index, element) {
                $("#" + pageTitle + "Form").append(
                    '<input type="hidden" class="item-count-hidden" name="items[' +
                    $(this).data("id") +
                    " ][" +
                    $(this).val() +
                    ']"></input>'
                );
            });

            form.submit();
        },
        errorPlacement: function (error, element) {
            if (element.is("select")) {
                error.insertAfter(element.next(".select2"));
            } else {
                error.insertAfter(element);
            }
        },
    });
}

if ($("#newCustomerForm").length > 0) {
    var customervalidator = $("#newCustomerForm").validate({
        rules: {
            name: {
                required: true,
                maxlength: 200,
                lettersonly: true,
            },
            new_customer_mobile: {
                minlength: 3,
                maxlength: 15,
            },
        },
        messages: {
            name: {
                required: "Please enter Customer Name",
                maxlength: "Length cannot be more than 200 characters",
            },
            new_customer_mobile: {
                required: "Please enter Mobile Number",
                maxlength: "Length cannot be more than 15 numbers",
                minlength: "Length must be 3 numbers",
            },
        },
        submitHandler: function (form) {
            var customerForm = $("#newCustomerForm").serializeArray(); // convert form to array
            customerForm.push({
                name: "mobile",
                value: $("#new_customer_mobile").val(),
            });
            customerForm.push({
                name: "email",
                value: $("#new_customer_email").val(),
            });

            $.ajax({
                url: customerRoute,
                type: "POST",
                processData: false,
                data: $.param(customerForm),
            }).done(function (data) {
                if (data.flagError == false) {
                    getCustomerDetails(data.customer.id);
                    $("#new-customer-modal").modal("close");
                } else {
                    showErrorToaster(data.message);
                    printErrorMsg(data.error);
                }
            });
        },
    });
}

function addNewCustomer() {
    customervalidator.resetForm();
    $("#newCustomerForm .form-control").removeClass("error");
    $("#newCustomerForm").trigger("reset");
    $("#newCustomerForm").find("input[type=text], textarea").val("");
    $("#new-customer-modal").modal("open");
}

jQuery.validator.addMethod(
    "lettersonly",
    function (value, element) {
        return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
    },
    "Please enter Letters only"
);

$("#reset-btn").click(function () {
    validator.resetForm();
    $("#" + pageTitle + "Form")
        .find("input[type=text], textarea, radio")
        .val("");
    $("#male").prop("checked", true);
});

// DataTable Initialization
var columns;
var formValue;
var table = $("#data-table-billing");
var url = table.data("url");
var form = table.data("form");
var length = table.data("length");

columns = [];
formValue = [];

table.find("thead th").each(function () {
    var column = { data: $(this).data("column") };
    columns.push(column);
});

table.DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    bLengthChange: false,
    pageLength: 10,
    scrollX: true,
    ajax: {
        type: "GET",
        url: url,
        data: function (data) {
            data.form = formValue;
        },
    },
    columns: columns,
});

$("#" + form + "-show-result-button").click(function () {
    formValue = $("#" + form).serializeArray();
    table.DataTable().draw();
});

$("#" + form + "-clear-button").click(function () {
    $("#" + form)
        .find("input[type=text]")
        .val("");
    $("#" + form)
        .find(".select2")
        .val("")
        .trigger("change");
    $("#" + form).trigger("reset");
    formValue = $("#" + form).serializeArray();
    table.DataTable().draw();
});

$("#search_customer_id").on("change", function () {
    formValue = $("#" + form).serializeArray();
    table.DataTable().draw();
});

$("#payment_status").on("change", function () {
    formValue = $("#" + form).serializeArray();
    table.DataTable().draw();
});

// Show active and Inactive Lists
$(".listBtn").on("click", function () {
    $("#status").val($(this).attr("data-type"));
    formValue = $("#" + form).serializeArray();
    table.DataTable().draw();
});

table.on("click", ".disable-item", function () {
    var id = $(this).attr("data-id");
    var postUrl = $(this).attr("data-url");
    
    swal({
        title: "Are you sure?",
        icon: "warning",
        dangerMode: true,
        buttons: { cancel: "No, Please!", delete: "Yes, Remove" },
    }).then(function (willDelete) {
        if (willDelete) {
            $.ajax({
                url: postUrl + "/" + id,
                type: "DELETE",
                dataType: "html",
            })
                .done(function (a) {
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        setTimeout(function () {
                            table.DataTable().draw();
                        }, 2000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                })
                .fail(function () {
                    showErrorToaster("Something went wrong!");
                });
        }
    });
});

table.on("click", ".restore-item", function () {
    var postUrl = $(this).attr("data-url");
    var id = $(this).attr("data-id");
    swal({
        title: "Are you sure?",
        icon: "warning",
        dangerMode: true,
        buttons: { cancel: "No, Please!", delete: "Yes, Activate" },
    }).then(function (willDelete) {
        if (willDelete) {
            $.ajax({
                url: postUrl + "/restore/" + id,
                type: "POST",
                dataType: "html",
            })
                .done(function (a) {
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        setTimeout(function () {
                            table.DataTable().draw();
                        }, 2000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                })
                .fail(function () {
                    showErrorToaster("Something went wrong!");
                });
        }
    });
});
table.on("click", ".force-delete-item", function () {
    var postUrl = $(this).attr("data-url");
    var id = $(this).attr("data-id");
    
    swal({
        title: "Are you sure?",
        icon: "warning",
        dangerMode: true,
        buttons: { cancel: "No, Please!", delete: "Yes, Delete" },
    }).then(function (willDelete) {
        if (willDelete) {
            $.ajax({
                url: postUrl + "/hard-delete/" + id,
                type: "POST",
                dataType: "html",
            })
                .done(function (a) {
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        setTimeout(function () {
                            table.DataTable().draw();
                        }, 2000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                })
                .fail(function () {
                    showErrorToaster("Something went wrong!");
                });
        }
    });
});

table.on("click", ".manage-status", function () {
    var postUrl = $(this).attr("data-url");
    var id = $(this).attr("data-id");
    $.ajax({ url: postUrl, data: { id: id }, type: "POST", dataType: "html" })
        .done(function (a) {
            var data = JSON.parse(a);
            if (data.flagError == false) {
                showSuccessToaster(data.message);
                setTimeout(function () {
                    table.DataTable().draw();
                }, 1000);
            } else {
                showErrorToaster(data.message);
                printErrorMsg(data.error);
            }
        })
        .fail(function () {
            showErrorToaster("Something went wrong!");
        });
});

$('input[name="validity_from"]').daterangepicker(
    {
        singleDatePicker: true,
        startDate: new Date(),
        showDropdowns: true,
        autoApply: true,
        timePicker: true,
        timePicker24Hour: timePicker,
        locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
    },
    function (ev, picker) {
        // console.log(picker.format('DD-MM-YYYY'));
    }
  );
  $('input[name="validity_to"]').daterangepicker(
    {
        singleDatePicker: true,
        startDate: new Date(),
        showDropdowns: true,
        autoApply: true,
        timePicker: true,
        timePicker24Hour: timePicker,
        locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
    },
    function (ev, picker) {
        // console.log(picker.format('DD-MM-YYYY'));
    }
  );