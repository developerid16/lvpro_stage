@extends('layouts.master-layouts')

@section('title')
    Reward Activity Log
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('li_1_link') {{ url('/') }} @endslot
        @slot('title') Reward Activity Log @endslot
    @endcomponent

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            <div></div>
            <a href="{{ url('admin/reward') }}" class="btn btn-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered sh_table">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>ACTION</th>
                            <th>MESSAGE</th>
                            <th>USER</th>
                            <th>DEPARTMENT</th>
                            <th>ROLE</th>
                            <th>IP ADDRESS</th>
                            <th>REWARD CREATED AT</th>
                            <th>LOG CREATED AT</th>
                            <th>DETAILS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            @php
                                $actionColor = match($log->action) {
                                    'create'       => 'success',
                                    'update'       => 'primary',
                                    'delete'       => 'warning',
                                    'restore'      => 'info',
                                    'force_delete' => 'danger',
                                    'approve'      => 'success',
                                    'reject'       => 'danger',
                                    default        => 'secondary',
                                };

                                $changedFields = $log->changed_fields ? json_decode($log->changed_fields, true) : [];
                                $newValues     = $log->new_values     ? json_decode($log->new_values, true)     : [];
                                $oldValues     = $log->old_values     ? json_decode($log->old_values, true)     : [];

                                // Reward created_at from new_values or old_values
                                $rewardCreatedAt = $newValues['created_at'] ?? $oldValues['created_at'] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                {{-- Action --}}
                                <td>
                                    <span class="badge bg-{{ $actionColor }} text-uppercase">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                {{-- Message --}}
                                <td>{{ $log->message ?? '-' }}</td>

                                {{-- User --}}
                                <td>{{ $log->user_name ?? '-' }}</td>

                                {{-- Department --}}
                                <td>{{ $log->department_name ?? '-' }}</td>

                                {{-- Role --}}
                                <td>{{ $log->role_name ?? '-' }}</td>

                                {{-- IP --}}
                                <td>{{ $log->ip_address ?? '-' }}</td>

                                {{-- Reward Created At --}}
                                <td>
                                    @if($rewardCreatedAt)
                                        {{ \Carbon\Carbon::parse($rewardCreatedAt)->format(config('safra.date-format')) }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Log Created At --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format(config('safra.date-format')) }}
                                </td>

                                {{-- Details --}}
                                <td>
                                    @if(!empty($changedFields) || !empty($newValues) || !empty($oldValues))
                                        <button class="btn btn-outline-primary btn-sm view-log-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#logDetailModal"
                                            data-action="{{ $log->action }}"
                                            data-changed='{{ $log->changed_fields ?? "[]" }}'
                                            data-new='{{ $log->new_values ?? "[]" }}'
                                            data-old='{{ $log->old_values ?? "[]" }}'>
                                            <i class="mdi mdi-eye"></i> View
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No activity logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-history me-1"></i> Log Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logDetailBody">
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    $('#logDetailModal').on('show.bs.modal', function (e) {

        const btn    = $(e.relatedTarget);
        const action = btn.data('action');

        let changedFields = {};
        let newValues     = {};
        let oldValues     = {};

        try { changedFields = JSON.parse(btn.data('changed') || '{}'); } catch(e) { changedFields = {}; }
        try { newValues     = JSON.parse(btn.data('new')     || '{}'); } catch(e) { newValues = {}; }
        try { oldValues     = JSON.parse(btn.data('old')     || '{}'); } catch(e) { oldValues = {}; }

        // Sensitive fields hide
        const hideFields = ['password', 'remember_token', 'token', 'secret', 'updated_at'];

        function buildTable(data, titleHtml, thLeft, thRight, colorClass) {
            const filtered = Object.entries(data).filter(([k]) => !hideFields.includes(k));
            if (!filtered.length) return '';

            let rows = filtered.map(([field, value]) => {
                const val = typeof value === 'object' && value !== null
                    ? `<code>${JSON.stringify(value)}</code>`
                    : (value ?? '-');
                return `<tr><td><code>${field}</code></td><td class="${colorClass}">${val}</td></tr>`;
            }).join('');

            return `
                <h6 class="fw-bold mb-2 ${colorClass}">${titleHtml}</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th width="220">${thLeft}</th><th>${thRight}</th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        }

        function buildChangedTable(data) {
            const filtered = Object.entries(data).filter(([k]) => !hideFields.includes(k));
            if (!filtered.length) return '';

            let rows = filtered.map(([field, change]) => {
                const from = typeof change.from === 'object' && change.from !== null
                    ? `<code>${JSON.stringify(change.from)}</code>`
                    : (change.from ?? '-');
                const to = typeof change.to === 'object' && change.to !== null
                    ? `<code>${JSON.stringify(change.to)}</code>`
                    : (change.to ?? '-');
                return `
                    <tr>
                        <td><code>${field}</code></td>
                        <td class="text-danger">${from}</td>
                        <td class="text-success">${to}</td>
                    </tr>`;
            }).join('');

            return `
                <h6 class="fw-bold mb-2 text-primary">
                    <i class="mdi mdi-pencil me-1"></i> Changed Fields
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="220">Field</th>
                                <th>From</th>
                                <th>To</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        }

        let html = '';

        if (action === 'create') {
            html += buildTable(
                newValues,
                '<i class="mdi mdi-plus-circle me-1"></i> Created Data',
                'Field', 'Value', 'text-success'
            );
        } else if (action === 'update') {
            html += buildChangedTable(changedFields);
        } else if (['delete', 'force_delete'].includes(action)) {
            html += buildTable(
                oldValues,
                '<i class="mdi mdi-delete me-1"></i> Deleted Data',
                'Field', 'Value', 'text-danger'
            );
        } else if (['approve', 'reject'].includes(action)) {
            html += buildTable(
                newValues,
                `<i class="mdi mdi-check-circle me-1"></i> ${action === 'approve' ? 'Approved' : 'Rejected'} Data`,
                'Field', 'Value',
                action === 'approve' ? 'text-success' : 'text-danger'
            );
        } else {
            // Default — show whatever available
            if (Object.keys(changedFields).length) html += buildChangedTable(changedFields);
            if (Object.keys(newValues).length)     html += buildTable(newValues, '<i class="mdi mdi-information me-1"></i> New Values', 'Field', 'Value', 'text-success');
            if (Object.keys(oldValues).length)     html += buildTable(oldValues, '<i class="mdi mdi-information me-1"></i> Old Values', 'Field', 'Value', 'text-danger');
        }

        if (!html) {
            html = '<p class="text-muted text-center py-3">No details available.</p>';
        }

        $('#logDetailBody').html(html);
    });
</script>
@endsection