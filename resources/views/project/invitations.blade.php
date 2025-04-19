@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Invitations') }}</div>

                <div class="card-body">
                    @if (!empty($invitations))
                        <table class="table">
                            <thead>
                                <tr class="align-middle">
                                    <th class="w-100" scope="col">{{ __('Project') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invitations as $invitation)
                                    <tr class="align-middle">
                                        <td class="w-100">{{ $invitation->project->name }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('project.invitation.accept.action', ['project_id' => $invitation->project_id, 'project_invitation_id' => $invitation->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-check"></i></a>
                                                <a href="{{ route('project.invitation.delete.action', ['project_id' => $invitation->project_id, 'project_invitation_id' => $invitation->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
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
