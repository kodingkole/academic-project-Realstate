<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal | Intern Estate')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body">
<div class="portal-layout">
    <aside class="portal-sidebar" id="portalSidebar">
        <a href="{{ route('admin.dashboard') }}" class="portal-brand">
            <span class="brand-icon">IE</span>
            <span class="brand-name">Intern<span>Estate</span></span>
        </a>
        <p class="sidebar-label">MAIN MENU</p>
        <nav class="sidebar-navigation">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span>⌂</span>Dashboard</a>
            @foreach(\App\Http\Controllers\AdminModuleController::MODULES as $key => $item)
                <a href="{{ route('admin.modules.index', $key) }}" class="{{ request()->route('module') === $key ? 'active' : '' }}">
                    <span>{{ $item['icon'] }}</span>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="sidebar-bottom">
            <a href="{{ route('landing') }}">← Public Website</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
        </div>
    </aside>
    <div class="portal-main">
        <header class="portal-topbar">
            <button id="sidebarToggle" class="sidebar-toggle" type="button" aria-label="Toggle sidebar">☰</button>
            <div><p class="topbar-date">{{ now()->format('l, d F Y') }}</p><h1>@yield('page-heading', 'Dashboard')</h1></div>
            <div class="user-menu">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div><strong>{{ auth()->user()->name }}</strong><span>System Administrator</span></div>
            </div>
        </header>
        <main class="portal-content">
            @if(session('success'))<div class="portal-alert">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
