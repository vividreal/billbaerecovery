"use strict";

var pageTitle 	    = $("#pageTitle").val();
var pageRoute 	    = $("#pageRoute").val();
var table;
var roomId;
var post_id;
var formMethod;
var validator;

// Form Validation with Ajax Submit
if ($("#" + pageTitle + "Form").length > 0) {
  validator = $("#" + pageTitle + "Form").validate({ 
    rules: {
      name: {
        required: true,
      }
    },
    messages: { 
      name: {
        required: "Please enter Name",
      }
    },
    submitHandler: function (form) {
      disableBtn("submit-btn");
      roomId        = $("#room_id").val();
      post_id       = "" == roomId ? "" : "/" + roomId;
      formMethod    = "" == roomId ? "POST" : "PUT";
      var forms     = $("#" + pageTitle + "Form");
      $.ajax({ url:pageRoute + post_id, type: formMethod, processData: false, data: forms.serialize(), 
      }).done(function (data) {
        enableBtn("submit-btn");
        if (data.flagError == false) {
          showSuccessToaster(data.message);          
          setTimeout(function () {
            window.location.href = pageRoute;
          }, 1000);
        } else {
          showErrorToaster(data.message);
          printErrorMsg(data.error);
        }
      });

    }
  })
}


$('#reset-btn').click(function() {
  validator.resetForm();
  $('#' + pageTitle + 'Form').find("input[type=text], textarea").val("");
});




// DataTable Initialization
var columns;
var formValue;
var table     = $('#data-table-rooms');
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
  ajax: { "type": "GET", "url": url, "data": function (data) { data.form = formValue; }
  },
  columns: columns,
});


table.on('click', '.delete-item', function() {
  var id      = $(this).attr('data-id');
  var postUrl = $(this).attr('data-url');
  swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons: { cancel: 'No, Please!', delete: 'Yes, Delete' }
  }).then(function (willDelete) {
    if (willDelete) {
      $.ajax({url: postUrl + "/" + id, type: "DELETE",
      }).done(function (data) {
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
    } 
  });
});





