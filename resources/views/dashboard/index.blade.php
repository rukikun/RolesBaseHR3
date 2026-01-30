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
        <p class="text-muted mb-0">Welcome back, {{ Auth::guard('employee')->user()->first_name ?? 'John' }}! Here's what's happening with your HR system today.</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active" aria-current="page">Home</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Performance Overview -->
<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 style="color: var(--jetlouge-primary);">Performance Overview</h3>
      <p class="text-muted mb-0">Last 7 days of attendance, productivity, and quality trends.</p>
    </div>
    <span class="badge bg-light text-dark border">Updated {{ now()->format('M d, Y') }}</span>
  </div>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card performance-card h-100">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
              <h5 class="mb-1">Team Performance Trends</h5>
              <small class="text-muted">Attendance, productivity, and quality scores.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <span class="badge performance-badge">Attendance {{ number_format($performanceData['summary']['attendance_avg'] ?? 0, 1) }}%</span>
              <span class="badge performance-badge bg-success">Productivity {{ number_format($performanceData['summary']['productivity_avg'] ?? 0, 1) }}%</span>
              <span class="badge performance-badge bg-warning text-dark">Quality {{ number_format($performanceData['summary']['quality_avg'] ?? 0, 1) }}%</span>
            </div>
          </div>
          <div class="chart-wrapper">
            <canvas id="performanceTrendChart" height="140"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card performance-card h-100">
        <div class="card-body d-flex flex-column">
          <div>
            <h6 class="fw-bold mb-1">Operational Load</h6>
            <small class="text-muted">Overtime hours across the team.</small>
          </div>
          <div class="chart-wrapper mt-3 flex-grow-1">
            <canvas id="overtimeChart" height="200"></canvas>
          </div>
          <div class="mt-3">
            <div class="metric-row">
              <span>Overtime Total</span>
              <strong>{{ number_format($performanceData['summary']['overtime_total'] ?? 0, 1) }} hrs</strong>
            </div>
            <div class="metric-row">
              <span>Weekly Attendance Avg</span>
              <strong>{{ number_format($performanceData['summary']['attendance_avg'] ?? 0, 1) }}%</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-md-4">
      <div class="performance-kpi">
        <div class="kpi-label">Attendance Rate</div>
        <div class="kpi-value">{{ number_format($performanceData['summary']['attendance_avg'] ?? 0, 1) }}%</div>
        <div class="kpi-note">Team average for the week</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="performance-kpi">
        <div class="kpi-label">Productivity Score</div>
        <div class="kpi-value">{{ number_format($performanceData['summary']['productivity_avg'] ?? 0, 1) }}%</div>
        <div class="kpi-note">Based on average hours worked</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="performance-kpi">
        <div class="kpi-label">Quality Score</div>
        <div class="kpi-value">{{ number_format($performanceData['summary']['quality_avg'] ?? 0, 1) }}%</div>
        <div class="kpi-note">On-time performance ratio</div>
      </div>
    </div>
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

