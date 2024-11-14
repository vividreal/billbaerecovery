"use strict";

var pageTitle = $("#pageTitle").val();
var pageRoute = $("#pageRoute").val();
var timePicker = $("#timePicker").val();
var timeFormat = $("#timeFormat").val();
var editable = $("#editable").val();
var table;
var staffId;
var attendanceId;
var post_id;
var formMethod;
var markDate;
var validator;



$('input[name="marked_date"]').daterangepicker(
    {
        singleDatePicker: true,
        startDate: new Date(),
        maxDate: new Date(),
        showDropdowns: true,
        autoApply: true,
        locale: { format: "DD-MM-YYYY" },
    },
    function (start, end, label) {
        // $("#marked_date").val(start.format('YYYY-MM-DD'));
        getData(start.format("YYYY-MM-DD"));
    }
);

$(function () {
    getData();
});

function getData(markDate = null) {
    $.ajax({
        url: pageRoute,
        type: "get",
        dataType: "json",
        data: { markDate: markDate, editable: editable },
    }).done(function (data) {
        if (data.flagError == false) {
            $("#attendance-table-data").html("");
            $("#attendance-table-data").html(data.html);
        } else {
            showErrorToaster("Errors Occurred! Please try again.");
        }
    });
}

$(".checkin-checkout").change(function () {
    this.value = this.checked ? 1 : 0;
});

$(".mark-attendance").on("click", function (e) {
    let userId = $(this).attr("data-userId");
    let staffId = $(this).attr("data-staffId");
    let staffStatus = $(this).attr("data-status");
    let mark = $("#mark_" + userId).val();
    let status = mark == 1 ? "Checked In" : "Checked Out";

    if (staffStatus == mark) {
        swal(
            "Warning !",
            `Staff is already ${status}! Please change status`,
            "warning"
        );
    } else {
        swal({
            title: "Are you sure?",
            text: `Staff status will change to ${status}`,
            icon: "warning",
            buttons: true,
        }).then((willDelete) => {
            if (willDelete) {
                disableBtn("submit-btn_" + userId);
                $.ajax({
                    url: pageRoute,
                    type: "post",
                    data: { userId: userId, mark: mark, staffId: staffId },
                    dataType: "json",
                }).done(function (data) {
                    enableBtn("submit-btn_" + userId);
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        setTimeout(function () {
                            // location.reload();
                            window.location.href = pageRoute;
                        }, 1000);
                    } else {
                        showErrorToaster(data.message);
                        printErrorMsg(data.error);
                    }
                });
            }
        });
    }
});

$(document).ready(function () {
    $("#attendance-table-data").on("click", ".edit-markings", function (e) {
        let attendanceId = $(this).attr("data-id");
        let action = $(this).attr("data-action");
        let time = $(this).attr("data-time");
        let staffId = $(this).attr("data-staffId");
        $("#attendance_time").val(time);

        alert(time)

        $('input[name="attendance_time"]')
            .daterangepicker({
                timePicker: true,
                singleDatePicker: true,
                timePickerIncrement: 1,
                timePicker24Hour: false,
                // autoApply: true,
                // parentEl: "#edit-marking-modal",
                locale: {
                    format: "hh:mm A",
                },
            })
            .on("show.daterangepicker", function (ev, picker) {
                picker.container.find(".calendar-table").hide();
            });
            
            $("#attendanceId").val(attendanceId);
            $("#markingAction").val(action);
            $("#staffID").val(staffId);
            $('#attendance_time_label').addClass('active');
        $("#edit-marking-modal").modal("open");
    });
});

// Form Validation with Ajax Submit
if ($("#editMarkingForm").length > 0) {
    validator = $("#editMarkingForm").validate({ 
      rules: {
        attendance_time: {
          required: true,
        },
      },
      messages: { 
        name: {
          required: "Field is required",
        },
      },
      submitHandler: function (form) {
        disableBtn("edit-marking-submit-btn");
        attendanceId    = $("#attendanceId").val();
        post_id         = "" == attendanceId ? "" : "/" + attendanceId;
        formMethod    = "" == attendanceId ? "POST" : "PUT";
        var forms     = $("#editMarkingForm");

        $.ajax({ url:pageRoute + post_id, type: formMethod, processData: false, data: forms.serialize(), 
        }).done(function (data) {
          enableBtn("submit-btn");
          if (data.flagError == false) {
            showSuccessToaster(data.message);          
            setTimeout(function () {
              window.location.href = pageRoute;
            }, 2000);
          } else {
            showErrorToaster(data.message);
            $("#edit-marking-modal").modal("close");
          }
        });
      }
    })
  }
