@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('deployment.index', ['project_id' => request()->get('project')->id]) }}" class="btn btn-sm btn-secondary text-white">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header">{{ __('Add deployment') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('deployment.add.action', ['project_id' => request()->get('project')->id]) }}">
                        @csrf

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
                            <label for="cluster_id" class="col-md-4 col-form-label text-md-end">{{ __('Cluster') }}</label>

                            <div class="col-md-6">
                                <select id="cluster_id" class="form-control @error('cluster_id') is-invalid @enderror" name="cluster_id">
                                    <option value="">{{ __('Select a cluster...') }}</option>
                                    @foreach ($clusters as $cluster)
                                        <option value="{{ $cluster->id }}"{{ old('cluster_id') == $cluster->id ? ' selected' : '' }}>{{ $cluster->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="template_id" class="col-md-4 col-form-label text-md-end">{{ __('Template') }}</label>

                            <div class="col-md-6">
                                <select id="template_id" class="form-control @error('template_id') is-invalid @enderror" name="template_id">
                                    <option value="">{{ __('Select a template...') }}</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}"{{ old('template_id') == $template->id ? ' selected' : '' }}>{{ $template->name }}</option>
                                    @endforeach
                                </select>

                                @error('template_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        @foreach ($templates as $template)
                            @if ($template->groupedFields->on_create->default->count() > 0 || $template->groupedFields->on_create->advanced->count() > 0)
                                <div class="border rounded py-4 mb-3 fields" id="fields{{ $template->id }}" style="display: none">
                                    <div class="row{{ $template->groupedFields->on_create->default->count() > 0 ? ' mb-3' : '' }}">
                                        <div class="col-md-6 offset-md-4">
                                            <h5 class="{{ $template->groupedFields->on_create->default->count() === 0 ? 'mb-0' : '' }}">{{ __('Configuration') }}</h5>
                                        </div>
                                    </div>

                                    @if ($template->groupedFields->on_create->default->count() > 0)
                                        @foreach ($template->groupedFields->on_create->default as $field)
                                            <div class="row mb-3">
                                                @if ($field->type !== 'input_checkbox')
                                                    <label class="col-md-4 col-form-label text-md-end" for="input_{{ $field->id }}">{{ __($field->label) }}{{ $field->required ? ' *' : '' }}</label>
                                                @endif
                                                <div class="col-md-6 d-flex align-items-center{{ $field->type === 'input_checkbox' ? ' offset-md-4' : '' }}">
                                                    @switch ($field->type)
                                                        @case ('input_text')
                                                            <input type="text" class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $field->value }}">
                                                            @break
                                                        @case ('input_number')
                                                            <input type="number" class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                            @break
                                                        @case ('input_range')
                                                            <div class="range-container" id="range_{{ $field->id }}">
                                                                <input type="range" class="form-range" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ ! empty($field->defaultOption) ? $field->defaultOption->value : $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                                <div class="ruler" id="input_{{ $field->id }}_ruler"></div>
                                                            </div>
                                                            @break
                                                        @case ('input_radio')
                                                            <div id="input_{{ $field->id }}">
                                                                @foreach ($field->options as $option)
                                                                    <div class="form-group d-flex gap-2 align-items-center">
                                                                        <input id="{{ $field->key }}{{ $option->id }}" type="radio" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $option->value }}" {{ $option->default ? ' checked' : '' }}>
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
                                                                        <input id="{{ $field->key }}" type="radio" class="form-check-input mt-0 has-image @error($field->key) is-invalid @enderror" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $option->value }}" {{ $option->default ? ' checked' : '' }}>
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
                                                                <input id="{{ $field->key }}" type="checkbox" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $field->value }}">
                                                                <label for="{{ $field->key }}" class="col-form-label text-md-left p-0">{{ __($field->label) }} {{ $field->required ? '*' : '' }}</label>
                                                            </div>
                                                            @error($field->key)
                                                            <span class="invalid-feedback d-block mb-3" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                            @break
                                                        @case ('select')
                                                            <select class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]">
                                                                @foreach ($field->options as $option)
                                                                    <option value="{{ $option->value }}"{{ $option->default ? ' selected' : '' }}>{{ $option->label }}</option>
                                                                @endforeach
                                                            </select>
                                                            @break
                                                        @case ('textarea')
                                                            <textarea class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}">{{ $field->value }}</textarea>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if ($template->groupedFields->on_create->advanced->count() > 0)
                                        <div class="row mt-4">
                                            <div class="col-md-6 offset-md-4">
                                                <a href="#" data-bs-toggle="collapse" data-bs-target="#advancedFields{{ $template->id }}">
                                                    {{ __('Show advanced fields') }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="collapse" id="advancedFields{{ $template->id }}">
                                            @foreach ($template->groupedFields->on_create->advanced as $field)
                                                <div class="row my-3">
                                                    @if ($field->type !== 'input_checkbox')
                                                        <label class="col-md-4 col-form-label text-md-end" for="input_{{ $field->id }}">{{ __($field->label) }}{{ $field->required ? ' *' : '' }}</label>
                                                    @endif
                                                    <div class="col-md-6 d-flex align-items-center{{ $field->type === 'input_checkbox' ? ' offset-md-4' : '' }}">
                                                        @switch ($field->type)
                                                            @case ('input_text')
                                                                <input type="text" class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $field->value }}">
                                                                @break
                                                            @case ('input_number')
                                                                <input type="number" class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                                @break
                                                            @case ('input_range')
                                                                <div class="range-container" id="range_{{ $field->id }}">
                                                                    <input type="range" class="form-range" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}" value="{{ ! empty($field->defaultOption) ? $field->defaultOption->value : $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                                    <div class="ruler" id="input_{{ $field->id }}_ruler"></div>
                                                                </div>
                                                                @break
                                                            @case ('input_radio')
                                                                <div id="input_{{ $field->id }}">
                                                                    @foreach ($field->options as $option)
                                                                        <div class="form-group d-flex gap-2 align-items-center">
                                                                            <input id="{{ $field->key }}{{ $option->id }}" type="radio" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $option->value }}" {{ $option->default ? ' checked' : '' }}>
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
                                                                            <input id="{{ $field->key }}" type="radio" class="form-check-input mt-0 has-image @error($field->key) is-invalid @enderror" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $option->value }}" {{ $option->default ? ' checked' : '' }}>
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
                                                                    <input id="{{ $field->key }}" type="checkbox" class="form-check-input mt-0 @error($field->key) is-invalid @enderror" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $field->value }}">
                                                                    <label for="{{ $field->key }}" class="col-form-label text-md-left p-0">{{ __($field->label) }} {{ $field->required ? '*' : '' }}</label>
                                                                </div>
                                                                @error($field->key)
                                                                <span class="invalid-feedback d-block mb-3" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                                @enderror
                                                                @break
                                                            @case ('select')
                                                                <select class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]">
                                                                    @foreach ($field->options as $option)
                                                                        <option value="{{ $option->value }}"{{ $option->default ? ' selected' : '' }}>{{ $option->label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @break
                                                            @case ('textarea')
                                                                <textarea class="form-control" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" placeholder="{{ $field->value }}">{{ $field->value }}</textarea>
                                                                @break
                                                        @endswitch
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @foreach ($template->groupedFields->on_create->hidden as $field)
                                <input type="hidden" id="input_{{ $field->id }}" name="data[{{ $template->id }}][{{ $field->key }}]" value="{{ $field->value }}">
                            @endforeach
                        @endforeach

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submit" disabled>
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
