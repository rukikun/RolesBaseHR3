@extends('layouts.hr')

@section('title', 'HR Dashboard - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">HR Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'John' }}! Here's what's happening with your HR system today.</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/admin_dashboard" class="text-decoration-none">Admin Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Home</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Time Clock Section -->
<div class="dashboard-section mb-4">
  <div class="time-clock text-center p-4" style="background-color: var(--jetlouge-light); border-radius: 8px;">
    <div class="current-time mb-3" id="live-clock" style="font-size: 36px; font-weight: bold; color: var(--jetlouge-primary);">--:--:--</div>
    <p class="text-muted mb-0">HR Management System Dashboard</p>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-users text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['total_employees'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Employees</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-user-check text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $employeeStats['present_today'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Present Today</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['pending_timesheets'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending Timesheets</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-umbrella-beach text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['leave_requests'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Leave Requests</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Today's Schedule Section -->
<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Today's Schedule</h3>
    <a href="{{ route('shift-schedule-management') }}#calendar-section" class="btn btn-primary" onclick="scrollToCalendar()">View Full Schedule</a>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="row">
        @forelse($todayShifts as $index => $shift)
        <div class="col-md-4 mb-3">
          <div class="shift-card-hr p-3 border rounded h-100">
            <div class="text-center mb-3">
              @if($index == 0)
                <i class="fas fa-sun text-warning fs-2 mb-2"></i>
              @elseif($index == 1)
                <i class="fas fa-cloud-sun text-info fs-2 mb-2"></i>
              @else
                <i class="fas fa-moon text-dark fs-2 mb-2"></i>
              @endif
              <h5 class="mb-1">{{ $shift['name'] }}</h5>
              <p class="text-muted mb-2 small">{{ $shift['time_range'] }}</p>
              <span class="badge bg-primary">{{ $shift['employee_count'] }} employees</span>
            </div>
            
            @if(!empty($shift['employees']))
              <div class="employee-list-hr">
                <h6 class="text-muted mb-2 small fw-bold">
                  Assigned Employees:
                  @if(count($shift['employees']) > 4)
                    <small class="text-info ms-1">(Scroll to see all {{ count($shift['employees']) }})</small>
                  @endif
                </h6>
                <div class="employee-list-container" data-employee-count="{{ count($shift['employees']) }}">
                  @foreach($shift['employees'] as $employee)
                    <div class="employee-item-hr d-flex align-items-center mb-2 p-2 bg-light rounded">
                      <div class="employee-avatar-hr me-2">
                        @if($employee['avatar'])
                          <img src="{{ asset('storage/' . $employee['avatar']) }}" alt="{{ $employee['name'] }}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                        @else
                          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px; font-weight: 600;">
                            {{ substr($employee['name'], 0, 1) }}
                          </div>
                        @endif
                      </div>
                      <div class="employee-info-hr flex-grow-1">
                        <div class="employee-name-hr small fw-bold text-truncate">{{ $employee['name'] }}</div>
                        @if(!empty($employee['position']))
                          <div class="employee-position-hr text-muted" style="font-size: 10px;">{{ $employee['position'] }}</div>
                        @endif
                      </div>
                    </div>
                  @endforeach
                  @if(count($shift['employees']) > 4)
                    <div class="scroll-indicator text-center text-muted small py-1">
                      <i class="fas fa-chevron-down"></i> Scroll for more
                    </div>
                  @endif
                </div>
              </div>
            @else
              <div class="text-center text-muted small">
                <i class="fas fa-user-slash mb-1"></i>
                <div>No employees assigned</div>
              </div>
            @endif
          </div>
        </div>
        @empty
        <div class="col-12">
          <div class="text-center p-4">
            <i class="fas fa-calendar-times text-muted fs-2 mb-2"></i>
            <h5 class="text-muted">No Shifts Scheduled</h5>
            <p class="text-muted mb-0">No shift schedules found for today</p>
          </div>
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Recent Time Entries Section -->
<div class="dashboard-section">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Recent Time Entries</h3>
    <a href="{{ route('timesheet-management') }}#attendance-tab" class="btn btn-primary" onclick="navigateToAttendanceLogs()">View All Entries</a>
  </div>
  <div class="card">
    <div class="card-body">
      <table class="table table-hover" id="recent-entries-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Total Hours</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentTimeEntries as $entry)
          <tr>
            <td>
              <div class="d-flex align-items-center">
                @if($entry->profile_picture)
                  <img src="{{ asset('storage/' . $entry->profile_picture) }}" alt="{{ $entry->employee_name }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                    {{ substr($entry->employee_name, 0, 1) }}
                  </div>
                @endif
                <span class="fw-medium">{{ $entry->employee_name }}</span>
              </div>
            </td>
            <td>{{ $entry->work_date ? $entry->work_date->format('M d, Y') : '--' }}</td>
            <td>{{ $entry->formatted_clock_in ?? '--' }}</td>
            <td>{{ $entry->formatted_clock_out ?? '--' }}</td>
            <td>
              @if($entry->total_hours)
                @if($entry->total_hours >= 8)
                  <span class="text-success fw-bold">{{ $entry->total_hours }} hrs</span>
                @else
                  <span class="text-muted">{{ $entry->total_hours }} hrs</span>
                @endif
              @else
                <span class="text-muted">--</span>
              @endif
            </td>
            <td>
              @if($entry->total_hours && $entry->total_hours >= 8)
                <span class="badge bg-success">
                  Clocked Out
                </span>
              @else
                <span class="badge bg-secondary">
                  Clocked Out
                </span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-clock me-2"></i>No recent time entries found
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-file-invoice-dollar text-primary fs-1 mb-3"></i>
        <h5>Claims</h5>
        <p class="text-muted">Manage expense claims</p>
        <a href="{{ route('claims-reimbursement') }}" class="btn btn-primary">View Claims</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-users text-success fs-1 mb-3"></i>
        <h5>Employees</h5>
        <p class="text-muted">Manage employee records</p>
        <a href="{{ route('employees') }}" class="btn btn-success">View Employees</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-umbrella-beach text-info fs-1 mb-3"></i>
        <h5>Leave</h5>
        <p class="text-muted">Manage leave requests</p>
        <a href="{{ route('leave-management') }}" class="btn btn-info">View Leave</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-clock text-warning fs-1 mb-3"></i>
        <h5>Timesheet</h5>
        <p class="text-muted">Track working hours</p>
        <a href="{{ route('timesheet-management') }}" class="btn btn-warning">View Timesheet</a>
      </div>
    </div>
  </div>
</div>

<script>
// Live clock
function updateClock() {
  const now = new Date();
  const timeString = now.toLocaleTimeString();
  document.getElementById('live-clock').textContent = timeString;
}

setInterval(updateClock, 1000);
updateClock();

// Employee list scroll functionality
document.addEventListener('DOMContentLoaded', function() {
  const employeeContainers = document.querySelectorAll('.employee-list-container');
  
  employeeContainers.forEach(container => {
    // Check if scrolling is needed
    function checkScrollNeeded() {
      const isScrollable = container.scrollHeight > container.clientHeight;
      if (!isScrollable) {
        const scrollIndicator = container.querySelector('.scroll-indicator');
        if (scrollIndicator) {
          scrollIndicator.style.display = 'none';
        }
      }
    }
    
    // Handle scroll events
    container.addEventListener('scroll', function() {
      const scrollTop = container.scrollTop;
      const scrollHeight = container.scrollHeight;
      const clientHeight = container.clientHeight;
      
      // Check if scrolled to bottom (with small tolerance)
      const isAtBottom = scrollTop + clientHeight >= scrollHeight - 5;
      
      if (isAtBottom) {
        container.classList.add('scrolled-bottom');
      } else {
        container.classList.remove('scrolled-bottom');
      }
    });
    
    // Initial check
    checkScrollNeeded();
    
    // Add smooth scroll behavior on wheel events
    container.addEventListener('wheel', function(e) {
      e.preventDefault();
      container.scrollTop += e.deltaY * 0.5; // Smoother scrolling
    });
  });
});

// Dashboard functionality
</script>

<style>
/* Modern Statistics Cards */
.stat-card-modern {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-card-modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--jetlouge-primary), var(--jetlouge-secondary));
}

.stat-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  position: relative;
}

.stat-icon-circle::after {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  background: linear-gradient(45deg, rgba(255,255,255,0.2), rgba(255,255,255,0.05));
  z-index: -1;
}

.stat-number {
  font-size: 2.2rem;
  font-weight: 700;
  color: #2c3e50;
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
  margin-top: 4px;
}

/* Color variations for icons */
.bg-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.bg-success {
  background: linear-gradient(135deg, #28a745, #1e7e34);
}

.bg-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
}

.bg-info {
  background: linear-gradient(135deg, #17a2b8, #138496);
}

/* HR Dashboard Shift Card Styling */
.shift-card-hr {
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border: 1px solid #e9ecef !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  position: relative;
  overflow: visible;
  padding: 20px !important;
  min-height: 280px;
}

.shift-card-hr:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  border-color: #007bff !important;
}

/* Removed the blue top border from individual shift cards */

.employee-item-hr {
  transition: all 0.2s ease;
  border: 1px solid #dee2e6;
  background: #ffffff !important;
  margin-bottom: 8px;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  padding: 8px 12px !important;
  width: 100%;
  box-sizing: border-box;
}

.employee-item-hr:hover {
  background-color: #e3f2fd !important;
  border-color: #2196f3;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
}

.employee-avatar-hr .rounded-circle {
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
  font-weight: 600;
  text-transform: uppercase;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Employee list container with vertical scrolling */
.employee-list-container {
  max-height: 200px; /* Increased height to show more employees */
  overflow-y: auto; /* Enable vertical scrolling */
  overflow-x: hidden; /* Disable horizontal scrolling */
  padding: 4px;
  padding-right: 12px; /* More space for scrollbar */
  margin-right: -4px; /* Compensate for padding */
  
  /* Hide scrollbar by default */
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer 10+ */
  
  /* Smooth scrolling */
  scroll-behavior: smooth;
}

/* Hide scrollbar for WebKit browsers (Chrome, Safari, Edge) */
.employee-list-container::-webkit-scrollbar {
  width: 8px; /* Slightly wider for better visibility */
  background: transparent;
}

.employee-list-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  margin: 2px 0;
}

.employee-list-container::-webkit-scrollbar-thumb {
  background: rgba(0, 123, 255, 0.2);
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
  opacity: 0; /* Hidden by default */
}

/* Show scrollbar when hovering over the entire employee list area */
.employee-list-hr:hover .employee-list-container::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.5);
}

