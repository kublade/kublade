<ul class="file-tree">
    <li>
        <span>
            <i class="bi bi-folder"></i> {{ $template->name }}
        </span>
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