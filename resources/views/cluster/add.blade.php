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
            <div class="card border border-secondary">
                <div class="card-header">{{ __('Add cluster') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('cluster.add.action', ['project_id' => request()->get('project')->id]) }}">
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

                        <div class="border rounded py-4 mb-3" id="git-credentials">
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('GIT Credentials') }}</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="git_url" class="col-md-4 col-form-label text-md-end">{{ __('URL') }}</label>

                                <div class="col-md-6">
                                    <input id="git_url" type="text" class="form-control @error('git.url') is-invalid @enderror" name="git[url]" value="{{ old('git.url') }}">

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
                                    <input id="git_branch" type="text" class="form-control @error('git.branch') is-invalid @enderror" name="git[branch]" value="{{ old('git.branch') ?? 'main' }}">

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
                                    <textarea id="git_credentials" type="text" class="form-control @error('git.credentials') is-invalid @enderror" name="git[credentials]">{{ old('git.credentials') }}</textarea>

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
                                    <input id="git_username" type="text" class="form-control @error('git.username') is-invalid @enderror" name="git[username]" value="{{ old('git.username') }}">

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
                                    <input id="git_email" type="email" class="form-control @error('git.email') is-invalid @enderror" name="git[email]" value="{{ old('git.email') }}">

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
                                    <input id="git_base_path" type="text" class="form-control @error('git.base_path') is-invalid @enderror" name="git[base_path]" value="{{ old('git.base_path') ?? '/' }}">

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
                                    <input id="k8s_api_url" type="text" class="form-control @error('k8s.api_url') is-invalid @enderror" name="k8s[api_url]" value="{{ old('k8s.api_url') }}">

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
                                    <textarea id="k8s_kubeconfig" type="text" class="form-control @error('k8s.kubeconfig') is-invalid @enderror" name="k8s[kubeconfig]">{{ old('k8s.kubeconfig') }}</textarea>

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
                                    <textarea id="k8s_service_account_token" type="text" class="form-control @error('k8s.service_account_token') is-invalid @enderror" name="k8s[service_account_token]">{{ old('k8s.service_account_token') }}</textarea>

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
                                    <input id="k8s_node_prefix" type="text" class="form-control @error('k8s.node_prefix') is-invalid @enderror" name="k8s[node_prefix]" value="{{ old('k8s.node_prefix') }}">

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
                                    <input id="namespace_utility" type="text" class="form-control @error('namespace.utility') is-invalid @enderror" name="namespace[utility]" value="{{ old('namespace.utility') ?? 'kube-system' }}">

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
                                    <input id="namespace_ingress" type="text" class="form-control @error('namespace.ingress') is-invalid @enderror" name="namespace[ingress]" value="{{ old('namespace.ingress') ?? 'kube-system' }}">

                                    @error('namespace.ingress')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="border rounded py-4" id="warnings">
                                    <div class="row mb-3">
                                        <div class="col-md-6 offset-md-4">
                                            <h5>{{ __('Warnings') }}</h5>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_alert_cpu" class="col-md-4 col-form-label text-md-end">{{ __('CPU') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_alert_cpu" type="number" class="form-control @error('resources.alert.cpu') is-invalid @enderror" name="resources[alert][cpu]" value="{{ old('resources.alert.cpu') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.alert.cpu')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_alert_memory" class="col-md-4 col-form-label text-md-end">{{ __('Memory') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_alert_memory" type="number" class="form-control @error('resources.alert.memory') is-invalid @enderror" name="resources[alert][memory]" value="{{ old('resources.alert.memory') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.alert.memory')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_alert_storage" class="col-md-4 col-form-label text-md-end">{{ __('Storage') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_alert_storage" type="number" class="form-control @error('resources.alert.storage') is-invalid @enderror" name="resources[alert][storage]" value="{{ old('resources.alert.storage') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.alert.storage')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_alert_pods" class="col-md-4 col-form-label text-md-end">{{ __('Pods') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_alert_pods" type="number" class="form-control @error('resources.alert.pods') is-invalid @enderror" name="resources[alert][pods]" value="{{ old('resources.alert.pods') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.alert.pods')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded py-4" id="warnings">
                                    <div class="row mb-3">
                                        <div class="col-md-6 offset-md-4">
                                            <h5>{{ __('Limits') }}</h5>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_limit_cpu" class="col-md-4 col-form-label text-md-end">{{ __('CPU') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_limit_cpu" type="number" class="form-control @error('resources.limit.cpu') is-invalid @enderror" name="resources[limit][cpu]" value="{{ old('resources.limit.cpu') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.limit.cpu')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_limit_memory" class="col-md-4 col-form-label text-md-end">{{ __('Memory') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_limit_memory" type="number" class="form-control @error('resources.limit.memory') is-invalid @enderror" name="resources[limit][memory]" value="{{ old('resources.limit.memory') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.limit.memory')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_limit_storage" class="col-md-4 col-form-label text-md-end">{{ __('Storage') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_limit_storage" type="number" class="form-control @error('resources.limit.storage') is-invalid @enderror" name="resources[limit][storage]" value="{{ old('resources.limit.storage') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.limit.storage')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="resources_limit_pods" class="col-md-4 col-form-label text-md-end">{{ __('Pods') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="resources_limit_pods" type="number" class="form-control @error('resources.limit.pods') is-invalid @enderror" name="resources[limit][pods]" value="{{ old('resources.limit.pods') ?? 80 }}">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            @error('resources.limit.pods')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
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
