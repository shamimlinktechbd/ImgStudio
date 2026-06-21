<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Image Studio') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('images.index') }}">Image Studio</a>
        <nav class="nav">
            <a href="{{ route('images.history') }}">History</a>
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                    <a href="{{ route('admin.backgrounds') }}">Backgrounds</a>
                @endif
                <span class="user-chip">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link-button">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a class="button small" href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>

    <main class="shell">
        @if(session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
