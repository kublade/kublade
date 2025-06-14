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
                            <form class="p-5" method="POST" action="{{ route('password.update') }}">
                                @csrf

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-floating mb-3">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" placeholder="{{ __('Email Address') }}" required autocomplete="email" autofocus>
                                    <label for="email">{{ __('Email Address') }}</label>

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating mb-3">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="{{ __('Password') }}" required autocomplete="new-password">
                                    <label for="password">{{ __('Password') }}</label>

                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating mb-4">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password">
                                    <label for="password-confirm">{{ __('Confirm Password') }}</label>
                                </div>

                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
