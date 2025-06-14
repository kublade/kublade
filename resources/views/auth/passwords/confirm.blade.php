@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card app__screenshot">
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
                            <form class="p-5" method="POST" action="{{ route('password.confirm') }}">
                                @csrf

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

                                <div class="row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Confirm Password') }}
                                        </button>

                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                                {{ __('Forgot Your Password?') }}
                                            </a>
                                        @endif
                                    </div>
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
