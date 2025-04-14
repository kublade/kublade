<ul class="field-tree">
    @foreach ($template->fields as $field)
        <li class="d-flex justify-content-between align-items-start flex-row file-tree-li">
            <span class="d-flex align-items-center gap-2">
                @if ($field->secret)
                    <i class="bi bi-shield-shaded"></i>
                @else
                    <i class="bi bi-code-square"></i>
                @endif
                <div class="d-flex flex-column lh-1">
                    {{ $field->label ?? __('N/A') }}
                    <span class="text-muted">{{ $field->key }}</span>
                </div>
            </span>
            <div class="file-tree-li-actions">
                <a href="{{ route('template.field.update.action', ['template_id' => $template->id, 'field_id' => $field->id]) }}" class="btn btn-sm btn-warning text-white p-1 lh-1">
                    <i class="bi bi-pencil file-tree-action"></i>
                </a>
                <a href="{{ route('template.field.delete.action', ['template_id' => $template->id, 'field_id' => $field->id]) }}" class="btn btn-sm btn-danger text-white p-1 lh-1">
                    <i class="bi bi-trash file-tree-action"></i>
                </a>
            </div>
        </li>
    @endforeach
</ul>