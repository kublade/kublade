<li>
    <a href="{{ route('template.details_file', ['template_id' => $template->id, 'file_id' => $structure->id]) }}">
        <i class="bi bi-file-earmark"></i> {{ $structure->name }}
        <div class="ms-auto">
            <button class="btn btn-sm btn-secondary text-white p-1 lh-1">
                <i class="bi bi-trash file-tree-action"></i>
            </button>
        </div>
    </a>
</li>