<div class="d-flex flex-column h-100 w-100 position-fixed top-0 left-0" id="app">
    @if (!in_array(Route::currentRouteName(), $authRoutes))
        @if (request()->attributes->has('update'))
            <div class="alert alert-warning rounded-0 border-0 mb-0" role="alert">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 d-flex align-items-center gap-3">
                            <i class="bi bi-arrow-up-circle fs-5"></i>
                            <div class="d-flex align-items-center justify-content-between gap-3 w-100">
                                <span>{!! __('A new version (<a href=":url" target="_blank" class="text-decoration-none fw-bold">:version</a>) of Kublade is available. Please update to the latest version.', ['url' => 'https://github.com/kublade/kublade/releases/tag/' . request()->attributes->get('update')['tag_name'], 'version' => request()->attributes->get('update')['tag_name']]) !!}</span>
                                <a href="{{ route('updated') }}" class="btn btn-sm btn-warning">
                                    {{ __('I\'ve updated!') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
                                @can('projects.' . request()->get('project')->id . ' . clusters.view')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('cluster.index', ['project_id' => request()->get('project')->id]) }}">{{ __('Clusters') }}</a>
                                    </li>
                                @endcan
                                @can('projects.' . request()->get('project')->id . ' . deployments.view')
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
                                <a id="userDropdown" class="btn btn-secondary text-white dropdown-toggle ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
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
    @endif

    <main class="py-4 flex-grow-1 overflow-auto position-relative{{ in_array(Route::currentRouteName(), $authRoutes) ? ' content-vertical-center' : '' }}">
        <div class="d-flex flex-column gap-4 w-100">
            @if (
                !in_array(Route::currentRouteName(), $authRoutes) && (
                    session()->has('error') || 
                    session()->has('warning') || 
                    session()->has('success') || 
                    session()->has('info') || 
                    session()->has('message')
                )
            )
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            @if (session()->has('error'))
                                <div class="alert alert-danger mb-0 d-flex align-items-center gap-3" role="alert">
                                    <i class="bi bi-exclamation-circle fs-5"></i>
                                    {{ session()->get('error') }}
                                </div>
                            @elseif (session()->has('warning'))
                                <div class="alert alert-warning mb-0 d-flex align-items-center gap-3" role="alert">
                                    <i class="bi bi-exclamation-circle fs-5"></i>
                                    {{ session()->get('warning') }}
                                </div>
                            @elseif (session()->has('success'))
                                <div class="alert alert-success mb-0 d-flex align-items-center gap-3" role="alert">
                                    <i class="bi bi-check-circle fs-5"></i>
                                    {{ session()->get('success') }}
                                </div>
                            @elseif (session()->has('info'))
                                <div class="alert alert-info mb-0 d-flex align-items-center gap-3" role="alert">
                                    <i class="bi bi-info-circle fs-5"></i>
                                    {{ session()->get('info') }}
                                </div>
                            @elseif (session()->has('message'))
                                <div class="alert alert-secondary mb-0 d-flex align-items-center gap-3" role="alert">
                                    <i class="bi bi-info-circle fs-5"></i>
                                    {{ session()->get('message') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        
            @yield('content')
        </div>

        @if (!in_array(Route::currentRouteName(), $authRoutes))
            <livewire:chat />
        @endif
    </main>
</div>
