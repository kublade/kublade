@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('template.details', ['template_id' => $template->id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Add file') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.file.add.action', ['template_id' => $template->id]) }}">
                        @csrf
                        <input type="hidden" name="template_directory_id" value="{{ $folder?->id }}">

                        <div class="row mb-3">
                            <label for="template_directory" class="col-md-4 col-form-label text-md-end">{{ __('Folder') }}</label>

                            <div class="col-md-6">
                                <input id="template_directory" type="text" class="form-control" value="{{ $folder?->path ?? '/' }}" required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="mime_type" class="col-md-4 col-form-label text-md-end">{{ __('Mime type') }}</label>

                            <div class="col-md-6">
                                <input id="mime_type" type="text" class="form-control @error('mime_type') is-invalid @enderror" name="mime_type" value="{{ old('mime_type') ?? 'text/yaml' }}" required readonly>

                                @error('mime_type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
