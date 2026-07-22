<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ company_logo() }}" alt="{{ company_name() }}" style="max-height: auto; width: auto;">
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-dashboard"></i>
                <div>{{ __('Dashboard') }}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-users"></i>
                <div>{{ __('Users') }}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <a href="{{ route('admin.categories.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-category"></i>
                <div>{{ __('Categories') }}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
            <a href="{{ route('admin.items.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-list"></i>
                <div>{{ __('Items') }}</div>
            </a>
        </li>


        <li class="menu-item {{ request()->routeIs('admin.addons.*') ? 'active' : '' }}">
            <a href="{{ route('admin.addons.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-shopping-cart-plus"></i>
                <div>{{ __('Add-ons') }}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}">
            <a href="{{ route('admin.translations.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-language"></i>
                <div>{{ __('Translations') }}</div>
            </a>
        </li>
        {{-- 
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-adjustments-alt"></i>
                <div>{{ __('Master Pages') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Analytics">{{ __('Pages') }}</div>
                    </a>
                </li>
            </ul>
        </li> --}}
    </ul>
</aside>
