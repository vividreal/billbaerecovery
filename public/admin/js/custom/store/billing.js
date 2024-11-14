"use strict";

var pageTitle 	      = $("#pageTitle").val();
var pageRoute 	      = $("#pageRoute").val();
var billing_id 	      = $("#billing_id").val();
var post_id;
var PageForm;
var formMethod;
var validator;
let canvas;

$('#gst_percentage').select2({ placeholder: "Please select default GST Percentage", allowClear: true });
$('#billing_country_id').select2({ placeholder: "Please select Country", allowClear: true });
$('#billing_state_id').select2({ placeholder: "Please select State", allowClear: true });
$('#billing_district_id').select2({ placeholder: "Please select District", allowClear: true });
$('#currency').select2({ placeholder: "Please select Currency", allowClear: true });

$(document).on('change', '#billing_country_id', function () {
  if(this.value != 101) {
    $("#store-billing-submit-btn").prop('disabled', true);
    showErrorToaster("Currently not supported in your selected country!");
    $(".print-error-msg").show();
  } else {
    $("#store-billing-submit-btn").prop('disabled', false);
    $(".print-error-msg").hide();
    $.ajax({ type: 'POST', url: getStatesByCountry, data:{'country_id':this.value }, dataType: 'json',
      success: function(data) {
        var selectTerms = '<option value="">Please select state</option>';
        $.each(data.data, function(key, value) {
          selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
        });
        var select = $('#billing_state_id');
        select.empty().append(selectTerms);
        $('#billing_district_id').empty().trigger("change");
      }
    });
    $.ajax({ type: 'POST', url:getCurrencies, data:{'country_id':this.value }, dataType: 'json',
      success: function(data) {
        var selectTerms = '<option value="">Please select currency</option>';
        $.each(data.data, function(key, value) {
          selectTerms += '<option value="' + value.id + '" >' + value.symbol + '</option>';
        });
        var select = $('#currency');
        select.empty().append(selectTerms);
      }
    });
  }      
});

$(document).on('change', '#billing_state_id', function () {
  $.ajax({ type: 'POST', url:get_districts_by_state, data:{'state_id':this.value }, dataType: 'json',
    success: function(data) {
      var selectTerms = '<option value="">Please select district</option>';
      $.each(data.data, function(key, value) {
        selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
      });
      var select = $('#billing_district_id');
      select.empty().append(selectTerms);
    }
  });
});

if ($("#billingForm").length > 0) {
  var billingValidator = $("#billingForm").validate({ 
      rules: {
        company_name: { maxlength: 250, required: true, },
        billing_country_id: { required: true, },
        billing_state_id: { required: true, },
        billing_district_id: { required: true, },
        currency: { required: true, },
      },
      messages: { 
        company_name: { maxlength: "Length cannot be more than 250 characters", required: "Please enter Company name", },
        billing_country_id: { required: "Please select Country", },
        billing_state_id: { required: "Please select State", },
        billing_district_id: { required: "Please select District", },
        currency: { required: "Please select Currency", },
      },
      submitHandler: function (form) {
          disableBtn('billing-submit-btn');
          post_id      = "" == billing_id ? "" : "/" + billing_id;
          var forms   = $("#billingForm");
          $.ajax({ url: pageRoute + post_id, type: "PUT", processData: false, data: forms.serialize(),
          }).done(function (data) {
            enableBtn('billing-submit-btn');
            if (data.flagError == false) {
              showSuccessToaster(data.message);
              setTimeout(function () { 
                location.reload();                    
              }, 1000);
            } else {
              showErrorToaster(data.message);
              printErrorMsg(data.error);
            }
          });
      }
  })
}

$('#billing-reset-btn').click(function() {
  billingValidator.resetForm();
  $('#billingForm').find("input[type=text], textarea").val("");
  $('input').removeClass('error');
  $("label").removeClass("error");
});

// GST Form
var gst_billing_id;
var GSTRoute 	      = $("#GSTRoute").val();

if ($("#storeGSTForm").length > 0) {
  var gstValidator = $("#storeGSTForm").validate({ 
    rules: {
      gst: {
        // required: true,
      },
      gst_percentage: {
        // required: true,
      }
    },
    messages: { 
      gst_percentage: {
        required: "Please select store default GST",
      },
      gst: {
        required: "Please enter store GST number",
      }
    },
    submitHandler: function (form) {
      disableBtn("gst-submit-btn");
      // gst_billing_id  = $("#gst_billing_id").val();
      var forms       = $("#storeGSTForm");
      $.ajax({ url: GSTRoute, type: 'POST', processData: false, data: forms.serialize(),
      }).done(function (data) {
        if (data.flagError == false) {
          showSuccessToaster(data.message);
          enableBtn("gst-submit-btn");
        } else {
          showErrorToaster(data.message);
          printErrorMsg(data.error);
        }
      });
    }
  })
}

