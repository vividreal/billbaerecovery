  var $modal = $('#profileCropModal');
  var image = document.getElementById('image');
  var cropper;
  $("body").on("change", ".image", function(e){

    var files = e.target.files;

    var done = function (url) {

      var ext =files[0].name.split('.').pop().toLowerCase();
      if($.inArray(ext, ['png','jpg','jpeg']) == -1) {
        showErrorToaster('Invalid extension!');
        return false;
      }

      image.src = url;
      $modal.modal('open');
    };
    var reader;
    var file;
    var url;
 
    if (files && files.length > 0) {
      file = files[0];
 
      if (URL) {
        done(URL.createObjectURL(file));
      } else if (FileReader) {
        reader = new FileReader();
        reader.onload = function (e) {
          done(reader.result);
        };
        reader.readAsDataURL(file);
      }
    }
  });

  $modal.modal({
      dismissible: true,
      onOpenEnd: function(modal, trigger) { 
        $('input[type="file"]').val('');
        cropper = new Cropper(image, {
          aspectRatio: 15 / 15,
        // viewMode: 3,
        // preview: '.preview',
        // autoCropArea:100,
      });
      },
      onCloseEnd: function() { 
        $('input[type="file"]').val('');
        cropper.destroy();
        cropper = null;
      } 
    }
  );

  $("#select-files").on("click", function () {
    $("#profile").click();
  })