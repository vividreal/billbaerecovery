"use strict";

var pageTitle = $("#pageTitle").val();
var pageRoute = $("#pageRoute").val();
var billingRoute = $("#billingRoute").val();
var timePicker = $("#timePicker").val();
var timeFormat = $("#timeFormat").val();
var timezone = $("#timezone").val();
var currency = $("#currency").val();
var mode = $("#mode").val();
var resourcesData = "";
var resource_field = "";
var rooms = "";
var today = "";
var table;
var scheduleId;
var post_id;
var formMethod;
var currentUrl = window.location.href;

function getBaseUrl(currentUrl) {
    // Create a URL object
    var parsedUrl = new URL(currentUrl);
    // Construct and return the base URL
    return parsedUrl.protocol + "//" + parsedUrl.host;
}

// Usage
var baseUrl = getBaseUrl(currentUrl);
// Form script start
$("#service_type").select2({ placeholder: "Please select Type" });
$("#services").select2({
    placeholder: "Please select Service",
    allowClear: true,
});
$("#packages").select2({ placeholder: "Please select Package" });
$("#user_id").select2({
    placeholder: "Please select Therapist",
    allowClear: true,
});
$("#room_id").select2({ placeholder: "Please select Room", allowClear: true });
// $("#customer_id").select2({
//     placeholder: "Please select Customer",
//     dropdownParent: "#manage-schedule-modal",
//     allowClear: true,
// });

$(document).on("change", "#room_id", function () {
    $("#room_id-error").hide();
});
$("#calendar_mode").select2({ placeholder: "View schedule as per:" });

$(document).on("change", "#service_type", function () {
    $("#bill_item").val();
    $("#itemDetailsDiv").hide();
    $("#package_of_service").hide();
    $("#itemDetailsDiv").find("li").remove();
    if (this.value == 1) {
        $("#services_block").show();
        $("#packages_block").hide();
        $("#service_from_package").hide();

        getServices();
    } else {
        $("#services_block").hide();
        $("#packages_block").show();
        $("#service_from_package").show();
        getPackages();
    }
});



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
    $("#itemDetailsDiv").hide();
    $("#itemDetails").find("tr:gt(0)").remove();
    type = $(this).data("type");
    last_selected = e.params.data.id;
    listItemDetails(type, last_selected, "select");
});

