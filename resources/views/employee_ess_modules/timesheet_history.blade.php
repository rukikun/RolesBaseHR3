@extends('layouts.employee_layout')

@section('title', 'Timesheet History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Timesheet History
                    </h4>
                    <a href="{{ route('employee.timesheet') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Timesheet
                    </a>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Entries</h6>
                                            <h3 class="mb-0">{{ $stats['totalEntries'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Hours</h6>
                                            <h3 class="mb-0">{{ number_format($stats['totalHours'] ?? 0, 1) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Approved</h6>
                                            <h3 class="mb-0">{{ $stats['approvedEntries'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Pending</h6>
                                            <h3 class="mb-0">{{ $stats['pendingEntries'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-hourglass-half fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Options -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="month" class="form-control" id="monthFilter" placeholder="Filter by month">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </button>
                        </div>
                    </div>

                    <!-- Timesheet History Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="timesheetHistoryTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Hours Worked</th>
                                    <th>Overtime</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timesheets as $timesheet)
                                <tr data-status="{{ $timesheet->status ?? 'pending' }}" data-date="{{ $timesheet->work_date ?? '' }}">
                                    <td>{{ isset($timesheet->work_date) ? date('M d, Y', strtotime($timesheet->work_date)) : 'N/A' }}</td>
                                    <td>{{ isset($timesheet->clock_in) ? date('h:i A', strtotime($timesheet->clock_in)) : 'N/A' }}</td>
                                    <td>{{ isset($timesheet->clock_out) ? date('h:i A', strtotime($timesheet->clock_out)) : 'N/A' }}</td>
                                    <td>{{ number_format($timesheet->hours_worked ?? 0, 1) }} hrs</td>
                                    <td>{{ number_format($timesheet->overtime_hours ?? 0, 1) }} hrs</td>
                                    <td>{{ $timesheet->description ?? 'No description' }}</td>
                                    <td>
                                        @php
                                            $status = $timesheet->status ?? 'pending';
                                            $badgeClass = match($status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>{{ isset($timesheet->created_at) ? date('M d, Y', strtotime($timesheet->created_at)) : 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No timesheet entries found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const monthFilter = document.getElementById('monthFilter');
    const table = document.getElementById('timesheetHistoryTable');
    const rows = table.querySelectorAll('tbody tr[data-status]');

    function filterTable() {
        const statusValue = statusFilter.value.toLowerCase();
        const monthValue = monthFilter.value;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status').toLowerCase();
            const rowDate = row.getAttribute('data-date');
            const rowMonth = rowDate ? rowDate.substring(0, 7) : '';

            const statusMatch = !statusValue || rowStatus === statusValue;
            const monthMatch = !monthValue || rowMonth === monthValue;

            if (statusMatch && monthMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    statusFilter.addEventListener('change', filterTable);
    monthFilter.addEventListener('change', filterTable);

    window.clearFilters = function() {
        statusFilter.value = '';
        monthFilter.value = '';
        filterTable();
    };
});
</script>
<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection
