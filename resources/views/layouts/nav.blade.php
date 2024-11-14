<!-- BEGIN: SideNav -->
@php
    $isMenuDark = '';
    $activeMenuColor = '';
    $menuCollapsed = '';
    $navLock = '';
    $navCollapsed = '';
    $menuStyle = '';

    if (!empty($themeSettings)) {
        $activeMenuColor =
            $themeSettings->activeMenuColor != ''
                ? $themeSettings->activeMenuColor . ' gradient-shadow'
                : $configData['activeMenuColor'];
        $isMenuDark = $themeSettings->isMenuDark == 0 ? 'sidenav-light' : 'sidenav-dark';
        $navLock = $themeSettings->menuCollapsed == 1 ? '' : 'nav-lock';
        $navCollapsed = $themeSettings->menuCollapsed == 1 ? 'nav-collapsed' : '';
        $menuStyle = $themeSettings->menuStyle;
    } else {
        $isMenuDark = $configData['sidenavMainColor'];
        $navbarBgColor = $configData['navbarLargeColor'];
        $menuStyle = 'sidenav-active-square';
    }
@endphp

@if (Auth::user()->is_admin == 1)
    <aside class="sidenav-main nav-expanded nav-lock nav-collapsible sidenav-light sidenav-active-square">
    @else
        <aside
            class="sidenav-main nav-expanded {{ $navLock }} nav-collapsible {{ $navCollapsed }} {{ $isMenuDark }} {{ $menuStyle }}">
@endif
<div class="brand-sidebar">
    <h1 class="logo-wrapper">
        <a class="brand-logo darken-1" href="{{ url('home/') }}">
            <img class="hide-on-med-and-down" src="{{ asset('admin/images/logo/logo.png') }}" alt="materialize logo" />
            <img class="show-on-medium-and-down hide-on-med-and-up" src="{{ asset('admin/images/logo/logo.png') }}"
                alt="materialize logo" />
            <span class="logo-text hide-on-med-and-down">Billbae</span>
        </a>
        <a class="navbar-toggler" href="javascript:"> <i class="material-icons">menu</i> </a>
    </h1>
