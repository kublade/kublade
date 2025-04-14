@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
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
