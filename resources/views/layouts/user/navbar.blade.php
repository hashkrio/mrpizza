<header class="site-header" id="siteHeader">
    <nav class="navbar navbar-user py-2">
        <div class="container">

            {{-- LEFT: Hamburger (mobile) --}}
            <button class="navbar-toggler border-0 p-0 d-lg-none order-1" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sideNav" aria-controls="sideNav" aria-label="Toggle navigation">
                <i class="icon-base nav-icon ti tabler-menu-2 fs-3"></i>
            </button>

            {{-- Brand / Logo (desktop only in navbar, hidden on mobile) --}}
            <a class="navbar-brand d-none d-lg-flex align-items-center order-1" href="{{ url('/') }}">
                <span class="logo-chip">
                    <img src="{{ asset(company_logo()) }}" alt="{{ company_name() }}" height="40">
                </span>
            </a>

            {{-- CENTER: Desktop nav links --}}
            <ul class="navbar-nav d-none d-lg-flex flex-row gap-3 mx-auto order-2 mb-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                        href="{{ url('/') }}">{{ __('Home') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/#aboutSection') }}">
                        {{ __('About') }}
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('menu') ? 'active' : '' }}"
                        href="{{ url('/menu') }}">{{ __('Menu') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/#bookingSection') }}">
                        {{ __('Booking') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/#contactSection') }}">
                        {{ __('Contact') }}
                    </a>
                </li>
            </ul>

            {{-- RIGHT: Icons (always outside nav) --}}
            <div class="d-flex align-items-center gap-3 order-3 ms-auto ms-lg-0">

                {{-- Language --}}
                <div class="dropdown">
                    <a class="text-white" href="javascript:void(0);" data-bs-toggle="dropdown"
                        aria-label="Select Language">
                        <i class="icon-base nav-icon ti tabler-language"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach ($languages as $lang)
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() == $lang->locale ? 'active' : '' }}"
                                    href="{{ route('language', $lang->locale) }}">{{ $lang->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @guest
                    <a href="{{ route('login') }}" class="auth-btn" aria-label="Login" title="Login">
                        <i class="icon-base nav-icon ti tabler-login-2"></i>
                    </a>
                    <a href="{{ route('register') }}" class="auth-btn" aria-label="Register" title="Register">
                        <i class="icon-base nav-icon ti tabler-user-plus"></i>
                    </a>
                @endguest

                @auth
                    <div class="dropdown">
                        <a href="#" class="auth-btn dropdown-toggle-no-caret d-flex align-items-center" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="My Account">
                            @if (Auth::user()->avatar ?? false)
                                <img src="{{ asset(Auth::user()->avatar) }}" alt="Profile" class="profile-avatar"
                                    width="34" height="34">
                            @else
                                <i class="icon-base nav-icon ti tabler-user-circle"></i>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <small class="text-muted d-block">{{ __('Signed in as') }}</small>
                                <strong>{{ Auth::user()->name }}</strong>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="icon-base nav-icon ti tabler-user me-2"></i> {{ __('My Profile') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('/orders') }}">
                                    <i class="icon-base nav-icon ti tabler-clipboard-list me-2"></i> {{ __('My Orders') }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="icon-base nav-icon ti tabler-logout-2 me-2"></i> {{ __('Logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth

                {{-- Cart --}}
                @php $cartCount = count(session('cart', [])); @endphp
                <a href="{{ route('cart') }}" class="cart-btn" aria-label="Cart">
                    <i class="icon-base nav-icon ti tabler-shopping-cart"></i>
                    <span class="cart-count {{ $cartCount ? '' : 'd-none' }}"
                        id="cartBadge">{{ $cartCount }}</span>
                </a>

            </div>
        </div>
    </nav>

    {{-- LEFT SIDE OFFCANVAS (mobile menu with logo inside) --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sideNav" data-bs-backdrop="false" data-bs-scroll="true"
        aria-labelledby="sideNavLabel">
        <div class="offcanvas-header border-bottom">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <span class="logo-chip">
                    <img src="{{ asset(company_logo()) }}" alt="{{ company_name() }}" height="40">
                </span>
            </a>
            <button type="button" class="btn border-0 text-white p-0 fs-3" data-bs-dismiss="offcanvas"
                aria-label="Close">
                <i class="icon-base nav-icon ti tabler-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav flex-column gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                        href="{{ url('/') }}">{{ __('Home') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('about') ? 'active' : '' }}" href="#aboutSection"
                        data-bs-dismiss="offcanvas">{{ __('About') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('menu') ? 'active' : '' }}"
                        href="{{ url('/menu') }}">{{ __('Menu') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('booking') ? 'active' : '' }}" href="#bookingSection"
                        data-bs-dismiss="offcanvas">{{ __('Booking') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="#contactSection"
                        data-bs-dismiss="offcanvas">{{ __('Contact') }}</a>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
    function updateCartBadge(count) {
        if (typeof count === 'undefined') return;
        const $b = $('#cartBadge');
        if ($b.length) $b.text(count).toggleClass('d-none', count < 1);
    }
</script>
