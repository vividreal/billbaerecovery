"use strict";

var pageTitle 	= $("#pageTitle").val();
var pageRoute 	= $("#pageRoute").val();
var table;
var userId;
var post_id;
var formMethod;
var validator;

$('#roles').select2({ placeholder: "Please select role", allowClear: true });

// Form Validation with Ajax Submit
if ($("#" + pageTitle + "Form").length > 0) {
  var get_email_url=get_email_url;
  validator = $("#" + pageTitle + "Form").validate({ 
    ignore: ".ignore-validation",
    rules: {
      name: {
        required: true,
        maxlength: 200,
        // lettersonly: true,
      },
      mobile:{
        required:true,
        minlength:10,
        maxlength:10
      },
      "roles[]": {
        required: true,
      },
      email: {
        required: true,
        email: true,
        emailFormat:true,
        remote: { url: get_email_url, type: "POST",
          data: {
            user_id: function () {
              return $('#user_id').val();
            }
          }
        },
      },
    },
    messages: { 
      name: {
        required: "Please enter Name",
        maxlength: "Length cannot be more than 200 characters",
      },
      mobile: {
        required: "Please enter Mobile Number",
        maxlength: "Length cannot be more than 10 numbers",
        minlength: "Length must be 10 numbers",
      },
      email: {
        required: "Please enter E-mail",
        email: "Please enter a valid E-mail address.",
        remote: "E-mail already existing"
      },
      "roles[]": {
        required: "Please choose Role",
      },
    },
    submitHandler: function (form) {
      disableBtn("submit-btn");
      userId    = $("#user_id").val();
      post_id       = "" == userId ? "" : "/" + userId;
      formMethod    = "" == userId ? "POST" : "PUT";
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

jQuery.validator.addMethod("emailFormat", function (value, element) {
  return this.optional(element) || /[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/igm.test(value);
}, "Please enter a valid E-mail address");  

jQuery.validator.addMethod("lettersonly", function (value, element) {
  return this.optional(element) || /^[a-zA-Z()._\-\s]+$/i.test(value);
}, "Please enter Letters only");

$('#reset-btn').click(function() {
  validator.resetForm();
  $('#' + pageTitle + 'Form').find("input[type=text], textarea, radio").val("");
  $("#male").prop("checked", true);
  $("#roles").val('').trigger('change');
});

// $(document).ready(function(){
//   $('input[name="dob"]').daterangepicker({
//     singleDatePicker: true,
//     showDropdowns: true,
//     locale: { format: 'DD-MM-YYYY ' },
//     maxYear: parseInt(moment().format('YYYY'),10)
//   }, function(start, end, label) {
//     var years = moment().diff(start, 'years');
//   });
// });

// $(document).on('change', '#country_id', function () {
//   $.ajax({ type: 'POST', url: "common/get-states-by-country", data:{'country_id':this.value }, dataType: 'json',
//     success: function(data) {
//       var selectTerms = '<option value="">Please select state</option>';
//       $.each(data.data, function(key, value) {
//         selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
//       });
//       var select = $('#state_id');
//       select.empty().append(selectTerms);
//       $('#district_id').empty().trigger("change");
//     }
//   });
// });

// $(document).on('change', '#state_id', function () {
//   $.ajax({ type: 'POST', url: "common/get-districts-by-state'", data:{'state_id':this.value }, dataType: 'json',
//     success: function(data) {
//       var selectTerms = '<option value="">Please select district</option>';
//       $.each(data.data, function(key, value) {
//         selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
//       });
//       var select = $('#district_id');
//       select.empty().append(selectTerms);
//     }
//   });
// });



















// DataTable Initialization
var columns;
var formValue;
var table     = $('#data-table-users');
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
  swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons: { cancel: 'No, Please!', delete: 'Yes, Remove' }
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

table.on('click', '.force-delete-item', function() {
  var postUrl = $(this).attr('data-url'); 
  var id      = $(this).attr('data-id');
  swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons: { cancel: 'No, Please!', delete: 'Yes, Delete' }
  }).then(function (willDelete) {
    if (willDelete) {
      $.ajax({url: postUrl + "/hard-delete/" + id, type: "POST", dataType: "html"
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


table.on('click', '.manage-status', function() {
  var postUrl = $(this).attr('data-url');
  var id      = $(this).attr('data-id');
  $.ajax({url: postUrl , data:{'id':id }, type: 'POST', dataType: "html"})
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
  }).fail(function () {
    showErrorToaster("Something went wrong!");
  });
});

// showMessage = function(message) {
//   $("#fullMessage").text(message)
//   $("#full-message-modal").modal("open");
// }

