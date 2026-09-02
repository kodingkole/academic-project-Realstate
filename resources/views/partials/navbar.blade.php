<header class="site-header">
    <div class="container navbar">
        <a href="{{ route('landing') }}" class="brand">
            <span class="brand-icon">IE</span>
            <span class="brand-name">
                Intern<span>Estate</span>
            </span>
        </a>

        <button
            class="mobile-menu-button"
            id="mobileMenuButton"
            type="button"
            aria-label="Open navigation"
        >
            ☰
        </button>

        <nav class="nav-links" id="navLinks">
            <a href="{{ route('landing') }}">Home</a>
            <a href="{{ route('landing') }}#projects">Projects</a>
            <a href="{{ route('landing') }}#services">Services</a>
            <a href="{{ route('landing') }}#process">How It Works</a>

            @auth
                @php
                    $dashRoute = match(auth()->user()->role) {
                        'investor' => route('investor.dashboard'),
                        'landowner' => route('landowner.dashboard'),
                        default => route('admin.dashboard'),
                    };
                @endphp

                <a href="{{ $dashRoute }}" class="nav-dashboard">
                    Dashboard ({{ ucfirst(auth()->user()->role) }})
                </a>

                <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                    @csrf
                    <button type="submit" class="nav-login">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('investor.register') }}" class="nav-dashboard" style="background: #10b981; color: #fff;">
                    Register as Investor
                </a>
                <a href="{{ route('login') }}" class="nav-login">
                    Sign In Portal
                </a>
            @endauth
        </nav>
    </div>
</header>