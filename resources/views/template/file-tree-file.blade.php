<li class="d-flex justify-content-between align-items-start flex-row file-tree-li">
    <a href="{{ route('template.details_file', ['template_id' => $template->id, 'file_id' => $structure->id]) }}" class="d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark"></i> {{ $structure->name }}
    </a>
    <div class="file-tree-li-actions">
        <a href="{{ route('template.file.update', ['template_id' => $template->id, 'file_id' => $structure->id]) }}" class="btn btn-sm btn-warning text-white p-1 lh-1" title="{{ __('Update') }}">
            <i class="bi bi-pencil file-tree-action"></i>
        </a>
        <a href="{{ route('template.file.delete.action', ['template_id' => $template->id, 'file_id' => $structure->id]) }}" class="btn btn-sm btn-danger text-white p-1 lh-1" title="{{ __('Delete') }}">
            <i class="bi bi-trash file-tree-action"></i>
        </a>
    </div>
</li>