/* Enhanced scrollbar on direct hover */
.employee-list-container:hover::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.7);
  border: 1px solid rgba(0, 123, 255, 0.3);
}

.employee-list-container:hover::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 123, 255, 0.9);
}

/* Firefox scrollbar styling */
.employee-list-hr:hover .employee-list-container {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.5) rgba(0, 0, 0, 0.05);
}

.employee-list-container:hover {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.7) rgba(0, 0, 0, 0.1);
}

/* Employee list container with proper spacing */
.employee-list-hr {
  position: relative;
  padding-bottom: 5px;
}

/* Scroll indicator styling */
.scroll-indicator {
  position: sticky;
  bottom: 0;
  background: linear-gradient(to top, rgba(248, 249, 250, 0.9), transparent);
  margin: 0 -4px -2px -4px;
  padding: 4px 0 2px 0;
  font-size: 10px;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.employee-list-container:hover .scroll-indicator {
  opacity: 1;
}

/* Hide scroll indicator when scrolled to bottom */
.employee-list-container.scrolled-bottom .scroll-indicator {
  display: none;
}

.shift-card-hr .badge {
  font-size: 0.7rem;
  padding: 0.35em 0.7em;
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
  box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

.employee-name-hr {
  color: #2c3e50;
  font-weight: 600;
}

.employee-position-hr {
  color: #6c757d;
  font-weight: 500;
}

/* Today's Schedule section styling */
.dashboard-section .card {
  position: relative;
  overflow: hidden;
}

.dashboard-section .card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #007bff, #0056b3);
  z-index: 1;
}

/* Enhanced table styling */
.table-hover tbody tr:hover {
  background-color: rgba(0,123,255,0.05);
}

.table thead th {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .stat-card-modern {
    padding: 20px;
    border-radius: 12px;
  }
  
  .stat-icon-circle {
    width: 48px;
    height: 48px;
    font-size: 18px;
  }
  
  .stat-number {
    font-size: 1.8rem;
  }
  
  .shift-card-hr {
    margin-bottom: 1rem;
    min-height: 250px;
    padding: 15px !important;
  }
  
  .employee-list-hr {
    position: relative;
    padding-bottom: 5px;
  }
  
  .employee-list-container {
    max-height: 180px; /* Maintain good height on mobile */
    overflow-y: auto;
    overflow-x: hidden;
    padding: 3px;
    padding-right: 10px;
    margin-right: -3px;
    scroll-behavior: smooth;
  }
  
  /* Mobile scrollbar adjustments */
  .employee-list-container::-webkit-scrollbar {
    width: 6px;
  }
  
  .employee-list-hr:hover .employee-list-container::-webkit-scrollbar-thumb {
    opacity: 1;
    background: rgba(0, 123, 255, 0.6);
  }
  
  .employee-item-hr {
    margin-bottom: 6px;
    padding: 6px 10px !important;
  }
}
</style>

<script>
function scrollToCalendar() {
  // Store the scroll target in sessionStorage so it persists across page navigation
  sessionStorage.setItem('scrollToCalendar', 'true');
}

function navigateToAttendanceLogs() {
  // Store the tab target in sessionStorage so it persists across page navigation
  sessionStorage.setItem('activateAttendanceTab', 'true');
}

// Check if we need to scroll when the shift-schedule page loads
if (sessionStorage.getItem('scrollToCalendar') === 'true') {
  sessionStorage.removeItem('scrollToCalendar');
  // Wait for page to load then scroll
  window.addEventListener('load', function() {
    setTimeout(function() {
      const calendarSection = document.getElementById('calendar-section') || 
                             document.querySelector('.calendar') || 
                             document.querySelector('[id*="calendar"]') ||
                             document.querySelector('.tab-pane');
      if (calendarSection) {
        calendarSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        // Fallback: scroll to bottom of page where calendar usually is
        window.scrollTo({ top: document.body.scrollHeight * 0.7, behavior: 'smooth' });
      }
    }, 500);
  });
}
</script>
@endsection
