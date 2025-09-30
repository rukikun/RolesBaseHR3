@extends('layouts.employee_layout')

@section('title', 'My Schedule')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>My Schedule
                    </h4>
                    <div>
                        <a href="{{ route('employee.shift.schedule') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-calendar-week me-1"></i>Full Schedule
                        </a>
                        <a href="{{ route('employee.dashboard.show') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Upcoming Shifts -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>Upcoming Shifts
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @forelse($upcomingShifts as $shift)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                                        <div>
                                            <h6 class="mb-1">{{ $shift->shift_type_name ?? 'Regular Shift' }}</h6>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ isset($shift->shift_date) ? date('M d, Y', strtotime($shift->shift_date)) : 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary">
                                                {{ isset($shift->start_time) ? date('h:i A', strtotime($shift->start_time)) : 'N/A' }} - 
                                                {{ isset($shift->end_time) ? date('h:i A', strtotime($shift->end_time)) : 'N/A' }}
                                            </span>
                                            <br>
                                            @php
                                                $status = $shift->status ?? 'scheduled';
                                                $badgeClass = match($status) {
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    'scheduled' => 'bg-info',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <small class="badge {{ $badgeClass }} mt-1">{{ ucfirst($status) }}</small>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                                        <p>No upcoming shifts scheduled</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Leave Requests & Quick Actions -->
                        <div class="col-md-4">
                            <!-- Upcoming Leave -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-plane me-2"></i>Upcoming Leave
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @forelse($leaveRequests as $leave)
                                    <div class="border-bottom py-2">
                                        <h6 class="mb-1">{{ $leave->leave_type_name ?? 'Leave' }}</h6>
                                        <p class="text-muted mb-1 small">
                                            {{ isset($leave->start_date) ? date('M d', strtotime($leave->start_date)) : 'N/A' }} - 
                                            {{ isset($leave->end_date) ? date('M d, Y', strtotime($leave->end_date)) : 'N/A' }}
                                        </p>
                                        @php
                                            $status = $leave->status ?? 'pending';
                                            $badgeClass = match($status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} small">{{ ucfirst($status) }}</span>
                                    </div>
                                    @empty
                                    <p class="text-muted text-center">No upcoming leave</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-bolt me-2"></i>Quick Actions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('employee.timesheet') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-clock me-1"></i>View Timesheets
                                        </a>
                                        <a href="{{ route('employee.leave.management') }}" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-plane me-1"></i>Request Leave
                                        </a>
                                        <a href="{{ route('employee.claims') }}" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-receipt me-1"></i>Submit Claim
                                        </a>
                                        <button class="btn btn-outline-warning btn-sm" onclick="logAttendance()">
                                            <i class="fas fa-sign-in-alt me-1"></i>Log Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar View (Optional) -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar me-2"></i>This Week's Overview
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        @php
                                            $startOfWeek = now()->startOfWeek();
                                            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                        @endphp
                                        @for($i = 0; $i < 7; $i++)
                                            @php
                                                $currentDay = $startOfWeek->copy()->addDays($i);
                                                $dayShifts = $upcomingShifts->filter(function($shift) use ($currentDay) {
                                                    return isset($shift->shift_date) && 
                                                           date('Y-m-d', strtotime($shift->shift_date)) === $currentDay->toDateString();
                                                });
                                            @endphp
                                            <div class="col">
                                                <div class="card {{ $currentDay->isToday() ? 'border-primary' : '' }}">
                                                    <div class="card-body p-2">
                                                        <h6 class="card-title mb-1">{{ $days[$i] }}</h6>
                                                        <p class="small text-muted mb-2">{{ $currentDay->format('M d') }}</p>
                                                        @if($dayShifts->count() > 0)
                                                            @foreach($dayShifts as $shift)
                                                                <span class="badge bg-primary small d-block mb-1">
                                                                    {{ isset($shift->start_time) ? date('H:i', strtotime($shift->start_time)) : 'N/A' }}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small">No shifts</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function logAttendance() {
    const timestamp = new Date().toISOString();
    
    fetch('{{ route("employee.attendance.log") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            timestamp: timestamp
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance logged successfully!');
        } else {
            alert('Error logging attendance: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error logging attendance');
    });
}
</script>
<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection
