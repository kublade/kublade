<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"{{ request()->cookie('theme') === 'dark' ? ' data-bs-theme=dark' : '' }}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicon/favicon.svg') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}" />

    @yield('css')

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @livewireStyles
</head>
<body>
    @php
        $authRoutes = ['login', 'register', 'password.request', 'password.email', 'password.reset', 'password.confirm'];
    @endphp
    @if (!in_array(Route::currentRouteName(), $authRoutes))
        <div class="card app__screenshot app__screenshot--panel shadow-lg">
            <div class="card-header app__screenshot-browserbar flex-shrink-0">
                <div class="app__screenshot-browserbar-button"></div>
                <div class="app__screenshot-browserbar-button"></div>
                <div class="app__screenshot-browserbar-button"></div>
            </div>
            <div class="card-body p-0 app__screenshot-browsercontent overflow-hidden">
                @include('layouts.content', ['authRoutes' => $authRoutes])
            </div>
        </div>
    @else
        @include('layouts.content', ['authRoutes' => $authRoutes])
    @endif

    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    @yield('javascript')

    <script>
        $(document).ready(function() {
            $('.content-vertical-center').toggleClass('overflowing', $(window).height() < $('.content-vertical-center')[0]?.scrollHeight);
        });

        $(window).on('resize', function() {
            $('.content-vertical-center').toggleClass('overflowing', $(window).height() < $('.content-vertical-center')[0]?.scrollHeight);
        });
    </script>

    @livewireScripts
</body>
</html>
