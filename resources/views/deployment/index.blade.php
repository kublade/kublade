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
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Deployments') }}
                    @if (!empty(request()->get('project')) && empty($deployment))
                        <a href="{{ route('deployment.add', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                    @endif
                </div>

                <div class="card-body">
                    @if (!empty($deployment))
                        {{ $deployment->name }}
                    @else
                        <table class="table">
                            <thead>
                                <tr class="align-middle">
                                    <th class="w-100" scope="col">{{ __('Deployment') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deployments as $deployment)
                                    <tr class="align-middle">
                                        <td class="w-100">
                                            {{ $deployment->name ?? __('N/A') }}
                                            <span class="small d-block">{{ $deployment->template->name }}</span>
                                        </td>
                                        <td>{!! $deployment->status !!}</td>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
