<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"{{ request()->cookie('theme') === 'dark' ? ' data-bs-theme=dark' : '' }}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @yield('css')

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-body shadow-sm">
            <div class="container">
                <a class="navbar-brand py-3 px-4 me-0 bg-secondary" href="{{ request()->get('project') ? route('project.details', ['project_id' => request()->get('project')->id]) : url('/') }}">
                    <img src="/logo.svg" class="logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item dropdown ms-4 me-4">
                                <a id="navbarDropdown" class="btn btn-secondary text-white dropdown-toggle d-flex gap-2 align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-boxes"></i>
                                    @if (!empty(request()->get('project')))
                                        {{ request()->get('project')->name }}
                                    @else
                                        {{ __('No project selected') }}
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-start" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('project.index') }}">
                                        Overview
                                    </a>
                                    <hr class="dropdown-divider">
                                    @if (request()->get('projects')->isNotEmpty())
                                        @foreach (request()->get('projects') as $project)
                                            <a class="dropdown-item" href="{{ route('project.details', ['project_id' => $project->id]) }}">
                                                {{ $project->name }}
                                            </a>
                                        @endforeach
                                        <hr class="dropdown-divider">
                                    @endif
                                    <a class="dropdown-item" href="{{ route('project.add') }}">
                                        {{ __('Add project') }}
                                    </a>
                                </div>
                            </li>
                            @if (!empty(request()->get('project')))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('project.details', ['project_id' => request()->get('project')->id]) }}">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('cluster.index', ['project_id' => request()->get('project')->id]) }}">{{ __('Clusters') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('deployment.index', ['project_id' => request()->get('project')->id]) }}">{{ __('Deployments') }}</a>
                                </li>
                                @if (request()->get('project')->user_id === Auth::id())
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('project.users', ['project_id' => request()->get('project')->id]) }}">{{ __('Users') }}</a>
                                    </li>
                                @endif
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item me-4">
                                <a class="nav-link" href="{{ route('switch-color-mode') }}">
                                    @if (request()->cookie('theme') === 'dark')
                                        <i class="bi bi-sun-fill"></i>
                                    @else
                                        <i class="bi bi-moon-fill"></i>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
                            <li class="nav-item me-4">
                                <a class="nav-link" href="{{ route('template.index') }}">{{ __('Templates') }}</a>
                            </li>
                            <li class="nav-item me-4">
                                <a class="nav-link" href="{{ route('switch-color-mode') }}">
                                    @if (request()->cookie('theme') === 'dark')
                                        <i class="bi bi-sun-fill"></i>
                                    @else
                                        <i class="bi bi-moon-fill"></i>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="btn btn-primary text-white dropdown-toggle ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('project.invitations') }}">
                                        {{ __('Invitations') }}
                                    </a>
                                    <hr class="dropdown-divider">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    @yield('javascript')
</body>
</html>
