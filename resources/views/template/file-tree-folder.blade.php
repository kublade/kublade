<li>
    <div class="d-flex justify-content-between align-items-start flex-row file-tree-li">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-folder"></i> {{ $structure->name }}
        </span>
        <div class="file-tree-li-actions">
            <a href="{{ route('template.file.add', ['template_id' => $template->id, 'folder_id' => $structure->id]) }}" class="btn btn-sm btn-primary p-1 lh-1" title="{{ __('Add file') }}">
                <i class="bi bi-file-earmark-plus file-tree-action"></i>
            </a>
            <a href="{{ route('template.folder.add', ['template_id' => $template->id, 'folder_id' => $structure->id]) }}" class="btn btn-sm btn-primary p-1 lh-1" title="{{ __('Add folder') }}">
                <i class="bi bi-folder-plus file-tree-action"></i>
            </a>
            <a href="{{ route('template.folder.update', ['template_id' => $template->id, 'folder_id' => $structure->id]) }}" class="btn btn-sm btn-warning text-white p-1 lh-1" title="{{ __('Update') }}">
                <i class="bi bi-pencil file-tree-action"></i>
            </a>
            <a href="{{ route('template.folder.delete.action', ['template_id' => $template->id, 'folder_id' => $structure->id]) }}" class="btn btn-sm btn-danger text-white p-1 lh-1" title="{{ __('Delete') }}">
                <i class="bi bi-trash file-tree-action"></i>
            </a>
        </div>
    </div>
    @if ($structure->children->count() > 0)
        <ul class="file-tree file-tree--child">
            @foreach ($structure->children as $child)
                @if ($child->type === 'folder')
                    @include('template.file-tree-folder', ['template' => $template, 'structure' => $child])
                @elseif ($child->type === 'file')
                    @include('template.file-tree-file', ['template' => $template, 'structure' => $child])
                @endif
            @endforeach
        </ul>
    @endif
</li>