<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 style="color: var(--jetlouge-primary);">
        Scheduled Shifts
      </h3>
    </div>
    <a href="{{ route('shift-schedule-management') }}#calendar-section" class="btn btn-primary" onclick="scrollToCalendar()">View Full Schedule</a>
  </div>
  <div class="card">
    <div class="card-body shift-cards-container p-2">
      <div class="row">
        @php
          // Reorder shifts: Morning first, then Afternoon, then others
          $reorderedShifts = collect($todayShifts)->sortBy(function($shift) {
            $name = strtolower($shift['name']);
            if (str_contains($name, 'morning')) return 1;
            if (str_contains($name, 'afternoon')) return 2;
            if (str_contains($name, 'night')) return 3;
            if (str_contains($name, 'split')) return 4;
            if (str_contains($name, 'weekend')) return 5;
            return 6;
          });
          
          // Filter out shifts with no employees (hide empty boxes)
          $shiftsWithEmployees = $reorderedShifts->filter(function($shift) {
            return !empty($shift['employees']) && count($shift['employees']) > 0;
          });
        @endphp
        
        @forelse($shiftsWithEmployees as $index => $shift)
        @php
          // Determine responsive column class
          $totalShifts = $shiftsWithEmployees->count();
          if ($totalShifts == 1) {
            $colClass = 'col-12';
          } elseif ($totalShifts == 2) {
            $colClass = 'col-lg-6 col-md-12';
          } elseif ($totalShifts == 3) {
            $colClass = 'col-lg-4 col-md-6 col-sm-12';
          } elseif ($totalShifts == 4) {
            $colClass = 'col-lg-3 col-md-6 col-sm-12';
          } elseif ($totalShifts == 5) {
            // First 3 cards: col-lg-4, last 2 cards: col-lg-6 (centered)
            $colClass = $index < 3 ? 'col-lg-4 col-md-6 col-sm-12' : 'col-lg-6 col-md-6 col-sm-12';
          } else {
            $colClass = 'col-lg-4 col-md-6 col-sm-12';
          }
        @endphp
        <div class="{{ $colClass }} mb-3">
          <div class="shift-card-hr p-3 border rounded h-100">
            <div class="text-center mb-3">
              @php
                $shiftName = strtolower($shift['name']);
              @endphp
              @if(str_contains($shiftName, 'morning'))
                <i class="fas fa-sun text-warning fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'afternoon'))
                <i class="fas fa-cloud-sun text-info fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'night'))
                <i class="fas fa-moon text-dark fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'split'))
                <i class="fas fa-clock text-secondary fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'weekend'))
                <i class="fas fa-calendar-alt text-success fs-2 mb-2"></i>
              @else
                <i class="fas fa-briefcase text-primary fs-2 mb-2"></i>
              @endif
              <h5 class="mb-1">{{ $shift['name'] }}</h5>
              <p class="text-muted mb-2 small">{{ $shift['time_range'] }}</p>
              <span class="badge bg-primary">{{ $shift['employee_count'] }} employees</span>
            </div>
            
            <div class="employee-list-hr">
              <h6 class="text-muted mb-2 small fw-bold">
                Assigned Employees:
                @if(count($shift['employees']) > 2)
                  <small class="text-info ms-1">(Scroll to see all {{ count($shift['employees']) }})</small>
                @endif
              </h6>
              <div class="employee-list-container" data-employee-count="{{ count($shift['employees']) }}">
                @forelse($shift['employees'] as $employee)
                  <div class="employee-item-hr d-flex align-items-center mb-2 p-2 bg-light rounded">
                    <div class="employee-avatar-hr me-2">
                      @if($employee['avatar'] ?? false)
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
                      @if(!empty($employee['specific_time']))
                        <div class="employee-specific-time text-info" style="font-size: 9px; font-weight: 500;">{{ $employee['specific_time'] }}</div>
                      @endif
                    </div>
                  </div>
                @empty
                  <div class="text-center text-muted py-3">
                    <i class="fas fa-user-slash mb-2"></i>
                    <div class="small">No employees assigned for today</div>
                  </div>
                @endforelse
                @if(count($shift['employees']) > 2)
                  <div class="scroll-indicator text-center text-muted small py-1">
                    <i class="fas fa-chevron-down"></i> Scroll for more
                  </div>
                @endif
              </div>
            </div>
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
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshRecentTimeEntries()" id="refresh-time-entries-btn">
        <i class="fas fa-sync-alt"></i>
      </button>
      <a href="{{ route('timesheet-management') }}#attendance-tab" class="btn btn-primary" onclick="navigateToAttendanceLogs()">View All Entries</a>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="recent-entries-table">
          <thead class="table-light">
            <tr>
              <th>Employee</th>
              <th>Date</th>
              <th>Clock In</th>
              <th>Clock Out</th>
              <th>Total Time</th>
              <th>Status</th>
            </tr>
          </thead>
        </table>
        <div class="recent-entries-scroll-container">
          <table class="table table-hover mb-0">
            <tbody id="recent-entries-tbody">
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
              @if($entry->total_hours !== null)
                @if($entry->total_hours >= 8)
                  <span class="text-success fw-bold">{{ $entry->formatted_total_time ?? $entry->total_hours . ' hrs' }}</span>
                @else
                  <span class="text-muted">{{ $entry->formatted_total_time ?? $entry->total_hours . ' hrs' }}</span>
                @endif
              @else
                <span class="text-muted">--</span>
              @endif
            </td>
            <td>
              @php
                $hasClockIn = !empty($entry->formatted_clock_in) && $entry->formatted_clock_in !== '--';
                $hasClockOut = !empty($entry->formatted_clock_out) && $entry->formatted_clock_out !== '--';
              @endphp
              @if($hasClockOut)
                <span class="badge bg-success">Clocked Out</span>
              @elseif($hasClockIn)
                <span class="badge bg-warning text-dark">Clocked In</span>
              @else
                <span class="badge bg-secondary">No Entry</span>
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
        <a href="{{ route('employees.index') }}" class="btn btn-success">View Employees</a>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const fallbackLabels = Array.from({ length: 7 }, (_, index) => {
  const date = new Date();
  date.setDate(date.getDate() - (6 - index));
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});

