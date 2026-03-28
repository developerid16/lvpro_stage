@extends('layouts.master-layouts')

@section('title')
    Activity Log
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('li_1_link') {{ url('/') }} @endslot
        @slot('title') Activity Log @endslot
    @endcomponent

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            <div></div>
            
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
                            <!-- <th>DETAILS</th> -->
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
                                <td>
                                    @if(!empty($changedFields))
                                        @foreach($changedFields as $field => $change)
                                            @if($field != 'updated_at')
                                                <div>
                                                    {{ ucfirst($field) }} changed from 
                                                    '<span style="color:red;">{{ $change['from'] ?? '-' }}</span>' 
                                                    to 
                                                    '<span style="color:green;">{{ $change['to'] ?? '-' }}</span>'
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        {{ $log->message ?? '-' }}
                                    @endif
                                </td>

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
                                <!-- <td>
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
                                </td> -->
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

@endsection

