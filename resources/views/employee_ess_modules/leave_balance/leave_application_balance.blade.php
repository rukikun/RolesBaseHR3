@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Leave Balance & Applications')

@section('content')
<!-- Page Header -->
<div class="page-header-container fade-in">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="height: 40px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Leave Management</h2>
        <p class="text-muted mb-0">Manage your leave requests and track your remaining balances.</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Leave Management</li>
      </ol>
    </nav>
  </div>
</div>
<!-- Leave Balance Cards -->
<div class="row g-4 mb-4">
  @if(isset($leave_balances) && !empty($leave_balances))
    <div class="col-md-4">
      <div class="stats-card slide-up">
        <div class="stats-icon primary">
          <i class="bi bi-calendar-check"></i>
        </div>
        <div class="stats-value">{{ $leave_balances['Vacation']['available'] ?? 15 }}</div>
        <p class="stats-label">Vacation Leave</p>
        <p class="stats-sublabel">{{ $leave_balances['Vacation']['used'] ?? 0 }} used of {{ $leave_balances['Vacation']['total'] ?? 15 }}</p>
        <div class="progress-clean mt-3">
          <div class="progress-bar-clean progress-bar-primary" style="width: {{ $leave_balances['Vacation']['percentage'] ?? 100 }}%"></div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="stats-card slide-up" style="animation-delay: 0.1s;">
        <div class="stats-icon success">
          <i class="bi bi-heart-pulse"></i>
        </div>
        <div class="stats-value">{{ $leave_balances['Sick']['available'] ?? 10 }}</div>
        <p class="stats-label">Sick Leave</p>
        <p class="stats-sublabel">{{ $leave_balances['Sick']['used'] ?? 0 }} used of {{ $leave_balances['Sick']['total'] ?? 10 }}</p>
        <div class="progress-clean mt-3">
          <div class="progress-bar-clean progress-bar-success" style="width: {{ $leave_balances['Sick']['percentage'] ?? 100 }}%"></div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="stats-card slide-up" style="animation-delay: 0.2s;">
        <div class="stats-icon warning">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="stats-value">{{ $leave_balances['Emergency']['available'] ?? 5 }}</div>
        <p class="stats-label">Emergency Leave</p>
        <p class="stats-sublabel">{{ $leave_balances['Emergency']['used'] ?? 0 }} used of {{ $leave_balances['Emergency']['total'] ?? 5 }}</p>
        <div class="progress-clean mt-3">
          <div class="progress-bar-clean progress-bar-warning" style="width: {{ $leave_balances['Emergency']['percentage'] ?? 100 }}%"></div>
        </div>
      </div>
    </div>
  @else
    <div class="col-md-4">
      <div class="stats-card slide-up">
        <div class="stats-icon primary">
          <i class="bi bi-calendar-check"></i>
        </div>
        <div class="stats-value">15</div>
        <p class="stats-label">Vacation Leave</p>
        <p class="stats-sublabel">0 used of 15</p>
        <div class="progress-clean mt-3">
          <div class="progress-bar-clean progress-bar-primary" style="width: 100%"></div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="stats-card slide-up" style="animation-delay: 0.1s;">
        <div class="stats-icon success">
          <i class="bi bi-heart-pulse"></i>
        </div>
        <div class="stats-value">10</div>
        <p class="stats-label">Sick Leave</p>
        <p class="stats-sublabel">0 used of 10</p>
        <div class="progress-clean mt-3">
          <div class="progress-bar-clean progress-bar-success" style="width: 100%"></div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="stats-card slide-up" style="animation-delay: 0.2s;">
        <div class="stats-icon warning">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="stats-value">5</div>
        <p class="stats-label">Emergency Leave</p>
        <p class="stats-sublabel">0 used of 5</p>
        <div class="progress-clean mt-3">
          <div class="progress-bar-clean progress-bar-warning" style="width: 100%"></div>
        </div>
      </div>
    </div>
  @endif
</div>

<!-- Leave Calendar -->
<div class="calendar-clean slide-up" style="animation-delay: 0.3s;">
  <div class="calendar-header">
    <h5 class="calendar-title">Leave Calendar</h5>
    <div>
      <button class="btn btn-jetlouge-outline btn-sm me-2" id="prev-month">
        <i class="bi bi-chevron-left"></i>
      </button>
      <span class="fw-semibold" id="current-month">November 2023</span>
      <button class="btn btn-jetlouge-outline btn-sm ms-2" id="next-month">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
  <div class="calendar-grid" id="calendar-days">
    <!-- Calendar days will be populated by JavaScript -->
  </div>
