@props([
    'available' => true,
    'mode' => 'ask',
    'type' => '',
    'action' => '',
    'path' => '',
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
    @endif
@else
    <div class="alert alert-warning mb-0">
        <strong>{{ __('Tool not available') }}</strong>
    </div>
@endif
