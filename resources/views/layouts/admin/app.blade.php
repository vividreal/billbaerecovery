<!DOCTYPE html>
@php
  use App\Helpers\Helper;
  $configData = Helper::applClasses();

  if(auth()->user()->is_admin != 1) {
    // Store theme settings
    $themeSettings = Helper::getThemeSettings(); 
  }

@endphp
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <base href="{{ url('/') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link rel="shortcut icon" href="{{ asset('admin/images/favicon.ico') }}"/>
    <link rel="apple-touch-icon" href="{{asset('admin/images/favicon/apple-touch-icon-152x152.png')}}">

    <!-- <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> -->

    <title>{{ config('app.name') }}</title>
    <meta name="description" content="@yield('seo_keyword', '')">
    <meta name="keyword" content="@yield('seo_description', '')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    @include('layouts.general_css')
    

</head>
<body class="vertical-layout vertical-menu-collapsible page-header-dark vertical-modern-menu preload-transitions 2-columns " 
      data-open="click" 
      data-menu="vertical-modern-menu" 
      data-col="2-columns">

      @include('layouts.admin.header')
 

      @include('layouts.nav')  

    <div id="main" class="">
      <div class="row">

        <div class="content-wrapper-before 
        @if(!empty($themeSettings) && $themeSettings->navbarBgColor != '') {{$themeSettings->navbarBgColor}} @else {{$configData['navbarLargeColor']}} @endif "></div>
            @include('layouts.breadcrumbs')
            <div class="col s12">
              <div class="container">

                  @yield('content')

              </div>
              <div class="content-overlay"></div>
            </div>

      </div>
  </div>
  <!-- END: Page Main-->
  <!-- Theme Customizer -->
  @include('layouts.customizer')
  <!--/ Theme Customizer -->
  

    @include('layouts.footer')
    @include('layouts.general_js')

    <!-- <script src="{{ asset('js/ajax-crud.js') }}"></script> -->
    @stack('page-scripts')


</body>
</html>