function normalizePerformanceData(data) {
  const normalized = data && typeof data === 'object' ? data : {};
  const labels = Array.isArray(normalized.labels) && normalized.labels.length
    ? normalized.labels
    : fallbackLabels;
  const fillSeries = (series) => labels.map((_, index) => {
    const value = Array.isArray(series) ? series[index] : null;
    return Number.isFinite(value) ? value : 0;
  });

  return {
    labels,
    attendance_rate: fillSeries(normalized.attendance_rate),
    productivity_score: fillSeries(normalized.productivity_score),
    quality_score: fillSeries(normalized.quality_score),
    overtime_hours: fillSeries(normalized.overtime_hours)
  };
}

let performancePayload = normalizePerformanceData(@json($performanceData ?? []));
let performanceLabels = performancePayload.labels || [];
let attendanceSeries = performancePayload.attendance_rate || [];
let productivitySeries = performancePayload.productivity_score || [];
let qualitySeries = performancePayload.quality_score || [];
let overtimeSeries = performancePayload.overtime_hours || [];

let performanceTrendChart = null;
let overtimeChart = null;

function initPerformanceCharts() {
  if (typeof Chart === 'undefined') {
    console.error('Chart.js not available.');
    return;
  }

  const trendCanvas = document.getElementById('performanceTrendChart');
  if (trendCanvas) {
    performanceTrendChart = new Chart(trendCanvas, {
      type: 'line',
      data: {
        labels: performanceLabels,
        datasets: [
          {
            label: 'Attendance %',
            data: attendanceSeries,
            borderColor: '#1e3a8a',
            backgroundColor: 'rgba(30, 58, 138, 0.15)',
            tension: 0.3,
            fill: false,
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#ffffff',
            pointBorderWidth: 2,
          },
          {
            label: 'Productivity %',
            data: productivitySeries,
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34, 197, 94, 0.12)',
            tension: 0.3,
            fill: false,
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#ffffff',
            pointBorderWidth: 2,
          },
          {
            label: 'Quality %',
            data: qualitySeries,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.12)',
            tension: 0.3,
            fill: false,
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#ffffff',
            pointBorderWidth: 2,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: value => `${value}%`
            }
          }
        },
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  }

  const overtimeCanvas = document.getElementById('overtimeChart');
  if (overtimeCanvas) {
    overtimeChart = new Chart(overtimeCanvas, {
      type: 'bar',
      data: {
        labels: performanceLabels,
        datasets: [
          {
            label: 'Overtime Hours',
            data: overtimeSeries,
            backgroundColor: 'rgba(30, 64, 175, 0.7)',
            borderColor: 'rgba(30, 64, 175, 1)',
            borderWidth: 1.5,
            borderRadius: 6,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => `${value}h`
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }
}

function updatePerformanceCharts(data) {
  const normalized = normalizePerformanceData(data);
  performancePayload = normalized;
  performanceLabels = performancePayload.labels || [];
  attendanceSeries = performancePayload.attendance_rate || [];
  productivitySeries = performancePayload.productivity_score || [];
  qualitySeries = performancePayload.quality_score || [];
  overtimeSeries = performancePayload.overtime_hours || [];

  if (performanceTrendChart) {
    performanceTrendChart.data.labels = performanceLabels;
    performanceTrendChart.data.datasets[0].data = attendanceSeries;
    performanceTrendChart.data.datasets[1].data = productivitySeries;
    performanceTrendChart.data.datasets[2].data = qualitySeries;
    performanceTrendChart.update();
  }

  if (overtimeChart) {
    overtimeChart.data.labels = performanceLabels;
    overtimeChart.data.datasets[0].data = overtimeSeries;
    overtimeChart.update();
  }
}

function loadPerformanceMetrics() {
  fetch('/api/dashboard/performance-metrics')
    .then(response => response.json())
    .then(payload => {
      if (payload.success && payload.data) {
        updatePerformanceCharts(payload.data);
      }
    })
    .catch(error => {
      console.error('Error loading performance metrics:', error);
    });
}

document.addEventListener('DOMContentLoaded', function() {
  initPerformanceCharts();
  loadPerformanceMetrics();

  const employeeContainers = document.querySelectorAll('.employee-list-container');
  employeeContainers.forEach(container => {
    function checkScrollNeeded() {
      const isScrollable = container.scrollHeight > container.clientHeight;
      if (!isScrollable) {
        const scrollIndicator = container.querySelector('.scroll-indicator');
        if (scrollIndicator) {
          scrollIndicator.style.display = 'none';
        }
      }
    }

    container.addEventListener('scroll', function() {
      const scrollTop = container.scrollTop;
      const scrollHeight = container.scrollHeight;
      const clientHeight = container.clientHeight;
      const isAtBottom = scrollTop + clientHeight >= scrollHeight - 5;

      if (isAtBottom) {
        container.classList.add('scrolled-bottom');
      } else {
        container.classList.remove('scrolled-bottom');
      }
    });

    checkScrollNeeded();

    container.addEventListener('wheel', function(e) {
      e.preventDefault();
      container.scrollTop += e.deltaY * 0.5;
    });
  });
});

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}

// Recent Time Entries Functions
function refreshRecentTimeEntries() {
  console.log('🔄 Refreshing Recent Time Entries...');

  const refreshBtn = document.getElementById('refresh-time-entries-btn');
  if (refreshBtn) {
    const originalIcon = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    refreshBtn.disabled = true;

    setTimeout(() => {
      refreshBtn.innerHTML = originalIcon;
      refreshBtn.disabled = false;
    }, 2000);
  }

  fetch('/recent-time-entries')
    .then(response => {
      console.log('📡 Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('📊 Recent Time Entries response:', data);

      if (data.success && data.entries) {
        updateRecentTimeEntriesTable(data.entries);
        console.log('✅ Recent Time Entries updated successfully');
        showNotification('Time entries refreshed successfully!', 'success');
      } else {
        console.error('❌ Failed to get recent time entries:', data.message);
        showNotification(`Failed to refresh time entries: ${data.message || 'Unknown error'}`, 'error');
      }
    })
    .catch(error => {
      console.error('❌ Error refreshing recent time entries:', error);
      showNotification(`Error occurred while refreshing time entries: ${error.message}`, 'error');
    });
}

function updateRecentTimeEntriesTable(entries) {
  const tableBody = document.querySelector('#recent-entries-tbody');
  if (!tableBody) return;

  tableBody.style.opacity = '0.7';

  let tableHtml = '';

  if (entries.length === 0) {
    tableHtml = `
      <tr>
        <td colspan="6" class="text-center text-muted py-4">
          <i class="fas fa-clock me-2"></i>No recent time entries found
        </td>
      </tr>
    `;
  } else {
    entries.forEach(entry => {
      const totalHoursDisplay = entry.total_hours !== null ?
        (entry.total_hours >= 8 ?
          `<span class="text-success fw-bold">${entry.formatted_total_time || parseFloat(entry.total_hours).toFixed(2) + ' hrs'}</span>` :
          `<span class="text-muted">${entry.formatted_total_time || parseFloat(entry.total_hours).toFixed(2) + ' hrs'}</span>`) :
        '<span class="text-muted">--</span>';

      const hasClockIn = entry.formatted_clock_in && entry.formatted_clock_in !== '--';
      const hasClockOut = entry.formatted_clock_out && entry.formatted_clock_out !== '--';
      let statusBadge = '<span class="badge bg-secondary">No Entry</span>';
      if (hasClockOut) {
        statusBadge = '<span class="badge bg-success">Clocked Out</span>';
      } else if (hasClockIn) {
        statusBadge = '<span class="badge bg-warning text-dark">Clocked In</span>';
      }

      const profileImage = entry.profile_picture ?
        `<img src="/storage/${entry.profile_picture}" alt="${entry.employee_name}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">` :
        `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
          ${entry.employee_name.charAt(0)}
        </div>`;

      tableHtml += `
        <tr>
          <td>
            <div class="d-flex align-items-center">
              ${profileImage}
              <span class="fw-medium">${entry.employee_name}</span>
            </div>
          </td>
          <td>${entry.work_date}</td>
          <td>${entry.formatted_clock_in}</td>
          <td>${entry.formatted_clock_out}</td>
          <td>${totalHoursDisplay}</td>
          <td>${statusBadge}</td>
        </tr>
      `;
    });
  }

  tableBody.innerHTML = tableHtml;

  setTimeout(() => {
    tableBody.style.opacity = '1';
  }, 200);
}
</script>

<style>
/* Dashboard Layout Fix - Remove Vertical Scroll */
.dashboard-section {
  margin-bottom: 1.5rem;
}

.dashboard-section:last-child {
  margin-bottom: 1rem; /* Add bottom margin to prevent cutoff */
}

/* Ensure content doesn't get cut off by scrollbar */
.row {
  margin-right: 0;
  margin-left: 0;
}

.card, .stat-card-modern {
  margin-right: 0;
}

/* Table responsive container */
.table-responsive {
  margin-right: 0;
  overflow-x: auto;
}

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
  min-height: 240px;
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

/* Employee list container with vertical scrolling - Limited to 2 visible employees */
.employee-list-container {
  max-height: 150px; /* Height for exactly 2 employee items (60px each) */
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

/* Improved shift card alignment - Fill available space */
.shift-cards-container .row {
  display: flex;
  flex-wrap: wrap;
  justify-content: stretch;
  align-items: stretch;
  margin: 0;
}

.shift-cards-container .row > div {
  display: flex;
  margin-bottom: 1rem;
  padding: 0 0.5rem;
}

/* Remove extra margins and ensure full width coverage */
.shift-cards-container {
  padding: 0;
}

.shift-card-hr {
  width: 100%;
  min-height: 280px;
  margin: 0.25rem;
  flex: 1;
}

/* Responsive card layout */
@media (min-width: 992px) {
  .shift-cards-container .row {
    justify-content: center;
  }
}

@media (min-width: 768px) and (max-width: 991px) {
  .shift-cards-container .row {
    justify-content: flex-start;
  }
  
  .shift-card-hr {
    min-height: 260px;
  }
}

@media (max-width: 767px) {
  .shift-cards-container .row {
    justify-content: center;
  }
  
  .shift-card-hr {
    min-height: 240px;
    margin: 0.125rem;
  }
}

/* Performance Graphs */
.performance-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 4px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
}

.performance-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--jetlouge-primary), var(--jetlouge-secondary));
}

.performance-card .card-body {
  padding: 20px 22px;
}

.performance-badge {
  background: rgba(0, 123, 255, 0.12);
  color: #1f2a44;
  font-weight: 600;
  border-radius: 16px;
  padding: 0.4rem 0.75rem;
  border: 1px solid rgba(0, 123, 255, 0.15);
}

.performance-badge.bg-success {
  background: rgba(40, 167, 69, 0.15) !important;
  color: #1e7e34 !important;
  border-color: rgba(40, 167, 69, 0.2) !important;
}

.performance-badge.bg-warning {
  background: rgba(255, 193, 7, 0.18) !important;
  color: #8a6d00 !important;
  border-color: rgba(255, 193, 7, 0.3) !important;
}

.chart-wrapper {
  position: relative;
  min-height: 230px;
}

.performance-kpi {
  background: #ffffff;
  border-radius: 14px;
  padding: 20px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
  position: relative;
  overflow: hidden;
}

.performance-kpi::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #007bff, #17a2b8);
}

.performance-kpi .kpi-label {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.performance-kpi .kpi-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1f2937;
}

.performance-kpi .kpi-note {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 4px;
}

.metric-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #374151;
  padding: 0.45rem 0;
  border-bottom: 1px dashed rgba(0, 0, 0, 0.08);
}