</div>
<ul class="sidenav sidenav-collapsible leftside-navigation collapsible sidenav-fixed menu-shadow" id="slide-out"
    data-menu="menu-navigation" data-collapsible="menu-accordion">
    {{-- @if (Request::is(ROUTE_PREFIX . 'home')) active {{ $activeMenuColor }} @endif  --}}
    <li class="bold"><a class="waves-effect waves-cyan" href="{{ url(ROUTE_PREFIX . '/home') }}"><i
                class="material-icons">settings_input_svideo</i><span class="menu-title"
                data-i18n="Dashboard">Dashboard</span></a></li>
    @role('Super Admin')
        <li class="bold"><a
                class="@if (Request::is(ROUTE_PREFIX . 'stores*') || Request::is(ROUTE_PREFIX . 'stores/create*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                href="{{ url(ROUTE_PREFIX . '/stores') }}"><i class="material-icons">business</i><span class="menu-title"
                    data-i18n="Stores">Stores</span></a></li>
        <li class="bold"><a
                class="@if (Request::is(ROUTE_PREFIX . 'roles*') || Request::is(ROUTE_PREFIX . 'roles/create*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                href="{{ url(ROUTE_PREFIX . '/roles') }}"><i class="material-icons">settings</i><span class="menu-title"
                    data-i18n="Stores">Roles</span></a></li>

        <li class="bold"><a
                class="@if (Request::is(ROUTE_PREFIX . 'notifications*') || Request::is(ROUTE_PREFIX . 'roles/notifications*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                href="javascript:"><i class="material-icons">notifications</i><span class="menu-title"
                    data-i18n="Stores">Notifications</span></a></li>
    @endrole

    @role('Company Admin')
        @can('schedule-list')
            <li class="bold"><a
                    class="@if (Request::is(ROUTE_PREFIX . 'schedules*') || Request::is(ROUTE_PREFIX . 'schedules/create*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                    href="{{ url(ROUTE_PREFIX . '/schedules/calendar/therapists') }}"><i
                        class="material-icons">schedule</i><span class="menu-title" data-i18n="Billing">Schedule</span></a>
            </li>
        @endcan
        @canany(['billing-create', 'billing-list', 'bill-overview', 'refund-bill'])
            <li class="bold">
                <a class="@if (Request::is(ROUTE_PREFIX . 'billings*') ||
                        Request::is(ROUTE_PREFIX . 'billings/create*') ||
                        Request::is(ROUTE_PREFIX . 'bill-overview*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan toggle-submenu"
                    href="javascript:void(0);">

                    <i class="material-icons">receipt</i>
                    <span class="menu-title" data-i18n="Billing">Billing</span>
                    <i class="material-icons right">keyboard_arrow_down</i>
                </a>
                <ul class="collapsible collapsible-sub" style="display: none;">
                    @can('billing-create')
                        <li><a href="{{ url(ROUTE_PREFIX . '/billings') }}"><i class="material-icons">note_add </i><span
                                    class="menu-title" data-i18n="Reports"> Create Billing </span> </a></li>
                    @endcan
                    @can('bill-overview')
                        <li><a href="{{ url(ROUTE_PREFIX . 'bill-overview') }}"><i class="material-icons">note</i><span
                                    class="menu-title" data-i18n="Reports">Sales Overview </span> </a></li>
                    @endcan
                    @can('refund-bill')
                        <li><a href="{{ url(ROUTE_PREFIX . 'refund-bill') }}"><i class="material-icons">redeem</i><span
                                    class="menu-title" data-i18n="Reports">Refund/Cancellation </span> </a></li>

                        <li><a href="{{ url(ROUTE_PREFIX . 'rebook') }}"><i class="material-icons">account_balance_wallet</i><span
                                    class="menu-title" data-i18n="Reports">Cancellation Fee </span> </a></li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('cashbook-view')
            <li class="bold"><a
                    class="@if (Request::is(ROUTE_PREFIX . 'cashbook*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                    href="{{ url(ROUTE_PREFIX . '/cashbook') }}"><i class="material-icons">account_balance</i><span
                        class="menu-title" data-i18n="Cashbook">Cashbook</span></a></li>
        @endcan
        @can('customer-list')
            <li class="bold"><a
                    class="@if (Request::is(ROUTE_PREFIX . 'customers*') ||
                            Request::is(ROUTE_PREFIX . 'customers/create*') ||
                            Request::is(ROUTE_PREFIX . 'customers/create')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                    href="{{ url(ROUTE_PREFIX . '/customers') }}"><i class="material-icons">people</i><span class="menu-title"
                        data-i18n="Customers">Customers</span></a></li>
        @endcan
        @can('report-view')
            <li class="bold"><a
                    class="@if (Request::is(ROUTE_PREFIX . 'reports*') || Request::is(ROUTE_PREFIX . 'reports/sales-report*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                    href="{{ url(ROUTE_PREFIX . '/reports/sales-report') }}"><i class="material-icons">report</i><span
                        class="menu-title" data-i18n="Reports">Reports</span></a></li>
        @endcan
        @can('attendence-list')
            <li class="bold"><a
                    class="@if (Request::is(ROUTE_PREFIX . 'attendance*') || Request::is(ROUTE_PREFIX . 'attendance*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan "
                    href="{{ url(ROUTE_PREFIX . '/attendance') }}"><i class="material-icons">checkdate</i><span
                        class="menu-title" data-i18n="Reports">Attendance</span></a></li>
        @endcan
        @canany(['inventory-list', 'product-list', 'category-list', 'stock-list'])
            <li class="bold">
                <a
                    class=" @if (Request::is(ROUTE_PREFIX . 'products*') ||
                            Request::is(ROUTE_PREFIX . 'categories*') ||
                            Request::is(ROUTE_PREFIX . 'stocks*')) active {{ $activeMenuColor }} @endif waves-effect waves-cyan toggle-submenu"href="javascript:void(0);">

                    <i class="material-icons">drafts </i>
                    <span class="menu-title" data-i18n="Inventory">Inventory Management</span>
                    <i class="material-icons right">keyboard_arrow_down</i>
                </a>
                <ul class="collapsible collapsible-sub" style="display: none;">
                    @can('category-list')
                        <li><a href="{{ route('categories.index') }}"><i class="material-icons">dashboard </i><span
                                    class="menu-title" data-i18n="Reports"> Create Category </span> </a></li>
                    @endcan
                    @can('product-list')
                        <li><a href="{{ route('products.index') }}"><i class="material-icons">insert_drive_file</i><span
                                    class="menu-title" data-i18n="Reports">Create Product </span> </a></li>
                    @endcan
                    @can('stock-list')
                        <li><a href="{{ route('stocks.index') }}"><i class="material-icons">subject</i><span class="menu-title"
                                    data-i18n="Reports">Create Stock </span> </a></li>
                    @endcan
                    @can('inventory-list')
                        <li><a href="{{ route('inventories.index') }}"><i class="material-icons">subject</i><span
                                    class="menu-title" data-i18n="Reports">Create Inventories </span> </a></li>
                    @endcan
                </ul>
            </li>
        @endcanany
    @endrole

</ul>
<div class="navigation-background"></div><a
    class="sidenav-trigger btn-sidenav-toggle btn-floating btn-medium waves-effect waves-light hide-on-large-only"
    href="#" data-target="slide-out"><i class="material-icons">menu</i></a>
</aside>
<!-- END: SideNav-->

<style>
    a.toggle-submenu.active+ul {
        display: block !important;
    }
</style>
