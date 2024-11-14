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

  if($themeSettings->isNavbarDark != null) {
    $navbarMain         = ($themeSettings->isNavbarDark == 0)?'navbar-light':'navbar-dark';
  } else {
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
            @if(isset($page->top_search))
            <div class="header-search-wrapper hide-on-med-and-down"><i class="material-icons">search</i>
              <input class="header-search-input z-depth-2" type="text" name="top_search" id="top_search" placeholder="Search @yield('search-title', '')" value="">
            </div>
            @endif
            <ul class="navbar-list right billbae-list">
              <!-- <li><a class="waves-effect waves-block waves-light" href="{{ url(ROUTE_PREFIX.'/users') }}"><i class="material-icons">person</i></a></li>
              <li><a class="waves-effect waves-block waves-light notification-button" href="javascript:void(0);" data-target="notifications-dropdown"><i class="material-icons">settings</i></a></li>
              <li><a class="waves-effect waves-block waves-light notification-button" href="javascript:void(0);" data-target="services-dropdown"><i class="material-icons">business</i></a></li> -->
              <li><a class="waves-effect waves-block waves-light notification-button" href="javascript:void(0);" data-target="notifications-dropdown1"><i class="material-icons">settings</i></a></li>
              <li class="hide-on-med-and-down"><a class="waves-effect waves-block waves-light toggle-fullscreen" href="javascript:void(0);"><i class="material-icons">settings_overscan</i></a></li>
              <li class="hide-on-large-only search-input-wrapper"><a class="waves-effect waves-block waves-light search-button" href="javascript:void(0);"><i class="material-icons">search</i></a></li>              
              <!-- <li><a class="waves-effect waves-block waves-light notification-button" href="javascript:void(0);" data-target="notifications-dropdown-2"><i class="material-icons">notifications_none<small class="notification-badge">5</small></i></a></li> -->
              <li><a class="waves-effect waves-block waves-light profile-button" href="javascript:void(0);" data-target="profile-dropdown"><span class="avatar-status avatar-online"><img src="{{$user_profile}}" alt="avatar" id="log_user_icon"><i></i></span></a></li>
            </ul>
            <!-- megamenu for settings-->
            <div class="dropdown-content mega-menu" id="notifications-dropdown1">
              <div class="mega-menu-wrap">
                <div class="row megaRow" id="megaRow">
                  @if(auth()->user()->is_admin != 1) 
                  <div class="col s12 m4 l4 colMr">
                    <h4 class="row mega-title">Store settings</h4>        
                    <ul>
                      @can('manage-store')
                        <li><a href="{{ url('store-profile') }}" class="@if(Request::is(ROUTE_PREFIX.'store-profile')) active @endif">Store profile </a></li>
                      @endcan
                      @can('manage-store-billing')
                        <li><a href="{{ url('store-billings') }}" class="@if(Request::is(ROUTE_PREFIX.'store-billings')) active @endif">Billing & Tax settings</a></li>
                        <li><a href="{{ url('store/billing-series') }}" class="@if(Request::is(ROUTE_PREFIX.'store/billing-series')) active @endif">Billing series settings</a></li>
                        <!-- <li><a href="{{ url('store/billings') }}" class="@if(Request::is(ROUTE_PREFIX.'store/billings')) active @endif">Payment types</a></li> -->
                      @endcan 
                    </ul>
                  </div>
                 
                  <div class="col s12 m4 l4 colMr">
                    <h4 class="row mega-title">Services</h4>     
                    <ul>
                      @can('service-list')
                        <li><a href="{{ url(ROUTE_PREFIX.'/services') }}" class="@if(Request::is(ROUTE_PREFIX.'services') || Request::is(ROUTE_PREFIX.'services/create') || Request::is(ROUTE_PREFIX.'services/*')) active @endif"> Services</a></li>
                      @endcan 
                      @can('package-list')
                        <li><a href="{{ url(ROUTE_PREFIX.'/packages') }}" class="@if(Request::is(ROUTE_PREFIX.'packages') || Request::is(ROUTE_PREFIX.'packages/create') || Request::is(ROUTE_PREFIX.'packages/*')) active @endif">Packages</a></li>
                        <!-- <li><a href="{{ url(ROUTE_PREFIX.'/service-category') }}" class="@if(Request::is(ROUTE_PREFIX.'service-category')) active @endif">Service categories</a></li> -->
                      @endcan
                      <li><a href="{{ url(ROUTE_PREFIX.'/rooms') }}" class="@if(Request::is(ROUTE_PREFIX.'rooms') || Request::is(ROUTE_PREFIX.'rooms/create') || Request::is(ROUTE_PREFIX.'rooms/*')) active @endif"> Rooms</a></li>
                      <li><a  class="@if (Request::is(ROUTE_PREFIX . 'membership*') || Request::is(ROUTE_PREFIX . 'membership/create*')) active  @endif "
                        href="{{ url(ROUTE_PREFIX . '/membership') }}"><span>MemberShip</span></a></li>
                   
                   
                    </ul>   
                  </div>
                  @endif
                  <div class="col s12 m4 l4 colMr">
                    <h4 class="row mega-title">Others</h4>     
                    <ul>
                      @if(auth()->user()->is_admin != 1) 
                      @can('user-list')
                        <li><a href="{{ url(ROUTE_PREFIX.'/users') }}" class="@if (Request::is(ROUTE_PREFIX.'users') ||  Request::is(ROUTE_PREFIX.'users/create') ||  Request::is(ROUTE_PREFIX.'users/*')) active @endif">User management </a></li>
                      @endcan    
                      @endif                  
                        <li><a href="{{ url('profile') }}" class="@if(Request::is(ROUTE_PREFIX.'/profile')) active @endif">Personal profile</a></li>
                        @if(auth()->user()->is_admin != 1) 
                        @can('staff-list')
                        <li><a href="{{ url(ROUTE_PREFIX.'/staffs') }}" class="@if(Request::is(ROUTE_PREFIX.'staffs') || Request::is(ROUTE_PREFIX.'staffs/*')) active @endif">Staff Management</a></li>
                      @endcan
                      @can('role-list')
                        <li><a href="{{ url(ROUTE_PREFIX.'/roles') }}" class="@if(Request::is(ROUTE_PREFIX.'roles') || Request::is(ROUTE_PREFIX.'roles/*')) active @endif">Roles & Permissions</a></li>
                      @endcan
                      @endif
                    </ul>
                  </div>
                </div>
              </div>	
            </div>

            <!-- notifications-dropdown-->
            <ul class="dropdown-content" id="notifications-dropdown">
              <li>
                <h6>Store settings</h6>
              </li>
              <li class="divider"></li>
        
              <!-- <li><a class="black-text" href="#!"><span class="material-icons icon-bg-circle amber small">trending_up</span> Generate monthly report</a>
                <time class="media-meta grey-text darken-2" datetime="2015-06-12T20:50:48+08:00">1 week ago</time>
              </li> -->
            </ul>

            <!-- Personal profile -->
            <ul class="dropdown-content" id="services-dropdown">
              <li><h6>Services</h6> </li>
              <li class="divider"></li>
              <li><a class="black-text" href="{{ url('services') }}"><span class="material-icons icon-bg-circle cyan small">add_shopping_cart</span> Services</a></li>
              <li><a class="black-text" href="{{ url('service-category') }}"><span class="material-icons icon-bg-circle cyan small">add_shopping_cart</span> Service Category</a></li>
            </ul>
            <!-- notifications-dropdown-->
            <ul class="dropdown-content" id="notifications-dropdown-2">
              <li>
                <h6>NOTIFICATIONS<span class="new badge">5</span></h6>
              </li>
              <li class="divider"></li>
              <li><a class="black-text" href="#!"><span class="material-icons icon-bg-circle cyan small">add_shopping_cart</span> A new order has been placed!</a>
                <time class="media-meta grey-text darken-2" datetime="2015-06-12T20:50:48+08:00">2 hours ago</time>
              </li>
              <li><a class="black-text" href="#!"><span class="material-icons icon-bg-circle red small">stars</span> Completed the task</a>
                <time class="media-meta grey-text darken-2" datetime="2015-06-12T20:50:48+08:00">3 days ago</time>
              </li>
              <li><a class="black-text" href="#!"><span class="material-icons icon-bg-circle teal small">settings</span> Settings updated</a>
                <time class="media-meta grey-text darken-2" datetime="2015-06-12T20:50:48+08:00">4 days ago</time>
              </li>
              <li><a class="black-text" href="#!"><span class="material-icons icon-bg-circle deep-orange small">today</span> Director meeting started</a>
                <time class="media-meta grey-text darken-2" datetime="2015-06-12T20:50:48+08:00">6 days ago</time>
              </li>
              <li><a class="black-text" href="#!"><span class="material-icons icon-bg-circle amber small">trending_up</span> Generate monthly report</a>
                <time class="media-meta grey-text darken-2" datetime="2015-06-12T20:50:48+08:00">1 week ago</time>
              </li>
            </ul>
            <!-- profile-dropdown-->
            <ul class="dropdown-content" id="profile-dropdown">
              <li><a class="grey-text text-darken-1" href="{{ url('profile') }}"><i class="material-icons">person_outline</i>Profile</a></li>
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
    <ul class="display-none" id="page-search-title">
      <li class="auto-suggestion-title"><a class="collection-item" href="#">
          <h6 class="search-title">PAGES</h6></a></li>
    </ul>
    <ul class="display-none" id="search-not-found">
      <li class="auto-suggestion"><a class="collection-item display-flex align-items-center" href="#"><span class="material-icons">error_outline</span><span class="member-info">No results found.</span></a></li>
    </ul>