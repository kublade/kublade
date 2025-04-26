@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('cluster.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Update cluster') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('cluster.update.action', ['project_id' => request()->get('project')->id, 'cluster_id' => request()->cluster_id]) }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? $cluster->name }}" required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="border rounded py-4 mb-3" id="git-credentials">
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('GIT Credentials') }}</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="git_url" class="col-md-4 col-form-label text-md-end">{{ __('URL') }}</label>

                                <div class="col-md-6">
                                    <input id="git_url" type="text" class="form-control @error('git.url') is-invalid @enderror" name="git[url]" value="{{ old('git.url') ?? $cluster->gitCredentials?->url }}">

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
                                    <input id="git_branch" type="text" class="form-control @error('git.branch') is-invalid @enderror" name="git[branch]" value="{{ old('git.branch') ?? $cluster->gitCredentials?->branch }}">

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
                                    <textarea id="git_credentials" type="text" class="form-control @error('git.credentials') is-invalid @enderror" name="git[credentials]">{{ old('git.credentials') ?? $cluster->gitCredentials?->credentials }}</textarea>

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
                                    <input id="git_username" type="text" class="form-control @error('git.username') is-invalid @enderror" name="git[username]" value="{{ old('git.username') ?? $cluster->gitCredentials?->username }}">

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
                                    <input id="git_email" type="email" class="form-control @error('git.email') is-invalid @enderror" name="git[email]" value="{{ old('git.email') ?? $cluster->gitCredentials?->email }}">

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
                                    <input id="git_base_path" type="text" class="form-control @error('git.base_path') is-invalid @enderror" name="git[base_path]" value="{{ old('git.base_path') ?? $cluster->gitCredentials?->base_path ?? '/' }}">

                                    @error('git.base_path')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border rounded py-4 mb-3" id="k8s-credentials">
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('Kubernetes Credentials') }}</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="k8s_api_url" class="col-md-4 col-form-label text-md-end">{{ __('API URL') }}</label>

                                <div class="col-md-6">
                                    <input id="k8s_api_url" type="text" class="form-control @error('k8s.api_url') is-invalid @enderror" name="k8s[api_url]" value="{{ old('k8s.api_url') ?? $cluster->k8sCredentials?->api_url }}">

                                    @error('k8s.api_url')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="k8s_kubeconfig" class="col-md-4 col-form-label text-md-end">{{ __('Kubeconfig') }}</label>

                                <div class="col-md-6">
                                    <textarea id="k8s_kubeconfig" type="text" class="form-control @error('k8s.kubeconfig') is-invalid @enderror" name="k8s[kubeconfig]">{{ old('k8s.kubeconfig') ?? $cluster->k8sCredentials?->kubeconfig }}</textarea>

                                    @error('k8s.kubeconfig')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="k8s_service_account_token" class="col-md-4 col-form-label text-md-end">{{ __('Service Account Token') }}</label>

                                <div class="col-md-6">
                                    <textarea id="k8s_service_account_token" type="text" class="form-control @error('k8s.service_account_token') is-invalid @enderror" name="k8s[service_account_token]">{{ old('k8s.service_account_token') ?? $cluster->k8sCredentials?->service_account_token }}</textarea>

                                    @error('k8s.service_account_token')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <label for="k8s_node_prefix" class="col-md-4 col-form-label text-md-end">{{ __('Worker Node Prefix') }}</label>

                                <div class="col-md-6">
                                    <input id="k8s_node_prefix" type="text" class="form-control @error('k8s.node_prefix') is-invalid @enderror" name="k8s[node_prefix]" value="{{ old('k8s.node_prefix') ?? $cluster->k8sCredentials?->node_prefix }}">

                                    @error('k8s.node_prefix')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border rounded py-4 mb-3" id="namespaces">
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('Kubernetes Namespaces') }}</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="namespace_utility" class="col-md-4 col-form-label text-md-end">{{ __('Utility') }}</label>

                                <div class="col-md-6">
                                    <input id="namespace_utility" type="text" class="form-control @error('namespace.utility') is-invalid @enderror" name="namespace[utility]" value="{{ old('namespace.utility') ?? $cluster->utilityNamespace?->name ?? 'kube-system' }}">

                                    @error('namespace.utility')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <label for="namespace_ingress" class="col-md-4 col-form-label text-md-end">{{ __('Ingress') }}</label>

                                <div class="col-md-6">
                                    <input id="namespace_ingress" type="text" class="form-control @error('namespace.ingress') is-invalid @enderror" name="namespace[ingress]" value="{{ old('namespace.ingress') ?? $cluster->ingressNamespace?->name ?? 'kube-system' }}">

                                    @error('namespace.ingress')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
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
