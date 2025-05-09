@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ !empty(request()->get('project')) && !empty($deployment) ? route('deployment.index', ['project_id' => request()->get('project')->id]) : route('project.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                @if (!empty(request()->get('project')) && empty($deployment))
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Deployments') }}
                        <a href="{{ route('deployment.add', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                    </div>
                @endif

                <div class="card-body d-flex flex-column gap-4">
                    @if (!empty($deployment))
                        <div class="row">
                            <div class="col-md">
                                <div class="border border-secondary rounded overflow-hidden">
                                    <h5 class="bg-secondary ps-3 pe-2 py-2 mb-0 border-bottom border-secondary d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6 py-2 text-white">{{ __('Status') }}</span>
                                    </h5>
                                    <p class="fs-3 mb-0 p-3 lh-1">{!! $deployment->simpleStatus !!}</p>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="border rounded overflow-hidden">
                                    <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6 py-2">{{ __('Namespace') }}</span>
                                    </h5>
                                    <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                        <i class="bi bi-collection"></i>
                                        <span class="lh-1 fs-5">{{ $deployment->uuid }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if ($deployment->statistics)
                                <div class="col-md">
                                    <div class="border rounded overflow-hidden">
                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                            <span class="fs-6 py-2">{{ __('CPU') }}</span>
                                        </h5>
                                        <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                            <i class="bi bi-cpu"></i>
                                            <span class="lh-1 fs-5">{{ number_format($deployment->statistics['cpu'], 2) }}%</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="border rounded overflow-hidden">
                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                            <span class="fs-6 py-2">{{ __('Memory') }}</span>
                                        </h5>
                                        <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                            <i class="bi bi-memory"></i>
                                            <span class="lh-1 fs-5">{{ number_format($deployment->statistics['memory'], 2) }}GiB</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="border rounded overflow-hidden">
                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                            <span class="fs-6 py-2">{{ __('Storage') }}</span>
                                        </h5>
                                        <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                            <i class="bi bi-device-hdd"></i>
                                            <span class="lh-1 fs-5">{{ number_format($deployment->statistics['storage'], 2) }}GiB</span>
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="col-md">
                                    <div class="border rounded overflow-hidden">
                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                            <span class="fs-6 py-2">{{ __('CPU') }}</span>
                                        </h5>
                                        <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                            <i class="bi bi-cpu"></i>
                                            <span class="lh-1 fs-5">{{ __('N/A') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="border rounded overflow-hidden">
                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                            <span class="fs-6 py-2">{{ __('Memory') }}</span>
                                        </h5>
                                        <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                            <i class="bi bi-memory"></i>
                                            <span class="lh-1 fs-5">{{ __('N/A') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="border rounded overflow-hidden">
                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                            <span class="fs-6 py-2">{{ __('Storage') }}</span>
                                        </h5>
                                        <p class="fs-3 mb-0 p-3 lh-1 d-flex align-items-center gap-3">
                                            <i class="bi bi-device-hdd"></i>
                                            <span class="lh-1 fs-5">{{ __('N/A') }}</span>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="block">
                            <div class="row">
                                <div class="col-md">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link{{ request()->get('tab') === 'details' || request()->get('tab') === null ? ' active' : '' }}" href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'details']) }}">{{ __('Details') }}</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link{{ request()->get('tab') === 'metrics' ? ' active' : '' }}" href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'metrics']) }}">{{ __('Metrics') }}</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link{{ request()->get('tab') === 'logs' ? ' active' : '' }}" href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'logs']) }}">{{ __('Logs') }}</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link{{ request()->get('tab') === 'files' ? ' active' : '' }}" href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'files']) }}">{{ __('Files') }}</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link{{ request()->get('tab') === 'versions' ? ' active' : '' }}" href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'versions']) }}">{{ __('History') }}</a>
                                        </li>
                                        @if ($deployment->template->netpol)
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link{{ request()->get('tab') === 'network-policies' ? ' active' : '' }}" href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'network-policies']) }}">{{ __('Network Policies') }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane{{ request()->get('tab') === 'details' || request()->get('tab') === null ? ' show active' : '' }}" id="details" role="tabpanel" aria-labelledby="details-tab">
                                            <div class="row mb-3">
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('Name') }}</span>
                                                        </h5>
                                                        <p class="mb-0 p-3 lh-1">{{ $deployment->name ?? __('N/A') }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('Template') }}</span>
                                                            <a href="{{ route('template.details', ['project_id' => request()->get('project')->id, 'template_id' => $deployment->template->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-eye"></i></a>
                                                        </h5>
                                                        <p class="mb-0 p-3 lh-1">{{ $deployment->template->name }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('Cluster') }}</span>
                                                        </h5>
                                                        <p class="mb-0 p-3 lh-1">{{ $deployment->cluster->name }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border rounded overflow-hidden">
                                                <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                    <span class="fs-6 py-2">{{ __('Settings') }}</span>
                                                    <a href="{{ route('deployment.update', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-pencil-square"></i></a>
                                                </h5>
                                                <div class="p-3">
                                                    @foreach ($deployment->template->groupedFields->all as $field)
                                                        @if ($field->secret)
                                                            @php
                                                                $value = $deployment->deploymentSecretData->where('key', $field->key)->first()->value;
                                                            @endphp
                                                        @else
                                                            @php
                                                                $value = $deployment->deploymentData->where('key', $field->key)->first()->value;
                                                            @endphp
                                                        @endif
                                                        <div class="row">
                                                            <div class="col-md">
                                                                <div class="input-group{{ !$loop->last ? ' mb-3' : '' }}">
                                                                    <span class="input-group-text align-items-start" id="field{{ $field->id }}">{{ $field->label }}</span>
                                                                    @if ($field->type === 'textarea')
                                                                        <textarea class="form-control" aria-label="{{ $field->label }}" aria-describedby="field{{ $field->id }}" readonly>{{ $value }}</textarea>
                                                                    @else
                                                                        <input type="text" class="form-control" aria-label="{{ $field->label }}" aria-describedby="field{{ $field->id }}" value="{{ $value }}" readonly>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane{{ request()->get('tab') === 'metrics' ? ' show active' : '' }}" id="metrics" role="tabpanel" aria-labelledby="metrics-tab">
                                            <div class="row mb-3">
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('Filter') }}</span>
                                                        </h5>
                                                        <form class="p-3" action="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'metrics']) }}" method="GET">
                                                            <input type="hidden" name="tab" value="metrics">

                                                            <div class="row mb-3">
                                                                <div class="col-md">
                                                                    <select class="form-select" id="aggregation" name="aggregation">
                                                                        <option value="minute" {{ request()->get('aggregation') === 'minute' ? 'selected' : '' }}>{{ __('Minute') }}</option>
                                                                        <option value="hour" {{ request()->get('aggregation') === 'hour' ? 'selected' : '' }}>{{ __('Hour') }}</option>
                                                                        <option value="day" {{ request()->get('aggregation') === 'day' || request()->get('aggregation') === null ? 'selected' : '' }}>{{ __('Day') }}</option>
                                                                        <option value="week" {{ request()->get('aggregation') === 'week' ? 'selected' : '' }}>{{ __('Week') }}</option>
                                                                        <option value="month" {{ request()->get('aggregation') === 'month' ? 'selected' : '' }}>{{ __('Month') }}</option>
                                                                        <option value="quarter" {{ request()->get('aggregation') === 'quarter' ? 'selected' : '' }}>{{ __('Quarter') }}</option>
                                                                        <option value="year" {{ request()->get('aggregation') === 'year' ? 'selected' : '' }}>{{ __('Year') }}</option>
                                                                        <option value="all" {{ request()->get('aggregation') === 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md">
                                                                    <input type="datetime-local" class="form-control" id="from" name="from" value="{{ request()->get('from') ?? Carbon\Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d\TH:i') }}">
                                                                </div>
                                                                <div class="col-md">
                                                                    <input type="datetime-local" class="form-control" id="to" name="to" value="{{ request()->get('to') ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d\TH:i') }}">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md">
                                                                    <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                                                                    <a href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'tab' => 'metrics']) }}" class="btn btn-secondary text-white">{{ __('Reset') }}</a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('CPU') }}</span>
                                                        </h5>
                                                        <div id="cpu-chart"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('RAM') }}</span>
                                                        </h5>
                                                        <div id="ram-chart"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('Storage') }}</span>
                                                        </h5>
                                                        <div id="storage-chart"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md">
                                                    <div class="border rounded overflow-hidden">
                                                        <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                            <span class="fs-6 py-2">{{ __('Traffic') }}</span>
                                                        </h5>
                                                        <div id="traffic-chart"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane{{ request()->get('tab') === 'files' ? ' show active' : '' }}" id="files" role="tabpanel" aria-labelledby="files-tab">
                                            <div class="border rounded overflow-hidden">
                                                <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                    <span class="fs-6 py-2">{{ __('Files') }}</span>
                                                </h5>
                                                <div class="d-flex flex-column flex-md-row">
                                                    <div class="col-md-4 d-flex">
                                                        <div class="p-3 border-end h-100 w-100 border-end-md-0">
                                                            @include('deployment.file-tree', ['deployment' => $deployment, 'template' => $deployment->template])
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 px-4">
                                                        @if (!empty($file))
                                                            @include('deployment.editor', ['content' => $file->interpret($deployment)])
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane{{ request()->get('tab') === 'logs' ? ' show active' : '' }}" id="logs" role="tabpanel" aria-labelledby="logs-tab">
                                            <div class="border rounded overflow-hidden">
                                                <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                    <span class="fs-6 py-2">{{ __('Logs') }}</span>
                                                </h5>
                                                <div class="d-flex flex-column flex-md-row">
                                                    <div class="col-md-4 d-flex">
                                                        <div class="p-3 border-end h-100 w-100 border-end-md-0">
                                                            @include('deployment.log-tree', ['deployment' => $deployment])
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 px-4">
                                                        @if (!empty($log))
                                                            @include('deployment.log-viewer', ['log' => $log])
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane{{ request()->get('tab') === 'versions' ? ' show active' : '' }}" id="versions" role="tabpanel" aria-labelledby="versions-tab">
                                            <div class="border rounded overflow-hidden">
                                                <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                    <span class="fs-6 py-2">{{ __('Versions') }}</span>
                                                </h5>
                                                <div class="p-3">
                                                    @php	
                                                        $commits = $deployment->commits()->paginate(10);
                                                    @endphp
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Hash') }}</th>
                                                                <th class="w-100">{{ __('Message') }}</th>
                                                                <th>{{ __('Created At') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($commits as $commit)
                                                                <tr class="align-middle">
                                                                    <td>{{ $commit->hash }}</td>
                                                                    <td class="w-100">{!! str_replace("\n", '<br>', $commit->message) !!}</td>
                                                                    <td class="text-nowrap">{{ $commit->created_at->format('Y-m-d H:i:s') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="bg-light border-start border-end" colspan="4">
                                                                        <a href="#" class="d-inline-block" data-bs-toggle="collapse" data-bs-target="#commit{{ $commit->id }}">
                                                                            {{ __('Show diff') }}
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="p-0 border-start border-end" colspan="4">
                                                                        <div class="collapse" id="commit{{ $commit->id }}">
                                                                            <div class="p-3">
                                                                                @if ($commit->diff->count() > 0)
                                                                                    @foreach ($commit->diff as $diff)
                                                                                        <span class="small mb-2 fw-bold d-block">{{ $diff['label'] }}</span>
                                                                                        <div class="input-group{{ !$loop->last ? ' mb-3' : '' }}">
                                                                                            <input type="text" class="form-control border border-danger rounded" aria-label="{{ $diff['label'] }}" aria-describedby="field{{ $diff['key'] }}" value="{{ $diff['current'] }}" readonly>
                                                                                            <span class="input-group-text align-items-center border-0 bg-transparent">
                                                                                                <i class="bi bi-arrow-right"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control border border-success rounded" aria-label="{{ $diff['label'] }}" aria-describedby="field{{ $diff['key'] }}" value="{{ $diff['previous'] }}" readonly>
                                                                                        </div>
                                                                                    @endforeach
                                                                                    <div class="row mt-3">
                                                                                        <div class="col-md">
                                                                                            <a href="{{ route('deployment.commit.revert.action', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id, 'commit_id' => $commit->id]) }}" class="btn btn-primary">{{ __('Revert') }}</a>
                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    <div class="alert alert-info mb-0 d-flex align-items-center gap-3">
                                                                                        <i class="bi bi-info-circle fs-5"></i>
                                                                                        {{ __('No changes') }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    {{ $deployment->commits()->paginate(10)->links('pagination::bootstrap-5') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane{{ request()->get('tab') === 'network-policies' ? ' show active' : '' }}" id="network-policies" role="tabpanel" aria-labelledby="network-policies-tab">
                                            <div class="border rounded overflow-hidden">
                                                <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                                    <span class="fs-6 py-2">{{ __('Network Policies') }}</span>
                                                </h5>
                                                <div class="d-flex flex-column flex-md-row">
                                                    <div class="col-md-4 d-flex">
                                                        <div class="p-3 border-end h-100 w-100 border-end-md-0">
                                                            @include('deployment.netpol-tree', ['deployment' => $deployment])
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 px-4">
                                                        @if (!empty($networkPolicy) || request()->get('network_policy_id') === 'new')
                                                            @include('deployment.netpol-editor', ['networkPolicy' => $networkPolicy])
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <table class="table">
                            <thead>
                                <tr class="align-middle">
                                    <th class="w-100" scope="col">{{ __('Deployment') }}</th>
                                    <th scope="col">{{ __('Template') }}</th>
                                    <th scope="col">{{ __('Cluster') }}</th>
                                    <th scope="col">{{ __('Namespace') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Statistics') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deployments as $deployment)
                                    <tr class="align-middle">
                                        <td class="w-100">{{ $deployment->name ?? __('N/A') }}</td>
                                        <td>{{ $deployment->template->name }}</td>
                                        <td>{{ $deployment->cluster->name }}</td>
                                        <td>
                                            <span class="text-nowrap badge bg-light text-body">{{ $deployment->uuid }}</span>
                                        </td>
                                        <td>{!! $deployment->status !!}</td>
                                        <td>
                                            @if ($deployment->statistics)
                                                <div class="d-flex gap-2">
                                                    <div class="d-flex flex-column gap-1 flex-grow-1">
                                                        <span class="small fw-bold">{{ __('CPU') }}</span>
                                                        <div class="border rounded d-flex gap-3 align-items-center">
                                                            <i class="bi bi-cpu fs-4 bg-light p-3 lh-1 rounded"></i>
                                                            <span class="me-3">
                                                                <span class="lh-1">{{ number_format($deployment->statistics['cpu'], 2) }}%</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column gap-1 flex-grow-1">
                                                        <span class="small fw-bold">{{ __('Memory') }}</span>
                                                        <div class="border rounded d-flex gap-3 align-items-center">
                                                            <i class="bi bi-memory fs-4 bg-light p-3 lh-1 rounded"></i>
                                                            <span class="me-3">
                                                                <span class="lh-1">{{ number_format($deployment->statistics['memory'], 2) }}GiB</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column gap-1 flex-grow-1">
                                                        <span class="small fw-bold">{{ __('Storage') }}</span>
                                                        <div class="border rounded d-flex gap-3 align-items-center">
                                                            <i class="bi bi-device-hdd fs-4 bg-light p-3 lh-1 rounded"></i>
                                                            <span class="me-3">
                                                                <span class="lh-1">{{ number_format($deployment->statistics['storage'], 2) }}GiB</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex gap-2">
                                                    <div class="d-flex flex-column gap-1 flex-grow-1">
                                                        <span class="small fw-bold">{{ __('CPU') }}</span>
                                                        <div class="border rounded d-flex gap-3 align-items-center">
                                                            <i class="bi bi-cpu fs-4 bg-light p-3 lh-1 rounded"></i>
                                                            <span class="me-3">
                                                                <span class="lh-1">{{ __('N/A') }}</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column gap-1 flex-grow-1">
                                                        <span class="small fw-bold">{{ __('Memory') }}</span>
                                                        <div class="border rounded d-flex gap-3 align-items-center">
                                                            <i class="bi bi-memory fs-4 bg-light p-3 lh-1 rounded"></i>
                                                            <span class="me-3">
                                                                <span class="lh-1">{{ __('N/A') }}</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column gap-1 flex-grow-1">
                                                        <span class="small fw-bold">{{ __('Storage') }}</span>
                                                        <div class="border rounded d-flex gap-3 align-items-center">
                                                            <i class="bi bi-device-hdd fs-4 bg-light p-3 lh-1 rounded"></i>
                                                            <span class="me-3">
                                                                <span class="lh-1">{{ __('N/A') }}</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('deployment.update', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <a href="{{ route('deployment.delete.action', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $deployments->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if (request()->get('tab') === 'metrics')
    @section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            const chartColors = [
                '#ff2b1c',
                '#ff7a6f',
                '#b31f15',
            ];

            const options = {
                colors: chartColors,
                chart: {
                    height: 350,
                    type: 'area',
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
                stroke: {
                    curve: 'smooth',
                },
                xaxis: {
                    type: 'datetime',
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy HH:mm'
                    },
                },
            };

            const cpuChart = new ApexCharts(document.querySelector("#cpu-chart"), {
                ...options,
                series: [
                    {
                        name: '{{ __('Utilization') }}',
                        data: {!! $metrics->pluck('values.cpu_core_percentage')->toJson() !!},
                    }
                ],
                xaxis: {
                    ...options.xaxis,
                    categories: {!! $metrics->pluck('timestamp')->toJson() !!},
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toFixed(2) + ' %';
                        }
                    },
                },
            });
            cpuChart.render();

            var ramChart = new ApexCharts(document.querySelector("#ram-chart"), {
                ...options,
                series: [
                    {
                        name: '{{ __('Utilization') }}',
                        data: {!! $metrics->pluck('values.memory_gigabytes')->toJson() !!},
                    }
                ],
                xaxis: {
                    ...options.xaxis,
                    categories: {!! $metrics->pluck('timestamp')->toJson() !!},
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toFixed(2) + ' GB';
                        }
                    },
                },
            });
            ramChart.render();

            var storageChart = new ApexCharts(document.querySelector("#storage-chart"), {
                ...options,
                series: [
                    {
                        name: '{{ __('Utilization') }}',
                        data: {!! $metrics->pluck('values.storage_gigabytes')->toJson() !!},
                    }
                ],
                xaxis: {
                    ...options.xaxis,
                    categories: {!! $metrics->pluck('timestamp')->toJson() !!},
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toFixed(2) + ' GB';
                        }
                    },
                },
            });
            storageChart.render();

            var trafficChart = new ApexCharts(document.querySelector("#traffic-chart"), {
                ...options,
                series: [
                    {
                        name: '{{ __('Utilization (In)') }}',
                        data: {!! $metrics->pluck('values.traffic_gigabytes_in')->toJson() !!},
                    }, 
                    {
                        name: '{{ __('Utilization (Out)') }}',
                        data: {!! $metrics->pluck('values.traffic_gigabytes_out')->toJson() !!},
                    }
                ],
                xaxis: {
                    ...options.xaxis,
                    categories: {!! $metrics->pluck('timestamp')->toJson() !!},
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toFixed(2) + ' GB';
                        }
                    },
                },
            });
            trafficChart.render();
        });
    </script>
    @endsection
@endif
