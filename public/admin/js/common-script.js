function printErrorMsg (msg) {
    $(".print-error-msg").find("ul").html('');
    $.each( msg, function( key, value ) {
        $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
    });
    $(".print-error-msg").show();
    // $(".print-error-msg").delay(1000).addClass("in").toggle(true).fadeOut(5000);
}

function showSuccessMsg (msg) {
    $(".print-success-msg").html(msg);
    $(".print-success-msg").delay(1000).addClass("in").toggle(true).fadeOut(3000);
}

function showSuccessToaster (msg) {
    toastr.success(msg)
}

function showErrorToaster (msg) {
    toastr.error(msg)      
}

$(".check_numeric").keydown(function (event) {
    if ((event.keyCode >= 48 && event.keyCode <= 57) || 
    (event.keyCode >= 96 && event.keyCode <= 105) || 
    event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
    event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) 
    {

    }
    else
    {
    event.preventDefault();
    }

});

function getChildElements(parent_id = null, selected = null, element = null, route = null){
    $.ajax({
        type: 'GET',
        url: route, data:{'parent_id': parent_id },
        dataType: 'json',
        delay: 250,
        success: function(data) {
            var selectTerms = '<option value="">Please select </option>';
            $.each(data.data, function(key, value) {
              selectTerms += '<option value="' + value.id + '" >' + value.name + '</option>';
            });
            var select = $('#'+element);
            select.empty().append(selectTerms);
        }
    });
}

// $(".print-success-msg").delay(1000).addClass("in").toggle(true).fadeOut(5000);
// $(".print-error-msg").delay(1000).addClass("in").toggle(true).fadeOut(5000);

$(".card-alert .close").click(function () {
    $(this).closest(".card-alert").fadeOut("slow");
});

function disableBtn(element) {
    $('#'+element).html('Please Wait... <i class="material-icons right">loop</i>');
    $('#'+element).attr("disabled", true);
}

function enableBtn(element) {
    $('#'+element).html('Submit <i class="material-icons right">keyboard_arrow_right</i>');
    $('#'+element).attr("disabled", false);
}



// function clearForm() {
//     validator.resetForm();
//     $('input').removeClass('error');
//     $("#manageScheduleForm .form-control").removeClass("error");
//     $('select').removeClass('error');
//     $('#manageScheduleForm').trigger("reset");
//     $('#manageScheduleForm').find("input[type=text], textarea, hidden").val("");
//     $('#service_type').select2({ placeholder: "Please select type"});
//     $('#services').select2({ placeholder: "Please select service", allowClear: true });
//     $('#packages').select2({ placeholder: "Please select package" });
//     $("input[name='item_ids[]']").remove();
//     $("input.disabled").attr("disabled", false);
//     $('#manageScheduleForm').find("input[type=hidden]").val("");
//     $('#itemDetails').html();
//     $("#cancelSchedule").hide();
//     $('#itemDetailsDiv').hide();
//     $(".form-action-btn").show();
// }