<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'ProvideLabs ID System' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/providelabs.css') }}">
</head>
<body>
    @auth
        <header class="topbar">
            <div class="topbar-inner">
                <a href="{{ route('dashboard') }}" class="brand">
                    <span class="brand-mark"></span>
                    <span>ProvideLabs ID System</span>
                </a>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard</a>
                    <a href="{{ route('cardholders.index') }}" @class(['active' => request()->routeIs('cardholders.*')])>Cardholders</a>
                    <a href="{{ route('cardholders.create') }}">New Record</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </nav>
            </div>
        </header>
    @endauth

    <main class="{{ auth()->check() ? 'container' : '' }}">
        @if (session('success'))
            <div class="flash success">{{ session('success') }}</div>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
