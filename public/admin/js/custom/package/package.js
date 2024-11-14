"use strict";
var currentURL = window.location.href;

var pageTitle 	= $("#pageTitle").val();
var pageRoute 	= $("#pageRoute").val();
var currency    = $("#currency").val();
var timePicker = $("#timePicker").val();
var timeFormat = $("#timeFormat").val();
var table;
var packageId;
var post_id;
var formMethod;
var validator;


$('#additional_tax').select2({ placeholder: "Please select Additional Tax", allowClear: true });
$('#gst_tax').select2({ placeholder: "Please select GST Tax %", allowClear: true });

$("#services").select2({ placeholder: "Please select services", allowClear: false }).on('select2:select select2:unselect', function (e) { 
  loadServices() 
  $(this).valid()
});

$("#price").change(function() {
  calculateDiscount();
});

function loadServices() {
  var service_ids = $('#services').val();
  var url=getServices;
  // '/common/get-services'
  if(service_ids != ''){
    $.ajax({ type: 'post', url: url, dataType: 'json', data: { data_ids:service_ids}, delay: 250,
      success: function(data) {
        if(data.data.length > 0) {
          var html = '';
          var cgst='';
          var sgst='';
          var serviceValue;
          $("#servicesTable").find("tr:gt(0)").remove();
          $.each(data.data, function(key, value) {
            if(value.tax_included==0){
              if(value.gsttax!==null){
                var totalGstPercetage=value.gsttax.percentage;
              }else{
                var totalGstPercetage=18;
              }
               serviceValue=value.price;
               cgst=sgst= (serviceValue *(totalGstPercetage/2)/100);
               serviceValue+=cgst+sgst;
               serviceValue=serviceValue.toFixed(2);              
            }else{
              serviceValue=value.price;
            }
            html += '<tr><td>' + value.name + '(' + (value.tax_included=== 1? 'Tax Included:'+value.price : 'Tax Excluded:'+value.price) + ')' + '</td><td>' + value.hours.name + '</td><td>' + currency + ' ' + serviceValue + '</td></tr>';
          });
          $('#servicesTable').append('<tfoot style="font-size: large;"><tr><td></td><td><strong>Total</strong></td><td><b>' + data.totalPrice + '</b></td></tr></tfoot>');
          $('#totalPrice').val(data.totalPrice);
          $("#price" ).prop( "disabled", false );
          $('#servicesTable').append(html);
          $('#usedServicesDiv').show();
          $('#price_info_message').hide();
          calculateDiscount();
        }
      }
    });
  }else{
    $('#usedServicesDiv').hide();
    $('#totalPrice').val('');
    $('#price').val('');
    $('#discount').val('');
    $('#price_info_message').show();
    $( "#price" ).prop( "disabled", true );
  }
}

function calculateDiscount() {
  var total = $('#totalPrice').val();
  var price = $('#price').val();
  if(price != '') {
    var discount = parseFloat(total) - parseFloat(price);
    if (discount < 0) {
      showErrorToaster("Warning! Package price is greater than Total price.");
    } else {
      $('#discount').val(discount);
    }
  }   
}

