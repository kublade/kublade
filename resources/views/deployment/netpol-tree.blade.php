<div class="d-flex flex-column gap-2">
    <div class="d-flex justify-content-between align-items-start flex-row file-tree-li">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-bricks"></i> {{ $deployment->template->name }}
        </span>
        <div class="file-tree-li-actions">
            <a href="{{ route('deployment.details', ['project_id' => $deployment->project_id, 'deployment_id' => $deployment->id, 'tab' => 'network-policies', 'network_policy_id' => 'new']) }}" class="btn btn-sm btn-primary p-1 lh-1" title="{{ __('Add') }}">
                <i class="bi bi-plus file-tree-action"></i>
            </a>
        </div>
    </div>
    <ul class="file-tree file-tree--child">
        @foreach ($deployment->networkPolicies as $networkPolicy)
        <li class="d-flex justify-content-between align-items-start flex-row file-tree-li">
            <a href="{{ route('deployment.details', ['project_id' => $deployment->project_id, 'deployment_id' => $deployment->id, 'tab' => 'network-policies', 'network_policy_id' => $networkPolicy->id]) }}" class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-shaded"></i> {{ $networkPolicy->target->name }} <i class="bi bi-arrow-left fs-6"></i> {{ $networkPolicy->source->name }}
            </a>
            <div class="file-tree-li-actions">
                <a href="{{ route('deployment.network-policies.delete.action', ['project_id' => $deployment->project_id, 'deployment_id' => $deployment->id, 'network_policy_id' => $networkPolicy->id]) }}" class="btn btn-sm btn-danger p-1 lh-1" title="{{ __('Delete') }}">
                    <i class="bi bi-trash file-tree-action"></i>
                </a>
            </div>
        </li>
        @endforeach
    </ul>
</div>
