<div class="py-3">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('deployment.netpol.put.action', ['project_id' => $deployment->project_id, 'deployment_id' => $deployment->id, 'network_policy_id' => $networkPolicy?->id ?? 'new']) }}" method="POST">
                @csrf

                @if (!empty($networkPolicy))
                    <input type="hidden" name="id" value="{{ $networkPolicy->id }}">
                @endif

                <div class="row mb-3">
                    <label for="source_deployment_id" class="col-md-4 col-form-label text-md-end">{{ __('Source Deployment') }}</label>

                    <div class="col-md-6">

                        <select id="source_deployment_id" class="form-control @error('source_deployment_id') is-invalid @enderror" name="source_deployment_id">
                            <option value="">{{ __('Select a deployment...') }}</option>
                            @foreach ($deployment->project->deployments as $deployment)
                                @if ($deployment->id !== request()->deployment_id || !empty($networkPolicy))
                                    <option value="{{ $deployment->id }}"{{ old('source_deployment_id') == $deployment->id || $networkPolicy?->source_deployment_id == $deployment->id || request()->deployment_id == $deployment->id ? ' selected' : '' }}>{{ $deployment->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3{{ request()->network_policy_id && request()->network_policy_id == 'new' ? ' d-none' : '' }}">
                    <label for="target_deployment_id" class="col-md-4 col-form-label text-md-end">{{ __('Target Deployment') }}</label>

                    <div class="col-md-6">
                        <select id="target_deployment_id" class="form-control @error('target_deployment_id') is-invalid @enderror" name="target_deployment_id">
                            <option value="">{{ __('Select a deployment...') }}</option>
                            @foreach ($deployment->project->deployments as $deployment)
                                <option value="{{ $deployment->id }}"{{ old('target_deployment_id') == $deployment->id || $networkPolicy?->target_deployment_id == $deployment->id || (request()->deployment_id == $deployment->id && empty($networkPolicy)) ? ' selected' : '' }}>{{ $deployment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