.metric-row:last-child {
  border-bottom: none;
}

/* Notification styling */
.alert.position-fixed {
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
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
    min-height: 220px;
    padding: 15px !important;
  }
  
  .employee-list-hr {
    position: relative;
    padding-bottom: 5px;
  }
  
  .employee-list-container {
    max-height: 110px;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 3px;
    padding-right: 10px;
    margin-right: -3px;
    scroll-behavior: smooth;
  }
  
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

  .performance-kpi {
    padding: 16px;
  }
}

/* Recent Time Entries Table Animation */
#recent-entries-tbody {
  transition: opacity 0.3s ease;
}

/* Recent Time Entries Scroll Container - Show only 3 entries */
.recent-entries-scroll-container {
  max-height: 156px; /* Height for exactly 3 table rows (52px each) */
  overflow-y: auto;
  overflow-x: hidden;
  border: 1px solid #dee2e6;
  border-top: none; /* Remove top border since header table has bottom border */
  border-radius: 0 0 0.375rem 0.375rem; /* Round bottom corners */
}

/* Scrollbar styling for Recent Time Entries */
.recent-entries-scroll-container::-webkit-scrollbar {
  width: 8px;
  background: transparent;
}

.recent-entries-scroll-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  margin: 2px 0;
}

.recent-entries-scroll-container::-webkit-scrollbar-thumb {
  background: rgba(0, 123, 255, 0.2);
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  opacity: 0.6;
}

.recent-entries-scroll-container:hover::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.7);
  border: 1px solid rgba(0, 123, 255, 0.3);
}

.recent-entries-scroll-container:hover::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 123, 255, 0.9);
}

/* Firefox scrollbar styling */
.recent-entries-scroll-container:hover {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.7) rgba(0, 0, 0, 0.1);
}

/* Ensure table columns align with header */
.recent-entries-scroll-container table {
  table-layout: fixed;
  width: 100%;
}

.recent-entries-scroll-container table th,
.recent-entries-scroll-container table td {
  border-top: none; /* Remove top borders since we have container border */
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
