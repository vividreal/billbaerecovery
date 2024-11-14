@php
$menuCollapsed    = '';
$sideNavlock      = '';
$isNavbarDark     = '';
$navbarMain       = '';
$activeMenuColor  = '';
$navBarFixed      = '';
$navCollapsed     = '';
$sideNavLock      = '';
$user_profile     = (Auth::user()->profile != null) ? asset('storage/store/users/' . Auth::user()->profile) : asset('admin/images/user-icon.png');

if(!empty($themeSettings)){
  $sideNavLock          = ($themeSettings->menuCollapsed == 1)?'':'sideNav-lock';
  $navCollapsed         = ($themeSettings->menuCollapsed == 1)?'':'nav-collapsed';
  $navbarBgColor        = ($themeSettings->navbarBgColor != '')?$themeSettings->navbarBgColor:$configData['navbarLargeColor'];
  $navBarFixed          = ($themeSettings->footerFixed == 1)?'navbar-fixed':'';

  if($themeSettings->isNavbarDark != null){
    $navbarMain         = ($themeSettings->isNavbarDark == 0)?'navbar-light':'navbar-dark';
  }else{
    $navbarMain         = 'navbar-dark '. $navbarBgColor;
  }
} else {
  
}
@endphp

<!-- BEGIN: SideNav -->
<header class="page-topbar" id="header">
      <div class="navbar {{$navBarFixed}}"> 
        <nav class="navbar-main navbar-color nav-collapsible {{$navCollapsed}} {{$sideNavLock}} {{$navbarMain}} no-shadow">
          <div class="nav-wrapper">
            @if(Auth::user()->is_admin != 1)
            <div class="header-search-wrapper hide-on-med-and-down"><i class="material-icons">search</i>
              <input class="header-search-input z-depth-2" type="text" name="Search" placeholder="Search @yield('search-title', '')" data-search="template-list">
              <ul class="search-list collection display-none"></ul>
            </div>
            @endif

            <ul class="navbar-list right billbae-list">
              <li><a class="waves-effect waves-block waves-light profile-button" href="javascript:void(0);" data-target="profile-dropdown"><span class="avatar-status avatar-online"><img src="{{$user_profile}}" alt="avatar" id="log_user_icon"><i></i></span></a></li>
            </ul>
            <!-- profile-dropdown-->
            <ul class="dropdown-content" id="profile-dropdown">
              @if(auth()->user()->is_admin != 1) 
              <li><a class="grey-text text-darken-1" href="{{ url('stores/user-profile') }}"><i class="material-icons">person_outline</i> Profile</a></li>
             @endif
              <!-- <li><a class="grey-text text-darken-1" href="javascript:"><i class="material-icons">chat_bubble_outline</i> Chat</a></li>
              <li><a class="grey-text text-darken-1" href="javascript:"><i class="material-icons">help_outline</i> Help</a></li> -->
              <li class="divider"></li>
              <!-- <li><a class="grey-text text-darken-1" href="javascript:"><i class="material-icons">lock_outline</i> Lock</a></li> -->
              <li><a class="grey-text text-darken-1" href="javascript:" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" ><i class="material-icons">keyboard_tab</i> Logout</a></li>
            </ul>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
          </div>
          <nav class="display-none search-sm">
            <div class="nav-wrapper billbae-nav-wrapper">
              <form id="navbarForm">
                <div class="input-field search-input-sm">
                  <input class="search-box-sm mb-0" type="search" required="" id="search" placeholder="Explore Materialize" data-search="template-list">
                  <label class="label-icon" for="search"><i class="material-icons search-sm-icon">search</i></label><i class="material-icons search-sm-close">close</i>
                  <ul class="search-list collection search-list-sm display-none"></ul>
                </div>
              </form>
            </div>
          </nav>
        </nav>
      </div>
    </header>
    <!-- END: Header-->
