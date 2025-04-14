@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Add field') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('template.field.add.action', ['template_id' => $template->id]) }}">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">

                        <div class="row mb-3">
                            <label for="template" class="col-md-4 col-form-label text-md-end">{{ __('Template') }}</label>

                            <div class="col-md-6">
                                <input id="template" type="text" class="form-control" value="{{ $template->name }}" required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="key" class="col-md-4 col-form-label text-md-end">{{ __('Type') }}</label>

                            <div class="col-md-6">
                                <select id="type" type="text" class="form-control @error('type') is-invalid @enderror" name="type">
                                    <option value="input_text"{{ old('type') == 'input_text' ? ' selected' : '' }}>{{ __('Text') }}</option>
                                    <option value="input_number"{{ old('type') == 'input_number' ? ' selected' : '' }}>{{ __('Number') }}</option>
                                    <option value="input_range"{{ old('type') == 'input_range' ? ' selected' : '' }}>{{ __('Range') }}</option>
                                    <option value="input_radio"{{ old('type') == 'input_radio' ? ' selected' : '' }}>{{ __('Radio') }}</option>
                                    <option value="input_radio_image"{{ old('type') == 'input_radio_image' ? ' selected' : '' }}>{{ __('Radio image') }}</option>
                                    <option value="input_checkbox"{{ old('type') == 'input_checkbox' ? ' selected' : '' }}>{{ __('Checkbox') }}</option>
                                    <option value="input_hidden"{{ old('type') == 'input_hidden' ? ' selected' : '' }}>{{ __('Hidden text') }}</option>
                                    <option value="select"{{ old('type') == 'select' ? ' selected' : '' }}>{{ __('Select') }}</option>
                                    <option value="textarea"{{ old('type') == 'textarea' ? ' selected' : '' }}>{{ __('Textarea') }}</option>
                                </select>

                                @error('type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="label" class="col-md-4 col-form-label text-md-end">{{ __('Label') }}</label>

                            <div class="col-md-6">
                                <input id="label" type="text" class="form-control @error('label') is-invalid @enderror" name="label" value="{{ old('label') }}">

                                @error('label')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="key" class="col-md-4 col-form-label text-md-end">{{ __('Key') }}</label>

                            <div class="col-md-6">
                                <input id="key" type="text" class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key') }}">

                                @error('key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="value" class="col-md-4 col-form-label text-md-end">{{ __('Value') }}</label>

                            <div class="col-md-6">
                                <input id="value" type="text" class="form-control @error('value') is-invalid @enderror" name="value" value="{{ old('value') }}">

                                @error('value')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="required" class="col-md-4 col-form-label text-md-end">{{ __('Required') }}</label>

                            <div class="col-md-6">
                                <input id="required" type="checkbox" class="form-check-input" name="required" value="1" {{ old('required') == '1' ? 'checked' : '' }}>

                                @error('required')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="secret" class="col-md-4 col-form-label text-md-end">{{ __('Secret') }}</label>

                            <div class="col-md-6">
                                <input id="secret" type="checkbox" class="form-check-input" name="secret" value="1" {{ old('secret') == '1' ? 'checked' : '' }}>

                                @error('secret')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="border rounded py-4 mb-3" id="options" style="display: none">
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('Options') }}</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="min" class="col-md-4 col-form-label text-md-end">{{ __('Min') }}</label>

                                <div class="col-md-6">
                                    <input id="min" type="number" class="form-control @error('min') is-invalid @enderror" name="min" value="{{ old('min') }}">

                                    @error('min')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label for="max" class="col-md-4 col-form-label text-md-end">{{ __('Max') }}</label>

                                <div class="col-md-6">
                                    <input id="max" type="number" class="form-control @error('max') is-invalid @enderror" name="max" value="{{ old('max') }}">

                                    @error('max')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="step" class="col-md-4 col-form-label text-md-end">{{ __('Step') }}</label>

                                <div class="col-md-6">
                                    <input id="step" type="number" class="form-control @error('step') is-invalid @enderror" name="step" value="{{ old('step') }}">

                                    @error('step')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
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

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#type').change(function() {
            $('#options').toggle($(this).val() === 'input_range' || $(this).val() === 'input_number');
        });
    });
</script>
@endsection
