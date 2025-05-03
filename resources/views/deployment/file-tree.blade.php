@if ($template->tree->isEmpty())
    <div class="alert alert-warning mb-0 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle fs-5"></i>
        {{ __('No files or folders') }}
    </div>
@else
    <ul class="file-tree">
        <li class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-start flex-row file-tree-li">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-folder"></i> {{ $template->name }}
                </span>
            </div>
            <ul class="file-tree file-tree--child d-flex flex-column gap-2">
                @foreach ($template->tree as $tree)
                    @if ($tree->type === 'folder')
                        @include('deployment.file-tree-folder', ['deployment' => $deployment, 'template' => $template, 'structure' => $tree])
                    @elseif ($tree->type === 'file')
                        @include('deployment.file-tree-file', ['deployment' => $deployment, 'template' => $template, 'structure' => $tree])
                    @endif
                @endforeach
            </ul>
        </li>
    </ul>
@endif