</div>

<!-- Filters -->
<div class="ess-card mb-4 slide-up" style="animation-delay: 0.4s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-funnel"></i>
      Filter Options
    </h5>
  </div>
  <div class="ess-card-body">
    <div class="row g-3">
      <div class="col-md-3">
        <label for="leave-type-filter" class="form-label">Leave Type</label>
        <select class="form-select" id="leave-type-filter">
          <option value="">All Types</option>
          <option value="Vacation">Vacation</option>
          <option value="Sick">Sick</option>
          <option value="Emergency">Emergency</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="status-filter" class="form-label">Status</label>
        <select class="form-select" id="status-filter">
          <option value="">All Status</option>
          <option value="Approved">Approved</option>
          <option value="Pending">Pending</option>
          <option value="Rejected">Rejected</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="date-filter" class="form-label">Date Range</label>
        <select class="form-select" id="date-filter">
          <option value="">All Dates</option>
          <option value="this_month">This Month</option>
          <option value="last_month">Last Month</option>
          <option value="this_year">This Year</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button id="reset-filters" class="btn btn-jetlouge-outline w-100">Reset Filters</button>
      </div>
    </div>
  </div>
</div>

<!-- Leave Records Table -->
<div class="ess-card slide-up" style="animation-delay: 0.5s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-list-ul"></i>
      Leave Requests
    </h5>
    <button class="btn btn-jetlouge" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
      <i class="bi bi-plus-circle me-1"></i> Apply for Leave
    </button>
  </div>
  <div class="ess-card-body p-0">
    <div class="table-responsive">
      <table class="table table-clean mb-0" id="leave-table">
        <thead>
          <tr>
            <th>Leave ID</th>
            <th>Leave Type</th>
            <th>Leave Days</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Applied Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leave_records as $leave)
            <tr>
              <td>{{ $leave->leave_id }}</td>
              <td>
                <span class="badge-clean badge-primary">
                  {{ $leave->leave_type }}
                </span>
              </td>
              <td>{{ $leave->leave_days }}</td>
              <td>{{ $leave->start_date }}</td>
              <td>{{ $leave->end_date }}</td>
              <td>
                @php
                  $statusClass = match(strtolower($leave->status)) {
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger',
                    'cancelled' => 'badge-danger',
                    'processing' => 'badge-info',
                    default => 'badge-warning'
                  };
                @endphp
                <span class="badge-clean {{ $statusClass }}">
                  {{ $leave->status }}
                </span>
              </td>
              <td>{{ $leave->applied_date }}</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-action btn-outline-primary view-leave" data-id="{{ $leave->id }}" data-leave-id="{{ $leave->leave_id }}">
                    <i class="bi bi-eye"></i>
                  </button>
                  @if($leave->status === 'Pending')
                    <button class="btn btn-action btn-outline-secondary edit-leave" data-id="{{ $leave->id }}" data-leave-id="{{ $leave->leave_id }}">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('employee.leave_applications.cancel', $leave->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-action btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this leave application?')">
                        <i class="bi bi-x-circle"></i>
                      </button>
                    </form>
                  @else
                    <span class="text-muted small">{{ ucfirst(strtolower($leave->status)) }}</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-info-circle me-2"></i>No leave records found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
      border-radius: 12px 12px 0 0 !important;
    }
    
    .badge-simulation {
  color: #fff !important;

  border: 2px solid #3f37c9;
  box-shadow: 0 0 0 2px #fff, 0 2px 8px rgba(67, 97, 238, 0.10);

      padding: 0.5em 0.8em;
      font-weight: 500;
      letter-spacing: 0.5px;
      border-radius: 6px;
    }
    
    .table th {
  border-bottom: 3px solid #3f37c9 !important;
  background-color: #f8f9fa;
  font-weight: 700;
  color: #3f37c9;

      background-color: #f8f9fa;
      font-weight: 600;
      color: #495057;
    }
    
    .leave-balance-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .balance-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      position: relative;
      overflow: hidden;
    }
    
    .balance-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 6px;
      height: 100%;
    }
    
    .balance-card.vacation::before {
      background: linear-gradient(to bottom, var(--vacation-color), #3a86ff);
    }
    
    .balance-card.sick::before {
      background: linear-gradient(to bottom, var(--sick-color), #7209b7);
    }
    
    .balance-card.emergency::before {
      background: linear-gradient(to bottom, var(--emergency-color), #b5179e);
    }
    
    .balance-type {
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }
    
    .balance-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0.5rem 0;
      line-height: 1;
    }
    
    .balance-total {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .progress {
      height: 8px;
      border-radius: 4px;
      margin: 1rem 0;
      background-color: #e9ecef;
    }
    
    .progress-bar {
      border-radius: 4px;
    }
    
    .vacation .progress-bar {
      background: linear-gradient(to right, var(--vacation-color), #3a86ff);
    }
    
    .sick .progress-bar {
      background: linear-gradient(to right, var(--sick-color), #7209b7);
    }
    
    .emergency .progress-bar {
      background: linear-gradient(to right, var(--emergency-color), #b5179e);
    }
    
    .calendar-container {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .calendar-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 0.5rem;
    }
    
    .calendar-day {
      text-align: center;
      padding: 0.5rem;
      border-radius: 8px;
      font-weight: 500;
    }
    
    .calendar-day.header {
      font-weight: 600;
      color: #6c757d;
      background-color: #f8f9fa;
    }
    
    .calendar-day.leave {
      background-color: rgba(67, 97, 238, 0.1);
      color: var(--primary-color);
      font-weight: 600;
    }
    
    .calendar-day.today {
      background-color: var(--primary-color);
      color: white;
    }
    
    .calendar-day.weekend {
      color: #6c757d;
      background-color: #f8f9fa;
    }
    
    .filter-container {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .btn-apply-leave {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 50px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
      transition: all 0.3s ease;
    }
    
    .btn-apply-leave:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
    }
    
    .status-approved {
      background-color: #28a745 !important;
      color: #fff !important;
      border: 2px solid #28a745;
      font-weight: bold;
    }
    
    .status-pending {
      background-color: #ffc107 !important;
      color: #212529 !important;
      border: 2px solid #ffc107;
      font-weight: bold;
    }
    
    .status-rejected {
      background-color: #dc3545 !important;
      color: #fff !important;
      border: 2px solid #dc3545;
      font-weight: bold;
    }
    
    .status-cancelled {
      background-color: #dc3545 !important;
      color: #fff !important;
      border: 2px solid #dc3545;
      font-weight: bold;
    }
    
    .status-processing {
      background-color: #17a2b8 !important;
      color: #fff !important;
      border: 2px solid #17a2b8;
      font-weight: bold;
    }
    
    .leave-type-vacation {
      background-color: #007bff !important;
      color: #fff !important;
      border: 2px solid #007bff;
      font-weight: bold;
    }
    
    .leave-type-sick {
      background-color: #6f42c1 !important;
      color: #fff !important;
      border: 2px solid #6f42c1;
      font-weight: bold;
    }
    
    .leave-type-emergency {
      background-color: #fd7e14 !important;
      color: #fff !important;
      border: 2px solid #fd7e14;
      font-weight: bold;
    }
    
    .action-btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
      border-radius: 4px;
      margin-right: 0.25rem;
    }
    
    .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .modal-header {
      border-bottom: 1px solid #eaeaea;
      padding: 1.5rem;
    }
    
    .modal-footer {
      border-top: 1px solid #eaeaea;
      padding: 1rem 1.5rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      border: 1px solid #ced4da;
    }
    
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
      border-color: var(--primary-color);
    }
    
    @media (max-width: 768px) {
      .leave-balance-container {
        grid-template-columns: 1fr;
      }
      
      .calendar-grid {
        gap: 0.25rem;
      }
      
      .calendar-day {
        padding: 0.25rem;
        font-size: 0.875rem;
      }
      
      .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .action-btn {
        margin-right: 0;
        margin-bottom: 0.25rem;
      }
    }
  </style>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

<!-- Success/Error Messages -->
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<main id="main-content" style="margin-left: 280px; padding: 2rem; margin-top: 3.5rem;">
  @if(isset($error_message) && $error_message)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ $error_message }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  <!-- Page Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">Leave Management</h2>
          <p class="text-muted mb-0">Manage your leave requests and track your remaining balances.</p>
        </div>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Leave Management</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Leave Balance Cards -->
  <div class="leave-balance-container">
    @if(isset($leave_balances) && !empty($leave_balances))
      <div class="balance-card vacation">
        <div class="balance-type text-primary">Vacation Leave</div>
        <div class="balance-value">{{ $leave_balances['Vacation']['available'] ?? 15 }}</div>
        <div class="balance-total">out of {{ $leave_balances['Vacation']['total'] ?? 15 }} days remaining</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: {{ $leave_balances['Vacation']['percentage'] ?? 100 }}%" aria-valuenow="{{ $leave_balances['Vacation']['percentage'] ?? 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="balance-stats">
          <small class="text-muted">Used: {{ $leave_balances['Vacation']['used'] ?? 0 }} days • Available: {{ $leave_balances['Vacation']['available'] ?? 15 }} days</small>
        </div>
      </div>
      
      <div class="balance-card sick">
        <div class="balance-type" style="color: #560bad;">Sick Leave</div>
        <div class="balance-value">{{ $leave_balances['Sick']['available'] ?? 10 }}</div>
        <div class="balance-total">out of {{ $leave_balances['Sick']['total'] ?? 10 }} days remaining</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: {{ $leave_balances['Sick']['percentage'] ?? 100 }}%" aria-valuenow="{{ $leave_balances['Sick']['percentage'] ?? 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="balance-stats">
          <small class="text-muted">Used: {{ $leave_balances['Sick']['used'] ?? 0 }} days • Available: {{ $leave_balances['Sick']['available'] ?? 10 }} days</small>
        </div>
      </div>
      
      <div class="balance-card emergency">
        <div class="balance-type" style="color: #b5179e;">Emergency Leave</div>
        <div class="balance-value">{{ $leave_balances['Emergency']['available'] ?? 5 }}</div>
        <div class="balance-total">out of {{ $leave_balances['Emergency']['total'] ?? 5 }} days remaining</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: {{ $leave_balances['Emergency']['percentage'] ?? 100 }}%" aria-valuenow="{{ $leave_balances['Emergency']['percentage'] ?? 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="balance-stats">
          <small class="text-muted">Used: {{ $leave_balances['Emergency']['used'] ?? 0 }} days • Available: {{ $leave_balances['Emergency']['available'] ?? 5 }} days</small>
        </div>
      </div>
    @else
      <div class="balance-card vacation">
        <div class="balance-type text-primary">Vacation Leave</div>
        <div class="balance-value">15</div>
        <div class="balance-total">out of 15 days remaining</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="balance-stats">
          <small class="text-muted">Used: 0 days • Available: 15 days</small>
        </div>
      </div>
      
      <div class="balance-card sick">
        <div class="balance-type" style="color: #560bad;">Sick Leave</div>
        <div class="balance-value">10</div>
        <div class="balance-total">out of 10 days remaining</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="balance-stats">
          <small class="text-muted">Used: 0 days • Available: 10 days</small>
        </div>
      </div>
      
      <div class="balance-card emergency">
        <div class="balance-type" style="color: #f72585;">Emergency Leave</div>
        <div class="balance-value">5</div>
        <div class="balance-total">out of 5 days remaining</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="balance-stats">
          <small class="text-muted">Used: 0 days • Available: 5 days</small>
        </div>
      </div>
    @endif
  </div>

  <!-- Leave Calendar -->
  <div class="calendar-container">
    <div class="calendar-header">
      <h5 class="fw-bold mb-0">Leave Calendar</h5>
      <div>
        <button class="btn btn-sm btn-outline-secondary me-2" id="prev-month">
          <i class="bi bi-chevron-left"></i>
        </button>
        <span class="fw-semibold" id="current-month">November 2023</span>
        <button class="btn btn-sm btn-outline-secondary ms-2" id="next-month">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
    <div class="calendar-grid" id="calendar-days">
      <!-- Calendar days will be populated by JavaScript -->
    </div>
  </div>

  <!-- Filters -->
  <div class="filter-container">
    <div class="row">
      <div class="col-md-3 mb-2">
        <label for="leave-type-filter" class="form-label">Leave Type</label>
        <select class="form-select" id="leave-type-filter">
          <option value="">All Types</option>
          <option value="Vacation">Vacation</option>
          <option value="Sick">Sick</option>
          <option value="Emergency">Emergency</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label for="status-filter" class="form-label">Status</label>
        <select class="form-select" id="status-filter">
          <option value="">All Status</option>
          <option value="Approved">Approved</option>
          <option value="Pending">Pending</option>
          <option value="Rejected">Rejected</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label for="date-filter" class="form-label">Date Range</label>
        <select class="form-select" id="date-filter">
          <option value="">All Dates</option>
          <option value="this_month">This Month</option>
          <option value="last_month">Last Month</option>
          <option value="this_year">This Year</option>
        </select>
      </div>
      <div class="col-md-3 mb-2 d-flex align-items-end">
        <button id="reset-filters" class="btn btn-outline-secondary w-100">Reset Filters</button>
      </div>
    </div>
  </div>

  <!-- Leave Records Table -->
  <div class="simulation-card card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0">Leave Requests</h4>
      <button class="btn btn-apply-leave" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
        <i class="bi bi-plus-circle me-1"></i> Apply for Leave
      </button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="leave-table">
          <thead class="table-light">
            <tr>
              <th class="fw-bold">Leave ID</th>
              <th class="fw-bold">Leave Type</th>
              <th class="fw-bold">Leave Days</th>
              <th class="fw-bold">Start Date</th>
              <th class="fw-bold">End Date</th>
              <th class="fw-bold">Status</th>
              <th class="fw-bold">Applied Date</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($leave_records as $leave)
              <tr>
                <td>{{ $leave->leave_id }}</td>
                <td>
                  <span class="badge leave-type-{{ strtolower($leave->leave_type) }} badge-simulation">
                    {{ $leave->leave_type }}
                  </span>
                </td>
                <td>{{ $leave->leave_days }}</td>
                <td>{{ $leave->start_date }}</td>
                <td>{{ $leave->end_date }}</td>
                <td>
                  <span class="badge status-{{ strtolower($leave->status) }} badge-simulation">
                    {{ $leave->status }}
                  </span>
                </td>
                <td>{{ $leave->applied_date }}</td>
                <td class="text-center action-buttons">
                  <button class="btn btn-sm btn-info text-white action-btn view-leave" data-id="{{ $leave->id }}" data-leave-id="{{ $leave->leave_id }}">
                    <i class="bi bi-eye"></i>
                  </button>
                  @if($leave->status === 'Pending')
                    <button class="btn btn-sm btn-warning action-btn edit-leave" data-id="{{ $leave->id }}" data-leave-id="{{ $leave->leave_id }}">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('employee.leave_applications.cancel', $leave->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger action-btn" onclick="return confirm('Are you sure you want to cancel this leave application?')">
                        <i class="bi bi-x-circle"></i>
                      </button>
                    </form>
                  @else
                    <span class="text-muted small">{{ ucfirst(strtolower($leave->status)) }}</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No leave records found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">Showing 1 to 5 of 15 entries</div>
        <nav aria-label="Leave pagination">
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
</main>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="card-header modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Apply for Leave</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="leaveForm">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="leave_type" class="form-label">Leave Type</label>
              <select class="form-select" id="leave_type" name="leave_type" required>
                <option value="">Select Leave Type</option>
                <option value="Vacation">Vacation Leave</option>
                <option value="Sick">Sick Leave</option>
                <option value="Emergency">Emergency Leave</option>
              </select>
              <div class="form-text" id="leave-balance-text">Available balance: </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="leave_days" class="form-label">Number of Days</label>
              <input type="number" class="form-control" id="leave_days" name="leave_days" required min="1" onchange="calculateEndDate()">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date" required onchange="calculateEndDate()">
            </div>
            <div class="col-md-6 mb-3">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="end_date" name="end_date" readonly>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Please provide a reason for your leave..." required></textarea>
          </div>
          
          <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Information During Leave</label>
            <input type="text" class="form-control" id="contact_info" name="contact_info" placeholder="Phone number or email where you can be reached">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Leave Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Leave Modal -->
<div class="modal fade" id="viewLeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i> Leave Application Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Leave ID</label>
            <p class="form-control-plaintext" id="view-leave-id">-</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Status</label>
            <p class="form-control-plaintext"><span id="view-status" class="badge">-</span></p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Leave Type</label>
            <p class="form-control-plaintext" id="view-leave-type">-</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Number of Days</label>
            <p class="form-control-plaintext" id="view-leave-days">-</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Start Date</label>
            <p class="form-control-plaintext" id="view-start-date">-</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">End Date</label>
            <p class="form-control-plaintext" id="view-end-date">-</p>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Reason</label>
          <p class="form-control-plaintext" id="view-reason">-</p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Contact Information</label>
          <p class="form-control-plaintext" id="view-contact-info">-</p>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Applied Date</label>
            <p class="form-control-plaintext" id="view-applied-date">-</p>
          </div>
          <div class="col-md-6 mb-3" id="view-approved-section" style="display: none;">
            <label class="form-label fw-bold">Approved Date</label>
            <p class="form-control-plaintext" id="view-approved-date">-</p>
          </div>
        </div>
        <div class="mb-3" id="view-remarks-section" style="display: none;">
          <label class="form-label fw-bold">Remarks</label>
          <p class="form-control-plaintext" id="view-remarks">-</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Leave Modal -->
<div class="modal fade" id="editLeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Edit Leave Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editLeaveForm">
        @csrf
        <input type="hidden" id="edit-leave-id" name="leave_id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_leave_type" class="form-label">Leave Type</label>
              <select class="form-select" id="edit_leave_type" name="leave_type" required>
                <option value="">Select Leave Type</option>
                <option value="Vacation">Vacation Leave</option>
                <option value="Sick">Sick Leave</option>
                <option value="Emergency">Emergency Leave</option>
              </select>
              <div class="form-text" id="edit-leave-balance-text">Available balance: </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_leave_days" class="form-label">Number of Days</label>
              <input type="number" class="form-control" id="edit_leave_days" name="leave_days" required min="1" onchange="calculateEditEndDate()">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="edit_start_date" name="start_date" required onchange="calculateEditEndDate()">
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="edit_end_date" name="end_date" readonly>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="edit_reason" class="form-label">Reason</label>
            <textarea class="form-control" id="edit_reason" name="reason" rows="3" placeholder="Please provide a reason for your leave..." required></textarea>
          </div>
          
          <div class="mb-3">
            <label for="edit_contact_info" class="form-label">Contact Information During Leave</label>
            <input type="text" class="form-control" id="edit_contact_info" name="contact_info" placeholder="Phone number or email where you can be reached">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Leave Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Leave balance data from backend
  const leaveBalances = {!! json_encode($leave_balances ?? []) !!} || {
    'Vacation': {'used': 0, 'total': 15, 'available': 15},
    'Sick': {'used': 0, 'total': 10, 'available': 10},
    'Emergency': {'used': 0, 'total': 5, 'available': 5}
  };
  
  // Update leave balance text when leave type changes
  document.getElementById('leave_type').addEventListener('change', function() {
    const type = this.value;
    const balanceText = document.getElementById('leave-balance-text');
    
    if (type && leaveBalances[type]) {
      balanceText.textContent = `Available balance: ${leaveBalances[type].available} days (${leaveBalances[type].used} used out of ${leaveBalances[type].total})`;
      balanceText.className = 'form-text';
      
      // Set max days to available balance
      document.getElementById('leave_days').setAttribute('max', leaveBalances[type].available);
    } else {
      balanceText.textContent = 'Please select a leave type to see your balance';
      balanceText.className = 'form-text text-muted';
    }
  });
  
  // Calculate end date based on start date and number of days
  function calculateEndDate() {
    const startDate = document.getElementById('start_date').value;
    const days = parseInt(document.getElementById('leave_days').value) || 0;
    
    if (startDate && days > 0) {
      const start = new Date(startDate);
      const end = new Date(start);
      
      // Subtract 1 day because the start day counts as 1
      end.setDate(start.getDate() + days - 1);
      
      // Format date as YYYY-MM-DD
      const endFormatted = end.toISOString().split('T')[0];
      document.getElementById('end_date').value = endFormatted;
    }
  }
  
  // Leave dates from backend
  const leaveDates = @json(($leave_records ?? collect())->where('status', 'Approved')->flatMap(function($leave) {
    $dates = [];
    $start = \Carbon\Carbon::parse($leave->start_date);
    $end = \Carbon\Carbon::parse($leave->end_date);
    while ($start->lte($end)) {
      $dates[] = $start->format('Y-m-d');
      $start->addDay();
    }
    return $dates;
  })->toArray());

  // Calendar functionality
  function generateCalendar(year, month) {
    const calendarDays = document.getElementById('calendar-days');
    calendarDays.innerHTML = '';
    
    // Add day headers
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    days.forEach(day => {
      const dayElement = document.createElement('div');
      dayElement.className = 'calendar-day header';
      dayElement.textContent = day;
      calendarDays.appendChild(dayElement);
    });
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
      const emptyDay = document.createElement('div');
      emptyDay.className = 'calendar-day';
      calendarDays.appendChild(emptyDay);
    }
    
    // Add days of the month
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
      const dayElement = document.createElement('div');
      dayElement.className = 'calendar-day';
      
      // Check if today
      if (year === today.getFullYear() && month === today.getMonth() && i === today.getDate()) {
        dayElement.classList.add('today');
      }
      
      // Check if weekend
      const dayOfWeek = new Date(year, month, i).getDay();
      if (dayOfWeek === 0 || dayOfWeek === 6) {
        dayElement.classList.add('weekend');
      }
      
      // Check if this date is a leave day
      const currentDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
      if (leaveDates.includes(currentDate)) {
        dayElement.classList.add('leave');
        dayElement.innerHTML = `${i} <i class="bi bi-star-fill ms-1" style="font-size: 0.6rem;"></i>`;
        dayElement.title = 'Approved Leave Day';
      } else {
        dayElement.textContent = i;
      }
      
      calendarDays.appendChild(dayElement);
    }
    
    // Update month/year display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('current-month').textContent = `${monthNames[month]} ${year}`;
  }
  
  // Initialize calendar with current month
  const currentDate = new Date();
  let currentYear = currentDate.getFullYear();
  let currentMonth = currentDate.getMonth();
  
  generateCalendar(currentYear, currentMonth);
  
  // Previous month button
  document.getElementById('prev-month').addEventListener('click', function() {
    currentMonth--;
    if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    }
    generateCalendar(currentYear, currentMonth);
  });
  
  // Next month button
  document.getElementById('next-month').addEventListener('click', function() {
    currentMonth++;
    if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    }
    generateCalendar(currentYear, currentMonth);
  });
  
  // Filter functionality
  document.getElementById('leave-type-filter').addEventListener('change', filterTable);
  document.getElementById('status-filter').addEventListener('change', filterTable);
  document.getElementById('date-filter').addEventListener('change', filterTable);
  
  document.getElementById('reset-filters').addEventListener('click', function() {
    document.getElementById('leave-type-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('date-filter').value = '';
    filterTable();
  });
  
  function filterTable() {
    const typeFilter = document.getElementById('leave-type-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const dateFilter = document.getElementById('date-filter').value;
    
    const rows = document.querySelectorAll('#leave-table tbody tr');
    
    rows.forEach(row => {
      let showRow = true;
      const typeCell = row.cells[1].textContent;
      const statusCell = row.cells[5].textContent;
      const dateCell = row.cells[3].textContent;
      
      // Apply type filter
      if (typeFilter && typeCell !== typeFilter) {
        showRow = false;
      }
      
      // Apply status filter
      if (statusFilter && statusCell !== statusFilter) {
        showRow = false;
      }
      
      // Apply date filter (simplified)
      if (dateFilter) {
        // In a real application, you would implement proper date range filtering
        showRow = true; // For demo purposes, we're not implementing full date filtering
      }
      
      row.style.display = showRow ? '' : 'none';
    });
  }
  
  // AJAX Form submission for new leave application
  document.getElementById('leaveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    
    fetch('{{ route("employee.leave_applications.store") }}', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
      }
    })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);
      
      if (!response.ok) {
        // Try to get error details from response
        return response.text().then(text => {
          console.error('Error response body:', text);
          throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}...`);
        });
      }
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.success) {
        // Show success message
        showToast('Success!', data.message, 'success');
        
        // Close modal and reset form
        bootstrap.Modal.getInstance(document.getElementById('applyLeaveModal')).hide();
        this.reset();
        
        // Reload page to show updated data
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        // Handle validation errors
        if (data.errors) {
          let errorMessages = Object.values(data.errors).flat().join(', ');
          showToast('Validation Error!', errorMessages, 'error');
        } else {
          showToast('Error!', data.error || data.message || 'Failed to submit leave application', 'error');
        }
      }
    })
    .catch(error => {
      console.error('Full error details:', error);
      showToast('Error!', 'An error occurred while submitting your application: ' + error.message, 'error');
    })
    .finally(() => {
      // Reset button state
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
  });

  // AJAX Form submission for edit leave application
  document.getElementById('editLeaveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const leaveId = document.getElementById('edit-leave-id').value;
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    
    fetch(`{{ url('employee/leave-applications') }}/${leaveId}`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
        'X-HTTP-Method-Override': 'PUT'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success message
        showToast('Success!', data.message, 'success');
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('editLeaveModal')).hide();
        
        // Reload page to show updated data
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showToast('Error!', data.error || 'Failed to update leave application', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('Error!', 'An error occurred while updating your application', 'error');
    })
    .finally(() => {
      // Reset button state
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
  });

  // View leave application
  document.querySelectorAll('.view-leave').forEach(button => {
    button.addEventListener('click', function() {
      const leaveId = this.getAttribute('data-id');
      
      fetch(`{{ url('employee/leave-applications') }}/${leaveId}`, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const leave = data.data;
          
          // Populate view modal
          document.getElementById('view-leave-id').textContent = leave.leave_id || '-';
          document.getElementById('view-leave-type').textContent = leave.leave_type || '-';
          document.getElementById('view-leave-days').textContent = leave.leave_days || '-';
          document.getElementById('view-start-date').textContent = leave.start_date || '-';
          document.getElementById('view-end-date').textContent = leave.end_date || '-';
          document.getElementById('view-reason').textContent = leave.reason || '-';
          document.getElementById('view-contact-info').textContent = leave.contact_info || 'Not provided';
          document.getElementById('view-applied-date').textContent = leave.applied_date || '-';
          
          // Set status badge
          const statusBadge = document.getElementById('view-status');
          statusBadge.textContent = leave.status || '-';
          statusBadge.className = `badge status-${leave.status?.toLowerCase() || 'pending'}`;
          
          // Show/hide approved date and remarks
          if (leave.approved_date) {
            document.getElementById('view-approved-section').style.display = 'block';
            document.getElementById('view-approved-date').textContent = leave.approved_date;
          } else {
            document.getElementById('view-approved-section').style.display = 'none';
          }
          
          if (leave.remarks) {
            document.getElementById('view-remarks-section').style.display = 'block';
            document.getElementById('view-remarks').textContent = leave.remarks;
          } else {
            document.getElementById('view-remarks-section').style.display = 'none';
          }
          
          // Show modal
          new bootstrap.Modal(document.getElementById('viewLeaveModal')).show();
        } else {
          showToast('Error!', 'Failed to load leave application details', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error!', 'An error occurred while loading details', 'error');
      });
    });
  });

  // Edit leave application
  document.querySelectorAll('.edit-leave').forEach(button => {
    button.addEventListener('click', function() {
      const leaveId = this.getAttribute('data-id');
      
      fetch(`{{ url('employee/leave-applications') }}/${leaveId}`, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const leave = data.data;
          
          // Populate edit modal
          document.getElementById('edit-leave-id').value = leave.id;
          document.getElementById('edit_leave_type').value = leave.leave_type;
          document.getElementById('edit_leave_days').value = leave.leave_days;
          document.getElementById('edit_start_date').value = leave.start_date;
          document.getElementById('edit_end_date').value = leave.end_date;
          document.getElementById('edit_reason').value = leave.reason;
          document.getElementById('edit_contact_info').value = leave.contact_info || '';
          
          // Update balance text
          updateEditLeaveBalance();
          
          // Show modal
          new bootstrap.Modal(document.getElementById('editLeaveModal')).show();
        } else {
          showToast('Error!', 'Failed to load leave application details', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error!', 'An error occurred while loading details', 'error');
      });
    });
  });

  // Update edit leave balance text
  function updateEditLeaveBalance() {
    const type = document.getElementById('edit_leave_type').value;
    const balanceText = document.getElementById('edit-leave-balance-text');
    
    if (type && leaveBalances[type]) {
      balanceText.textContent = `Available balance: ${leaveBalances[type].available} days (${leaveBalances[type].used} used out of ${leaveBalances[type].total})`;
      balanceText.className = 'form-text';
      
      // Set max days to available balance
      document.getElementById('edit_leave_days').setAttribute('max', leaveBalances[type].available);
    } else {
      balanceText.textContent = 'Please select a leave type to see your balance';
      balanceText.className = 'form-text text-muted';
    }
  }

  // Update edit leave balance when type changes
  document.getElementById('edit_leave_type').addEventListener('change', updateEditLeaveBalance);

  // Calculate end date for edit form
  function calculateEditEndDate() {
    const startDate = document.getElementById('edit_start_date').value;
    const days = parseInt(document.getElementById('edit_leave_days').value) || 0;
    
    if (startDate && days > 0) {
      const start = new Date(startDate);
      const end = new Date(start);
      
      // Subtract 1 day because the start day counts as 1
      end.setDate(start.getDate() + days - 1);
      
      // Format date as YYYY-MM-DD
      const endFormatted = end.toISOString().split('T')[0];
      document.getElementById('edit_end_date').value = endFormatted;
    }
  }

  // Toast notification function
  function showToast(title, message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toast-container';
      toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
      toastContainer.style.zIndex = '9999';
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    
    const toastHtml = `
      <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
        <div class="toast-header ${bgClass} text-white border-0">
          <strong class="me-auto">${title}</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
      this.remove();
    });
  }
  
  // Set minimum start date to today
  const today = new Date();
  const formattedToday = today.toISOString().split('T')[0];
  document.getElementById('start_date').setAttribute('min', formattedToday);
</script>
</body>
</html>