@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Project') }}</div>

                <div class="card-body">
                    @if (!empty(request()->get('project')))
                        {{ request()->get('project')->name }}
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-100" scope="col">{{ __('Project') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (request()->get('projects') as $project)
                                    <tr>
                                        <td class="w-100">{{ $project->name }}</td>
                                        <td class="d-flex gap-2">
                                            <a href="{{ route('project.details', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('project.update', ['project_id' => $project->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                            <a href="{{ route('project.delete.action', ['project_id' => $project->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
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
