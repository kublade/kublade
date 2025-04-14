@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @if (!empty($template))
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{ __('Files') }}
                    </div>
                    <div class="card-body">
                        @include('template.file-tree', ['template' => $template])
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ __('Fields') }}</span>
                        <a href="{{ route('template.field.add', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @include('template.field-tree', ['template' => $template])
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ __('Ports') }}</span>
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        
                    </div>
                </div>
            </div>
        @endif
        <div class="{{ !empty($template) ? 'col-md-8' : 'col-md-12' }}">
            <div class="card">
                @if (!empty($template))
                    @if (!empty($file))
                        <div class="card-header d-flex justify-content-between align-items-center">
                            {{ $file->path }}
                        </div>
                    @endif
                @endif
                <div class="card-body{{ !empty($file) ? ' p-0 overflow-hidden rounded' : '' }}">
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
                            <thead>
                                <tr>
                                    <th class="w-100" scope="col">{{ __('Template') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                    <tr>
                                        <td class="w-100">{{ $template->name }}</td>
                                        <td class="d-flex gap-2">
                                            <a href="{{ route('template.details', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('template.update', ['template_id' => $template->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                            <a href="{{ route('template.delete.action', ['template_id' => $template->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
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
