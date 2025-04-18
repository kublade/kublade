<ul class="file-tree">
    <li>
        <div class="d-flex justify-content-between align-items-start flex-row file-tree-li">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-folder"></i> {{ $template->name }}
            </span>
            <div class="file-tree-li-actions">
                <a href="{{ route('template.file.add', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary p-1 lh-1">
                    <i class="bi bi-file-earmark-plus file-tree-action"></i>
                </a>
                <a href="{{ route('template.folder.add', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary p-1 lh-1">
                    <i class="bi bi-folder-plus file-tree-action"></i>
                </a>
            </div>
        </div>
        <ul class="file-tree file-tree--child">
            @foreach ($template->tree as $tree)
                @if ($tree->type === 'folder')
                    @include('template.file-tree-folder', ['template' => $template, 'structure' => $tree])
                @elseif ($tree->type === 'file')
                    @include('template.file-tree-file', ['template' => $template, 'structure' => $tree])
                @endif
            @endforeach
        </ul>
    </li>
</ul>