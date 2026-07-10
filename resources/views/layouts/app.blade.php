@props(['title' => 'ProvideLabs ID System'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - ProvideLabs ID System</title>

    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body>
    <div id="global-loader" class="global-loader" hidden>
        <div class="loader-card">
            <div class="providelabs-gear">
                <div class="gear-center"></div>
            </div>

            <div class="loader-text">Processing, please wait...</div>
            <div class="loader-subtext">Do not close or refresh this page.</div>
        </div>
    </div>

    <header class="topbar">
        <div class="brand">
            <div class="brand-mark"></div>
            <strong>ProvideLabs ID System</strong>
        </div>

        @auth
            <nav class="nav">
                @if (auth()->user()?->role === 'admin')
                    <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                        Dashboard
                    </a>

                    <a href="{{ route('cardholders.index') }}" @class(['active' => request()->routeIs('cardholders.index')])>
                        Cardholders
                    </a>
                @endif

                <a href="{{ route('cardholders.create') }}" @class(['active' => request()->routeIs('cardholders.create')])>
                    New Record
                </a>

                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-button">
                        Logout
                    </button>
                </form>
            </nav>
        @endauth
    </header>

    <main class="shell">
        @if (session('success'))
            <div class="alert success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert error-alert">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <script src="{{ asset('assets/global-loader.js') }}"></script>
</body>
</html>