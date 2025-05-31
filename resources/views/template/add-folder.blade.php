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
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header">{{ __('Add folder') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.folder.add.action', ['template_id' => $template->id]) }}">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $folder?->id }}">

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
