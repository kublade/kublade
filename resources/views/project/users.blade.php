@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('project.details', ['project_id' => request()->project_id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Users') }}
                    <a href="{{ route('project.invitation.create', ['project_id' => request()->project_id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                </div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr class="align-middle">
                                <th class="w-100" scope="col">{{ __('User') }}</th>
                                <th scope="col">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $invitation)
                                <tr class="align-middle">
                                    <td class="w-100">{{ $invitation->user->name }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('project.invitation.delete.action', ['project_id' => $invitation->project_id, 'project_invitation_id' => $invitation->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $invitations->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
