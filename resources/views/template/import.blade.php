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
                <div class="card-header">{{ __('Import template') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.import.action') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="netpol" class="col-md-4 col-form-label text-md-end">{{ __('Enable network policy') }}</label>

                            <div class="col-md-6">
                                <input id="netpol" type="checkbox" class="form-check-input @error('netpol') is-invalid @enderror" name="netpol" value="1" {{ old('netpol') ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="url" class="col-md-4 col-form-label text-md-end">{{ __('Repository URL') }}</label>

                            <div class="col-md-6">
                                <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ old('url') }}" placeholder="https://charts.bitnami.com/bitnami" required>

                                @error('url')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="chart" class="col-md-4 col-form-label text-md-end">{{ __('Helm Chart Name') }}</label>

                            <div class="col-md-6">
                                <input id="chart" type="text" class="form-control @error('chart') is-invalid @enderror" name="chart" value="{{ old('chart') }}" placeholder="postgresql" required>

                                @error('chart')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="repo" class="col-md-4 col-form-label text-md-end">{{ __('Repository Name') }}</label>

                            <div class="col-md-6">
                                <input id="repo" type="text" class="form-control @error('repo') is-invalid @enderror" name="repo" value="{{ old('repo') ?? 'helm-repo' }}" placeholder="helm-repo">

                                @error('repo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="namespace" class="col-md-4 col-form-label text-md-end">{{ __('Default Resource Namespace') }}</label>

                            <div class="col-md-6">
                                <input id="namespace" type="text" class="form-control @error('namespace') is-invalid @enderror" name="namespace" value="{{ old('namespace') ?? 'default' }}" placeholder="default">

                                @error('namespace')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

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
