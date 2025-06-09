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
            <div class="card border border-secondary">
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
                            <label for="cluster" class="col-md-4 col-form-label text-md-end">{{ __('Cluster') }}</label>

                            <div class="col-md-6">
                                <input id="cluster" type="text" class="form-control" value="{{ $deployment->cluster?->name }}" required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="template" class="col-md-4 col-form-label text-md-end">{{ __('Template') }}</label>

                            <div class="col-md-6">
                                <input id="template" type="text" class="form-control" value="{{ $deployment->template->name }}" required readonly>
                            </div>
                        </div>

                        @if ($deployment->template->groupedFields->on_update->default->count() > 0 || $deployment->template->groupedFields->on_update->advanced->count() > 0)
                            <div class="border rounded py-4 mb-3 fields" id="fields{{ $deployment->template->id }}">
                                <div class="row{{ $deployment->template->groupedFields->on_update->default->count() > 0 ? ' mb-3' : '' }}">
                                    <div class="col-md-6 offset-md-4">
                                        <h5 class="{{ $deployment->template->groupedFields->on_update->default->count() === 0 ? 'mb-0' : '' }}">{{ __('Configuration') }}</h5>
                                    </div>
                                </div>
                                
                                @if ($deployment->template->groupedFields->on_update->default->count() > 0)
                                    @foreach ($deployment->template->groupedFields->on_update->default as $field)
                                        @if ($field->secret)
                                            @php
                                                $value = $deployment->deploymentSecretData->where('key', $field->key)->first()?->value;
                                            @endphp
                                        @else
                                            @php
                                                $value = $deployment->deploymentData->where('key', $field->key)->first()?->value;
                                            @endphp
                                        @endif

                                        <div class="row mb-3">
                                            @if ($field->type !== 'input_checkbox')
                                                <label class="col-md-4 col-form-label text-md-end" for="input_{{ $field->id }}">{{ __($field->label) }}{{ $field->required ? ' *' : '' }}</label>
                                            @endif
                                            <div class="col-md-6 d-flex align-items-center{{ $field->type === 'input_checkbox' ? ' offset-md-4' : '' }}">
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
                                    @endforeach
                                @endif

                                @if ($deployment->template->groupedFields->on_update->advanced->count() > 0)
                                    <div class="row mt-4">
                                        <div class="col-md-6 offset-md-4">
                                            <a href="#" data-bs-toggle="collapse" data-bs-target="#advancedFields{{ $deployment->template->id }}">
                                                {{ __('Show advanced fields') }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="collapse" id="advancedFields{{ $deployment->template->id }}">
                                        @foreach ($deployment->template->groupedFields->on_update->advanced as $field)
                                            @if ($field->secret)
                                                @php
                                                    $value = $deployment->deploymentSecretData->where('key', $field->key)->first()->value;
                                                @endphp
                                            @else
                                                @php
                                                    $value = $deployment->deploymentData->where('key', $field->key)->first()->value;
                                                @endphp
                                            @endif

                                            <div class="row my-3">
                                                @if ($field->type !== 'input_checkbox')
                                                    <label class="col-md-4 col-form-label text-md-end" for="input_{{ $field->id }}">{{ __($field->label) }}{{ $field->required ? ' *' : '' }}</label>
                                                @endif
                                                <div class="col-md-6 d-flex align-items-center{{ $field->type === 'input_checkbox' ? ' offset-md-4' : '' }}">
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
                                                                <input id="{{ $field->key }}" type="checkbox" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" value="{{ $field->value }}"{{ $value === $field->value ? ' checked' : '' }}>
                                                                <label for="{{ $field->key }}" class="col-form-label text-md-left p-0">{{ __($field->label) }} {{ $field->required ? '*' : '' }}</label>
                                                            </div>
                                                            @error($field->key)
                                                            <span class="invalid-feedback d-block mb-3" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
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
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        @foreach ($deployment->template->groupedFields->on_update->hidden as $field)
                            <input type="hidden" id="input_{{ $field->id }}" name="data[{{ $deployment->template->id }}][{{ $field->key }}]" value="{{ $value ?? $field->value }}">
                        @endforeach

                        <div class="border rounded py-4 mb-3 fields" id="limits{{ $deployment->template->id }}">
                            <div class="row mb-2">
                                <div class="col-md-6 offset-md-4">
                                    <h5>{{ __('Limits') }}</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="limit_is_active" class="col-md-4 col-form-label text-md-end">{{ __('Enable') }}</label>

                                <div class="col-md-6 d-flex align-items-center">
                                    <input id="limit_is_active" type="checkbox" class="form-check-input" name="limit[is_active]" value="1" {{ $deployment->limit?->is_active ? 'checked' : '' }}>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="limit_memory" class="col-md-4 col-form-label text-md-end">{{ __('Memory') }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('limit.memory') is-invalid @enderror" id="limit_memory" name="limit[memory]" value="{{ $deployment->limit?->memory }}">
                                        <span class="input-group-text">{{ __('Bytes') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label for="limit_cpu" class="col-md-4 col-form-label text-md-end">{{ __('CPU') }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('limit.cpu') is-invalid @enderror" id="limit_cpu" name="limit[cpu]" value="{{ $deployment->limit?->cpu }}" step="0.001">
                                        <span class="input-group-text">{{ __('Cores') }}</span>
                                    </div>
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
