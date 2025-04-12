<li>
    <span>
        <i class="bi bi-folder"></i> {{ $structure->name }}
        <div class="ms-auto">
            <button class="btn btn-sm btn-primary p-1 lh-1">
                <i class="bi bi-file-earmark-plus file-tree-action"></i>
            </button>
            <button class="btn btn-sm btn-primary p-1 lh-1">
                <i class="bi bi-folder-plus file-tree-action"></i>
            </button>
            <button class="btn btn-sm btn-secondary text-white p-1 lh-1">
                <i class="bi bi-trash file-tree-action"></i>
            </button>
        </div>
    </span>
    @if ($structure->children->count() > 0)
        <ul class="file-tree file-tree--child">
            @foreach ($structure->children as $child)
                @if ($child->type === 'folder')
                    @include('template.file-tree-folder', ['structure' => $child])
                @elseif ($child->type === 'file')
                    @include('template.file-tree-file', ['structure' => $child])
                @endif
            @endforeach
        </ul>
    @endif
</li>