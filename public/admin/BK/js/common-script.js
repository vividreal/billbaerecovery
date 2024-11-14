function printErrorMsg (msg) {
    $(".print-error-msg").find("ul").html('');
    $.each( msg, function( key, value ) {
        $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
    });
    $(".print-error-msg").delay(1000).addClass("in").toggle(true).fadeOut(5000);
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

