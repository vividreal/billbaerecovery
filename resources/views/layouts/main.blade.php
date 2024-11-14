<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login | Billing App</title>
<meta name="description" content="">
<meta name="author" content="">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="format-dctection" content="telephone=no">
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="{{ asset('/assets/css/vendor/bootstrap.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('/assets/css/vendor/datepicker.css') }}"/> 
<link rel="stylesheet" href="{{ asset('/assets/css/vendor/selectBox.css') }}"/> 
<link rel="stylesheet" href="{{ asset('/assets/css/vendor/meanmenu.css') }}"/> 
<link rel="stylesheet" href="{{ asset('/assets/css/vendor/datatables.min.css') }}"/> 
<link rel="stylesheet" href="{{ asset('/assets/css/vendor/font-awesome.css') }}"/> 
<link rel="stylesheet" href="{{ asset('/assets/css/style.css') }}"/> 
<link rel="stylesheet" href="{{ asset('/assets/css/layout.css') }}"/> 
    
</head>
<body class="sign-up">

<div class="main-outercon">
    <!--Begin Content section-->
    <section class="section-container d-flex align-items-center">
        <div class="container"> 
          @yield('content')
        </div>
    </section>
    <!--End Content section-->
</div>   
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="{{ asset('/assets/js/vendor/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/vendor/bootstrap.min.js') }}"></script> 
    <script src="{{ asset('/assets/js/vendor/icheck.min.js') }}"></script> 
    <script src="{{ asset('/assets/js/vendor/datepicker.js') }}"></script> 
    <script src="{{ asset('/assets/js/vendor/selectBox.js') }}"></script> 
    <script src="{{ asset('/assets/js/vendor/meanmenu.min.js') }}"></script> 
    <script src="{{ asset('/assets/js/vendor/datatables.min.js') }}"></script> 
       <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    <script src="{{ asset('/assets/js/main.js') }}"></script>
    <script>
        jQuery('#sign_in_button').click(function(){
      jQuery('#failure_msg ul li').html(' '); 
  }); 
    </script>
  </body>
</html>