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
            <div class="card">
                @if (empty(request()->get('project')))
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Projects') }}
                        <a href="{{ route('project.add') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                    </div>
                @endif

                <div class="card-body">
                    @if (!empty(request()->get('project')))
                        <div class="row">
                            <div class="col-md">
                                <div class="border rounded overflow-hidden">
                                    <h5 class="bg-light ps-3 pe-2 py-2 border-bottom d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6">{{ __('Clusters') }}</span>
                                        <a href="{{ route('cluster.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-arrow-right"></i></a>
                                    </h5>
                                    <p class="h1 mb-0 p-3 lh-1">{{ request()->get('project')->clusters()->count() }}</p>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="border rounded overflow-hidden">
                                    <h5 class="bg-light ps-3 pe-2 py-2 border-bottom d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6">{{ __('Deployments') }}</span>
                                        <a href="{{ route('deployment.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-arrow-right"></i></a>
                                    </h5>
                                    <p class="h1 mb-0 p-3 lh-1">{{ request()->get('project')->deployments()->count() }}</p>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="border rounded overflow-hidden">
                                    <h5 class="bg-light ps-3 pe-2 py-2 border-bottom d-flex justify-content-between align-items-center gap-3">
                                        <span class="fs-6">{{ __('Users') }}</span>
                                        <a href="{{ route('project.users', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white"><i class="bi bi-arrow-right"></i></a>
                                    </h5>
                                    <p class="h1 mb-0 p-3 lh-1">{{ request()->get('project')->invitations()->count() }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <table class="table">
                            <thead>
                                <tr class="align-middle">
                                    <th class="w-100" scope="col">{{ __('Project') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (request()->get('projects') as $project)
                                    <tr class="align-middle">
                                        <td class="w-100">{{ $project->name }}</td>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
