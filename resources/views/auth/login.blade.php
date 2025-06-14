@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card app__screenshot shadow-lg">
                <div class="card-header app__screenshot-browserbar">
                    <div class="app__screenshot-browserbar-button"></div>
                    <div class="app__screenshot-browserbar-button"></div>
                    <div class="app__screenshot-browserbar-button"></div>
                </div>
                <div class="card-body p-0 app__screenshot-browsercontent">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ url('/') }}" class="d-flex flex-column align-items-center justify-content-center gap-3 bg-banner h-100 p-5 text-white navbar-brand">
                                <img src="/logo.svg" class="logo">
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form class="p-5" method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="form-floating mb-3">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ __('Email Address') }}" required autocomplete="email" autofocus>
                                    <label for="email">{{ __('Email Address') }}</label>

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating mb-3">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="{{ __('Password') }}" required autocomplete="current-password">
                                    <label for="password">{{ __('Password') }}</label>

                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3 d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>

                                <div class="d-flex align-items-center justify-content-between gap-3 mb-5">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Login') }}
                                    </button>

                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link p-0" href="{{ route('password.request') }}">
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    @endif
                                </div>

                                @if (config('services.github.enabled') || config('services.gitlab.enabled') || config('services.bitbucket.enabled') || config('services.google.enabled') || config('services.azure.enabled') || config('services.slack.enabled'))
                                    <div class="d-flex flex-column gap-3">
                                        @if (config('services.github.enabled'))
                                            <a href="{{ route('auth.social.redirect', 'github') }}" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi bi-github fs-5 lh-base"></i>
                                                {{ __('Login with GitHub') }}
                                            </a>
                                        @endif
                                        @if (config('services.gitlab.enabled'))
                                            <a href="{{ route('auth.social.redirect', 'gitlab') }}" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi bi-gitlab fs-5 lh-base"></i>
                                                {{ __('Login with GitLab') }}
                                            </a>
                                        @endif
                                        @if (config('services.bitbucket.enabled'))
                                            <a href="{{ route('auth.social.redirect', 'bitbucket') }}" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2">
                                                <i class="fa-brands fa-bitbucket fs-5 lh-base"></i>
                                                {{ __('Login with Bitbucket') }}
                                            </a>
                                        @endif
                                        @if (config('services.google.enabled'))
                                            <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi bi-google fs-5 lh-base"></i>
                                                {{ __('Login with Google') }}
                                            </a>
                                        @endif
                                        @if (config('services.azure.enabled'))
                                            <a href="{{ route('auth.social.redirect', 'azure') }}" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi bi-microsoft fs-5 lh-base"></i>
                                                {{ __('Login with Azure') }}
                                            </a>
                                        @endif
                                        @if (config('services.slack.enabled'))
                                            <a href="{{ route('auth.social.redirect', 'slack') }}" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi bi-slack fs-5 lh-base"></i>
                                                {{ __('Login with Slack') }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
