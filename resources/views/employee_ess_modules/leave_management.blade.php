<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Leave Management - Employee Portal</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  
  <style>
    :root {
      --jetlouge-primary: #2c3e50;
      --jetlouge-secondary: #3498db;
      --jetlouge-accent: #e74c3c;
      --jetlouge-light: #ecf0f1;
      --jetlouge-dark: #34495e;
    }
    
    .leave-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }
    
    .leave-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .balance-card {
      background: linear-gradient(135deg, var(--jetlouge-primary), var(--jetlouge-secondary));
      color: white;
      border-radius: 15px;
    }
    
    .working-modal {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      z-index: 2000 !important;
      background: rgba(0, 0, 0, 0.5) !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }

    .working-modal .modal-dialog {
      position: relative !important;
      z-index: 2001 !important;
      margin: 0 !important;
    }

    .working-modal input,
    .working-modal select,
    .working-modal textarea,
    .working-modal button {
      pointer-events: auto !important;
      z-index: 2002 !important;
    }

    .working-modal .form-control,
    .working-modal .form-select {
      background-color: #fff !important;
      border: 1px solid #ced4da !important;
      color: #495057 !important;
    }
  </style>
  <link rel="stylesheet" href="{{ asset('assets/css/working-modal-ess.css') }}">
</head>
<body style="background-color: #f8f9fa !important;">

  <!-- Employee Topbar -->
  @include('employee_ess_modules.partials.employee_topbar')

  <!-- Employee Sidebar -->
  @include('employee_ess_modules.partials.employee_sidebar')

  <!-- Main Content -->
  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Leave Management</h2>
            <p class="text-muted mb-0">Apply for leave and track your requests</p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Leave Management</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="working-modal-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="working-modal-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- Leave Balance Cards -->
    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="row g-3">
          @forelse($leaveBalances ?? [] as $balance)
            <div class="col-md-6">
              <div class="card leave-card">
                <div class="card-body text-center">
                  <div class="mb-3">
                    <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                  </div>
                  <h5 class="card-title">{{ $balance->leave_type_name ?? 'Leave Type' }}</h5>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <small class="text-muted">Available</small>
                      <div class="fw-bold text-success">{{ $balance->available_days ?? 0 }} days</div>
                    </div>
                    <div>
                      <small class="text-muted">Used</small>
                      <div class="fw-bold text-warning">{{ $balance->used_days ?? 0 }} days</div>
                    </div>
                    <div>
                      <small class="text-muted">Total</small>
                      <div class="fw-bold text-primary">{{ $balance->total_days ?? 0 }} days</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="col-12">
              <div class="card leave-card">
                <div class="card-body text-center py-5">
                  <i class="bi bi-calendar-x text-muted display-4 mb-3"></i>
                  <h5 class="text-muted">No Leave Balances Found</h5>
                  <p class="text-muted">Contact HR to set up your leave entitlements.</p>
                </div>
              </div>
            </div>
          @endforelse
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card balance-card h-100">
          <div class="card-body text-center d-flex flex-column justify-content-center">
            <i class="bi bi-calendar-plus mb-3" style="font-size: 3rem;"></i>
            <h4 class="mb-3">Apply for Leave</h4>
            <p class="mb-4">Submit a new leave request for approval</p>
            <button class="btn btn-light btn-lg" onclick="openWorkingModal('leaveApplicationModal')">
              <i class="bi bi-plus-circle me-2"></i>New Leave Request
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Leave Requests -->
    <div class="row">
      <div class="col-12">
        <div class="card leave-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="bi bi-list-ul me-2"></i>My Leave Requests
            </h5>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" id="statusFilter" onchange="filterRequests()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
              <button class="btn btn-outline-secondary btn-sm" onclick="refreshRequests()">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="leaveRequestsBody">
                  @forelse($leaveRequests ?? [] as $request)
                    <tr>
                      <td>{{ $request->leave_type_name ?? 'Unknown' }}</td>
                      <td>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</td>
                      <td>{{ $request->total_days ?? 1 }} days</td>
                      <td>
                        @php
                          $statusClass = match($request->status ?? 'pending') {
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'pending' => 'bg-warning',
                            default => 'bg-secondary'
                          };
                        @endphp
                        <span class="badge {{ $statusClass }}">
                          {{ ucfirst($request->status ?? 'pending') }}
                        </span>
                      </td>
                      <td>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</td>
                      <td>
                        <div class="btn-group btn-group-sm">
                          <button class="btn btn-outline-primary" onclick="viewLeaveRequest({{ $request->id }})">
                            <i class="bi bi-eye"></i>
                          </button>
                          @if(($request->status ?? 'pending') === 'pending')
                            <button class="btn btn-outline-danger" onclick="cancelLeaveRequest({{ $request->id }})">
                              <i class="bi bi-x-circle"></i>
                            </button>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        No leave requests found. Apply for your first leave request.
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

  </main>

  <!-- Leave Application Modal -->
  <div class="modal fade working-modal" id="leaveApplicationModal" tabindex="-1">
    <div class="working-modal-dialog">
      <div class="working-modal-content">
        <div class="working-modal-header">
          <h5 class="working-modal-title">Apply for Leave</h5>
          <button type="button" class="working-modal-close" onclick="closeWorkingModal(this.closest('.working-modal').id)"></button>
        </div>
        <form action="{{ route('employee.leave.store') }}" method="POST" id="leaveApplicationForm">
          @csrf
          <div class="working-modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label for="leave_type_id" class="form-label">Leave Type *</label>
                <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                  <option value="">Select leave type</option>
                  @foreach($leaveTypes ?? [] as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label for="start_date" class="form-label">Start Date *</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
              </div>
              <div class="col-md-6">
                <label for="end_date" class="form-label">End Date *</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
              </div>
              <div class="col-12">
                <div class="alert alert-info" id="leaveDaysInfo" style="display: none;">
                  <i class="bi bi-info-circle me-2"></i>
                  Total days: <span id="totalDays">0</span>
                </div>
              </div>
              <div class="col-12">
                <label for="reason" class="form-label">Reason *</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" 
                          placeholder="Please provide a reason for your leave request..." required></textarea>
              </div>
            </div>
          </div>
          <div class="working-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeWorkingModal(this.closest('.working-modal').id)">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-2"></i>Submit Application
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Leave Request Modal -->
  <div class="modal fade working-modal" id="viewLeaveModal" tabindex="-1">
    <div class="working-modal-dialog modal-lg">
      <div class="working-modal-content">
        <div class="working-modal-header">
          <h5 class="working-modal-title">Leave Request Details</h5>
          <button type="button" class="working-modal-close" onclick="closeWorkingModal(this.closest('.working-modal').id)"></button>
        </div>
        <div class="working-modal-body" id="leaveRequestDetails">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="working-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeWorkingModal(this.closest('.working-modal').id)">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Set minimum date to today
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('start_date').min = today;
      document.getElementById('end_date').min = today;
      
      // Calculate leave days when dates change
      document.getElementById('start_date').addEventListener('change', calculateLeaveDays);
      document.getElementById('end_date').addEventListener('change', calculateLeaveDays);
    });

    function calculateLeaveDays() {
      const startDate = new Date(document.getElementById('start_date').value);
      const endDate = new Date(document.getElementById('end_date').value);
      
      if (startDate && endDate && endDate >= startDate) {
        const timeDiff = endDate.getTime() - startDate.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
        
        document.getElementById('totalDays').textContent = daysDiff;
        document.getElementById('leaveDaysInfo').style.display = 'block';
        
        // Update end date minimum
        document.getElementById('end_date').min = document.getElementById('start_date').value;
      } else {
        document.getElementById('leaveDaysInfo').style.display = 'none';
      }
    }

    function viewLeaveRequest(requestId) {
      const modal = new bootstrap.Modal(document.getElementById('viewLeaveModal'));
      
      fetch(`/employee/leave-request/${requestId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const request = data.request;
          const statusClass = {
            'approved': 'success',
            'rejected': 'danger',
            'pending': 'warning'
          }[request.status] || 'secondary';
          
          document.getElementById('leaveRequestDetails').innerHTML = `
            <div class="row g-3">
              <div class="col-md-6">
                <strong>Leave Type:</strong><br>
                ${request.leave_type_name}
              </div>
              <div class="col-md-6">
                <strong>Status:</strong><br>
                <span class="badge bg-${statusClass}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
              </div>
              <div class="col-md-4">
                <strong>Start Date:</strong><br>
                ${new Date(request.start_date).toLocaleDateString('en-US', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}
              </div>
              <div class="col-md-4">
                <strong>End Date:</strong><br>
                ${new Date(request.end_date).toLocaleDateString('en-US', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}
              </div>
              <div class="col-md-4">
                <strong>Total Days:</strong><br>
                ${request.total_days} days
              </div>
              <div class="col-12">
                <strong>Reason:</strong><br>
                <div class="bg-light p-3 rounded mt-2">
                  ${request.reason}
                </div>
              </div>
              <div class="col-md-6">
                <strong>Applied:</strong><br>
                ${new Date(request.created_at).toLocaleDateString('en-US', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                })}
              </div>
              ${request.approved_at ? `
              <div class="col-md-6">
                <strong>Processed:</strong><br>
                ${new Date(request.approved_at).toLocaleDateString('en-US', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                })}
              </div>
              ` : ''}
            </div>
          `;
        } else {
          document.getElementById('leaveRequestDetails').innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Error loading request details: ${data.message || 'Unknown error'}
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('leaveRequestDetails').innerHTML = `
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Error loading request details. Please try again.
          </div>
        `;
      });
      
      modal.show();
    }

    function cancelLeaveRequest(requestId) {
      if (confirm('Are you sure you want to cancel this leave request?')) {
        fetch(`/employee/leave-request/${requestId}/cancel`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error cancelling request: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error cancelling request. Please try again.');
        });
      }
    }

    function filterRequests() {
      const status = document.getElementById('statusFilter').value;
      const params = new URLSearchParams();
      if (status) params.append('status', status);
      window.location.href = `{{ route('employee.leave') }}?${params.toString()}`;
    }

    function refreshRequests() {
      location.reload();
    }
  </script>

  <script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
</body>
</html>
