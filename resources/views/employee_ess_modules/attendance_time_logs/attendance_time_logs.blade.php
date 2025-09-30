@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Attendance & Time Logs')

@section('styles')
  <style>
    .clock-container {
      background: linear-gradient(135deg, #3f37c9, #4361ee);
      border-radius: 16px;
      color: white;
      padding: 2rem;
      text-align: center;
      margin-bottom: 2rem;
      box-shadow: 0 10px 30px rgba(67, 97, 238, 0.15);
    }
    
    #current-time {
      font-size: 4rem;
      font-weight: 700;
      letter-spacing: 2px;
      font-family: 'Courier New', monospace;
    }
    
    #current-date {
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
      opacity: 0.9;
    }
    
    .attendance-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .btn-time-in {
      background: linear-gradient(135deg, #4cc9f0, #4895ef);
      border: none;
      color: white;
      padding: 0.8rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .btn-time-out {
      background: linear-gradient(135deg, #f72585, #b5179e);
      border: none;
      color: white;
      padding: 0.8rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .btn-time-in:hover, .btn-time-out:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      color: white;
    }
    
    .btn-time-in:disabled, .btn-time-out:disabled {
      opacity: 0.7;
      transform: none;
    }
    
    @media (max-width: 768px) {
      #current-time {
        font-size: 2.5rem;
      }
      
      .attendance-actions {
        flex-direction: column;
        align-items: center;
      }
      
      .btn-time-in, .btn-time-out {
        width: 100%;
        max-width: 300px;
      }
    }
  </style>
@endsection

@section('content')

    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Attendance & Time Logs</h2>
            <p class="text-muted mb-0">Track your attendance and manage work hours</p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance & Time Logs</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Real-time Clock & Attendance Actions -->
    <div class="clock-container">
      <div id="current-date"></div>
      <div id="current-time"></div>
      <div class="attendance-actions mt-4">
        <button id="time-in-btn" class="btn btn-time-in">
          <i class="bi bi-alarm-fill me-2"></i>Time In
        </button>
        <button id="time-out-btn" class="btn btn-time-out" disabled>
          <i class="bi bi-alarm me-2"></i>Time Out
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="stats-card fade-in" style="animation-delay: 0s;">
          <div class="stats-icon primary">
            <i class="bi bi-clock"></i>
          </div>
          <div class="stats-value" id="today-hours">{{ $stats['today_hours'] ?? '0h 0m' }}</div>
          <p class="stats-label">Today's Hours</p>
          <p class="stats-sublabel">Time worked today</p>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="stats-card fade-in" style="animation-delay: 0.1s;">
          <div class="stats-icon success">
            <i class="bi bi-calendar-week"></i>
          </div>
          <div class="stats-value" id="week-hours">{{ $stats['week_hours'] ?? '0h 0m' }}</div>
          <p class="stats-label">This Week</p>
          <p class="stats-sublabel">Total hours this week</p>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="stats-card fade-in" style="animation-delay: 0.2s;">
          <div class="stats-icon info">
            <i class="bi bi-percent"></i>
          </div>
          <div class="stats-value" id="attendance-rate">{{ $stats['attendance_rate'] ?? '0%' }}</div>
          <p class="stats-label">Attendance Rate</p>
          <p class="stats-sublabel">This month</p>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="stats-card fade-in" style="animation-delay: 0.3s;">
          <div class="stats-icon warning">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div class="stats-value" id="late-count">{{ $stats['late_count'] ?? '0' }}</div>
          <p class="stats-label">Late Arrivals</p>
          <p class="stats-sublabel">This month</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="ess-card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Attendance Records</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label for="month-filter" class="form-label">Month</label>
            <select class="form-select" id="month-filter">
              <option value="">All Months</option>
              <option value="1">January</option>
              <option value="2">February</option>
              <option value="3">March</option>
              <option value="4">April</option>
              <option value="5">May</option>
              <option value="6">June</option>
              <option value="7">July</option>
              <option value="8">August</option>
              <option value="9">September</option>
              <option value="10">October</option>
              <option value="11">November</option>
              <option value="12">December</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label for="year-filter" class="form-label">Year</label>
            <select class="form-select" id="year-filter">
              <option value="">All Years</option>
              <option value="2023">2023</option>
              <option value="2024" selected>2024</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label for="status-filter" class="form-label">Status</label>
            <select class="form-select" id="status-filter">
              <option value="">All Status</option>
              <option value="Present">Present</option>
              <option value="Absent">Absent</option>
              <option value="Late">Late</option>
              <option value="Early Departure">Early Departure</option>
            </select>
          </div>
          <div class="col-md-3 mb-3 d-flex align-items-end">
            <button id="reset-filters" class="btn btn-outline-jetlouge w-100">
              <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Logs Table -->
    <div class="ess-card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Daily Attendance Logs</h5>
        <div>
          <button class="btn btn-sm btn-outline-jetlouge me-2">
            <i class="bi bi-download me-1"></i> Export
          </button>
          <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Print
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-clean" id="attendance-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours Worked</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($attendance_logs as $log)
                <tr>
                  <td>{{ $log->id }}</td>
                  <td>{{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}</td>
                  <td>
                    @if($log->time_in)
                      @php
                        try {
                          $timeIn = \Carbon\Carbon::parse($log->time_in);
                          echo $timeIn->format('g:i A');
                        } catch (Exception $e) {
                          // Try to extract time from datetime string
                          if (strpos($log->time_in, ' ') !== false) {
                            $timePart = explode(' ', $log->time_in)[1];
                            $timeIn = \Carbon\Carbon::createFromFormat('H:i:s', $timePart);
                            echo $timeIn->format('g:i A');
                          } else {
                            echo $log->time_in;
                          }
                        }
                      @endphp
                    @else
                      --:--
                    @endif
                  </td>
                  <td>
                    @if($log->time_out)
                      @php
                        try {
                          $timeOut = \Carbon\Carbon::parse($log->time_out);
                          echo $timeOut->format('g:i A');
                        } catch (Exception $e) {
                          // Try to extract time from datetime string
                          if (strpos($log->time_out, ' ') !== false) {
                            $timePart = explode(' ', $log->time_out)[1];
                            $timeOut = \Carbon\Carbon::createFromFormat('H:i:s', $timePart);
                            echo $timeOut->format('g:i A');
                          } else {
                            echo $log->time_out;
                          }
                        }
                      @endphp
                    @else
                      --:--
                    @endif
                  </td>
                  <td>
                    @if($log->hours_worked)
                      @php
                        $hours = floor($log->hours_worked);
                        $minutes = round(($log->hours_worked - $hours) * 60);
                        echo "{$hours}h {$minutes}m";
                      @endphp
                    @else
                      0h 0m
                    @endif
                  </td>
                  <td>
                    <span class="badge-clean status-{{ strtolower($log->status) }}">
                      {{ $log->status }}
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-jetlouge">
                      <i class="bi bi-three-dots"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-info-circle me-2"></i>No attendance logs found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          <nav aria-label="Attendance pagination">
            <ul class="pagination">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
              </li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

@endsection

@section('scripts')
  <script>
    // Real-time clock functionality
    function updateClock() {
      const now = new Date();
      const timeEl = document.getElementById('current-time');
      const dateEl = document.getElementById('current-date');
      
      // Format time (12-hour format)
      let hours = now.getHours();
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const seconds = now.getSeconds().toString().padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12; // 0 should be 12
      const displayHours = hours.toString().padStart(2, '0');
      timeEl.textContent = `${displayHours}:${minutes}:${seconds} ${ampm}`;
      
      // Format date
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      dateEl.textContent = now.toLocaleDateString('en-US', options);
    }
    
    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
    
    // Time In/Out functionality
    document.getElementById('time-in-btn').addEventListener('click', function() {
      const now = new Date();
      let hours = now.getHours();
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12;
      const timeString = `${hours}:${minutes} ${ampm}`;
      
      // Show confirmation
      if (confirm(`Time In at ${timeString}?`)) {
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
        
        // Send AJAX request
        fetch('{{ route("employee.attendance.time_in") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update UI
            document.getElementById('time-out-btn').disabled = false;
            this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked In';
            
            // Show notification
            showNotification(data.message, 'success');
            
            // Update today's hours display
            updateTodayHours();
          } else {
            // Re-enable button on error
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
            showNotification(data.message, 'danger');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
          showNotification('Error recording time in. Please try again.', 'danger');
        });
      }
    });
    
    document.getElementById('time-out-btn').addEventListener('click', function() {
      const now = new Date();
      let hours = now.getHours();
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12;
      const timeString = `${hours}:${minutes} ${ampm}`;
      
      // Show confirmation
      if (confirm(`Time Out at ${timeString}?`)) {
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
        
        // Send AJAX request
        fetch('{{ route("employee.attendance.time_out") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update UI
            this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked Out';
            
            // Show notification
            showNotification(data.message, 'success');
            
            // Reload the page after 2 seconds to show updated attendance
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            // Re-enable button on error
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-alarm me-2"></i>Time Out';
            showNotification(data.message, 'danger');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-alarm me-2"></i>Time Out';
          showNotification('Error recording time out. Please try again.', 'danger');
        });
      }
    });
    
    // Filter functionality
    document.getElementById('month-filter').addEventListener('change', filterTable);
    document.getElementById('year-filter').addEventListener('change', filterTable);
    document.getElementById('status-filter').addEventListener('change', filterTable);
    
    document.getElementById('reset-filters').addEventListener('click', function() {
      document.getElementById('month-filter').value = '';
      document.getElementById('year-filter').value = '2024';
      document.getElementById('status-filter').value = '';
      filterTable();
    });
    
    function filterTable() {
      const monthFilter = document.getElementById('month-filter').value;
      const yearFilter = document.getElementById('year-filter').value;
      const statusFilter = document.getElementById('status-filter').value;
      
      const rows = document.querySelectorAll('#attendance-table tbody tr');
      
      rows.forEach(row => {
        let showRow = true;
        const dateCell = row.cells[1].textContent;
        const statusCell = row.cells[5].querySelector('.badge').textContent;
        
        // Apply month filter
        if (monthFilter && dateCell) {
          const date = new Date(dateCell);
          if (date.getMonth() + 1 != monthFilter) {
            showRow = false;
          }
        }
        
        // Apply year filter
        if (yearFilter && dateCell) {
          const date = new Date(dateCell);
          if (date.getFullYear() != yearFilter) {
            showRow = false;
          }
        }
        
        // Apply status filter
        if (statusFilter && statusCell !== statusFilter) {
          showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
      });
    }
    
    // Notification function
    function showNotification(message, type) {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      // Add to page
      document.body.appendChild(notification);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 5000);
    }
    
    // Check current attendance status from server
    function checkAttendanceStatus() {
      fetch('{{ route("employee.attendance.status") }}', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const timeInBtn = document.getElementById('time-in-btn');
          const timeOutBtn = document.getElementById('time-out-btn');
          
          if (data.has_timed_in && !data.has_timed_out) {
            // Already timed in, waiting for time out
            timeInBtn.disabled = true;
            timeInBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked In';
            timeOutBtn.disabled = false;
          } else if (data.has_timed_out) {
            // Already completed for the day
            timeInBtn.disabled = false;
            timeInBtn.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
            timeOutBtn.disabled = true;
            timeOutBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Clocked Out';
          } else {
            // Ready to time in
            timeInBtn.disabled = false;
            timeInBtn.innerHTML = '<i class="bi bi-alarm-fill me-2"></i>Time In';
            timeOutBtn.disabled = true;
            timeOutBtn.innerHTML = '<i class="bi bi-alarm me-2"></i>Time Out';
          }
        }
      })
      .catch(error => {
        console.error('Error checking attendance status:', error);
      });
    }
    
    // Update today's hours in real-time
    function updateTodayHours() {
      // This function updates the today's hours display
      // It will be called after time in to show live hours
      setInterval(() => {
        fetch('{{ route("employee.attendance.status") }}', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.has_timed_in && !data.has_timed_out && data.time_in) {
            // Calculate current working hours
            const timeIn = new Date();
            const [hours, minutes, seconds] = data.time_in.split(':');
            timeIn.setHours(hours, minutes, seconds);
            
            const now = new Date();
            const diffMs = now - timeIn;
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            
            document.getElementById('today-hours').textContent = `${diffHours}h ${diffMinutes}m`;
          }
        })
        .catch(error => {
          console.error('Error updating hours:', error);
        });
      }, 60000); // Update every minute
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      checkAttendanceStatus();
      
      // Start real-time updates if user has timed in
      fetch('{{ route("employee.attendance.status") }}', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.has_timed_in && !data.has_timed_out) {
          updateTodayHours();
        }
      })
      .catch(error => {
        console.error('Error initializing:', error);
      });
    });
  </script>
@endsection