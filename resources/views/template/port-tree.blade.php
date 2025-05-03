@if ($template->ports->isEmpty())
    <div class="alert alert-warning mb-0 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle fs-5"></i>
        {{ __('No ports defined') }}
    </div>
@else
    <ul class="field-tree">
        @foreach ($template->ports as $port)
            <li class="d-flex justify-content-between align-items-start flex-row file-tree-li">
                <span class="d-flex align-items-center gap-3">
                    <i class="bi bi-ethernet"></i>
                    <div class="d-flex flex-column lh-1 align-items-start">
                        {{ $port->group }}
                        <span class="text-muted">{{ $port->claim ?? __('default') }}:{{ $port->preferred_port ?? __('any') }}</span>
                    </div>
                </span>
                <div class="file-tree-li-actions">
                    <a href="{{ route('template.port.update.action', ['template_id' => $template->id, 'port_id' => $port->id]) }}" class="btn btn-sm btn-warning text-white p-1 lh-1">
                        <i class="bi bi-pencil file-tree-action"></i>
                    </a>
                    <a href="{{ route('template.port.delete.action', ['template_id' => $template->id, 'port_id' => $port->id]) }}" class="btn btn-sm btn-danger text-white p-1 lh-1">
                        <i class="bi bi-trash file-tree-action"></i>
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
@endif
