"use strict";

var pageTitle 	= $("#pageTitle").val();
var pageRoute 	= $("#pageRoute").val();
var table;
var customerId;
var post_id;
var formMethod;
var validator;

$('#phone_code').select2({ placeholder: "Please select Phone code"});
$('#country_id').select2({ placeholder: "Please select country", allowClear: true });
$('#state_id').select2({ placeholder: "Please select state", allowClear: true });
$('#district_id').select2({ placeholder: "Please select district", allowClear: true });

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
var get_states_by_country=get_states_by_country;
$(document).on('change', '#country_id', function () {
  $.ajax({ type: 'POST', url:get_states_by_country, data:{'country_id':this.value }, dataType: 'json',
    success: function(data) {
      var selectTerms = '<option value="">Please select state</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });
      var select = $('#state_id');
      select.empty().append(selectTerms);
      $('#district_id').empty().trigger("change");
    }
  });
});
var get_districts_by_state=get_districts_by_state;
$(document).on('change', '#state_id', function () {
  $.ajax({ type: 'POST', url:get_districts_by_state, data:{'state_id':this.value }, dataType: 'json',
    success: function(data) {
      var selectTerms = '<option value="">Please select district</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });
      var select = $('#district_id');
      select.empty().append(selectTerms);
    }
  });
});
var customerEmail=customerEmail;
// Form Validation with Ajax Submit
if ($("#" + pageTitle + "Form").length > 0) {
  validator = $("#" + pageTitle + "Form").validate({ 
    ignore: ".ignore-validation",
    rules: {
      name: {
        // required: true,
        maxlength: 200,
        lettersonly: true,
      },
     
      phone_code: {
        // required: true,
      },
      // email: {
      //   // email: true,
      //   emailFormat:true,
      //   remote: { url:customerEmail, type: "POST",
      //     data: {
      //       user_id: function () {
      //         return $('#customer_id').val();
      //       }
      //     }
      //   },
      // },
    },
    messages: { 
      name: {
        required: "Please enter Customer Name",
        maxlength: "Length cannot be more than 200 characters",
      },
      phone_code: {
        // required: "Please select Phone code",
      },
      mobile: {
        // required: "Please enter Mobile Number",
        maxlength: "Length cannot be more than 15 numbers",
        minlength: "Length must be 3 numbers",
        digits: "Please enter a valid Mobile Number",
      },
      email: {
        // required: "Please enter Customer E-mail",
        email: "Please enter a valid E-mail address",
        remote: "E-mail already existing"
      },
    },
    submitHandler: function (form) {
      disableBtn("submit-btn");
      customerId    = $("#customer_id").val();
      post_id       = "" == customerId ? "" : "/" + customerId;
      formMethod    = "" == customerId ? "POST" : "PUT";
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

jQuery.validator.addMethod("mobileFormat", function (value, element) {
  return this.optional(element) || /^([0-9\s\-\+\(\)]*)$/igm.test(value);
}, "Please enter a valid mobile number"); 

$('#reset-btn').click(function() {
  validator.resetForm();
  $('#' + pageTitle + 'Form').find("input[type=text], textarea, radio").val("");
  $("#male").prop("checked", true);
});


function importBrowseModal() {
  $("#import-browse-modal").modal("open");
}

if ($("#importCustomerForm").length > 0) {
  var validator = $("#importCustomerForm").validate({ 
    rules: {
      file: {
        required: true,
        extension: "csv"
      }
    },
    messages: { 
      file: {
        required: "Please select a file.",
        extension: "Please upload a file with .csv extension.",
      }
    },
    submitHandler: function (form) {
      $('#import-submit-btn').html('Please Wait...');
      $("#import-submit-btn"). attr("disabled", true);
      form.submit();
    },
    errorPlacement: function(error, element) {
      if (element.is("file")) {
        error.insertAfter(element.next('.errorDiv'));
      }else {
        error.insertAfter(element);
      }
    }
  })
}

// DataTable Initialization
var columns;
var formValue;
var table     = $('#data-table-customers');
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
$(".validity_class").hide();
$("#in_store_credit_yes").click(function(){
  $("#in_store_credit").show();
  $("#validity_from").show();
  $("#validity_to").show();
  $("#credit_lable").show();
  $(".instoreLable").show();
  $("#gst_tax").show();
  $(".validity_class").show();
})



// Check if "No" radio button is checked
if ($("#in_store_credit_no").is(":checked")) {
  $("#in_store_credit").hide();
  $("#credit_lable").hide();
  $("#validity_from").hide();
  $("#validity_to").hide();
  $(".instoreLable").hide();
  $("#gst_tax").hide();
  $(".validity_class").hide();
} else {
  console.log("No radio button is not checked");
}
$("#in_store_credit_no").click(function () {
  $("#in_store_credit").hide();
  $("#credit_lable").hide();
  $("#gst_tax").hide();
  $("#validity_from").hide();
  $("#validity_to").hide();
  var customerId = $('#in_store_credit').data('customer-id');
  $.ajax({
      url: pageRoute + '/in-store-credit/',
      method: "POST",
      dataType: "json",
      data: {
          customer_id: customerId
      },
      success: function (response) {
        console.log(response);
      },
      error: function (xhr, status, error) {
      }
  });
});
$('input[name="validity_from"]').daterangepicker(
  {
      singleDatePicker: true,
      startDate: new Date(),
      showDropdowns: true,
      autoApply: true,
      timePicker: true,
      locale: { format: "DD-MM-YYYY HH:mm:ss" },
  },
  function (ev, picker) {
      // This function can be used to handle the selected date
  }
);
$('input[name="validity_to"]').daterangepicker(
  {
    singleDatePicker: true,
    showDropdowns: true,
    autoApply: true,
    timePicker: true,
    autoUpdateInput: true,
    timePicker24Hour: timePicker,
    locale: { format: "DD-MM-YYYY " },
  },
  function (ev, picker) {
      // console.log(picker.format('DD-MM-YYYY'));
  }
);


