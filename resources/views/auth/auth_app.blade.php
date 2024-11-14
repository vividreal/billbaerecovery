<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <base href="{{ url('/') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link rel="shortcut icon" href="{{ asset('admin/images/favicon.ico') }}"/>
    <link rel="apple-touch-icon" href="{{asset('admin/images/favicon/apple-touch-icon-152x152.png')}}">
    <link rel=canonical href="{{ url('/') }}"/>
    <title>Login | {{ config('app.name') }} </title>
    <meta name="description" content="@yield('seo_keyword', '')">
    <meta name="keyword" content="@yield('seo_description', '')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    @include('layouts.general_css')
    
    <link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/vendors.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/login.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/toastr/toastr.min.css') }}">
    
    @stack('page-css')

</head>
<body class="vertical-layout vertical-menu-collapsible page-header-dark vertical-modern-menu preload-transitions 1-column login-bg blank-page" data-open="click" data-menu="vertical-modern-menu" data-col="1-column">

        <div class="row">
          <div class="col s12">
              <div class="container">
              
              @yield('content')

              </div>
              <div class="content-overlay"></div>


      </div>
  </div>
    <script src="{{asset('admin/js/vendors.min.js')}}"></script>
    <script src="{{asset('admin/vendors/toastr/toastr.min.js')}}"></script>
    <!-- <script src="{{ asset('js/ajax-crud.js') }}"></script> -->
    @stack('page-scripts')


</body>
</html>
