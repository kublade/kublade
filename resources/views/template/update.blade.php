@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('template.index') }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header">{{ __('Update template') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.update.action', ['template_id' => request()->template_id]) }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? $template->name }}" required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="netpol" class="col-md-4 col-form-label text-md-end">{{ __('Enable network policy') }}</label>

                            <div class="col-md-6 d-flex align-items-center">
                                <input id="netpol" type="checkbox" class="form-check-input @error('netpol') is-invalid @enderror" name="netpol" value="1" {{ old('netpol') ?? $template->netpol ? 'checked' : '' }}>
                            </div>
                        </div>

                        @if ($template->gitCredentials)
                            <div class="border rounded py-4 mb-3" id="git-credentials">
                                <div class="row mb-3">
                                    <div class="col-md-6 offset-md-4">
                                        <h5>{{ __('GIT Credentials') }}</h5>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="git_url" class="col-md-4 col-form-label text-md-end">{{ __('URL') }}</label>

                                    <div class="col-md-6">
                                        <input id="git_url" type="text" class="form-control @error('git.url') is-invalid @enderror" name="git[url]" value="{{ old('git.url') ?? $template->gitCredentials->url }}">

                                        @error('git.url')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="git_branch" class="col-md-4 col-form-label text-md-end">{{ __('Branch') }}</label>

                                    <div class="col-md-6">
                                        <input id="git_branch" type="text" class="form-control @error('git.branch') is-invalid @enderror" name="git[branch]" value="{{ old('git.branch') ?? $template->gitCredentials->branch ?? 'main' }}">

                                        @error('git.branch')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="git_credentials" class="col-md-4 col-form-label text-md-end">{{ __('Credentials') }}</label>

                                    <div class="col-md-6">
                                        <textarea id="git_credentials" type="text" class="form-control @error('git.credentials') is-invalid @enderror" name="git[credentials]">{{ old('git.credentials') ?? $template->gitCredentials->credentials }}</textarea>

                                        @error('git.credentials')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="git_username" class="col-md-4 col-form-label text-md-end">{{ __('Username') }}</label>

                                    <div class="col-md-6">
                                        <input id="git_username" type="text" class="form-control @error('git.username') is-invalid @enderror" name="git[username]" value="{{ old('git.username') ?? $template->gitCredentials->username }}">

                                        @error('git.username')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="git_email" class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>

                                    <div class="col-md-6">
                                        <input id="git_email" type="email" class="form-control @error('git.email') is-invalid @enderror" name="git[email]" value="{{ old('git.email') ?? $template->gitCredentials->email }}">

                                        @error('git.email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="git_base_path" class="col-md-4 col-form-label text-md-end">{{ __('Base Path') }}</label>

                                    <div class="col-md-6">
                                        <input id="git_base_path" type="text" class="form-control @error('git.base_path') is-invalid @enderror" name="git[base_path]" value="{{ old('git.base_path') ?? $template->gitCredentials->base_path ?? '/' }}">

                                        @error('git.base_path')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
