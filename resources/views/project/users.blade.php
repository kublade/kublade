@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Users') }}
                    <a href="{{ route('project.invitation.create', ['project_id' => request()->project_id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                </div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-100" scope="col">{{ __('User') }}</th>
                                <th scope="col">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $invitation)
                                <tr>
                                    <td class="w-100">{{ $invitation->user->name }}</td>
                                    <td class="d-flex gap-2">
                                        <a href="{{ route('project.invitation.delete.action', ['project_id' => $invitation->project_id, 'project_invitation_id' => $invitation->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
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
