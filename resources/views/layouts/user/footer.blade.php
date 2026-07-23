<footer class="site-footer">
    <div class="container">
        <div class="row gy-4">

            {{-- Brand --}}
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <span class="logo-chip">
                        <img src="{{ company_logo() }}" alt="{{ company_name() }}" height="38">
                    </span>
                </div>
                <p class="footer-text">
                    From tailored software development to robust cybersecurity,
                    we're your gateway to a future of technological excellence.
                    Dive into a dynamic digital experience.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="Instagram"><i class="icon-base ti tabler-brand-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="icon-base ti tabler-brand-whatsapp"></i></a>
                    <a href="#" aria-label="X"><i class="icon-base ti tabler-brand-twitter"></i></a>
                    <a href="#" aria-label="Facebook"><i class="icon-base ti tabler-brand-facebook"></i></a>
                    <a href="#" aria-label="YouTube"><i class="icon-base ti tabler-brand-youtube"></i></a>
                </div>
            </div>

            {{-- Menu --}}
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="footer-title">{{ __('Menu') }}</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
                    <li><a href="{{ url('/#aboutSection') }}">{{ __('About Us') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('Our Services') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('Testimonials') }}</a></li>
                </ul>
            </div>

            {{-- Useful Links --}}
            <div class="col-lg-3 col-md-6 col-6">
                <h5 class="footer-title">{{ __('Useful Links') }}</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('About Company') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('Payment Gateway') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('Terms & Conditions') }}</a></li>
                </ul>
            </div>

            {{-- FAQ --}}
            <div class="col-lg-3 col-md-6 col-6">
                <h5 class="footer-title">FAQ</h5>
                <ul class="footer-links">
                    <li><a href="#bookingSection">{{ __('Booking') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('Delivery') }}</a></li>
                    <li><a href="{{ route('menu') }}">{{ __('Our Menu') }}</a></li>
                    <li><a href="{{ url('/') }}">{{ __('Best Food') }}</a></li>
                </ul>
            </div>

        </div>

        {{-- Contact row --}}
        <div class="row footer-contact mt-4 pt-4">
            <div class="col-md-4">
                <div class="fc-item">
                    <i class="icon-base ti tabler-phone"></i>
                    <span>{{ company_setting()->mobile ?? '' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="fc-item">
                    <i class="icon-base ti tabler-mail"></i>
                    <span>{{ company_setting()->email ?? '' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="fc-item">
                    <i class="icon-base ti tabler-map-pin"></i>
                    <span>{{ company_setting()->address ?? '' }}</span>
                </div>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="footer-bottom">
            <p class="mb-0">&copy; {{ date('Y') }} {{ company_name() }}. {{ __('All rights reserved') }}.</p>
        </div>
    </div>
</footer>
