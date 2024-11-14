"use strict";

var pageTitle 	= $("#pageTitle").val();
var pageRoute 	= $("#pageRoute").val();
var table;
var serviceId;
var post_id;
var formMethod;
var validator;

$('#gst_tax').select2({ placeholder: "Please select GST Tax %", allowClear: true });
$('#hours_id').select2({ placeholder: "Please select Service Time", allowClear: true });
$('#additional_tax').select2({ placeholder: "Select Additional Tax", allowClear: true });

// Form Validation with Ajax Submit
if ($("#" + pageTitle + "Form").length > 0) {
  validator = $("#" + pageTitle + "Form").validate({ 
    ignore: ".ignore-validation",
    rules: {
      name: {
        required: true,
        maxlength: 200,
      },
      search_service_category: {
        required: true,
      },
      hours_id: {
        required: true,
      },
      price: {
        required: true,
      },
      gst_tax: {
        // required: true,
      }
    },
    messages: { 
      name: {
        required: "Please enter Name",
        maxlength: "Length cannot be more than 200 characters",
      },
      search_service_category: {
        required: "Please enter Service Category",
      },
      hours_id: {
        required: "Please select Hours",
      },
      price: {
        required: "Please enter Price",
      },
      gst_tax: {
        required: "Please select GST Tax percentage",
      }
    },
    submitHandler: function (form) {
      disableBtn("submit-btn");
      serviceId     = $("#service_id").val();
      post_id       = "" == serviceId ? "" : "/" + serviceId;
      formMethod    = "" == serviceId ? "POST" : "PUT";
      var forms     = $("#" + pageTitle + "Form");

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
          printErrorMsg(data.error);
        }
      });
    }
  })
}

$('#preset-btn').click(function() {
  validator.resetForm();
  $('#' + pageTitle + 'Form').find("input[type=text], textarea, radio").val("");
  $("#hours_id").val('').trigger('change');
  $("#gst_tax").val('').trigger('change');
  $("#additional_tax").val('').trigger('change');
  $("#lead_before").val('').trigger('change');
  $("#lead_after").val('').trigger('change');
  $('#tax_included').prop('checked', false);
  $('input').removeClass('error');
  $("#" + pageTitle + "Form label").removeClass("error");
});


// DataTable Initialization
var columns;
var formValue;
var table     = $('#data-table-services');
var url       = table.data('url');
var form      = table.data('form');
var length    = table.data('length');

columns   = [];
formValue = [];

table.find('thead th').each(function () {
  var column = {'data': $(this).data('column')};
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
$(".listBtn").on("click", function()  {
  $("#status").val($(this).attr('data-type'));
  formValue = $('#' + form ).serializeArray();
  table.DataTable().draw();
});


table.on('click', '.disable-item', function() {
  var id      = $(this).attr('data-id');
  var postUrl = $(this).attr('data-url');
  swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons: { cancel: 'No, Please!', delete: 'Yes, Inactivate' }
  }).then(function (willDelete) {
    if (willDelete) {
      $.ajax({url: postUrl + "/" + id, type: "DELETE", dataType: "html"
      }).done(function (a) {
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
      }).fail(function () {
        showErrorToaster("Something went wrong!");
      });
    } 
  });
});

table.on('click', '.restore-item', function() {
  var postUrl = $(this).attr('data-url'); 
  var id      = $(this).attr('data-id');
  swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons: { cancel: 'No, Please!', delete: 'Yes, Activate' }
  }).then(function (willDelete) {
    if (willDelete) {
      $.ajax({url: postUrl + "/restore/" + id, type: "POST", dataType: "html"
      }).done(function (a) {
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
      }).fail(function () {
        showErrorToaster("Something went wrong!");
      });
    } 
  });
});

