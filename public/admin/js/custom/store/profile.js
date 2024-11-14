"use strict";

var pageTitle 	      = $("#pageTitle").val();
var pageRoute 	      = $("#pageRoute").val();
var logoStoreRoute    = $("#logoStoreRoute").val();
var logoDeleteRoute   = $("#logoDeleteRoute").val();
var store_id 	        = $("#store_id").val();
var post_id;
var PageForm;
var formMethod;
var validator;
let canvas;

$('#country_id').select2({ placeholder: "Please select Country", allowClear: true });
$('#state_id').select2({ placeholder: "Please select State", allowClear: true });
$('#district_id').select2({ placeholder: "Please select District", allowClear: true });
$('#timezone').select2({ placeholder: "Please select Timezone", allowClear: true });


$(document).on('change', '#phone_code', function () {
  if (this.value != 101) {
    $("#store-profile-submit-btn").prop('disabled', true);
    showErrorToaster("Currently not supported in your selected country!");
  } else {
    $("#store-profile-submit-btn").prop('disabled', false);
  }
});


$(document).on('change', '#country_id', function () {
  if (this.value != 101) {
    $("#store-profile-submit-btn").prop('disabled', true);
    showErrorToaster("Currently not supported in your selected country!");
    $(".print-error-msg").show();
  } else {
    $("#store-profile-submit-btn").prop('disabled', false);
    $(".print-error-msg").hide();
    $.ajax({ type: 'POST', url: getStatesByCountry, data:{'country_id':this.value }, dataType: 'json',
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

    $.ajax({ type: 'POST', url:getTimezone, data:{'country_id':this.value }, dataType: 'json',
      success: function(data) {
        var selectTerms = '<option value="">Please select timezone</option>';
        $.each(data.data, function(key, value) {
          selectTerms += '<option value="' + value.zone_name + '" >' + value.zone_name + '</option>';
        });
        var select = $('#timezone');
        select.empty().append(selectTerms);
      }
    });
  }
});

$(document).on('change', '#state_id', function () {
  $.ajax({ type: 'POST', url: get_districts_by_state, data:{'state_id':this.value }, dataType: 'json',
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

if ($("#profileForm").length > 0) {
  var validator = $("#profileForm").validate({ 
    rules: {
      name: {
        required: true,
        maxlength: 200,
      }, 
      country_id: {
        required: true,
      },
      timezone: {
        required: true,
      },
      contact: {
        required: true,
        minlength: 3,
        maxlength: 15,
        digits:true
      },
      email: {
        email: true,
        remote: { url:is_unique_store_email, type: "POST",
          data: {
            store_id: function () {
              return $('#store_id').val();
            }
          }
        },
      },
    },
    messages: { 
      name: {
        required: "Please enter store name",
        maxlength: "Length cannot be more than 200 characters",
      },
      country_id: {        
        required: "Please choose country",
      },
      timezone: {
        required: "Please choose timezone",
      },
      contact: {
        digits: "Please enter a valid mobile number",
        required: "Please enter mobile",
        maxlength: "Length cannot be more than 15 characters",
      },
      email: {
        email: "Please enter a valid email address.",
        remote: "Email already existing"
      },
    },
    submitHandler: function (form) {
      disableBtn('profile-submit-btn');
      post_id      = "" == store_id ? "" : "/" + store_id;
      var forms   = $("#profileForm");
      $.ajax({ url: pageRoute + post_id, type: "PUT", processData: false, data: forms.serialize(),
      }).done(function (data) {
        enableBtn('profile-submit-btn');
        if(data.flagError == false) {
          showSuccessToaster(data.message);
          setTimeout(function () { 
            location.reload();               
          }, 1000);
        } else {
          showErrorToaster(data.message);
          printErrorMsg(data.error);
        }
      });
    },
    errorPlacement: function(error, element) {
      if (element.is("select")) {
        error.insertAfter(element.next('.select2'));
      } else {
        error.insertAfter(element);
      }
    }
  })
} 

$('#profile-reset-btn').click(function() {
  validator.resetForm();
  $('#profileForm').find("input[type=text], textarea").val("");
  $('input').removeClass('error');
  $("label").removeClass("error");
  // $("#timezone").val('').trigger('change');
  // $('#state_id').empty().trigger("change");
  // $('#district_id').empty().trigger("change");
});


$('#profile').change(function() {   
  var ext = $('#profile').val().split('.').pop().toLowerCase();
  if ($.inArray(ext, ['png','jpg','jpeg']) == -1) {
    showErrorToaster("Invalid format. Allowed JPG, JPEG or PNG.");
  } else {
    let reader = new FileReader();
    reader.onload = (e) => { 
      $('#store_logo').attr('src', e.target.result); 
      $(".logo-action-btn").show();
      $(".logo-onload-btn").hide();
    }
    reader.readAsDataURL(this.files[0]); 
  }    
});

$("#removeLogoDisplayBtn").click(function(event) {
  event.preventDefault();
  var old_logo = $("#log_url").val();
  $("#store_logo").attr("src", old_logo); 
  $(".logo-action-btn").hide();
  $(".logo-onload-btn").show();
});

$('#storeLogoForm').submit(function(e) {
  disableBtn("uploadLogoBtn");
  var formData = new FormData(this);
  $.ajax({ url: logoStoreRoute, type: "POST", data: formData, cache:false, contentType: false, processData: false,
    success: function(data) {
      enableBtn("uploadLogoBtn");
      if (data.flagError == false) {
        showSuccessToaster(data.message);  
        $(".logo-action-btn").hide();
        $("#deleteLogoBtn").show();
        $(".logo-onload-btn").show();
        $("#store_logo").attr("src", data.logo);
      } else {
        showErrorToaster(data.message);
        printErrorMsg(data.error);
      }
    }
  });
});

$("#deleteLogoBtn").click(function(event) {
  event.preventDefault();
  swal({ title: "Are you sure?", icon: 'warning', dangerMode: true, buttons: { cancel: 'No, Please!', delete: 'Yes, Delete It' }
  }).then(function (willDelete) {
    if (willDelete) {
      $.ajax({url: logoDeleteRoute, type: "post"}).done(function (data) {
        if (data.flagError == false) {
          showSuccessToaster(data.message);
          $("#deleteLogoBtn").hide();          
          $("#store_logo").attr("src", data.logo);
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


$("#select-files").on("click", function () {
  $("#profile").click();
})

jQuery.validator.addMethod("emailFormat", function (value, element) {
  return this.optional(element) || /[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/igm.test(value);
}, "Please enter a valid email address"); 

jQuery.validator.addMethod("mobileFormat", function (value, element) {
  return this.optional(element) || /^([0-9\s\-\+\(\)]*)$/igm.test(value);
}, "Please enter a valid mobile number");  

