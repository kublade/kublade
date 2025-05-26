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

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @yield('css')

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="d-flex flex-column h-100 w-100 position-fixed top-0 left-0" id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-secondary shadow-sm">
            <div class="container">
                <a class="navbar-brand py-3 me-0 bg-transparent" href="{{ request()->get('project') ? route('project.details', ['project_id' => request()->get('project')->id]) : url('/') }}">
                    <img src="/logo.svg" class="logo">
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <i class="bi bi-list fs-2 text-white"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            @can('projects.view')
                                <li class="nav-item dropdown ms-4 me-4">
                                    <a id="projectDropdown" class="btn btn-secondary text-white dropdown-toggle d-flex gap-2 align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        <i class="bi bi-boxes"></i>
                                        @if (!empty(request()->get('project')))
                                            {{ request()->get('project')->name }}
                                        @else
                                            {{ __('No project selected') }}
                                        @endif
                                        <i class="bi bi-chevron-down"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-start" aria-labelledby="projectDropdown">
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
                            @endcan
                            @if (!empty(request()->get('project')))
                                @can('projects.view')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('project.details', ['project_id' => request()->get('project')->id]) }}">Dashboard</a>
                                    </li>
                                @endcan
                                @can('clusters.view')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('cluster.index', ['project_id' => request()->get('project')->id]) }}">{{ __('Clusters') }}</a>
                                    </li>
                                @endcan
                                @can('deployments.view')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('deployment.index', ['project_id' => request()->get('project')->id]) }}">{{ __('Deployments') }}</a>
                                    </li>
                                @endcan
                            @endif
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @can('dark-mode')
                                <li class="nav-item ms-4">
                                    <a class="nav-link" href="{{ route('switch-color-mode') }}">
                                        @if (request()->cookie('theme') === 'dark')
                                            <i class="bi bi-sun-fill"></i>
                                        @else
                                            <i class="bi bi-moon-fill"></i>
                                        @endif
                                    </a>
                                </li>
                            @endcan
                        @else
                            @can('templates.view')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('template.index') }}">{{ __('Templates') }}</a>
                                </li>
                            @endcan
                            @canany(['users.view', 'roles.view'])
                                <li class="nav-item dropdown">
                                    <a id="usersDropdown" class="nav-link dropdown-toggle ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ __('Users') }}
                                        <i class="bi bi-chevron-down"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="usersDropdown">
                                        @can('users.view')
                                            <a class="dropdown-item" href="{{ route('user.index') }}">{{ __('Users') }}</a>
                                        @endcan
                                        @can('roles.view')
                                            <a class="dropdown-item" href="{{ route('role.index') }}">{{ __('Roles') }}</a>
                                        @endcan
                                    </div>
                                </li>
                            @endcan
                            @can('dark-mode')
                                <li class="nav-item ms-4">
                                    <a class="nav-link" href="{{ route('switch-color-mode') }}">
                                        @if (request()->cookie('theme') === 'dark')
                                            <i class="bi bi-sun-fill"></i>
                                        @else
                                            <i class="bi bi-moon-fill"></i>
                                        @endif
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item dropdown ms-4">
                                <a id="userDropdown" class="btn btn-primary text-white dropdown-toggle ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                    <i class="bi bi-chevron-down"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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

        <main class="py-4 flex-grow-1 overflow-auto position-relative">
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
