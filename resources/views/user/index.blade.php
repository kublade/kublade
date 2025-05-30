@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Users') }}
                    <a href="{{ route('user.add') }}" class="btn btn-sm btn-primary" title="{{ __('Add') }}">
                        <i class="bi bi-plus"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead class="font-monospace">
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Roles') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                                    <td>
                                        <a href="{{ route('user.update', $user->id) }}" class="btn btn-sm btn-warning" title="{{ __('Update') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('user.delete.action', $user->id) }}" class="btn btn-sm btn-danger{{ Auth::id() === $user->id ? ' disabled' : '' }}" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </a> 
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

