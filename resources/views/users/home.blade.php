@extends('layouts.user.app')

@section('title', __('Home') . ' - ' . company_name())

@section('content')

    {{-- ===================== HERO ===================== --}}
    <section class="hero-section">
        {{-- <div class="hero-shape"></div> --}}
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-animate="fade-right">
                    <span class="hero-eyebrow">Welcome To {{ company_name() }}</span>
                    <h1 class="hero-title">
                        Get your <span class="text-yellow">Food</span>
                        <span class="text-yellow">Dreams</span> Here
                    </h1>
                    <p class="hero-text">
                        Handcrafted pizzas baked fresh in our fire-wood oven, topped with
                        the finest ingredients and a whole lot of love. From classic
                        Margherita to bold house specials, every slice is made to order
                        and delivered hot to your door.
                    </p>
                    <a href="{{ url('/menu') }}" class="btn btn-brand">Get It Now</a>
                </div>

                <div class="col-lg-6" data-animate="fade-left">
                    <div class="hero-image-wrap">
                        <span class="best-offer">
                            <strong>BEST</strong>
                            <span>OFFER</span>
                        </span>
                        <span class="hero-disc"></span>
                        <div class="hero-pizza-frame spin-slow">
                            <img src="{{ asset('assets/img/banner-img.svg') }}" alt="Delicious Pizza" class="hero-pizza">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== ABOUT 1 ===================== --}}
    <section class="about-section bg-cream py-6" id="aboutSection">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-5" data-animate="fade-up">
                    <div class="about-img-frame">
                        <div class="about-square-card">
                            <img src="{{ asset('assets/img/burger.png') }}" alt="Tasty Burger" class="img-primary">
                            <img src="{{ asset('assets/img/1burger.png') }}" alt="Hot Burger" class="img-secondary">
                        </div>
                    </div>
                </div>
                <div class="col-lg-7" data-animate="fade-up">
                    <span class="section-eyebrow"><span class="line"></span> About Us</span>
                    <h2 class="section-title">Where Your Dreams<br>Become Reality</h2>
                    <p class="section-text">
                        At {{ company_name() }}, great food starts with great ingredients.
                        We knead our dough daily, simmer our sauces from scratch, and pile
                        on generous toppings so every bite bursts with flavor. Whether you're
                        craving a juicy burger or a wood-fired pizza, we cook it fresh and
                        serve it with a smile.
                    </p>
                    <div class="d-flex align-items-center flex-wrap gap-4 mt-4">
                        <a href="{{ url('/#aboutSection') }}" class="btn btn-brand">View More</a>
                        <a href="tel:+351967906906" class="phone-link">+351 967906906</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FEATURES STRIP ===================== --}}
    <section class="features-strip">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4" data-animate="zoom">
                    <div class="feature-item">
                        <div class="feature-ic"><i class="ti tabler-tools-kitchen-2"></i></div>
                        <div>
                            <h5>ABSOLUTE DINING</h5>
                            <p>Cozy in-house dining</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-animate="zoom">
                    <div class="feature-item">
                        <div class="feature-ic"><i class="ti tabler-box-seam"></i></div>
                        <div>
                            <h5>FAST DELIVERY</h5>
                            <p>Hot food within 30 minutes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-animate="zoom">
                    <div class="feature-item">
                        <div class="feature-ic"><i class="ti tabler-paper-bag"></i></div>
                        <div>
                            <h5>PICKUP READY</h5>
                            <p>Grab your order on the go</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== ABOUT 2 ===================== --}}
    <section class="about-section py-6">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6 order-lg-1 order-2" data-animate="fade-right">
                    <span class="section-eyebrow"><span class="line"></span> About Us</span>
                    <h2 class="section-title">Fire Wood Oven Makes The Best Pizza</h2>
                    <p class="section-text">
                        There's a reason our pizzas taste like no other. Baked in a
                        traditional wood-fired oven at blazing temperatures, the crust
                        turns perfectly crisp on the outside and soft within, kissed with
                        that unmistakable smoky flavor. It's an age-old craft we're proud
                        to bring to every pie we serve.
                    </p>
                    <a href="{{ url('/#aboutSection') }}" class="btn btn-brand mt-3">View More</a>
                </div>
                <div class="col-lg-6 order-lg-2 order-1" data-animate="fade-left">
                    <div class="about-circle-img">
                        <span class="steam-particle"></span>
                        <span class="steam-particle"></span>
                        <span class="steam-particle"></span>
                        <span class="steam-particle"></span>
                        <img src="{{ asset('assets/img/event-img.png') }}" alt="Wood Oven Pizza">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== SPECIAL MENU ===================== --}}
    <section class="menu-section">
        <div class="container">
            <div class="text-center" data-animate="fade-up">
                <h2 class="menu-heading">{{ __('Choose our special Menu!') }}</h2>
                <p class="menu-sub">
                    {{ __('Explore a menu crafted to delight every craving — from wood-fired pizzas and juicy burgers to fresh sides and sweet treats. Each dish is prepared with quality ingredients and a passion for flavor you can taste in every bite.') }}
                </p>
            </div>

            <div class="row gy-4 mt-4">
                @forelse ($categories as $category)
                    <div class="col-lg-3 col-sm-6" data-animate="fade-up">
                        <div class="menu-card">
                            <div class="menu-card-img">
                                @if ($category->image)
                                    <img src="{{ asset('public/storage/' . $category->image) }}"
                                        alt="{{ $category->name }}">
                                @else
                                    <img src="{{ asset('assets/img/no-img-item.jpg') }}" alt="{{ $category->name }}">
                                @endif
                            </div>
                            <div class="menu-card-body">
                                <h4>{{ $category->name }}</h4>
                                {{-- <a href="{{ url('/menu?category=' . $category->id) }}" class="btn btn-outline-brand btn-sm">
                                {{ __('View Menu') }}
                            </a> --}}
                                <button type="button" class="btn btn-outline-brand btn-sm js-view-menu"
                                    data-category="{{ $category->id }}">
                                    {{ __('View Menu') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted">
                        {{ __('No categories available yet.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ===================== WELCOME / GALLERY ===================== --}}
    <section class="welcome-section bg-cream" id="bookingSection">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-5" data-animate="fade-right">
                    <h2 class="welcome-title">
                        Welcome To <span class="text-red">Mr. Pizza</span>
                        <span class="text-green">Tavira</span>
                    </h2>
                    <p class="section-text">
                        Tucked in the heart of Tavira, we're a family-run pizzeria serving
                        authentic wood-fired pizzas and hearty comfort food. Come for the
                        flavors, stay for the warm, welcoming atmosphere that keeps our
                        regulars coming back.
                    </p>
                    <ul class="welcome-list">
                        <li><i class="icon-base ti tabler-chevron-right"></i> Fresh dough made daily</li>
                        <li><i class="icon-base ti tabler-chevron-right"></i> Locally sourced ingredients</li>
                        <li><i class="icon-base ti tabler-chevron-right"></i> Dine in, takeaway or delivery</li>
                    </ul>
                    <a href="{{ url('/#aboutSection') }}" class="btn btn-brand mt-2">View More</a>
                </div>

                <div class="col-lg-7" data-animate="fade-left">
                    <div class="welcome-gallery">
                        <div class="wg-item wg-1">
                            <img src="{{ asset('assets/img/chef.png') }}" alt="Chef">
                        </div>
                        <div class="wg-item wg-2">
                            <img src="{{ asset('assets/img/pizza-stack.png') }}" alt="Pizza Stack">
                        </div>
                        <div class="wg-item wg-3">
                            <img src="{{ asset('assets/img/spices.png') }}" alt="Spices">
                        </div>
                        <div class="wg-item wg-4">
                            <img src="{{ asset('assets/img/wood-fire.png') }}" alt="Wood Oven">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CONTACT ===================== --}}
    <section class="contact-section py-6" id="contactSection">
        <div class="contact-blob-green"></div>
        <div class="contact-blob-tomato"></div>
        <div class="container position-relative">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6" data-animate="fade-right">
                    <div class="delivery-img">
                        <img src="{{ asset('assets/img/delivery-scooter.png') }}" alt="Fast Delivery"
                            class="float-anim">
                    </div>
                </div>

                <div class="col-lg-6" data-animate="fade-left">
                    <h2 class="contact-heading">contact us <span class="underline"></span></h2>

                    <div class="contact-item">
                        <div class="contact-ic"><i class="icon-base ti tabler-phone"></i></div>
                        <span>{{ company_setting()->mobile ?? '' }}</span>
                    </div>
                    <div class="contact-item">
                        <div class="contact-ic"><i class="icon-base ti tabler-mail"></i></div>
                        <span>{{ company_setting()->email ?? '' }}</span>
                    </div>
                    <div class="contact-item">
                        <div class="contact-ic"><i class="icon-base ti tabler-map-pin"></i></div>
                        <span>{{ company_setting()->address ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@push('js_script')
    <form id="menuGoForm" action="{{ route('menu.preselect') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="category" id="menuGoCategory">
    </form>

    <script>
        $(document).on('click', '.js-view-menu', function() {
            $('#menuGoCategory').val($(this).data('category'));
            $('#menuGoForm').submit();
        });
    </script>
@endpush
