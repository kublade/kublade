<li class="d-flex justify-content-between align-items-start flex-row file-tree-li">
    <a href="{{ route('deployment.details', ['project_id' => $deployment->project_id, 'deployment_id' => $deployment->id, 'tab' => 'files', 'file_id' => $structure->id]) }}" class="d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark"></i> {{ $structure->name }}
    </a>
</li>