$("#gst-reset-btn").click(function() {
  gstValidator.resetForm();
  $('#storeGSTForm').find("input[type=text]").val("");
  $('input').removeClass('error');
  $("#storeGSTForm label").removeClass("error");
  $("#gst_percentage").val('').trigger('change')
});


// Additional Tax
var additionalTaxId;
var additionalTaxForm;
var additionalTaxPost_id;
var taxTable;
var additionalTaxRoute = $("#additionalTaxRoute").val();

// DataTable Initialization
var columns;
var formValue;
var taxTable    = $('#data-table-taxes');
var url         = taxTable.data('url');
var form        = taxTable.data('form');
var length      = taxTable.data('length');
columns   = [];
formValue = [];

taxTable.find('thead th').each(function () {
  var column = {'data': $(this).data('column')};
  columns.push(column);
});

taxTable.DataTable({
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
 
function manageAdditionalTax(additionalTax_id){
  $('#additionalTax-reset-btn').click();
  if (additionalTax_id === null) {
    $("#additionalTax_id").val('');
    $("#additionalTaxFields .label-placeholder").show();
    $("#additionalTax-modal").modal("open");
  } else {
    $.ajax({url: additionalTaxRoute  + "/" + additionalTax_id + "/edit", type: "GET", dataType: "html"})
    .done(function (a) {
      var data = JSON.parse(a);
      if (data.flagError == false) {
        $("#additionalTax_id").val(data.data.id);
        $("#additionalTaxForm input[name=name]").val(data.data.name);
        $("#additionalTaxForm input[name=percentage]").val(data.data.percentage);
        $("#information").val(data.data.information);
        $("#additionalTaxFields .label-placeholder").hide();
        $("#additionalTax-modal").modal("open");
      }
    }).fail(function () {
      printErrorMsg("Please try again...", "error");
    });
  }
}

if ($("#additionalTaxForm").length > 0) {
  var additionalTaxValidator = $("#additionalTaxForm").validate({ 
    rules: {
      name: { required: true, maxlength: 100, },
      percentage: { required: true, },
      information: { maxlength: 250, }
    },
    messages: { 
      name: { required: "Please enter Additional tax name", maxlength: "Length cannot be more than 100 characters", },
      percentage: { required: "Please enter Tax percentage", },
      information: { maxlength: "Length cannot be more than 250 characters", }
    },
    submitHandler: function (form) {
      additionalTaxId       = $("#additionalTax_id").val();
      additionalTaxPost_id  = "" == additionalTaxId ? "" : "/" + additionalTaxId;
      formMethod            = "" == additionalTaxId ? "POST" : "PUT";
      additionalTaxForm     = $("#additionalTaxForm");

      $.ajax({ url: additionalTaxRoute + additionalTaxPost_id, type: formMethod, processData: false, data: additionalTaxForm.serialize()
      }).done(function (data) {
        if (data.flagError == false) {
          showSuccessToaster(data.message);    
          $('#additionalTax-reset-btn').click();            
          $("#additionalTax-modal").modal("close");
          setTimeout(function () {
            taxTable.DataTable().draw();
          }, 2000);
        } else {
          showErrorToaster(data.message);
          printErrorMsg(data.error);
        }
      });
    }
  })
}

taxTable.on('click', '.delete-item', function() {
  var postUrl = $(this).attr('data-url'); 
  var id      = $(this).attr('data-id');
  var title   = $(this).attr('data-title');
  swal({ title: "Are you sure?",icon: 'warning', dangerMode: true, buttons: {cancel: 'No, Please!', delete: 'Yes, Delete'}
  }).then(function (willDelete) {
    if (willDelete) {
      $.ajax({url: additionalTaxRoute + "/" + id, type: "DELETE", dataType: "html"
      }).done(function (a) {
        var data = JSON.parse(a);
        if (data.flagError == false) {
          showSuccessToaster(data.message);          
          setTimeout(function () {
            taxTable.DataTable().draw();
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

$('#additionalTax-reset-btn').click(function() {
  additionalTaxValidator.resetForm();
  $('#additionalTaxForm').find("input[type=text], textarea").val("");
  $('input').removeClass('error');
  $("label").removeClass("error");
});

























