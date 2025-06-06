@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ url('/') }}" class="d-flex flex-column align-items-center justify-content-center gap-3 bg-banner h-100 p-5 text-white navbar-brand">
                                <img src="/logo.svg" class="logo">
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form class="p-5" method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="row mb-3">
                                    <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="form-check mb-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                            <label class="form-check-label lh-1" for="remember">
                                                {{ __('Remember Me') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-8 offset-md-4 d-flex align-items-center gap-3">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Login') }}
                                        </button>

                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link p-0" href="{{ route('password.request') }}">
                                                {{ __('Forgot Your Password?') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if (config('services.github.enabled') || config('services.gitlab.enabled') || config('services.bitbucket.enabled') || config('services.google.enabled') || config('services.azure.enabled') || config('services.slack.enabled'))
                                    <div class="row mb-0 mt-5">
                                        <div class="col-md-8 offset-md-4 d-flex flex-column gap-3">
                                            @if (config('services.github.enabled'))
                                                <a href="{{ route('auth.social.redirect', 'github') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi bi-github fs-5 lh-base"></i>
                                                    {{ __('Login with GitHub') }}
                                                </a>
                                            @endif
                                            @if (config('services.gitlab.enabled'))
                                                <a href="{{ route('auth.social.redirect', 'gitlab') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi bi-gitlab fs-5 lh-base"></i>
                                                    {{ __('Login with GitLab') }}
                                                </a>
                                            @endif
                                            @if (config('services.bitbucket.enabled'))
                                                <a href="{{ route('auth.social.redirect', 'bitbucket') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                                                    <i class="fa-brands fa-bitbucket fs-5 lh-base"></i>
                                                    {{ __('Login with Bitbucket') }}
                                                </a>
                                            @endif
                                            @if (config('services.google.enabled'))
                                                <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi bi-google fs-5 lh-base"></i>
                                                    {{ __('Login with Google') }}
                                                </a>
                                            @endif
                                            @if (config('services.azure.enabled'))
                                                <a href="{{ route('auth.social.redirect', 'azure') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi bi-microsoft fs-5 lh-base"></i>
                                                    {{ __('Login with Azure') }}
                                                </a>
                                            @endif
                                            @if (config('services.slack.enabled'))
                                                <a href="{{ route('auth.social.redirect', 'slack') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi bi-slack fs-5 lh-base"></i>
                                                    {{ __('Login with Slack') }}
                                                </a>
                                            @endif
                                        </div>
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
