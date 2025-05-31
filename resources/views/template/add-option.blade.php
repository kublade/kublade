@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('template.field.update', ['template_id' => $template->id, 'field_id' => $field->id]) }}" class="btn btn-sm btn-secondary text-white">
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
                <div class="card-header">{{ __('Add option') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.field.option.add.action', ['template_id' => $template->id, 'field_id' => $field->id]) }}">
                        @csrf
                        <input type="hidden" name="template_field_id" value="{{ $field->id }}">

                        <div class="row mb-3">
                            <label for="template" class="col-md-4 col-form-label text-md-end">{{ __('Template') }}</label>

                            <div class="col-md-6">
                                <input id="template" type="text" class="form-control" value="{{ $template->name }}" required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="field" class="col-md-4 col-form-label text-md-end">{{ __('Field') }}</label>

                            <div class="col-md-6">
                                <input id="field" type="text" class="form-control" value="{{ $field->label }} ({{ $field->key }})" required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="label" class="col-md-4 col-form-label text-md-end">{{ __('Label') }}</label>

                            <div class="col-md-6">
                                <input id="label" type="text" class="form-control" name="label" value="{{ old('label') }}">

                                @error('label')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="value" class="col-md-4 col-form-label text-md-end">{{ __('Value') }}</label>

                            <div class="col-md-6">
                                <input id="value" type="text" class="form-control" name="value" value="{{ old('value') }}">

                                @error('value')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="default" class="col-md-4 col-form-label text-md-end">{{ __('Default') }}</label>

                            <div class="col-md-6">
                                <input id="default" type="checkbox" class="form-check-input" name="default" value="1" {{ old('default') == '1' ? 'checked' : '' }}>
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
