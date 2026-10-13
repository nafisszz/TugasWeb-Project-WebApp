<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Beranda') - RuangKu UNIMED</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">RuangKu<small>UNIMED</small></a>
    <nav>
        <a href="{{ route('availability') }}" class="{{ request()->routeIs('availability') ? 'on' : '' }}">Ketersediaan</a>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'on' : '' }}">Dasbor</a>
                <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'on' : '' }}">Pengajuan</a>
                <a href="{{ route('admin.rooms.index') }}" class="{{ request()->routeIs('admin.rooms.*') ? 'on' : '' }}">Ruangan</a>
            @else
                <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'on' : '' }}">Beranda</a>
                <a href="{{ route('booking.create') }}" class="{{ request()->routeIs('booking.create') ? 'on' : '' }}">Ajukan</a>
            @endif
            <span class="who">{{ auth()->user()->name }}</span>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="linkbtn">Keluar</button></form>
        @else
            <a href="{{ route('login') }}">Masuk</a>
            <a href="{{ route('register') }}">Daftar</a>
        @endauth
    </nav>
</header>
<main>
    @if(session('ok'))<p class="flash ok">{{ session('ok') }}</p>@endif
    @if(session('err'))<p class="flash err">{{ session('err') }}</p>@endif
    @yield('content')
</main>
</body>
</html>
