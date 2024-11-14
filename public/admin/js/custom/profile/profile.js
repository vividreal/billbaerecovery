"use strict";

var pageTitle 	    = $("#pageTitle").val();
var pageRoute 	    = $("#pageRoute").val();
var passwordRoute 	= $("#passwordRoute").val();
var photoRoute 	    = $("#photoRoute").val();
var userId          = $('#user_id').val();
var table;
var post_id;
var PageForm;
var formMethod;
var validator;
var projectId;

let canvas;


$('#sortBy').select2({ placeholder: "Sort By", allowClear: true});

$(document).on('change', '#phone_code', function () {
  if (this.value != 101) {
    $("#profile-submit-btn").prop('disabled', true);
    showErrorToaster("Currently not supported in your selected country!");
    $(".print-error-msg").show();
  } else {
    $("#profile-submit-btn").prop('disabled', false);
    $(".print-error-msg").hide();
  }
});

if ($("#profileForm").length > 0) {
  var is_unique_email=is_unique_email;
  var profileValidator = $("#profileForm").validate({ 
    rules: {
      name: {
        required: true,
        maxlength: 200,
      }, 
      mobile: {
        required: true,
        minlength: 3,
        maxlength: 15,
        digits:true
      },
      email: {
        required: true,
        email: true,
        emailFormat:true,
        remote: { url: is_unique_email, type: "POST",
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
        digits: "Please enter a valid Mobile number",
        required: "Please enter Mobile",
        maxlength: "Length cannot be more than 15 characters",
      },
      email: {
        required: "Please enter Store E-mail",
        email: "Please enter a valid E-mail address.",
        remote: "E-mail already existing"
      },
    },
    submitHandler: function (form) {
      disableBtn('profile-submit-btn');
      var forms     = $("#profileForm");
      post_id       = "" == userId ? "" : "/" + userId;

      $.ajax({ url: pageRoute + post_id, type: "PUT", processData: false, data: forms.serialize(), 
      }).done(function (data) {
        enableBtn('profile-submit-btn');
        if (data.flagError == false) {
          showSuccessToaster(data.message);
          setTimeout(function () { 
            location.reload();                   
          }, 1000);
        } else {
          showErrorToaster(data.message);
          printErrorMsg(data.error);
          $('#profile-reset-btn').click();
        }
      });
    }
  })
}

$('#profile-reset-btn').click(function() {
  profileValidator.resetForm();
  $('#profileForm').find("input[type=text], textarea, hidden").val("");
  $('input').removeClass('error');
  $("#profileForm label").removeClass("error");
  // $("#profileForm .label-placeholder").addClass('active');
});

jQuery.validator.addMethod("emailFormat", function (value, element) {
  return this.optional(element) || /[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/igm.test(value);
}, "Please enter a valid email address"); 

jQuery.validator.addMethod("mobileFormat", function (value, element) {
  return this.optional(element) || /^([0-9\s\-\+\(\)]*)$/igm.test(value);
}, "Please enter a valid mobile number");  


// Password Form
if ($("#passwordForm").length > 0) {
  var passwordValidator = $("#passwordForm").validate({ 
    rules: {
      old_password: {
        required: true,
      },
      new_password: {
        required: true,
       
      },
      new_password_confirmation: {
        equalTo: "#new_password"
      },
    },
    messages: { 
      old_password: {
        required: "Please enter Password",
      },
      new_password: {
        required: "Please enter password",
       
      },
      new_password_confirmation: {
        equalTo: "Passwords are not matching",
      }
    },
    submitHandler: function (form) {
      disableBtn('password-submit-btn');
      var forms   = $("#passwordForm");

      $.ajax({ url: passwordRoute, type: 'POST', data: forms.serialize(),
      }).done(function (data) {
          $('#password-reset-btn').click();
          enableBtn('password-submit-btn');
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

$('#password-reset-btn').click(function() {
  passwordValidator.resetForm();
  $('#passwordForm').find("input[type=password]").val("");
  $("#passwordForm label").removeClass("error");
  $("#passwordForm .label-placeholder").addClass('active');
});

$("#profileImageSubmitBtn").click(function () {
    canvas = cropper.getCroppedCanvas({
        viewport: {
            width: 100,
            height: 100,
            type: 'circle'
        },
    });
    canvas.toBlob(function (blob) {
        disableBtn('profileImageSubmitBtn');
        var url = URL.createObjectURL(blob);
        var reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function () {
            var base64data = reader.result;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: photoRoute,
                data: { 'image': base64data },
                success: function (data) {
                    enableBtn('profileImageSubmitBtn');
                    if (data.flagError == false) {
                        showSuccessToaster(data.message);
                        $("#user_profile").attr("src", data.logo);
                        $("#log_user_icon").attr("src", data.logo);
                        $modal.modal('close');
                    } else {
                        showErrorToaster(data.message);
                        if (data.errors) {
                            printErrorMsg(data.errors);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    enableBtn('profileImageSubmitBtn');
                    showErrorToaster("An error occurred while processing your request.");
                }
            });
        }
    });
});

function printErrorMsg(errors) {
    var errorMsg = "<ul>";
    $.each(errors, function (key, value) {
        errorMsg += "<li>" + value[0] + "</li>";
    });
    errorMsg += "</ul>";
    $(".error-msg").html(errorMsg);
}