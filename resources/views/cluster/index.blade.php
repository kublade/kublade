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
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Clusters') }}
                    <a href="{{ route('cluster.add', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                </div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr class="align-middle">
                                <th class="w-100" scope="col">{{ __('Cluster') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clusters as $cluster)
                                <tr class="align-middle">
                                    <td class="w-100">{{ $cluster->name }}</td>
                                    <td></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('cluster.update', ['project_id' => $cluster->project_id, 'cluster_id' => $cluster->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                            <a href="{{ route('cluster.delete.action', ['project_id' => $cluster->project_id, 'cluster_id' => $cluster->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
