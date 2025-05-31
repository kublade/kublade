@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('project.details', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Clusters') }}
                    <a href="{{ route('cluster.add', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-primary" title="{{ __('Add') }}">
                        <i class="bi bi-plus"></i>
                    </a>
                </div>

                <div class="card-body p-0">
                    <table class="table">
                        <thead class="font-monospace">
                            <tr class="align-middle">
                                <th class="w-100" scope="col">{{ __('Cluster') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Statistics') }}</th>
                                <th scope="col">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clusters as $cluster)
                                <tr class="align-middle">
                                    <td class="w-100">{{ $cluster->name }}</td>
                                    <td>
                                        @if ($cluster->status === \App\Models\Kubernetes\Clusters\Status::STATUS_ONLINE)
                                            <span class="badge bg-success">{{ __('Online') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Offline') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($cluster->statistics)
                                            <div class="d-flex gap-2">
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('CPU') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($cluster->statistics['metrics']['alerts']['critical']['cpu'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($cluster->statistics['metrics']['alerts']['warning']['cpu'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($cluster->statistics['metrics']['utilization']['cpu'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($cluster->statistics['metrics']['usage']['cpu'], 2) }}% / {{ number_format($cluster->statistics['metrics']['capacity']['cpu'], 0) }}%</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Memory') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($cluster->statistics['metrics']['alerts']['critical']['memory'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($cluster->statistics['metrics']['alerts']['warning']['memory'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($cluster->statistics['metrics']['utilization']['memory'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($cluster->statistics['metrics']['usage']['memory'], 2) }}GiB / {{ number_format($cluster->statistics['metrics']['capacity']['memory'], 0) }}GiB</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Storage') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($cluster->statistics['metrics']['alerts']['critical']['storage'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($cluster->statistics['metrics']['alerts']['warning']['storage'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($cluster->statistics['metrics']['utilization']['storage'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($cluster->statistics['metrics']['usage']['storage'], 2) }}GiB / {{ number_format($cluster->statistics['metrics']['capacity']['storage'], 0) }}GiB</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Pods') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        @if ($cluster->statistics['metrics']['alerts']['critical']['pods'])
                                                            <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @elseif ($cluster->statistics['metrics']['alerts']['warning']['pods'])
                                                            <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @else
                                                            <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        @endif
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ number_format($cluster->statistics['metrics']['utilization']['pods'], 2) }}%</span><br>
                                                            <span class="small lh-1 text-nowrap">{{ number_format($cluster->statistics['metrics']['usage']['pods'], 0) }} / {{ number_format($cluster->statistics['metrics']['capacity']['pods'], 0) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex gap-2">
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('CPU') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ __('N/A') }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Memory') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                    <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ __('N/A') }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Storage') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                        <span class="me-3">
                                                            <span class="lh-1">{{ __('N/A') }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 flex-grow-1">
                                                    <span class="small fw-bold">{{ __('Pods') }}</span>
                                                    <div class="border rounded d-flex gap-3 align-items-center">
                                                        <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
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
                                            <a href="{{ route('cluster.update', ['project_id' => $cluster->project_id, 'cluster_id' => $cluster->id]) }}" class="btn btn-sm btn-warning" title="{{ __('Update') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('cluster.delete.action', ['project_id' => $cluster->project_id, 'cluster_id' => $cluster->id]) }}" class="btn btn-sm btn-danger" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @if ($cluster->statistics)
                                    <tr class="border-bottom-0">
                                        <td colspan="4">
                                            <a href="#" class="d-flex align-items-center gap-2 justify-content-between text-decoration-none small collapsed" data-bs-toggle="collapse" data-bs-target="#clusterStatistics{{ $cluster->id }}">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-hdd-stack"></i>
                                                    {{ __('Show nodes') }}
                                                </div>
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="p-0" colspan="4">
                                            <div class="collapse border-top" id="clusterStatistics{{ $cluster->id }}">
                                                <table class="table mt-0 mb-0">
                                                    <thead class="font-monospace">
                                                        <tr class="align-middle">
                                                            <th class="w-100">{{ __('Node') }}</th>
                                                            <th>{{ __('Statistics') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($cluster->statistics['nodes'] as $node)
                                                            <tr class="align-middle{{ $loop->last ? ' border-0' : '' }}">
                                                                <td class="w-100">{{ $node['name'] }}</td>
                                                                <td>
                                                                    <div class="d-flex gap-2">
                                                                        <div class="d-flex flex-column gap-1 flex-grow-1">
                                                                            <span class="small fw-bold">{{ __('CPU') }}</span>
                                                                            <div class="border rounded d-flex gap-3 align-items-center">
                                                                                @if ($node['metrics']['alerts']['critical']['cpu'])
                                                                                    <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                                                @elseif ($node['metrics']['alerts']['warning']['cpu'])
                                                                                    <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                                                @else
                                                                                    <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                                                @endif
                                                                                <span class="me-3">
                                                                                    <span class="lh-1">{{ number_format($node['metrics']['utilization']['cpu'], 2) }}%</span><br>
                                                                                    <span class="small lh-1 text-nowrap">{{ number_format($node['metrics']['usage']['cpu'], 2) }}% / {{ number_format($node['metrics']['capacity']['cpu'], 0) }}%</span>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex flex-column gap-1 flex-grow-1">
                                                                            <span class="small fw-bold">{{ __('Memory') }}</span>
                                                                            <div class="border rounded d-flex gap-3 align-items-center">
                                                                                @if ($node['metrics']['alerts']['critical']['memory'])
                                                                                    <i class="bi bi-exclamation-circle text-danger fs-4 bg-light p-3 lh-1 rounded"></i>
                                                                                @elseif ($node['metrics']['alerts']['warning']['memory'])
                                                                                    <i class="bi bi-exclamation-triangle text-warning fs-4 bg-light p-3 lh-1 rounded"></i>
                                                                                @else
                                                                                    <i class="bi bi-check-circle text-success fs-4 bg-light p-3 lh-1 rounded"></i>
                                                                                @endif
                                                                                <span class="me-3">
                                                                                    <span class="lh-1">{{ number_format($node['metrics']['utilization']['memory'], 2) }}%</span><br>
                                                                                    <span class="small lh-1 text-nowrap">{{ number_format($node['metrics']['usage']['memory'], 2) }}GiB / {{ number_format($node['metrics']['capacity']['memory'], 0) }}GiB</span>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    {{ $clusters->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
