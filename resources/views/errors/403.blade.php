
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <base href="{{ url('/') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link rel="shortcut icon" href="{{ asset('admin/images/favicon.ico') }}"/>
    <link rel="apple-touch-icon" href="{{asset('admin/images/favicon/apple-touch-icon-152x152.png')}}">

    <!-- <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> -->

    <title>{{ config('app.name') }}
    @if(!Request::is('/'))
     | @yield('seo_title', '')
    @endif
    </title>
    <meta name="description" content="@yield('seo_keyword', '')">
    <meta name="keyword" content="@yield('seo_description', '')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/page-404.css')}}">
    @include('layouts.general_css')
    

</head>
<body class="vertical-layout vertical-menu-collapsible page-header-dark vertical-modern-menu 1-column bg-full-screen-image blank-page blank-page" data-open="click" data-menu="vertical-modern-menu" data-col="1-column">
    <div class="row">
      <div class="col s12">
        <div class="container"><div class="section section-404 p-0 m-0 height-100vh">
  <div class="row">
    <!-- 404 -->
    <div class="col s12 center-align white">
      <img src="{{asset('admin/images/gallery/error-2.png')}}" class="bg-image-404" alt="">
      <h1 class="error-code m-0">403</h1>
      <h6 class="mb-2">Warning! User don't have the right access to this page</h6>
      <a class="btn waves-effect waves-light gradient-45deg-deep-purple-blue gradient-shadow mb-4" href="{{ url()->previous() }}">Back</a>
    </div>
  </div>
</div>
        </div>
        <div class="content-overlay"></div>
      </div>
    </div>
</body>
</html>



