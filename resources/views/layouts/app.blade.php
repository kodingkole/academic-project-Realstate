<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Intern Real Estate and Construction ERP platform"
    >

    <title>
        @yield('title', 'Intern Real Estate')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @include('partials.navbar')

    @if(session('success'))
        <div class="flash-message">
            <div class="container">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <div>
                <a href="{{ route('landing') }}" class="footer-logo">
                    Intern<span>Estate</span>
                </a>

                <p>
                    Transparent real estate investment and
                    construction management in one platform.
                </p>
            </div>

            <div>
                <h4>Contact</h4>
                <p>Dhaka, Bangladesh</p>
                <p>support@internestate.test</p>
                <p>+880 1700-000000</p>
            </div>
        </div>

        <div class="footer-bottom">
            © {{ date('Y') }} Intern Estate. Academic Internship Project.
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
