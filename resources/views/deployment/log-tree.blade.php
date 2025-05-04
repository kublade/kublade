@if ($deployment->logs->isEmpty())
    <div class="alert alert-warning mb-0 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle fs-5"></i>
        {{ __('No logs') }}
    </div>
@else
    <ul class="file-tree">
        @foreach ($deployment->logs as $log)
        <li class="d-flex justify-content-between align-items-start flex-row file-tree-li">
            <a href="{{ route('deployment.details', ['project_id' => $deployment->project_id, 'deployment_id' => $deployment->id, 'tab' => 'logs', 'log_id' => $log->id]) }}" class="d-flex align-items-center gap-2">
                <i class="bi bi-box"></i> {{ $log->pod->name }}
            </a>
        </li>
        @endforeach
    </ul>
@endif
