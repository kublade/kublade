<li class="d-flex flex-column gap-2">
    <div class="d-flex justify-content-between align-items-start flex-row file-tree-li gap-1">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-folder"></i> {{ $structure->name }}
        </span>
    </div>
    @if ($structure->children->count() > 0)
        <ul class="file-tree file-tree--child d-flex flex-column gap-2">
            @foreach ($structure->children as $child)
                @if ($child->type === 'folder')
                    @include('deployment.file-tree-folder', ['deployment' => $deployment, 'template' => $template, 'structure' => $child])
                @elseif ($child->type === 'file')
                    @include('deployment.file-tree-file', ['deployment' => $deployment, 'template' => $template, 'structure' => $child])
                @endif
            @endforeach
        </ul>
    @endif
</li>
