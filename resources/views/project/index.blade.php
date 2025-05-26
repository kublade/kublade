@extends('layouts.app')

@section('content')
<div class="container">
    @if (!empty(request()->get('project')))
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="{{ route('project.index') }}" class="btn btn-sm btn-secondary text-white">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (empty(request()->get('project')))
                <div class="card mb-3 border border-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Infrastructure') }}
                    </div>
                    <div class="card-body">
                        <div class="border rounded overflow-hidden">
                            <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                <span class="fs-6 py-2">{{ __('Overview') }}</span>
                            </h5>
                            <div class="statistics p-3 d-flex flex-column gap-2">
                                <div class="row row-gap-2">
                                    <div class="col-md-6 d-flex flex-column gap-1">
                                        <span class="small fw-bold">{{ __('CPU') }}</span>
                                        <div class="border rounded d-flex gap-3 align-items-center">
                                            @if ($statistics['alerts']['critical']['cpu'])
                                                <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @elseif ($statistics['alerts']['warning']['cpu'])
                                                <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @else
                                                <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @endif
                                            <span class="me-3">
                                                <span class="lh-1">{{ number_format($statistics['metrics']['utilization']['cpu'], 2) }}%</span><br>
                                                <span class="small lh-1 text-nowrap">{{ number_format($statistics['metrics']['usage']['cpu'], 2) }}% / {{ number_format($statistics['metrics']['capacity']['cpu'], 0) }}%</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex flex-column gap-1">
                                        <span class="small fw-bold">{{ __('Memory') }}</span>
                                        <div class="border rounded d-flex gap-3 align-items-center">
                                            @if ($statistics['alerts']['critical']['memory'])
                                                <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @elseif ($statistics['alerts']['warning']['memory'])
                                                <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @else
                                                <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @endif
                                            <span class="me-3">
                                                <span class="lh-1">{{ number_format($statistics['metrics']['utilization']['memory'], 2) }}%</span><br>
                                                <span class="small lh-1 text-nowrap">{{ number_format($statistics['metrics']['usage']['memory'], 2) }}GiB / {{ number_format($statistics['metrics']['capacity']['memory'], 0) }}GiB</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex flex-column gap-1">
                                        <span class="small fw-bold">{{ __('Storage') }}</span>
                                        <div class="border rounded d-flex gap-3 align-items-center">
                                            @if ($statistics['alerts']['critical']['storage'])
                                                <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @elseif ($statistics['alerts']['warning']['storage'])
                                                <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @else
                                                <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @endif
                                            <span class="me-3">
                                                <span class="lh-1">{{ number_format($statistics['metrics']['utilization']['storage'], 2) }}%</span><br>
                                                <span class="small lh-1 text-nowrap">{{ number_format($statistics['metrics']['usage']['storage'], 2) }}GiB / {{ number_format($statistics['metrics']['capacity']['storage'], 0) }}GiB</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex flex-column gap-1">
                                        <span class="small fw-bold">{{ __('Pods') }}</span>
                                        <div class="border rounded d-flex gap-3 align-items-center">
                                            @if ($statistics['alerts']['critical']['pods'])
                                                <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @elseif ($statistics['alerts']['warning']['pods'])
                                                <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @else
                                                <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                            @endif
                                            <span class="me-3">
                                                <span class="lh-1">{{ number_format($statistics['metrics']['utilization']['pods'], 2) }}%</span><br>
                                                <span class="small lh-1 text-nowrap">{{ number_format($statistics['metrics']['usage']['pods'], 0) }} / {{ number_format($statistics['metrics']['capacity']['pods'], 0) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="card border border-secondary">
                @if (empty(request()->get('project')))
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Projects') }}
                        <a href="{{ route('project.add') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                    </div>
                @else
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Project') }}
                    </div>
                @endif

                <div class="card-body{{ empty(request()->get('project')) ? ' p-0' : '' }}">
                    @if (!empty(request()->get('project')))
                        <div class="row row-gap-3">
                            <div class="col-md-6">
                                <div class="border rounded overflow-hidden">
                                    <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6 py-2">{{ __('Clusters') }}</span>
                                        <a href="{{ route('cluster.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-arrow-right"></i></a>
                                    </h5>
                                    <p class="fs-3 mb-0 p-3 lh-1">{{ request()->get('project')->clusters()->count() }}</p>
                                    <div class="statistics p-3 border-top d-flex flex-column gap-2">
                                        <div class="row row-gap-2">
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('CPU') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    @if (request()->get('project')->clusterStatistics['alerts']['critical']['cpu'])
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @elseif (request()->get('project')->clusterStatistics['alerts']['warning']['cpu'])
                                                        <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @else
                                                        <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @endif
                                                    <span class="me-3">
                                                        <span class="lh-1">{{ number_format(request()->get('project')->clusterStatistics['metrics']['utilization']['cpu'], 2) }}%</span><br>
                                                        <span class="small lh-1 text-nowrap">{{ number_format(request()->get('project')->clusterStatistics['metrics']['usage']['cpu'], 2) }}% / {{ number_format(request()->get('project')->clusterStatistics['metrics']['capacity']['cpu'], 0) }}%</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('Memory') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    @if (request()->get('project')->clusterStatistics['alerts']['critical']['memory'])
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @elseif (request()->get('project')->clusterStatistics['alerts']['warning']['memory'])
                                                        <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @else
                                                        <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @endif
                                                    <span class="me-3">
                                                        <span class="lh-1">{{ number_format(request()->get('project')->clusterStatistics['metrics']['utilization']['memory'], 2) }}%</span><br>
                                                        <span class="small lh-1 text-nowrap">{{ number_format(request()->get('project')->clusterStatistics['metrics']['usage']['memory'], 2) }}GiB / {{ number_format(request()->get('project')->clusterStatistics['metrics']['capacity']['memory'], 0) }}GiB</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('Storage') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    @if (request()->get('project')->clusterStatistics['alerts']['critical']['storage'])
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @elseif (request()->get('project')->clusterStatistics['alerts']['warning']['storage'])
                                                        <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @else
                                                        <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @endif
                                                    <span class="me-3">
                                                        <span class="lh-1">{{ number_format(request()->get('project')->clusterStatistics['metrics']['utilization']['storage'], 2) }}%</span><br>
                                                        <span class="small lh-1 text-nowrap">{{ number_format(request()->get('project')->clusterStatistics['metrics']['usage']['storage'], 2) }}GiB / {{ number_format(request()->get('project')->clusterStatistics['metrics']['capacity']['storage'], 0) }}GiB</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('Pods') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    @if (request()->get('project')->clusterStatistics['alerts']['critical']['pods'])
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @elseif (request()->get('project')->clusterStatistics['alerts']['warning']['pods'])
                                                        <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @else
                                                        <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    @endif
                                                    <span class="me-3">
                                                        <span class="lh-1">{{ number_format(request()->get('project')->clusterStatistics['metrics']['utilization']['pods'], 2) }}%</span><br>
                                                        <span class="small lh-1 text-nowrap">{{ number_format(request()->get('project')->clusterStatistics['metrics']['usage']['pods'], 0) }} / {{ number_format(request()->get('project')->clusterStatistics['metrics']['capacity']['pods'], 0) }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded overflow-hidden">
                                    <h5 class="bg-light ps-3 pe-2 py-2 mb-0 border-bottom d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6 py-2">{{ __('Deployments') }}</span>
                                        <a href="{{ route('deployment.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-arrow-right"></i></a>
                                    </h5>
                                    <p class="fs-3 mb-0 p-3 lh-1">{{ request()->get('project')->deployments()->count() }}</p>
                                    <div class="statistics p-3 border-top d-flex flex-column gap-2">
                                        <div class="row row-gap-2">
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('CPU') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    <i class="bi bi-cpu fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    <span class="me-3">
                                                        @if (request()->get('project')->deploymentStatistics['cpu'] !== null)
                                                            <span class="lh-1">{{ number_format(request()->get('project')->deploymentStatistics['cpu'], 2) }}%</span>
                                                        @else
                                                            <span class="lh-1">{{ __('N/A') }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('Memory') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    <i class="bi bi-memory fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    <span class="me-3">
                                                        @if (request()->get('project')->deploymentStatistics['memory'] !== null)
                                                            <span class="lh-1">{{ number_format(request()->get('project')->deploymentStatistics['memory'], 2) }}GiB</span>
                                                        @else
                                                            <span class="lh-1">{{ __('N/A') }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <span class="small fw-bold">{{ __('Storage') }}</span>
                                                <div class="border rounded d-flex gap-3 align-items-center">
                                                    <i class="bi bi-device-hdd fs-4 bg-light p-3 lh-1 rounded"></i>
                                                    <span class="me-3">
                                                        @if (request()->get('project')->deploymentStatistics['storage'] !== null)
                                                            <span class="lh-1">{{ number_format(request()->get('project')->deploymentStatistics['storage'], 2) }}GiB</span>
                                                        @else
                                                            <span class="lh-1">{{ __('N/A') }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <table class="table">
                            <thead class="font-monospace">
                                <tr class="align-middle">
                                    <th class="w-100" scope="col">{{ __('Project') }}</th>
                                    <th>{{ __('Statistics') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projects as $project)
                                    <tr class="align-middle">
                                        <td class="w-100">{{ $project->name }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('CPU') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($project->clusterStatistics['alerts']['critical']['cpu'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($project->clusterStatistics['alerts']['warning']['cpu'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($project->clusterStatistics['metrics']['utilization']['cpu'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($project->clusterStatistics['metrics']['usage']['cpu'], 2) }}% / {{ number_format($project->clusterStatistics['metrics']['capacity']['cpu'], 0) }}%</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Memory') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($project->clusterStatistics['alerts']['critical']['memory'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($project->clusterStatistics['alerts']['warning']['memory'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($project->clusterStatistics['metrics']['utilization']['memory'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($project->clusterStatistics['metrics']['usage']['memory'], 2) }}GiB / {{ number_format($project->clusterStatistics['metrics']['capacity']['memory'], 0) }}GiB</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Storage') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($project->clusterStatistics['alerts']['critical']['storage'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($project->clusterStatistics['alerts']['warning']['storage'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($project->clusterStatistics['metrics']['utilization']['storage'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($project->clusterStatistics['metrics']['usage']['storage'], 2) }}GiB / {{ number_format($project->clusterStatistics['metrics']['capacity']['storage'], 0) }}GiB</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Pods') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($project->clusterStatistics['alerts']['critical']['pods'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($project->clusterStatistics['alerts']['warning']['pods'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($project->clusterStatistics['metrics']['utilization']['pods'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($project->clusterStatistics['metrics']['usage']['pods'], 0) }} / {{ number_format($project->clusterStatistics['metrics']['capacity']['pods'], 0) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('project.details', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('project.update', ['project_id' => $project->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <a href="{{ route('project.delete.action', ['project_id' => $project->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $projects->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
