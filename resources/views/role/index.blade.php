@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Roles') }}
                    <a href="{{ route('role.add') }}" class="btn btn-sm btn-primary" title="{{ __('Add') }}">
                        <i class="bi bi-plus"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead class="font-monospace">
                            <tr class="align-middle">
                                <th class="w-100" scope="col">{{ __('Role') }}</th>
                                <th scope="col">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr class="align-middle">
                                    <td class="w-100">{{ $role->name }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('role.update', ['role_id' => $role->id]) }}" class="btn btn-sm btn-warning" title="{{ __('Update') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('role.delete.action', ['role_id' => $role->id]) }}" class="btn btn-sm btn-danger" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $roles->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

