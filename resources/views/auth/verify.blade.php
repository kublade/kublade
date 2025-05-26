@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column align-items-start justify-content-start gap-3 bg-banner h-100 p-5 text-white">
                                <h5 class="h1 mb-2 font-monospace">{{ __('Verify Your Email Address') }}</h5>
                                <p class="mb-0">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif

                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            {{ __('If you did not receive the email') }},
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