// Form Validation with Ajax Submit
if ($("#" + pageTitle + "Form").length > 0) {
  validator = $("#" + pageTitle + "Form").validate({ 
    ignore: ".ignore-validation",
    rules: {
      name: {
        required: true,
        maxlength: 250,
      },
      price: {
        required: true,
      },
      "services[]": {
        required: true,
      },
      gst_tax: {
        // required: true,
      }
    },
    messages: { 
      name: {
        required: "Please enter Package Name",
        maxlength: "Length cannot be more than 250 characters",
      },
      price: {
        required: "Please enter Price",
      },
      "services[]": {
        required: "Please choose Services",
      },
      gst_tax: {
        required: "Please select GST Tax",
      }
    },
    submitHandler: function (form) {
      disableBtn("submit-btn");
      packageId     = $("#package_id").val();
      post_id       = "" == packageId ? "" : "/" + packageId;
      formMethod    = "" == packageId ? "POST" : "PUT";
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

$('#reset-btn').click(function() {
  validator.resetForm();
  $('#' + pageTitle + 'Form').find("input[type=text], textarea, radio").val("");
  $("#services").val('').trigger('change');
  $("#validity_mode").val('').trigger('change');
  $("#validity").val('').trigger('change');
  $("#gst_tax").val('').trigger('change');
  $("#additional_tax").val('').trigger('change');
  $('#tax_included').prop('checked', false);
  $('#usedServicesDiv').hide();
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

$('.view-link').click(function() {
  var detailId = $(this).attr('id');
  showMessage(detailId);
});

function showMessage(id) {
 
  var newRoute = currentURL.replace('/packages', '');
 // var get_package_services=get_package_services;
  $.ajax({
    type: 'GET', 
    url: newRoute+'/common/get-package-services/'+ id, 
    success: function(response) {     
      var cgst='';
      var sgst='';
      var serviceValue;  
      var currencySymbol = '₹'; 
      var discount=response.package.service_price - response.package.price;
      var packageDiv=$('<div>').addClass('package-info');
      packageDiv.append('<h3>' + response.package.name + '</h3>');
      packageDiv.append('<p>Package Offer Price: ' + currencySymbol+response.package.price + '</p>');
      packageDiv.append('<p>Package Price: ' + currencySymbol+response.package.service_price + '</p>');
      packageDiv.append('<p>Package Discount:' + currencySymbol+discount+ '</p>');
      var serviceTable = $('<table>');
      serviceTable.append('<thead>'+'<th>Service'+'</th>'+'<th>Time'+'</th>'+'<th>Price'+'</th>'+'<th>Service Value'+'</th>'+'<th>Tax'+'</th>');
      serviceTable.append('</thead>');

      $.each(response.services, function(index, element) {
        var row = $('<tr>');
        if(element.services.gsttax!==null){
          var totalGstPercetage=element.services.gsttax.percentage;
        }else{
          var totalGstPercetage=18;
        }
        if(element.services.tax_included== 0){
         
           serviceValue=element.services.price;
           cgst=sgst=(serviceValue *(totalGstPercetage/2)/100);
           serviceValue+=cgst+sgst;
           serviceValue=serviceValue.toFixed(2);
          
        }else{
          serviceValue =element.services.price / (1+(totalGstPercetage/100));
          serviceValue=serviceValue.toFixed(2);

        }
    row.append('<td>' + element.services.name + '</td>');
    row.append('<td>' + element.services.hours.name + '</td>');
    row.append('<td>' + currencySymbol + element.services.price + '</td>');
    row.append('<td>' + currencySymbol + serviceValue + '</td>');
    row.append('<td>' + (element.services.tax_included === 1 ? 'Tax Included' : 'Tax Excluded') + '</td>');
    serviceTable.append(row);
    var serviceDiv = $('<div>').addClass('service-info');
    serviceDiv.append(serviceTable);
    packageDiv.append(serviceDiv);

    // Append the packageDiv to the fullMessage container
    $('#fullMessage').append(packageDiv);
      });
    } 
});
$('#full-message-modal').modal('open');
}
$('input[name="validity_from"]').daterangepicker(
  {
      singleDatePicker: true,
      startDate: new Date(),
      showDropdowns: true,
      autoApply: true,
      timePicker: true,
      timePicker24Hour: timePicker,
      locale: { format: "DD-MM-YYYY " },
  },
  function (ev, picker) {
      // console.log(picker.format('DD-MM-YYYY'));
  }
);
$('input[name="validity_to"]').daterangepicker(
  {
      singleDatePicker: true,
      startDate: new Date(),
      showDropdowns: true,
      autoApply: true,
      timePicker: true,
      // timePicker24Hour: timePicker,
      locale: { format: "DD-MM-YYYY " },
  },
  function (ev, picker) {
      // console.log(picker.format('DD-MM-YYYY'));
  }
);