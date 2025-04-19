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
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Projects') }}
                    @if (empty(request()->get('project')))
                        <a href="{{ route('project.add') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                    @endif
                </div>

                <div class="card-body">
                    @if (!empty(request()->get('project')))
                        {{ request()->get('project')->name }}
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