function getServices(itemIds = null) {
    $("#itemDetails").find("tr:gt(0)").remove();
    $("#pre-loader-div").show();
    var url = get_all_services;
    $.ajax({
        type: "GET",
        url: url,
        dataType: "json",
        delay: 250,
        success: function (data) {
            var selectTerms = '<option value="">Please select Service</option>';
            $.each(data.data, function (key, value) {
                selectTerms +=
                    '<option value="' +
                    value.id +
                    '" >' +
                    value.name +
                    " </option>";
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

function getPackages(itemIds = null) {
    $("#itemDetails").find("tr:gt(0)").remove();
    $("#pre-loader-div").show();
    var getPackage = getPackageList;
    $.ajax({
        type: "GET",
        url: getPackage,
        dataType: "json",
        delay: 250,
        success: function (data) {
            var selectTerms =
                '<option value="">Please choose Packages</option>';
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
            var values = $("input[name='item_ids[]']")
                .map(function () {

                    return $(this).val();

                })
                .get();
            if (values != "") {
                select.val(values).trigger("change");
                listItemDetails('packages', values, 'select')
            }
        },
    });
}

function listItemDetails(type, latest_value, action) {
    var data_ids = [];
    scheduleId = $("#schedule_id").val();
    data_ids = $("#" + type).val();
    
    if (Array.isArray(latest_value)) {
        if (latest_value.length > 0) {
            data_ids = [...latest_value];
            data_ids = [...new Set(data_ids)];

        }
    }
    var url = '';
    var itemCount = $("#item_count").val();
    if (type == 'packages') {
        url = get_packages_details;
    } else if (type == 'services') {
        url = get_details;
    }
    if (action == "select") {
        if (data_ids != "") {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                data: { data_ids: data_ids, type: type, scheduleId: scheduleId },
                delay: 250,
                success: function (data) {
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

function manageItemCount(action, rowID, price, tax_percentage, total_percentage, tax_included, additionaltax) {
    let newVal;
    let oldValue = $("#itemCount" + rowID).val();
    var serviceAmount = 0;
    var total_cgst_amount = 0;
    var total_sgst_amount = 0;
    var totalPayable = 0;
    var serviceValue = 0;
    newVal = action == "inc" ?
        parseFloat(oldValue) + 1 :
        oldValue > 1 ?
            parseFloat(oldValue) - 1 :
            (newVal = 1);
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

$("#calendar_mode").change(function () {
    if (this.value == "therapists") {
        window.location.href = pageRoute + "/calendar/therapists";
    } else {
        window.location.href = pageRoute + "/calendar/rooms";
    }
});

$(function () {
    getSalesData();
    if (mode == "therapists") {
        getTherapists();
    } else {
        getRooms();
    }


});

function getTherapists() {
    var url = therapists;
    $.ajax({
        type: "GET",
        url: url,
        success: function (data) {
            if (data.flagError == false) {
                resourcesData = data.data;
                $("#calendar").fullCalendar("refetchResources");
                loadCalendar();
            }
        },
    });
}

function getRooms() {
    var url = rooms_get_all;
    // "rooms/get/all"
    $.ajax({
        type: "GET",
        url: url,
        delay: 250,
        success: function (data) {
            if (data.flagError == false) {
                resourcesData = data.data;
                $("#calendar").fullCalendar("refetchResources");
                loadCalendar();
            }
        },
    });
}

function getSalesData() {
    // "/common/billings/get-report-by-date"
    var url = get_report_by_date
    $.ajax({
        type: "POST",
        url: url,
        delay: 250,
        success: function (data) {
            if (data.flagError == false) {
                $("#total_bookings").text(data.total_bookings);
                $("#booking_amount").text(data.booking_amount);
                $("#total_sales").text(data.total_sales);
                $("#sales_amount").text(data.sales_amount);
                $("#canceled_schedule").text(data.total_canceled);
                $("#checked_in_customer").text(data.checked_in_customer);
                $("#not_checked_in_customer").text(data.not_checked_in_customer);
            }
        },
    });
}

function loadCalendar() {
    var currentDate = new Date();
    var calendar = $("#calendar").fullCalendar({
        refetchResourcesOnNavigate: true,
        timeZone: timezone,
        defaultView: "agendaDay",
        slotDuration: "00:05:00",
        displayEventTime: false,
        editable: true,
        timeFormat: timeFormat + ":mm A",
        eventDurationEditable: false,
        selectable: true,
        minTime: "09:00:00",
        maxTime: "22:05:00",
        eventLimit: true, // allow "more" link when too many events
        header: {
            left: "prev,next today",
            center: '',
            right: "month,agendaWeek,agendaDay",
        },
        viewRender: function (view, element) {
            var formattedDate;
            var resourceId;
            var resourceName;
            if (view.type === 'agendaDay') {
                formattedDate = moment(view.start).format('DD-MM-YYYY');
            } else if (view.type === 'agendaWeek') {
                var start = moment(view.start);
                var end = moment(view.start).add(6, 'days');
                formattedDate = moment(start).format('DD-MM-YYYY') + ' to ' + moment(end).format('DD-MM-YYYY');
            } else if (view.type === 'month') {

                formattedDate = moment(view.intervalStart).format('MMMM YYYY');
            }
            var formattedBanner = '<span style="font-weight: bold;">' + formattedDate + '</span>';
            $('.fc-center').html(formattedBanner);
        },
        allDaySlot: false,
        initialView: "resourceTimeGridDay",
        resources: resourcesData,
        events: function (start, end, timezone, callback) {
            var month, year;
            if (this.view.type === 'month') {
                month = moment(this.view.intervalStart).format('M');
                year = moment(this.view.intervalStart).format('YYYY');
            } else {
                month = moment(start).format('M');
                year = moment(start).format('YYYY');
            }

            $.ajax({
                url: `${pageRoute}/get/calendar/bookings`,
                dataType: 'json',
                data: {
                    // Assuming your backend API accepts start and end dates
                    start: start.format(),
                    end: end.format(),
                    month: month,
                    year: year,
                    resource_val: $("#calendar_mode").val(),
                },
                success: function (response) {
                    callback(response); // Pass the fetched events to fullCalendar
                },
                error: function () {
                    alert("There was an error while fetching events!");
                }
            });
        },

        loading: function (bool) {
            $("#preCalendar").html(
                '<div class="progress"><div class="indeterminate"></div></div>'
            ); // Add your script to show loading
        },
        eventAfterAllRender: function (event, view) {
            $("#preCalendar").hide();
        },
        eventRender: function (event, element, view) {
            if (event.allDay === "true") {
                event.allDay = true;
            } else {
                event.allDay = false;
            }
            element.find(".fc-title").append("<br/>" + event.description);
            element
                .find(".fc-title")
                .append(
                    "<br/>" +
                    "Room: " +
                    event.room.name +
                    "<br/>" +
                    "Therapist: " +
                    event.user.name
                );
            if (event.package) {
                element
                    .find(".fc-title")
                    .append(
                        "<br/>" +
                        "Package: " +
                        event.package.name +
                        "<br/>" +
                        "Price: " +
                        event.package.price
                    );
            }
            if ([1, 3, 4, 5, 6].includes(event.payment_status)) {
                element.find(".fc-title").append("<br/><span class='paid-tag'>Paid</span>");
            }
            if (event.checked_in === 1) {
                element.find(".fc-title").append("<br/><span class='checked_in-tag'>Checked-in</span>");
            }
            if (event.package_id) {
                element.find(".fc-title").append("<br/><span class='package_class'>PKG</span>");
            }

        },
        select: function (start, end, jsEvent, view, resource) {
            var resourceId;
            var resourceName;
            if (resource) {
                resourceId = resource.id;
                resourceName = resource.title;
            } else {
                if (view.calendar && view.calendar.getResources) {
                    var resources = view.calendar.getResources();
                    if (resources && resources.length > 0) {
                        resourceId = resources[0].id;
                        resourceName = resources[0].title;
                    }
                }
            }


            clearForm();
            if (mode == "therapists") {
                $("#user_id").val(resourceId);
                $("#user_id").select2().trigger("change");
            } else {
                $("#room_id").val(resourceId);
                $("#room_id").select2().trigger("change");
            }

            $('input[name="start_time"]').daterangepicker({
                singleDatePicker: true,
                startDate: start.format("DD-MM-YYYY " + timeFormat + ":mm A"),
                showDropdowns: true,
                autoApply: true,
                timePicker: true,
                timePicker24Hour: timePicker,
                locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
                minDate: moment().startOf('day') // This sets the minimum selectable date to today
            }, function (ev, picker) {
                // $("#start").val(start.format());
                // console.log(picker.format('DD-MM-YYYY'));
            });
            // Adding a custom class to the daterangepicker div element
            $('input[name="start_time"]').on('show.daterangepicker', function (ev, picker) {
                picker.container.addClass('schedules-daterangepicker');
            });
            var currentDate = moment().startOf('day');
            var selectedDate = moment(start).startOf('day');
            var underlyingDate = selectedDate._d;
            if (moment(underlyingDate).isSameOrAfter(currentDate, 'day')) {
                $("#manage-schedule-modal").modal("open");
            }
        },
        eventClick: function (event) {
            var currentDate = moment().startOf('day');
            var eventDate = moment(event.start).subtract(1, 'days').startOf('day');
            if (eventDate.isAfter(currentDate, 'day')) {
                $("#check_label").hide();
            } else {
                $("#check_label").show();
            }
            if (eventDate.isSameOrAfter(currentDate, 'day')) {
                clearForm();
                var event_id = event.id;
                $.ajax({
                    type: "GET",
                    url: pageRoute + "/" + event_id,
                    delay: 250,
                    success: function (data) {
                        if (data.flagError == false) {
                            var $form = $("#manageScheduleForm");
                            if (data.data.package_id != null) {
                                var $input = $(
                                    '<input type="hidden" name="item_ids[]" value="' +
                                    data.data.package_id +
                                    '" />'
                                );
                            } else {
                                var $input = $(
                                    '<input type="hidden" name="item_ids[]" value="' +
                                    data.data.item_id +
                                    '" />'
                                );
                            }
                            if (eventDate.isAfter(currentDate, 'day')) {
                                var inputResschedule = '<input type="hidden" id="reschedule_status" name="reschedule_status" value="reschedule" />'
                            }
                            $form.append($input);
                            $form.append(inputResschedule);
                            $(".label-placeholder").addClass("active");
                            $("#customer_id").val(data.data.customer_id);
                            $("#item_count").val(data.item_count);
                            $("#package_id").val(data.data.package_id);
                            $("#schedule_refund_id").val(data.data.id);
                            $("#bill_id").val(data.data.billing_id);
                            $("#service_id").val(data.data.item_id);
                            $("#service_item_id").val(data.data.item_id);
                            $("#billing_id").val(data.data.billing_id);
                            $("#schedule_package_id").val(data.data.package_id)

                            $('input[name="start_time"]').daterangepicker({
                                singleDatePicker: true,
                                startDate: data.start_formatted,
                                showDropdowns: true,
                                autoApply: true,
                                timePicker: true,
                                timePicker24Hour: timePicker,
                                locale: { format: "DD-MM-YYYY " + timeFormat + ":mm A" },
                                minDate: moment().startOf('day')

                            },
                                function (ev, picker) {

                                }
                            );
                            if ([1, 3, 4, 5, 6].includes(data.data.payment_status)) {
                                $("#payment_status").val(1);
                            }
                            $("#newCustomerBtn").hide();
                            $("#search_customer").val(data.customer_name);
                            $("#search_customer").attr("disabled", true);

                            $("#room_id").val(data.data.room_id);
                            $("#room_id").select2().trigger("change");
                            $("#customer_name").val(data.customer_name);
                            $("#mobile").val(data.data.mobile);
                            $("#email").val(data.data.email);
                            $("#user_id").val(data.data.user_id);
                            $("#user_id").select2().trigger("change");

                            var service_type = data.type == "services" ? 1 : 2;
                            $("#service_type").val(service_type);
                            $("#service_type").select2().trigger("change");
                            $("#schedule_id").val(data.data.id);
                            $("#cancelSchedule").show();
                            $("#receivePaymentBtn").show();
                            $("#package_of_service").val(data.data.item_id);
                            $("#package_of_service").select2().trigger("change");
                            $("input.disabled").attr("disabled", true);
                            data.data.checked_in == 1 ?
                                $("#checked_in").prop("checked", true) :
                                $("#checked_in").prop("checked", false);
                            if ([1, 3, 4, 5, 6].includes(data.data.payment_status)) {
                                $("#receivePaymentBtn").hide();


                            }
                            if ([1, 3, 4, 5, 6].includes(parseInt(data.data.payment_status))) {
                                if (!$('#hidden_service_type').length) {
                                    $('<input>', {
                                        type: 'hidden',
                                        id: 'hidden_service_type',
                                        name: 'service_type'
                                    }).appendTo('#manageScheduleForm'); // Append to the form with id 'manageScheduleForm'
                                }

                                if (!$('#hidden_services').length) {
                                    $('<input>', {
                                        type: 'hidden',
                                        id: 'hidden_services',
                                        name: 'bill_item[]'
                                    }).appendTo('#manageScheduleForm'); // Append to the form with id 'manageScheduleForm'
                                }

                                if (!$('#hidden_packages').length) {
                                    $('<input>', {
                                        type: 'hidden',
                                        id: 'hidden_packages',
                                        name: 'bill_item[]'
                                    }).appendTo('#manageScheduleForm'); // Append to the form with id 'manageScheduleForm'
                                }
                                var selectedServiceType = $('#service_type').val();
                                var selectedServices = $('#services').val();
                                var selectedPackages = $('#packages').val();

                                // Update hidden inputs with selected values
                                $("#hidden_service_type").val(selectedServiceType);
                                $("#hidden_services").val(selectedServices ? selectedServices.join(',') : ''); // Convert array to comma-separated string
                                $("#hidden_packages").val(selectedPackages ? selectedPackages.join(',') : ''); // Convert array to comma-separated string

                                // Disable the select fields visually
                                $('#service_type').prop('disabled', true);
                                $('#services').prop('disabled', true);
                                $('#packages').prop('disabled', true);
                            } else {
                                // Enable the select fields if conditions are not met
                                $('#service_type').prop('enabled', false);
                                $('#services').prop('disabled', false);
                                $('#packages').prop('disabled', false);
                            }



                            $("#manage-schedule-modal").modal("open");
                        }
                    },
                });
            } else {


            }
        },
        eventDrop: function (event, dayDelta, minuteDelta, allDay, revertFunc) {
            let updateId;
            let resourceName = "";
            let changeText = "";
            let roomId;
            $.ajax({
                type: "GET",
                url: baseUrl + new_url + mode + "/" + event.resourceId,
                async: false,
                success: function (data) {
                    resourceName = data;
                },
            });
            changeText =
                mode == "therapists" ?
                    "&Therapist:" + resourceName.therapist.name + " !" :
                    "&Room: " + resourceName.room.name + " !";
            updateId =
                mode == "therapists" ?
                    resourceName.therapist.id :
                    resourceName.room.id;
            roomId = event.room.id;

            // swal({
            //     title: "Are you sure about this change?",
            //     text: "Start time:" +
            //         event.start.format(timeFormat + ":mm A") +
            //         changeText,
            //     icon: "warning",
            //     dangerMode: true,
            //     buttons: { cancel: "No, Please!", delete: "Yes, Change It" },
            // }).then(function (willDelete) {
            //     if (willDelete) {
            $.ajax({
                url: pageRoute + "/re-schedule",
                type: "POST",
                data: {
                    schedule_id: event.id,
                    start_time: event.start.format(),
                    mode: mode,
                    update_id: updateId,
                    roomId: roomId,

                },
            })
                .done(function (data) {
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        event.resourceId = updateId;
                        event.roomId = roomId; // Assuming roomId is a property of event
                        // $("#calendar").fullCalendar("updateEvent", event);
                        $("#calendar").fullCalendar("refetchEvents");
                    } else {

                        showErrorToaster(data.message);
                        event.resourceId = updateId;
                        event.roomId = roomId; // Assuming roomId is a property of event
                        $("#calendar").fullCalendar("refetchEvents");
                    }
                })
                .fail(function () {
                    showErrorToaster("Something went wrong!");
                });
            // } else {
            //     // setTimeout(function () {
            //     //     window.location.reload(); // Example: reload the current page
            //     // }, 1000);
            //     $("#calendar").fullCalendar("refetchEvents");
            // }
            // });
        },
        dayClick: function (date, jsEvent, view) {
            $('#service_type').prop('enabled', false);
            //$('#modal1').modal('open');
        },
    });
}

function getCustomerDetails(customer_id) {
    var url = get_customer_details;
    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        data: { customer_id: customer_id },
        delay: 250,
        success: function (data) {
            $(".label-placeholder").addClass("active");
            // $("#search_customer").val(data.data.name + ' - ' + data.data.mobile);
            $("#customer_id").val(customer_id);
            $("#customer_name").val(data.data.name);
            $("#mobile").val(data.data.mobile);
            $("#email").val(data.data.email);
            $("#customer_id").val(customer_id);
        },
    });
}
if ($("#manageScheduleForm").length > 0) {
    var validator = $("#manageScheduleForm").validate({
        ignore: ".ignore-validation",
        rules: {
            customer_name: {
                required: true,
                maxlength: 200,
                lettersonly: true,
            },
            search_customer: {
                required: true,
            },
            user_id: {
                required: true,
            },
            room_id: {
                required: true,
            },
            mobile: {
                minlength: 10,
                maxlength: 10,
            },
            email: {
                email: true,
            },
            // "bill_item[]": {
            //     required: true,
            // },
        },
        messages: {
            customer_name: {
                required: "Please enter Customer Name",
                maxlength: "Length cannot be more than 200 characters",
            },
            search_customer: {
                required: "Please search Name or Mobile for existing Customers",
            },
            room_id: {
                required: "Please select Room",
            },
            user_id: {
                required: "Please select a Therapist",
            },
            mobile: {
                maxlength: "Length cannot be more than 10 digits",
                minlength: "Length must be 10 Numbers",
            },
            email: {
                email: "Please enter a valid E-mail Id.",
            },
            // "bill_item[]": {
            //     required: "Please select an Item",
            // },
        },
        submitHandler: function (form) {
            var packageType = $(".package_items").data('type');
            var selectedPackage = $("select[name='bill_item[]']").val();
            var customerID = $("#customer_id").val();
            var new_customer_name = $("#customer_name").val();
            if ((customerID == '') && (new_customer_name == '')) {
                showErrorToaster("Customer not found! Please select or Add new Customer");
            } else {
                disableBtn("schedule-submit-btn");
                disableBtn("receivePaymentBtn");
                scheduleId = $("#schedule_id").val();
                post_id = "" == scheduleId ? "" : "/" + scheduleId;
                formMethod = "" == scheduleId ? "POST" : "PUT";
                $("#manageScheduleForm").append(
                    '<input type="hidden" class="form-method-hidden" name="form_method" value="' + formMethod + '"></input>'
                );
                var scheduleForm = $("#manageScheduleForm");
                $(".itemCount").each(function (index, element) {
                    $("#manageScheduleForm").append(
                        '<input type="hidden" class="item-count-hidden" name="items[' +
                        $(this).data("id") +
                        " ][" +
                        $(this).val() +
                        ']"></input>'
                    );
                });
                $.ajax({
                    url: pageRoute + post_id,
                    type: formMethod,
                    data: scheduleForm.serialize(),
                    success: function (data) {
                        if (data.flagError == true) {
                            showErrorToaster(data.message);
                            setTimeout(function () {
                                window.location.reload(); // Example: reload the current page
                            }, 4000);
                            enableBtn("schedule-submit-btn");
                            enableBtn("receivePaymentBtn");
                        } else {
                            if (data.redirect === "redirect") {
                                window.location.href =
                                    billingRoute + "/invoice/" + data.billing_id;
                            } else if (data.redirect === "refresh") {
                                if (data.payment_status == 1) {
                                    window.location.href =
                                        billingRoute + "/" + data.billing_id;
                                } else {
                                    window.location.href =
                                        billingRoute + "/invoice/" + data.billing_id;
                                }
                            } else if (data.redirect === "submit") {
                                setTimeout(function () {
                                    window.location.reload(); // Example: reload the current page
                                }, 4000);
                            } else {
                                $("#receivePaymentBtn").html(
                                    'Receive payment <i class="material-icons right">keyboard_arrow_right</i>'
                                );
                                $("#receivePaymentBtn").attr("disabled", false);
                                $("#schedule-submit-btn").html('Submit <i class="material-icons right">keyboard_arrow_right</i>');
                                $("#schedule-submit-btn").attr("disabled", false);
                                $("#manage-schedule-modal").modal("close");
                                showSuccessToaster(data.message);
                                $("#calendar").fullCalendar("refetchEvents");
                                clearForm();
                                getSalesData();
                            }
                        }
                    },
                });
                clearForm();
            }

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

jQuery.validator.addMethod(
    "lettersonly",
    function (value, element) {
        return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
    },
    "Letters only please"
);

function clearForm() {
    validator.resetForm();
    $("input").removeClass("error");
    $("#manageScheduleForm .form-control").removeClass("error");
    $("select").removeClass("error");
    $("#manageScheduleForm").trigger("reset");
    $("#manageScheduleForm").find("input[type=text], textarea, hidden").val("");
    $("#service_type").select2({ placeholder: "Please select type" });
    $(".service-type").attr("disabled", false);
    $(".service-type").empty().trigger("change");
    $("#item_count").val(1);
    $("#customer_id").attr("disabled", false);
    $("#customer_id").val("").trigger("change");
    $("#room_id").val("").trigger("change");
    $(".new-customer-form").hide();
    $("#newCustomerBtn").show();
    $("#add_new_customer").prop("checked", false);
    $("input[name='item_ids[]']").remove();
    $("input.disabled").attr("disabled", false);
    $("#manageScheduleForm").find("input[type=hidden]").val("");
    $("#itemDetails").html();
    $("#cancelSchedule").hide();
    $("#itemDetailsDiv").hide();
    $(".form-action-btn").show();
    $('#search_customer').prop('disabled', false).val('');
    $("#customerActionDiv").hide();
    $("#newCustomerBtn").show();

}
$("#cancelSchedule").click(function () {
    var payment_status = $("#payment_status").val();
    var serviceId = $("#service_item_id").val();
    var billId = $("#bill_id").val();
    var scheduleListUrl = scheduleServiceList;
    var packageId = $("#schedule_package_id").val();
    if ([1, 3, 4, 5, 6].includes(payment_status)) {
        swal({
            title: "Are you sure?",
            text: "This Service is paid. Do you still want to cancel?",
            icon: "warning",
            dangerMode: true,
            buttons: {
                cancel: "No, Please!",
                confirm: "Yes, Cancel It"
            },
        }).then(function (willCancel) {
            if (willCancel) {
                $.ajax({
                    url: scheduleListUrl,
                    type: 'GET',
                    data: {
                        bill_id: billId,
                        packageId: packageId
                    },
                    success: function (response) {
                        if (response.flagError == false) {
                            populateServiceCheckboxes(response.data, serviceId, packageId);

                        } else {
                            showErrorToaster(response.message);
                            printErrorMsg(response.error);
                            setTimeout(function () {
                                window.location.reload(); // Example: reload the current page
                            }, 1000);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr, status, error);
                        console.error(xhr.responseText);
                    }
                });

                $("#manage-schedule-modal").modal("close");
                $("#manage-refund-modal").modal("open");
                enableBtn("refund-submit-btn");
                // Detach previous submit handlers and attach a new one
                $('#manageRefundForm').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    var refundAmounts = [];
                    disableBtn("refund-submit-btn");
                    $('input[name="refund_amount[]"]').each(function () {
                        var refundAmount = $(this).val();
                        var paymentName = $(this).data('name');
                        var paymentId = $(this).data('id');
                        refundAmounts.push({
                            name: paymentName,
                            id: paymentId,
                            amount: refundAmount
                        });
                    });

                    var refundBillPaymentUrl = refundBillPayment;
                    var refundAmountsJson = JSON.stringify(refundAmounts);

                    var formData = $(this).serialize() + '&refundAmounts=' + encodeURIComponent(refundAmountsJson);

                    $.ajax({
                        url: refundBillPaymentUrl,
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            if (response.flagError == false) {
                                showSuccessToaster(response.message);
                                $("#manage-refund-modal").modal("close");
                                setTimeout(function () {
                                    window.location.reload(); // Example: reload the current page
                                }, 1000);
                            } else {
                                enableBtn("refund-submit-btn");
                                showErrorToaster(response.message);
                                printErrorMsg(response.error);

                            }
                        },
                        error: function (xhr, status, error) {
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
            icon: "warning",
            dangerMode: true,
            buttons: { cancel: "No, Please!", delete: "Yes, Cancel It" },
        }).then(function (willDelete) {
            if (willDelete) {
                var schedule_id = $("#schedule_id").val();
                $.ajax({
                    url: pageRoute + "/" + schedule_id,
                    type: "DELETE",
                    dataType: "html",
                })
                    .done(function (a) {
                        var data = JSON.parse(a);
                        if (data.flagError == false) {
                            showSuccessToaster(data.message);
                            $("#manage-schedule-modal").modal("close");
                            $("#calendar").fullCalendar("refetchEvents");
                            getSalesData();
                        } else {
                            showErrorToaster(data.message);
                            setTimeout(function () {
                                window.location.reload();
                            }, 4000);
                        }
                    })
                    .fail(function () {
                        showErrorToaster("Something went wrong!");
                    });
            }
        });
    }
});


function populateServiceCheckboxes(schedules, serviceId, packageId) {

    var balanceInstore = 0;
    var instoreCredit = 0;
    var tableBody = $("#service_append");
    var packageTableBody = $("#package_append");
    tableBody.empty(); // Clear any existing checkboxes
    packageTableBody.empty(); // Clear the package append section as well
    var itemList = $("#itemList");
    itemList.empty();
    var instoreCreditUsed = schedules.instoreCreditUsed.toFixed(2);
   
    if (schedules.services.length > 1) {
        $("#service_append").show();
        var serviceRow = $("<table><tr><td colspan=''><h6>Service Schedules</h6></td><td><h6 id='total_service_amount'>Total Price: </h6></td><td><h6 id='total_discount_amount'>Total Discount Amount: </h6></td><td><h6 id='total_due_amount'>Total Due Amount: </h6></td></tr></table>");
        var serviceContainer = $("<table><tr style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'><td colspan=''><div class='checkbox-container'></div></td></tr></table>");
        var serviceDiv = serviceContainer.find('.checkbox-container');

        if (schedules.billItems.length > 0) {
            $("#total_discount_amount").show();
        } else {
            $("#total_discount_amount").hide();
        }

        schedules.services.forEach(function (schedule, index) {
            var isChecked = serviceId == schedule.item_id ? "checked" : "";
            var discount = 0;
            // Check for discounts in bill items
            schedules.billItems.forEach(function (billItem) {
                if (billItem.item_id === schedule.item_id) {
                    discount = billItem.discount_value;
                }
            });
            var checkbox = $(
                "<label for='cancel_service_" + index + "'>" +
                "<input type='checkbox' class='service_checkbox' id='cancel_service_" + index + "' name='cancel_service[]' data-discount='" + discount + "' data-price='" + schedule.service.price + "' value='" + schedule.item_id + "' " + isChecked + ">" +
                schedule.service.name +
                "</label>"
            );
            serviceDiv.append(checkbox);
            listItemDetails('services', schedule.item_id, 'select')

        });

        $("#service_append").append(serviceRow);
        $("#service_append").append(serviceContainer);
    } else {
        if (schedules.services.length == 1) {
            var serviceRow = $("<table><tr style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'><td colspan=''><h6>Service Schedules</h6></td><td><h6 id='total_service_amount'>Total Price: </h6></td><td><h6 id='total_discount_amount'>Total Discount Amount: </h6></td><td><h6 id='total_due_amount'>Total Due Amount: </h6></td></tr></table>");
            $("#service_append").append(serviceRow);
            listItemDetails('services', schedules.services[0].item_id, 'select')

        }
        $("#package_append").hide();
    }
    if (schedules.packages.length > 1) {
        $("#service_append").hide();
        $("#package_append").show();
        var packageRow = $("<table><tr><td ><h6>Package Schedules</h6></td><td><h6 id='total_package_amount'>Total Price: </h6></td><td><h6 id='total_due_amount'>Total Due Amount: </h6></td></tr></table>");
        var packageContainer = $("<table><tr style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'><td><div class='checkbox-container'></div></td></tr></table>");
        var packageDiv = packageContainer.find('.checkbox-container');
        schedules.packages.forEach(function (schedule, index) {
            var isChecked = packageId == schedule.package_id ? "checked" : "";
            var checkbox = $(
                "<label for='cancel_package_" + index + "'>" +
                "<input type='checkbox' class='package_checkbox' id='cancel_package_" + index + "' name='cancel_package[]' data-price='" + schedule.package.price + "' value='" + schedule.package_id + "' " + isChecked + ">" +
                schedule.package.name +
                "</label>"
            );
            packageDiv.append(checkbox);
            listItemDetails('packages', schedule.package_id, 'select')

        });

        packageTableBody.append(packageRow);
        packageTableBody.append(packageContainer);
    } else {
        if (schedules.packages.length == 1) {
            var packageRow = $("<table><tr style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'><td ><h6>Package Schedules</h6></td><td><h6 id='total_package_amount'>Total Price: </h6></td><td><h6 id='total_due_amount'>Total Due Amount: </h6></td></tr></table>");
            tableBody.append(packageRow);
            listItemDetails('packages', schedules.packages[0].package_id, 'select')

        }
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
        if (schedules.services.length > 1) {
            $('.service_checkbox:checked').each(function () {
                var serviceId = $(this).val();
                var price = parseFloat($(this).data('price'));
                var discount = parseFloat($(this).data('discount')) || 0;
                serviceIds.push(serviceId);

                totalPrice += price;
                totaldiscount += discount;
                    totalDiscountPrice = totalPrice - totaldiscount;
                    
                if (dueAmount > 0) {
                    if (totalDiscountPrice > 0 && dueAmount > totalDiscountPrice || dueAmount > totalPrice) {
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

                listItemDetails('services', serviceIds, 'select')

            });
        } else {
            if (schedules.services[0]) {
                totalPrice = parseFloat(schedules.services[0].service.price);
                schedules.billItems.forEach(function (billItem, index) {
                    if(billItem.item_id==schedules.services[0].service.id){
                        totaldiscount = parseFloat(billItem.discount_value) || 0;
                        totalDiscountPrice += totalPrice - totaldiscount;
                    }                
               });
                if (totalDueAmount > 0 && totalDueAmount == totalPrice) {
                    $(".paymentType").hide();
                } else {
                    $(".paymentType").show();
                }
            } else if (schedules.packages.length > 1) {
                $('.package_checkbox:checked').each(function () {
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
            } else if (schedules.packages[0]) {
                totalPrice = parseFloat(schedules.packages[0].package.price);
            } else {
                totalPrice = 0;
            }
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
        console.log(cancelFee);
        
        $("#refund_amount").val(instoreCredit);
        $("#total_paid_refund").text('₹' + instoreCredit);
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
$(document).ready(function () {
    function calculateTotalRefundAmount() {
        var hasProcessed = false; 
        var product = $('#total_service_amount').text();
        var totalDiscount = $("#total_discount_amount").text();
        var packageAmount = $('#total_package_amount').text();
        var dueAmount = $("#total_due_amount").text();
       
        var totalDueAmount = parseFloat(dueAmount.replace(/[^\d.-]/g, ''));
        var totalDiscountPrice = parseFloat(totalDiscount.replace(/[^\d.-]/g, ''));
        var productPrice = 0;
        var totalCancelFee=$("#total_cancellation_fee").text();
        var previousTotalCancelFee= parseFloat(totalCancelFee.replace(/[^\d.-]/g, ''));
        
        
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
        $('input[name="refund_amount[]"]').each(function () {
            var refundAmount = parseFloat($(this).val()) || 0; // Ensure the value is treated as a float, defaulting to 0 if invalid
            var paymentName = $(this).data('name');
            var paymentId = $(this).data('id');
            refundAmounts.push({
                name: paymentName,
                id: paymentId,
                amount: refundAmount
            });

        });
        var totalRefundAmount = refundAmounts.reduce(function (total, refund) {
            return total + refund.amount;
        }, 0);

        if (totalDiscountPrice > 0) {
            totalCancellationFee = totalDiscountPrice > totalRefundAmount ? totalDiscountPrice-totalDueAmount - totalRefundAmount : 0;
        } else {
            totalCancellationFee = productPrice > totalRefundAmount ? productPrice - totalRefundAmount : 0;
        }

        totalCancellationFee = parseFloat(totalCancellationFee);
        // if(previousTotalCancelFee >totalCancellationFee){
            
        //     swal({
        //         icon: 'error',
        //         title: 'Oops...',
        //         text: 'Refund amount exceeds the allowed limit of  RS ' +previousTotalCancelFee+  ' because there is a pending amount RS '+totalDueAmount+' on this bill.',
        //         confirmButtonText: 'OK'
        //     }).then(function (willCancel) {
        //         if (willCancel) {
                
        //         }
        //     });
        // }
        // if(totalCancellationFee>0 && totalDueAmount>0){
        //     totalCancellationFee -=totalDueAmount;
        // }
        if (totalCancellationFee < 0) {
            totalCancellationFee = 0;
        }
console.log(totalCancellationFee);

        if (isNaN(totalCancellationFee)) {
            totalCancellationFee = 0.00; // Set default value to 0.00 if NaN
        }

        $('#total_paid_refund').text('₹' + totalRefundAmount.toFixed(2));
        $('#total_cancellation_fee').text('₹' + totalCancellationFee.toFixed(2));
    }

    // Attach the change event handler to the refund amount inputs
    $(document).on('change', 'input[name="refund_amount[]"]', function () {
        calculateTotalRefundAmount();
    });

    // Initial calculation in case there are any pre-filled values
    calculateTotalRefundAmount();
});

$("#receivePaymentBtn").click(function () {
    var form = $("#manageScheduleForm");
    $("#receive_payment").val(1);
    form.submit();
});

function getCustomerDetails(customer_id) {
    var url = get_customer_details;
    // /common/get-customer-details
    $.ajax({
        type: "GET",
        url: url,
        dataType: "json",
        data: { customer_id: customer_id },
        delay: 250,
        success: function (data) {
            var customerMobile = "";
            if (data.data.mobile != null) {
                customerMobile = " - " + data.data.mobile;
            }
            $("#search_customer").val(data.data.name + customerMobile);
            $("#customer_name").val(data.data.name);
            $("#customer_mobile").val(data.data.mobile);
            $("#customer_email").val(data.data.email);
            $("#customer_id").val(customer_id);
            $("#newCustomerBtn").hide();
            $(".user-details").show();
            var customerViewURL = data.url;
            $("#customerViewLink").attr("href", customerViewURL);
            $("#customerActionDiv").show();
            $("#" + pageTitle + "Form label").removeClass("error");
        },
    });
}

function removeCustomer() {
    $('#search_customer').prop('disabled', false).val('');
    $("#customer_id").val('');
    $("#customerActionDiv").hide();
    $("#newCustomerBtn").show();
    showSuccessToaster("Customer removed successfully.");

    // $("#search_customer").val("");
    // $("#customer_name").val("");
    // $("#customer_mobile").val("");
    // $("#customer_email").val("");
    // $("#customer_details_div").hide();
    // $(".user-details").hide();
}

$("#add_new_customer").click(function () {
    $('#search_customer').val('');
    $('#customer_name').val('');
    $('#search_customer').prop('disabled', (i, v) => !v);
    $("#customer_id").val('');
    $(this).text(function (i, text) {
        return text === "Add New Customer" ? "Search Customer" : "Add New Customer";
    })
    $(".new-customer-form").toggle();
    $("#customer_name").toggleClass("ignore-validation");

});
var columns;
var formValue;
var table = $('#data-table-schedulers');
var url = getScheduleList;
var form = table.data('form');
var length = table.data('length');

columns = [];
formValue = [];

table.find('thead th').each(function () {
    var column = { 'data': $(this).data('column') };
    columns.push(column);
});

table.DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    bLengthChange: false,
    pageLength: 10,
    ajax: {
        "type": "GET",
        "url": url,
        "data": function (data) {
            data.form = formValue;
        }
    },
    columns: columns,
});

// Show active and Inactive Lists
$(".listBtn").on("click", function () {
    $("#status").val($(this).attr('data-type'));
    formValue = $('#' + form).serializeArray();
    table.DataTable().draw();
});
