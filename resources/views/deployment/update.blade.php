@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('deployment.details', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Update deployment') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('deployment.update.action', ['project_id' => request()->get('project')->id, 'deployment_id' => $deployment->id]) }}">
                        @csrf
                        <input type="hidden" name="deployment_id" value="{{ $deployment->id }}">
                        <input type="hidden" name="template_id" value="{{ $deployment->template->id }}">

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? $deployment->name }}" required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="template" class="col-md-4 col-form-label text-md-end">{{ __('Template') }}</label>

                            <div class="col-md-6">
                                <input id="template" type="text" class="form-control" value="{{ $deployment->template->name }}" required readonly>
                            </div>
                        </div>

                        <div class="border rounded py-4 mb-3 fields" id="fields{{ $deployment->template->id }}">
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('Fields') }}</h5>
                                </div>
                            </div>

                            @foreach ($deployment->template->fields as $field)
                                @if ($field->set_on_update)
                                    @if ($field->secret)
                                        @php
                                            $value = $deployment->deploymentSecretData->where('key', $field->key)->first()->value;
                                        @endphp
                                    @else
                                        @php
                                            $value = $deployment->deploymentData->where('key', $field->key)->first()->value;
                                        @endphp
                                    @endif

                                    <div class="row mb-3">
                                        @if (! in_array($field->type, [
                                            'input_checkbox',
                                            'input_hidden',
                                        ]))
                                            <label class="col-md-4 col-form-label text-md-end" for="input_{{ $field->id }}">{{ __($field->label) }}{{ $field->required ? ' *' : '' }}</label>
                                        @endif
                                        <div class="col-md-6 d-flex align-items-center{{ in_array($field->type, [
                                            'input_checkbox',
                                            'input_hidden',
                                        ]) ? ' offset-md-4' : '' }}">
                                            @switch ($field->type)
                                                @case ('input_text')
                                                    <input type="text" class="form-control" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $value ?? $field->value }}">
                                                    @break
                                                @case ('input_number')
                                                    <input type="number" class="form-control" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $value ?? $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                    @break
                                                @case ('input_range')
                                                    <div class="range-container" id="range_{{ $field->id }}">
                                                        <input type="range" class="form-range" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $value ?? (! empty($field->defaultOption) ? $field->defaultOption->value : $field->value) }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                        <div class="ruler" id="input_{{ $field->id }}_ruler"></div>
                                                    </div>
                                                    @break
                                                @case ('input_radio')
                                                    <div id="input_{{ $field->id }}">
                                                        @foreach ($field->options as $option)
                                                            <div class="form-group d-flex gap-2 align-items-center">
                                                                <input id="{{ $field->key }}{{ $option->id }}" type="radio" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" value="{{ $option->value }}" {{ $value === $option->value || $value === null && $option->default ? ' checked' : '' }}>
                                                                <label for="{{ $field->key }}{{ $option->id }}" class="col-form-label text-md-left p-0">{{ __($option->label) }}</label>
                                                            </div>
                                                            @error($field->key)
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        @endforeach
                                                    </div>
                                                    @break
                                                @case ('input_radio_image')
                                                    <div id="input_{{ $field->id }}">
                                                        @foreach ($field->options as $option)
                                                            <div class="form-group d-flex gap-2 align-items-center">
                                                                <input id="{{ $field->key }}" type="radio" class="form-check-input mt-0 has-image @error($field->key) is-invalid @enderror" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" value="{{ $option->value }}" {{ $value === $option->value || $value === null && $option->default ? ' checked' : '' }}>
                                                                <img src="{{ $option->label }}" class="radio-image">
                                                            </div>
                                                            @error($field->key)
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        @endforeach
                                                    </div>
                                                    @break
                                                @case ('input_checkbox')
                                                    <div class="form-group d-flex gap-2 align-items-center" id="input_{{ $field->id }}">
                                                        <input id="{{ $field->key }}" type="checkbox" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" value="{{ $value ?? $field->value }}"{{ $value === $field->value ? ' checked' : '' }}>
                                                        <label for="{{ $field->key }}" class="col-form-label text-md-left p-0">{{ __($field->label) }} {{ $field->required ? '*' : '' }}</label>
                                                    </div>
                                                    @error($field->key)
                                                    <span class="invalid-feedback d-block mb-3" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                    @break
                                                @case ('input_hidden')
                                                    <input type="hidden" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" value="{{ $value ?? $field->value }}">
                                                    @break
                                                @case ('select')
                                                    <select class="form-control" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]">
                                                        @foreach ($field->options as $option)
                                                            <option value="{{ $option->value }}"{{ $value === $option->value || $value === null && $option->default ? ' selected' : '' }}>{{ $option->label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @break
                                                @case ('textarea')
                                                    <textarea class="form-control" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}">{{ $value ?? $field->value }}</textarea>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                @endif
                            @endforeach
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
    function generateRuler(rangeId, rulerId, containerId) {
        const range = document.getElementById(rangeId);
        const ruler = document.getElementById(rulerId);

        const min = parseInt(range.min, 10);
        const max = parseInt(range.max, 10);
        const step = parseInt(range.step, 10);

        const numTicks = Math.floor((max - min) / step) + 1;

        $('#' + rangeId).css('--ticks', numTicks);
        $('#' + containerId).css('--ticks', numTicks);

        ruler.innerHTML = '';

        for (let i = 0; i < numTicks; i++) {
            const value = min + (i * step);
            const tick = document.createElement('div');
            tick.classList.add('tick');
            ruler.appendChild(tick);

            if (i === 0 || i === numTicks - 1) {
                tick.setAttribute('data-value', value);
            }
        }
    }

    $(document).ready(function() {
        $('#template_id').change(function() {
            $('.fields').hide();
            $('#fields' + $(this).val()).show();

            $('#submit').prop('disabled', $('#template_id').val() == '');
        });

        $('.range-container').each(function() {
            generateRuler($(this).find('input').attr('id'), $(this).find('.ruler').attr('id'), $(this).attr('id'));
        });
    });
</script>
@endsection
