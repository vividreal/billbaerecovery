<!-- Theme Customizer -->
@php
use App\Helpers\Helper;
$themeColours = Helper::themeColours();
$isMenuDark       = '';
$activeMenuColor  = '';
$navbarBgColor    = '';
$menuCollapsed    = 0;
$defaultNav       = '';
$isFooterFixed    = '';
$menuStyle        = '';

if(!empty($themeSettings)){
  $activeMenuColor      = ($themeSettings->activeMenuColor != '')?$themeSettings->activeMenuColor:$configData['activeMenuColor'];
  $navbarBgColor        = ($themeSettings->navbarBgColor != '')?$themeSettings->navbarBgColor:$configData['navbarBgColor'];
  $isMenuDark           = ($themeSettings->isMenuDark == 0)?'sidenav-light':'sidenav-dark';
  $menuCollapsed        = ($themeSettings->menuCollapsed == 0)?0:1;
  $defaultNav           = ($themeSettings->isNavbarDark == 0)?0:1;
  $isFooterFixed        = ($themeSettings->footerFixed == 0)?0:1;
  $menuStyle            = $themeSettings->menuStyle;
}else{
  $activeMenuColor      = $configData['activeMenuColor'];
  $isMenuDark           = $configData['sidenavMainColor'];
  $menuStyle            = 'sidenav-active-square';
}

@endphp
@php
    $routePrefix = session('ROUTE_PREFIX', 'default_prefix');
   
@endphp
<a href="#" data-target="theme-cutomizer-out"
   class="btn btn-customizer pink accent-2 white-text sidenav-trigger theme-cutomizer-trigger">
   <i class="material-icons">settings</i></a>

<!--/ Theme Customizer -->

<div id="theme-cutomizer-out" class="theme-cutomizer sidenav row">
   <div class="col s12">
      <a class="sidenav-close" href="javascript:"><i class="material-icons">close</i></a>
      <h5 class="theme-cutomizer-title">Theme Customizer </h5>
      <p class="medium-small">Customize & Preview in Real Time</p>
      <form id="themeSettingsForm" name="themeSettingsForm" role="form" method="" action="" class="ajax-submit"> 
         {{ csrf_field() }}
         {!! Form::hidden('theme_settings_id', $themeSettings->id ?? '' , ['id' => 'theme_settings_id'] ); !!}
         {!! Form::hidden('navbarBgColor', '' , ['id' => 'navbarBgColor'] ); !!}
         {!! Form::hidden('activeMenuColor', '' , ['id' => 'activeMenuColor'] ); !!}
         
      
         <div class="menu-options">
            <h6 class="mt-6">Menu Options</h6>
            <hr class="customize-devider" />
            <div class="menu-options-form row">

               <div class="input-field col s12 menu-color mb-0">
                  <p class="mt-0">Menu Color</p>
                  <div class="gradient-color center-align">
                     @if(!empty($themeColours))
                        @foreach($themeColours as $colours)
                           <span class="menu-color-option {{$colours->name}} @if($activeMenuColor == $colours->name) selected @endif" data-color="{{$colours->name}}"></span>
                        @endforeach
                     @endif
                  </div>
               </div>

               <div class="input-field col s12">
                  <div class="switch">
                     Menu Dark
                     <label class="float-right"><input class="menu-dark-checkbox" name="isMenuDark" id="isMenuDark" type="checkbox" @if($isMenuDark == 'sidenav-dark') checked @endif/> <span class="lever ml-0"></span
                     ></label>
                  </div>
               </div>
               <div class="input-field col s12">
                  <div class="switch">
                     Menu Collapsed
                     <label class="float-right"
                        ><input class="menu-collapsed-checkbox" name="menuCollapsed" id="menuCollapsed" type="checkbox" @if($menuCollapsed == 1) checked @endif type="checkbox"/> <span class="lever ml-0"></span
                     ></label>
                  </div>
               </div>
               <div class="input-field col s12">
                  <div class="switch">
                     <p class="mt-0">Menu Selection</p>
                     <label>
                        <input
                           class="menu-selection-radio with-gap"
                           value="sidenav-active-square"
                           name="menuSelection"
                           type="radio"
                           @if($menuStyle == "sidenav-active-square") checked @endif
                        />
                        <span>Square</span>
                     </label>
                     <label>
                        <input
                           class="menu-selection-radio with-gap"
                           value="sidenav-active-rounded"
                           name="menuSelection"
                           type="radio"
                           @if($menuStyle == "sidenav-active-rounded") checked @endif
                        />
                        <span>Rounded</span>
                     </label>
                     <label>
                        <input class="menu-selection-radio with-gap" value="" name="menuSelection" @if($menuStyle == "") checked @endif type="radio" />
                        <span>Normal</span>
                     </label>
                  </div>
               </div>
            </div>
         </div>
         <h6 class="mt-6">Navbar and Footer Options</h6>
         <hr class="customize-devider" />
         <div class="navbar-options row">
            <div class="input-field col s12 navbar-color mb-0">
               <p class="mt-0">Navbar and Footer Color</p>
               <div class="gradient-color center-align">
                     @if(!empty($themeColours))
                        @foreach($themeColours as $colours)
                           <span class="navbar-color-option {{$colours->name}} @if($navbarBgColor == $colours->name) selected @endif" data-color="{{$colours->name}}"></span>
                        @endforeach
                     @endif
                  
      
               </div>
            </div>
            <!-- <div class="input-field col s12">
               <div class="switch">
                  Default Navbar and Footer
                  <label class="float-right"
                     ><input class="navbar-dark-default-checkbox" id="defaultNav" name="defaultNav" @if($defaultNav != '') checked @endif type="checkbox"/> <span class="lever ml-0"></span
                  ></label>
               </div>
            </div>

            <div class="input-field col s12">
               <div class="switch">
                  Navbar and Footer Dark
                  <label class="float-right"
                     ><input class="navbar-dark-checkbox" type="checkbox"/> <span class="lever ml-0"></span
                  ></label>
               </div>
            </div> -->

            <div class="input-field col s12">
               <div class="switch">
                  Navbar and Footer Fixed
                  <label class="float-right"
                     ><input class="navbar-fixed-checkbox" type="checkbox" id="footerFixed" name="footerFixed" @if($isFooterFixed == 1) checked @endif/> <span class="lever ml-0"></span
                  ></label>
               </div>
            </div>
         </div>
         <button class="btn waves-effect waves-light " type="submit" name="action" id="themeSettingBtn">Submit<i class="material-icons right">send</i></button>


      </form>
   </div>
</div>

@push('page-scripts')
<script type="text/javascript">

$("#themeSettingBtn").click(function(e){
e.preventDefault();
var forms = $("#themeSettingsForm");

$('.menu-color-option').each(function (){
   if($(this).hasClass("selected")){
      $('#activeMenuColor').val($(this).data("color"));
   }
});

$('.navbar-color-option').each(function (){
   if($(this).hasClass("selected")){
      $('#navbarBgColor').val($(this).data("color"));
   }
});



console.log(data.flagError);

      $.ajax({ url: "{{ url( $routePrefix.'/store/theme-settings') }}", type: "POST", processData: false, 
      data: forms.serialize(), dataType: "html",
      }).done(function (a) {
          var data = JSON.parse(a);
          if(data.flagError == false){
            window.location.href = "{{ url( $routePrefix.'/home')}}"; 
          }else{
            // showErrorToaster(data.message);
            // printErrorMsg(data.error);
          }
      });



});
</script>
@endpush