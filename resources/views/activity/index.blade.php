@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border border-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Activities') }}
                </div>

                <div class="card-body d-flex flex-column gap-4 p-0">
                    <table class="table">
                        <thead class="font-monospace">
                            <tr class="align-middle">
                                <th class="w-100" scope="col">{{ __('Date') }}</th>
                                <th scope="col">{{ __('User') }}</th>
                                <th scope="col">{{ __('Event') }}</th>
                                <th scope="col">{{ __('Subject Type') }}</th>
                                <th scope="col">{{ __('Subject ID') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activities as $activity)
                                <tr class="align-middle">
                                    <td class="w-100">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $activity->causer->name }}</td>
                                    <td class="text-capitalize">
                                        @if ($activity->event == 'created')
                                            <span class="badge bg-success">{{ __('Created') }}</span>
                                        @elseif ($activity->event == 'updated')
                                            <span class="badge bg-warning">{{ __('Updated') }}</span>
                                        @elseif ($activity->event == 'deleted')
                                            <span class="badge bg-danger">{{ __('Deleted') }}</span>
                                        @elseif ($activity->event == 'restored')
                                            <span class="badge bg-info">{{ __('Restored') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $activity->event }}</span>
                                        @endif
                                    </td>
                                    <td>{{ str_replace('App\\Models\\', '', $activity->subject_type) }}</td>
                                    <td>
                                        <span class="text-nowrap badge bg-light text-body">{{ $activity->subject_id }}</span>
                                    </td>
                                </tr>
                                @if ($activity->properties)
                                    <tr class="border-bottom-0">
                                        <td colspan="5">
                                            <a href="#" class="d-flex align-items-center gap-2 justify-content-between text-decoration-none small collapsed" data-bs-toggle="collapse" data-bs-target="#activityProperties{{ $activity->id }}">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-gear"></i>
                                                    {{ __('Show properties') }}
                                                </div>
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="p-0" colspan="5">
                                            <div class="collapse border-top p-3" id="activityProperties{{ $activity->id }}">
                                                <pre class="mb-0">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    {{ $activities->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
