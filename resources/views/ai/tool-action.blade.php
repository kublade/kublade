@props([
    'available' => true,
    'mode' => 'ask',
    'type' => '',
    'action' => '',
    'path' => '',
    'group' => '',
    'claim' => '',
    'preferred_port' => '',
    'random' => '',
    'field_type' => '',
    'advanced' => false,
    'required' => false,
    'secret' => false,
    'label' => '',
    'key' => '',
    'value' => '',
    'min' => '',
    'max' => '',
    'step' => '',
    'set_on_create' => false,
    'set_on_update' => false,
    'options' => collect(),
    'content' => null,
    'group' => null,
    'claim' => null,
    'preferred_port' => null,
    'random' => false,
    'templateId' => null
])

@if($available)
    @if($type === 'template_file' || $type === 'template_folder')
        <div class="alert alert-secondary my-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">{{ $action }}</span>
                <span class="badge bg-secondary">{{ $type === 'template_file' ? __('File') : __('Folder') }}</span>
                <strong class="text-secondary">{{ Illuminate\Support\Str::startsWith($path, '/') ? $path : '/' . $path }}</strong>
            </div>
            @if($content)
                <pre class="mb-0 mt-3 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $content }}</pre>
            @endif
            @if($mode === 'agent')
                <form action="{{ route('ai.tool.action') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="action" value="{{ $action }}">
                    <input type="hidden" name="path" value="{{ Illuminate\Support\Str::startsWith($path, '/') ? $path : '/' . $path }}">
                    <textarea name="content" class="d-none">{!! $content !!}</textarea>
                    <input type="hidden" name="template_id" value="{{ $templateId }}">
                    <button type="submit" class="btn btn-primary mt-3"{{ !$templateId ? ' disabled' : '' }}>{{ __('Apply') }}</button>
                </form>
            @endif
        </div>
    @elseif($type === 'template_port')
        <div class="alert alert-secondary my-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">{{ $action }}</span>
                <span class="badge bg-secondary">{{ __('Port') }}</span>
            </div>
            <div class="d-flex flex-column gap-1 lh-1 align-items-start">
                <span class="d-flex align-items-center justify-content-between w-100 gap-2 mt-3">{{ __('Group') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $group ?? __('N/A') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Claim') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $claim ?? __('default') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Preferred port') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $preferred_port ?? __('Any') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Random') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $random ? __('Yes') : __('No') }}</pre></span>
            </div>
            @if($mode === 'agent')
                <form action="{{ route('ai.tool.action') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="action" value="{{ $action }}">
                    <input type="hidden" name="group" value="{{ $group }}">
                    <input type="hidden" name="claim" value="{{ $claim }}">
                    <input type="hidden" name="preferred_port" value="{{ $preferred_port }}">
                    <input type="hidden" name="random" value="{{ $random }}">
                    <input type="hidden" name="template_id" value="{{ $templateId }}">
                    <button type="submit" class="btn btn-primary mt-3"{{ !$templateId ? ' disabled' : '' }}>{{ __('Apply') }}</button>
                </form>
            @endif
        </div>
    @elseif($type === 'template_field')
        <div class="alert alert-secondary my-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">{{ $action }}</span>
                <span class="badge bg-secondary">{{ __('Field') }}</span>
            </div>
            <div class="d-flex flex-column gap-1 lh-1 align-items-start">
                <span class="d-flex align-items-center justify-content-between w-100 gap-2 mt-3">{{ __('Advanced') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $advanced ? __('Yes') : __('No') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Type') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $field_type ?? __('N/A') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Required') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $required ? __('Yes') : __('No') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Secret') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $secret ? __('Yes') : __('No') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Label') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $label ?? __('N/A') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Key') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $key ?? __('N/A') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Value') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $value ?? __('N/A') }}</pre></span>
                @if($field_type === 'input_number' || $field_type === 'input_range')
                    <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Min') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $min ?? __('N/A') }}</pre></span>
                    <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Max') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $max ?? __('N/A') }}</pre></span>
                    <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Step') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $step ?? __('N/A') }}</pre></span>
                @endif
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Set on create') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $set_on_create ? __('Yes') : __('No') }}</pre></span>
                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ __('Set on update') }}: <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $set_on_update ? __('Yes') : __('No') }}</pre></span>
                @if($field_type === 'input_radio' || $field_type === 'input_radio_image' || $field_type === 'select')
                    @if($options->isNotEmpty())
                        <span class="d-flex align-items-center justify-content-between w-100 gap-2 text-secondary mt-3 mb-2">{{ __('Options') }}</span>
                        <div class="d-flex flex-column gap-1 lh-1 align-items-start w-100">
                            @foreach($options as $option)
                                <span class="d-flex align-items-center justify-content-between w-100 gap-2">{{ $option['label'] }}: <span class="d-flex align-items-center gap-1">{!! $option['default'] ? '<pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">Default</pre>' : '' !!} <pre class="m-0 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $option['value'] }}</pre></span></span>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
            @if($mode === 'agent')
                <form action="{{ route('ai.tool.action') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="action" value="{{ $action }}">
                    <input type="hidden" name="field[advanced]" value="{{ $advanced }}">
                    <input type="hidden" name="field[type]" value="{{ $field_type }}">
                    <input type="hidden" name="field[required]" value="{{ $required }}">
                    <input type="hidden" name="field[secret]" value="{{ $secret }}">
                    <input type="hidden" name="field[label]" value="{{ $label }}">
                    <input type="hidden" name="field[key]" value="{{ $key }}">
                    <input type="hidden" name="field[value]" value="{{ $value }}">
                    @if($field_type === 'input_number' || $field_type === 'input_range')
                        <input type="hidden" name="field[min]" value="{{ $min }}">
                        <input type="hidden" name="field[max]" value="{{ $max }}">
                        <input type="hidden" name="field[step]" value="{{ $step }}">
                    @endif
                    <input type="hidden" name="field[set_on_create]" value="{{ $set_on_create }}">
                    <input type="hidden" name="field[set_on_update]" value="{{ $set_on_update }}">
                    @if($field_type === 'input_radio' || $field_type === 'input_radio_image' || $field_type === 'select')
                        @foreach($options as $index => $option)
                            <input type="hidden" name="field[options][{{ $index }}][label]" value="{{ $option['label'] }}">
                            <input type="hidden" name="field[options][{{ $index }}][value]" value="{{ $option['value'] }}">
                            <input type="hidden" name="field[options][{{ $index }}][default]" value="{{ $option['default'] }}">
                        @endforeach
                    @endif
                    <input type="hidden" name="template_id" value="{{ $templateId }}">
                    <button type="submit" class="btn btn-primary mt-3"{{ !$templateId ? ' disabled' : '' }}>{{ __('Apply') }}</button>
                </form>
            @endif
        </div>
    @endif
@else
    <div class="alert alert-warning d-flex align-items-center gap-3 my-3">
        <i class="bi bi-exclamation-triangle fs-5"></i>
        {{ __('Tool not available.') }}
    </div>
@endif
