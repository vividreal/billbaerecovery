"use strict";

var pageTitle = $("#pageTitle").val();
var pageRoute = $("#pageRoute").val();
var billingId = $("#billing_id").val();
var paymentTypes = $("#payment_types").val();
var currency = $("#currency").val();
var grandTotal = 0;
var table;
var customerId;
var post_id;
var formMethod;
var validator;
$(".payment-types").select2({
    placeholder: "Please select payment type",
    allowClear: true,
});
$("#discount_type").select2({
    placeholder: "Please select Discount type",
    allowClear: true,
});

$(function () {
    getInvoiceDetails();
});

function getInvoiceDetails(discount = null) {
    $.ajax({
        url: pageRoute + "/invoice/get-data",
        type: "post",
        dataType: "json",
        data: { billingId: billingId },
        success: function (data) {
            if (data.flagError == false) {
                $("#invoiceTable").html(data.html);
                $(".tooltipped").tooltip();
                $("#subTotal").html(currency + " " + Number(data.sub_total).toFixed(2)
                );
                $(".totalPayable").html(currency + "" + Number(data.sub_total).toFixed(2))
                $(".display_item").hide();
                if (data.customerDues > 0) {
                    $("#customerDues").html(currency + " " + Number(data.customerDues).toFixed(2)
                    );
                    $(".display_item").show();

                }
                $("#inStoreCredit").html(currency + " " + Number(data.total_instore_credit).toFixed(2));
                $("#inStoreCredit_non_membership").html(currency + "" + Number(data.inStoreCredit).toFixed(2));

                $("#membershipInStoreCredit").html(currency + "" + Number(data.customerPendingAmount).toFixed(2)
                );
                $(".inStoreCreditBalance").html("-" + currency + " " + Number(data.instoreCreditBalance).toFixed(2)
                );
                $(".discountAmount").html("-" + currency + " " + Number(data.discountAmount).toFixed(2)
                );

                $(".grandTotal").html(currency + " " + Number(data.grand_total).toFixed(2)
                );
                $(".subTotal").html(currency + " " + Number(data.sub_total).toFixed(2));

                $("#in_store_credit_amount").val(data.total_instore_credit);
                $("#discount_amount").val(data.discountAmount);
                if (data.instoreCreditBalance > 0) {
                    $("#in_store_credit").val(data.instoreCreditBalance)
                    // $('.in_store_credit').val(data.inStoreCredit)
                }
                $('#packagePrice').html(currency + " " + Number(data.package.price).toFixed(2));
            } else {
                showErrorToaster(data.message);
                printErrorMsg(data.error);
            }
        },
    });
}

