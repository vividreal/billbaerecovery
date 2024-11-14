

@php
$navbarBgColor    = '';
$footerFixed      = '';

if(!empty($themeSettings)){
  $navbarBgColor          = ($themeSettings->navbarBgColor != '')?$themeSettings->navbarBgColor:$configData['navbarLargeColor'];
  $footerFixed            = ($themeSettings->footerFixed == 1)?'footer-fixed':'footer-static';
  
}else{
  $navbarBgColor        = $configData['navbarLargeColor'];
}

@endphp

<!-- BEGIN: Footer-->
<footer class="page-footer footer {{$footerFixed}} footer-dark {{$navbarBgColor}} gradient-shadow navbar-border navbar-shadow">
  <div class="footer-copyright">
    <div class="container">
      <span>&copy; {{ now()->year }}<a href="https://vividreal.com/"
          target="_blank">&nbsp;Billbae</a> All rights reserved.
      </span>
      <span class="right hide-on-small-only">
        Design and Developed by <a href="https://vividreal.com/" target="_blank">Vividreal</a>
      </span>
    </div>
  </div>
</footer>

<!-- END: Footer-->
