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
                            <div class="p-5">
                                @if (session('status'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.email') }}">
                                    @csrf

                                    <div class="form-floating mb-4">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ __('Email Address') }}" required autocomplete="email" autofocus>
                                        <label for="email">{{ __('Email Address') }}</label>

                                        @error('email')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Send Password Reset Link') }}
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
</div>
@endsection
