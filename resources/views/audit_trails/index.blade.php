@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">
                Audit Trail
                @if($user->isAdmin())
                    <span class="badge bg-primary ms-2">Admin View (HR + Employees)</span>
                @elseif($user->isHr())
                    <span class="badge bg-info ms-2">HR View (Employees Only)</span>
                @endif
            </h4>
            <small class="text-muted">Review key actions taken by HR and employees.</small>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from" class="form-label mb-1">From date</label>
                    <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label mb-1">To date</label>
                    <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>
                    <a href="{{ route('audit_trails.index') }}" class="btn btn-outline-secondary ms-2">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Actor</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Target</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->actor_name ?? 'System' }}</td>
                            <td class="text-capitalize">{{ $log->actor_type }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td style="max-width: 320px;">
                                <span class="d-inline-block text-truncate" style="max-width: 320px;" title="{{ $log->description }}">
                                    {{ $log->description ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($log->target_type)
                                    {{ $log->target_type }} @if($log->target_id)#{{ $log->target_id }}@endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No audit trail entries found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

