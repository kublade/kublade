@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Add port') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.port.add.action', ['template_id' => $template->id]) }}">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">

                        <div class="row mb-3">
                            <label for="template" class="col-md-4 col-form-label text-md-end">{{ __('Template') }}</label>

                            <div class="col-md-6">
                                <input id="template" type="text" class="form-control" value="{{ $template->name }}" required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="group" class="col-md-4 col-form-label text-md-end">{{ __('Group') }}</label>

                            <div class="col-md-6">
                                <input id="group" type="text" class="form-control @error('group') is-invalid @enderror" name="group" value="{{ old('group') }}" required>

                                @error('group')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="claim" class="col-md-4 col-form-label text-md-end">{{ __('Claim') }}</label>

                            <div class="col-md-6">
                                <input id="claim" type="text" class="form-control @error('claim') is-invalid @enderror" name="claim" value="{{ old('claim') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="preferred_port" class="col-md-4 col-form-label text-md-end">{{ __('Preferred port') }}</label>

                            <div class="col-md-6">
                                <input id="preferred_port" type="number" class="form-control @error('preferred_port') is-invalid @enderror" name="preferred_port" value="{{ old('preferred_port') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="random" class="col-md-4 col-form-label text-md-end">{{ __('Random') }}</label>

                            <div class="col-md-6">
                                <input id="random" type="checkbox" class="form-check-input @error('random') is-invalid @enderror" name="random" value="1" {{ old('random') ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
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
