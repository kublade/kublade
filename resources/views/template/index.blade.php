@extends('layouts.app')

@section('content')
<div class="container">
    @if (!empty($template))
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="{{ route('template.index') }}" class="btn btn-sm btn-secondary text-white">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
        @if ($template->gitCredentials)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="alert alert-secondary mb-0 d-flex align-items-center gap-3">
                        <i class="bi bi-git fs-5"></i>
                        {{ __('This template is synced from a Git repository. Changing the template manually may result in unexpected behavior!') }}
                    </div>
                </div>
            </div>
        @endif
    @endif
    <div class="row justify-content-center">
        @if (!empty($template))
            <div class="col-md-4">
                <div class="card mb-3 border border-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Files') }}
                    </div>
                    <div class="card-body">
                        @include('template.file-tree', ['template' => $template])
                    </div>
                </div>
                <div class="card mb-3 border border-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ __('Fields') }}</span>
                        <a href="{{ route('template.field.add', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary" title="{{ __('Add') }}">
                            <i class="bi bi-plus"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @include('template.field-tree', ['template' => $template])
                    </div>
                </div>
                <div class="card border border-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ __('Ports') }}</span>
                        <a href="{{ route('template.port.add', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary" title="{{ __('Add') }}">
                            <i class="bi bi-plus"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @include('template.port-tree', ['template' => $template])
                    </div>
                </div>
            </div>
        @endif
        <div class="{{ !empty($template) ? 'col-md-8' : 'col-md-12' }}">
            <div class="card border border-secondary">
                @if (!empty($template))
                    @if (!empty($file))
                        <div class="card-header d-flex justify-content-between align-items-center">
                            {{ $file->path }}
                        </div>
                    @endif
                @else
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Templates') }}
                        <div class="d-flex gap-2">
                            <a href="{{ route('template.import') }}" class="btn btn-sm btn-primary" title="{{ __('Import') }}">
                                <i class="bi bi-download"></i>
                            </a>
                            <a href="{{ route('template.sync') }}" class="btn btn-sm btn-secondary" title="{{ __('Sync') }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                            <a href="{{ route('template.add') }}" class="btn btn-sm btn-primary" title="{{ __('Add') }}">
                                <i class="bi bi-plus"></i>
                            </a>
                        </div>
                    </div>
                @endif
                <div class="card-body{{ !empty($file) ? ' p-0 pt-3 overflow-hidden rounded' : '' }}{{ empty($template) ? ' d-flex flex-column gap-4 p-0' : '' }}">
                    @if (!empty($template))
                        @if (!empty($file))
                            <form action="{{ route('template.file.update.action', ['template_id' => $template->id, 'file_id' => $file->id]) }}" method="POST">
                                @csrf
                                @include('template.editor', ['template' => $template, 'file' => $file])
                                <input type="hidden" name="name" value="{{ $file->name }}">
                                <input type="hidden" name="template_directory_id" value="{{ $file->template_directory_id }}">
                                <input type="hidden" name="mime_type" value="{{ $file->mime_type }}">
                                <div class="d-flex p-3">
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </form>
                        @else
                            {{ __('No file selected.') }}
                        @endif
                    @else
                        <table class="table">
                            <thead class="font-monospace">
                                <tr class="align-middle">
                                    <th class="w-100" scope="col">{{ __('Template') }}</th>
                                    <th scope="col">{{ __('Type') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                    <tr class="align-middle">
                                        <td class="w-100">{{ $template->name }}</td>
                                        <td>
                                            @if ($template->gitCredentials)
                                                <span class="badge bg-secondary">{{ __('Git') }}</span>
                                            @else
                                                <span class="badge bg-primary">{{ __('Local') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($template->gitCredentials)
                                                @if ($template->gitCredentials->synced_at)
                                                    <span class="badge bg-success">{{ __('Available') }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ __('Syncing') }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-success">{{ __('Available') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('template.details', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary" title="{{ __('View') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('template.update', ['template_id' => $template->id]) }}" class="btn btn-sm btn-warning" title="{{ __('Update') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ route('template.delete.action', ['template_id' => $template->id]) }}" class="btn btn-sm btn-danger" title="{{ __('Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $templates->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
