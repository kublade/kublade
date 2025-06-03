@props([
    'available' => true,
    'mode' => 'ask',
    'type' => '',
    'action' => '',
    'path' => '',
    'content' => null,
    'templateId' => null
])

@if($available)
    <div class="alert alert-secondary mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary">{{ $action }}</span>
            <strong class="text-secondary">{{ $path }}</strong>
        </div>
        @if($content)
            <pre class="mb-0 mt-3 border border-secondary rounded p-3 overflow-hidden overflow-x-auto bg-light">{{ $content }}</pre>
            @if($mode === 'agent')
                <form action="{{ route('ai.tool.action') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="action" value="{{ $action }}">
                    <input type="hidden" name="path" value="{{ $path }}">
                    <textarea name="content" class="d-none">{!! $content !!}</textarea>
                    <input type="hidden" name="template_id" value="{{ $templateId }}">
                    <button type="submit" class="btn btn-primary mt-3"{{ !$templateId ? ' disabled' : '' }}>{{ __('Apply') }}</button>
                </form>
            @endif
        @endif
    </div>
@else
    <div class="alert alert-warning mb-0">
        <strong>{{ __('Tool not available') }}</strong>
    </div>
@endif