function manageDiscount(e) {
    var id = $(e).data("id");
    var action = $(e).data("action");
    $("#billing_item_id").val(id);
    $("#discount_action").val(action);
    $("#discount-reset-btn").click();
    if (action == "add") {
        $("#discount_value").val("");
        $("#discount-modal").modal("open");
        var $creditField = $(".in_store_credit");
        if ($creditField.length > 0) {
            $creditField.val("");
        }
    } else {
        swal({
            title: "Are you sure you want to Remove the Applied Discount?",
            icon: "warning",
            dangerMode: true,
            buttons: { cancel: "No, Please!", delete: "Yes, Remove" },
        }).then(function (willDelete) {
            if (willDelete) {
                var forms = $("#discountForm");
                $.ajax({
                    url: pageRoute + "/manage-discount",
                    type: "POST",
                    processData: false,
                    data: forms.serialize(),
                    dataType: "html",
                }).done(function (a) {
                    var data = JSON.parse(a);
                    if (data.flagError == false) {
                        var $creditField = $(".in_store_credit");
                        if ($creditField.length > 0) {
                            $creditField.val("");
                        }
                        getInvoiceDetails();
                      
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                });
            }
        });
    }
}

if ($("#discountForm").length > 0) {
    var discountValidator = $("#discountForm").validate({
        rules: {
            discount_value: {
                required: true,
                digits: true,
            },
            discount_type: {
                required: true,
            },
        },
        messages: {
            discount_value: {
                required: "Please enter Discount value",
            },
            discount_type: {
                required: "Please select Discount type",
            },
        },
        submitHandler: function (form) {
            disableBtn("discount-submit-btn");
            var discountForm = $("#discountForm");
            $.ajax({
                url: pageRoute + "/manage-discount",
                type: "POST",
                processData: false,
                data: discountForm.serialize(),
            }).done(function (data) {
                enableBtn("discount-submit-btn");
                if (data.flagError == false) {
                    getInvoiceDetails();
                    $("#discount-modal").modal("close");
                } else {
                    showErrorToaster(data.message);
                    printErrorMsg(data.error);
                }
            });
        },
    });
}

$("#discount-reset-btn").click(function () {
    discountValidator.resetForm();
    $("#" + pageTitle + "Form")
        .find("input[type=text]")
        .val("");
    $("#discount_type").val("").trigger("change");
});

var submitFlag = true;
var balanceAmount = 0;
let customerPaidAmount = 0;


$(".customer-payments").change(function () {
    var instoreAmount = 0;
    var in_store_credit_amount = parseFloat($("#in_store_credit_amount").val()) || 0;
    var membershipCredit = parseFloat($("#membershipInStoreCredit").text().replace(/[^0-9.]/g, '')) || 0;
    var discountAmount = parseFloat($(".discountAmount").text().replace(/[^0-9.]/g, '')) || 0;
    var customerDues =  parseFloat($("#customerDues").text().replace(/[^0-9.]/g, ''))|| 0;   

    var instoreCreditPayment = 0;
    var currentPaymentValue = parseFloat($(this).val()) || 0;
    var subTotalValue=0;
    var subTotal = 0;
    // Sum all payments
    $(".customer-payments").each(function () {
        var paymentValue = parseFloat($(this).val()) || 0;
        instoreAmount += paymentValue;

        // Check if this is an in-store credit payment
        if ($(this).data("name") == 'In-storeCredit') {
            instoreCreditPayment = paymentValue;
        }
    });


    // Validation checks for in-store credit payment
    if ($(this).data("name") == 'In-storeCredit' && currentPaymentValue > in_store_credit_amount) {
        if (in_store_credit_amount == 0) {
            $(this).parent().find("label").text("This Customer has No Credit Balance");
        } else {
            $(this).parent().find("label").text("Enter the Amount, do not exceed the limit");
        }
        $(this).parent().find("label").show();
        instoreAmount -= currentPaymentValue; // Remove invalid in-store credit amount
        submitFlag = false;
    } else {
        submitFlag = true;
        $(this).parent().find("label").hide();
    }

    var finalCreditAmount = Math.max(in_store_credit_amount - instoreCreditPayment, 0);
    
    $("#inStoreCredit").text(finalCreditAmount.toFixed(2));
    if (instoreCreditPayment > 0) {
        $("#inStoreCredit_non_membership").text(finalCreditAmount.toFixed(2));
    }
    if (membershipCredit > 0) {
        $("#membershipInStoreCredit").text(finalCreditAmount.toFixed(2));
    }

    var grandTotal = parseFloat($(".grandItemTotal").text().replace(/[^0-9.-]/g, '')) || 0;
    subTotalValue = parseFloat($('#subTotal').text().replace(/[^0-9.]/g, '')) || 0;
    
    grandTotal+=customerDues;
    subTotal =Math.max(Math.max(grandTotal - instoreAmount, 0) - discountAmount, 0);

    var payableGrandTotal = grandTotal - instoreAmount;
    
    $(".inStoreCreditBalance").text("₹ " + instoreCreditPayment.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $(".subTotal").text("₹ " + subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
});


function removeInputContainer(button) {
    $(".in-store-error-label").hide();
    $(".customer-payments").val('');
    var instoreAmount = $(".in_store_credit").val();
    var customerId = $(".in_store_credit").data("customerid");
    var billingId = $(".remove-button").data("billingid");
    var instoreCredit = $(".inStoreCreditBalance").val();
    var totalInstore = parseFloat(instoreAmount) - parseFloat(instoreCredit);
    var grandTotal = $("#grand_total").val();
    var payableGrandTotal = grandTotal + instoreAmount;
    var discountAmount=parseFloat($(".discountAmount").text().replace(/[^0-9.]/g, ''));
    payableGrandTotal = parseFloat(payableGrandTotal) + parseFloat(instoreAmount);
    // $(".grandTotal").html(payableGrandTotal);
    
    $.ajax({
        url: pageRoute + "/remove-in-store-payment",
        type: "post",
        dataType: "json",
        data: {
            customerId: customerId,
            billingId: billingId
        },
        success: function (data) {
            if (data.flagError == false) {
                var billingItems = data.billing_items;
                $.each(billingItems, function (index, item) {
                    var taxArray = item.tax_array;
                    $(".serviceValue_" + index).html(currency + " " + Number(taxArray.amount).toFixed(2));
                    $(".cgst_" + index).html(currency + " " + Number(taxArray.cgst).toFixed(2));
                    $(".sgst_" + index).html(currency + " " + Number(taxArray.sgst).toFixed(2));
                    $(".totalPayable_" + index).html(currency + " " + Number(taxArray.total_amount).toFixed(2));
                    $(".inStoreCreditAmount_" + index).html(currency + " " + Number(data.instoreCreditBalance).toFixed(2));
                });
                $("#in_store_credit").val('');
                $("#inStoreCredit").html(currency + " " + Number(data.total_instore_credit).toFixed(2));
                $("#inStoreCredit_non_membership").html(currency + " " + Number(data.over_paid).toFixed(2));
                $("#membershipInStoreCredit").html(currency + " " + Number(data.customerPendingAmount).toFixed(2));
                $(".inStoreCreditBalance").html(data.instoreCreditBalance);
                $(".grandTotal").html(currency + " " + Number(data.grand_total).toFixed(2));
                $(".subTotal").html(currency + " " + Number(data.sub_total).toFixed(2));
            } else {
                location.reload();
               
            }
        },
    });
}
$("#submit-payment-btn").click(function (e) {
    let total = 0;
    let balanceAmount = 0;
    var customerPaid = 0;
    var grandTotal = 0;
    var discountAmount = 0;
    var instorecredit = 0;
    var customerDues = 0;
    var customerPaidInstore = 0;
    var type = $("#service_type").data('type');
    // var subTotal = $(".subTotalItem").text().replace(/[^0-9.]/g, '');
    var subTotal = $(".subTotal").val();
    instorecredit =  parseFloat($(".inStoreCreditBalance").text().replace(/[^0-9.]/g, ''))||0;
    customerDues =  parseFloat($("#customerDues").text().replace(/[^0-9.]/g, ''))||0;    
    grandTotal =  parseFloat($(".grandItemTotal").text().replace(/[^0-9.]/g, ''))||0;   
    $(".grandTotal").val(grandTotal);
    $(".subTotal").val(grandTotal);
    $("input[name='payment_value[]']").each(function (index, item) {
        if ($(this).val() != "" && $.isNumeric($(this).val())) {
            customerPaid += parseFloat($(this).val());
        }
    });
    discountAmount =  parseFloat($(".discountAmount").text().replace(/[^0-9.]/g, '')); 
    subTotal=grandTotal+customerDues-discountAmount-instorecredit;   
    subTotal = isNaN(subTotal) ? 0 : subTotal;        
    if (discountAmount > 0) {
        customerPaid-=instorecredit;        
        if (customerPaid > subTotal) {            //  - parseFloat(discountAmount);           
            balanceAmount = parseFloat(customerPaid) - parseFloat(subTotal)
            balanceAmount = isNaN(balanceAmount) ? 0 : balanceAmount;
            if (balanceAmount == 0) {
                var message = "";
            } else {
                var message = "You have Additional " + " \u20B9 "
                    + balanceAmount.toFixed(2) + " " +
                    "will be added to In-store credit!";
            }
        }else if(customerPaid == subTotal){
            var message = "";
        } else {
            balanceAmount = parseFloat(subTotal) - parseFloat(customerPaid);
            var message = "You have " + balanceAmount.toFixed(2) + " " +"Amount is Due!";
        }
        swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            dangerMode: true,
            buttons: { cancel: "No, Please!", delete: "Yes, Submit" },
        }).then(function (willDelete) {
            if (willDelete) {
                $("#pending_amount").val(1);
                submitForm();
            }
        });
    }
    else if (grandTotal > customerPaid) {        
        balanceAmount = parseFloat(grandTotal) - parseFloat(customerPaid);        
        if (customerDues > 0) {
            balanceAmount += parseFloat(customerDues);
        }
        if (type == "membership") {
            swal({
                title: "Are you sure?",
                icon: "warning",
                text:
                "You have total " + " \u20B9 "
                + balanceAmount.toFixed(2) + " " +
                "due!",
                dangerMode: true,
                buttons: { cancel: "No, Please!", delete: "Yes, Select" },
            }).then(function (willDelete) {
                if (willDelete) {
                    $("#pending_amount").val(1);
                    submitForm();
                }
            });
        } else {            
            swal({
                title: "Are you sure?",
                text:
                    "You have total " + " \u20B9 "
                    + balanceAmount.toFixed(2) + " " +
                    "due!",
                icon: "warning",
                dangerMode: true,
                buttons: { cancel: "No, Please!", delete: "Yes, Select" },
            }).then(function (willDelete) {
                if (willDelete) {
                    $("#pending_amount").val(1);
                    submitForm();
                }
            });
        }
    } else if (customerPaid > grandTotal) {
        balanceAmount = parseFloat(customerPaid) - parseFloat(grandTotal);        
        if (customerDues > 0) {
            balanceAmount -= parseFloat(customerDues);
        }
        if( balanceAmount > 0){
            swal({
                title: "Are you sure?",
                text:
                    "Additional " + " \u20B9 "
                    + balanceAmount.toFixed(2) + " " +
                    "will be added to In-store credit!",
                icon: "success",
                dangerMode: true,
                buttons: { cancel: "No, Please!", delete: "Yes, Submit" },
            }).then(function (willDelete) {
                if (willDelete) {
                    submitForm();
                }
            });
        }else
        {
            swal({
                title: "Are you sure?",
                icon: "success",
                dangerMode: true,
                buttons: { cancel: "No, Please!", delete: "Yes, Submit" },
            }).then(function (willDelete) {
                if (willDelete) {
                    submitForm();
                }
            });
        }
    }
    else if (subTotal == grandTotal) {
        balanceAmount = parseFloat(subTotal) - parseFloat(grandTotal);
        if (customerDues > 0) {
            balanceAmount -= parseFloat(customerDues);
        }
        swal({
            title: "Are you sure?",
            icon: "success",
            dangerMode: true,
            buttons: { cancel: "No, Please!", delete: "Yes, Submit" },
        }).then(function (willDelete) {
            if (willDelete) {
                submitForm();
            }
        });
    } else if (subTotal < customerPaid) {
        balanceAmount = parseFloat(subTotal) - parseFloat(customerPaid);
        if (customerDues > 0) {
            balanceAmount -= parseFloat(customerDues);
        }
        swal({
            title: "Are you sure?",
            icon: "success",
            dangerMode: true,
            buttons: { cancel: "No, Please!", delete: "Yes, Submit" },
        }).then(function (willDelete) {
            if (willDelete) {
                submitForm();
            }
        });
    } else {
        submitForm();
    }
});

function submitForm() {
    var paymentForm = $("#paymentForm"); 
    if (submitFlag == true) {         
        disableBtn("submit-payment-btn");
        $("input[name='payment_value[]']").each(function (index, item) {
            if ($(this).val() != "" && $.isNumeric($(this).val())) {
                $("#paymentForm").append(
                    '<input class="document-hidden" type="hidden" name="payment_type[]" value="' +
                    $(this).data("id") +
                    '">'
                );
                $("#paymentForm").append(
                    '<input class="document-hidden" type="hidden" name="payment_amount[]" value="' +
                    $(this).val() +
                    '">'
                );
            }
        });
        $.ajax({
            url: pageRoute + "/store-payment",
            type: "POST",
            processData: false,
            data: paymentForm.serialize(),
        }).done(function (data) {           
            if (data.flagError == false) {
                showSuccessToaster(data.message);
                setTimeout(function () {
                    window.location.href = pageRoute + "/" + billingId;
                }, 2000);
            } else {
                enableBtn("submit-payment-btn");
                swal({
                    title: "Are you sure?",
                    text: data.message,
                    icon: "success",
                    dangerMode: true,
                    buttons: { cancel: "No, Please!", delete: "Yes, Submit" },
                }).then(function (willDelete) {
                    if (willDelete) {
                        location.reload();
                        window.href = pageRoute + "/invoice/"+billingId
                    }
                });
            }
        });
    } else {
        showErrorToaster("Errors occurred. Please enter valid amounts.");
    }
}
