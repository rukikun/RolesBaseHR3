@extends('layouts.hr')

@section('title', 'Reports - HR System')

@php
use Illuminate\Support\Str;
@endphp

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Reports</h2>
        <p class="text-muted mb-0">Analyze HR data with generated reports and summaries</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Reports</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Create Report Modal -->
<div class="modal fade" id="report-create-modal" tabindex="-1" aria-labelledby="report-create-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="report-create-modal-label">Create New Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('reports.store') }}">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Report Title</label>
              <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select name="category" class="form-select" required>
                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>
                @foreach(['Attendance', 'Payroll', 'Leave', 'Performance', 'Compliance', 'Operations'] as $category)
                  <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Period Start</label>
              <input type="date" name="period_start" class="form-control" value="{{ old('period_start') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Period End</label>
              <input type="date" name="period_end" class="form-control" value="{{ old('period_end') }}">
            </div>
            <div class="col-12">
              <label class="form-label">Summary</label>
              <textarea name="summary" class="form-control" rows="3" maxlength="1000">{{ old('summary') }}</textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Report</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Report Modal -->
<div class="modal fade" id="report-edit-modal" tabindex="-1" aria-labelledby="report-edit-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="report-edit-modal-label">Edit Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Report Title</label>
              <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select name="category" class="form-select" required>
                <option value="" disabled>Select category</option>
                @foreach(['Attendance', 'Payroll', 'Leave', 'Performance', 'Compliance', 'Operations'] as $category)
                  <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Period Start</label>
              <input type="date" name="period_start" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Period End</label>
              <input type="date" name="period_end" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Summary</label>
              <textarea name="summary" class="form-control" rows="3" maxlength="1000"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="generated">Generated</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <ul class="mb-0">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
$reportStats = $reportStats ?? [
    'total_reports' => 0,
    'generated_reports' => 0,
    'draft_reports' => 0,
    'scheduled_reports' => 0,
    'total_records' => 0
];
$highlightReports = $reports->take(3);
@endphp

<!-- Report Highlights -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-lightbulb me-2"></i>Report Highlights
    </h5>
    <span class="badge bg-primary">Latest</span>
  </div>
  <div class="card-body">
    <div class="row g-4">
      @forelse($highlightReports as $report)
        <div class="col-lg-4 col-md-6">
          <div class="leave-balance-box report-highlight">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">{{ $report->title }}</h6>
                <p class="text-muted small mb-0">{{ $report->category }}</p>
              </div>
              <span class="badge bg-info">{{ ucfirst($report->status ?? 'draft') }}</span>
            </div>
            <p class="text-muted small mt-3 mb-3">{{ Str::limit($report->summary ?? 'No summary available.', 80) }}</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted small">{{ $report->period_label }}</span>
              <a href="{{ route('reports.show', $report) }}" class="btn btn-view-balance">
                <i class="fas fa-eye me-2"></i>Preview
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-light border text-center mb-0">
            No reports available yet.
          </div>
        </div>
      @endforelse
    </div>
  </div>
</div>

<!-- Report Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-chart-bar text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $reportStats['total_reports'] }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Reports</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-check-circle text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $reportStats['generated_reports'] }}</h3>
          <p class="text-muted mb-0 small stat-label">Generated</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-pen text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $reportStats['draft_reports'] }}</h3>
          <p class="text-muted mb-0 small stat-label">Drafts</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $reportStats['scheduled_reports'] }}</h3>
          <p class="text-muted mb-0 small stat-label">Scheduled</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Report Actions -->
<div class="row mb-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-sliders-h me-2"></i>Report Actions
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-primary mb-2 me-2" data-bs-toggle="modal" data-bs-target="#report-create-modal">
          <i class="fas fa-plus-circle me-2"></i>New Report
        </button>
        <a class="btn btn-success mb-2" href="{{ route('reports.export') }}">
          <i class="fas fa-file-export me-2"></i>Export Summary
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Reports Table -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="card-title mb-0">
        <i class="fas fa-file-alt me-2"></i>Report Library
      </h5>
      <small class="text-muted">Total records across reports: {{ number_format($reportStats['total_records']) }}</small>
    </div>
    <div>
      <select id="status-filter" class="form-select form-select-sm d-inline-block w-auto me-2">
        <option value="">All Status</option>
        <option value="generated">Generated</option>
        <option value="draft">Draft</option>
        <option value="scheduled">Scheduled</option>
      </select>
      
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="reports-table">
        <thead class="table-light">
          <tr>
            <th>Report</th>
            <th>Category</th>
            <th>Period</th>
            <th>Status</th>
            <th>Records</th>
            <th>Generated By</th>
            <th>Generated At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="reports-tbody">
          @forelse($reports as $report)
            @php
              $status = $report->status ?? 'draft';
              $badgeClass = match($status) {
                'generated' => 'success',
                'scheduled' => 'info',
                'draft' => 'warning',
                default => 'secondary'
              };
              $generatedAt = $report->generated_at ?? $report->created_at;
            @endphp
            <tr>
              <td>
                <div class="fw-semibold">{{ $report->title }}</div>
                <small class="text-muted">{{ Str::limit($report->summary ?? 'No summary available.', 60) }}</small>
              </td>
              <td>
                <span class="badge bg-light text-dark">{{ $report->category }}</span>
              </td>
              <td>{{ $report->period_label }}</td>
              <td>
                <span class="badge bg-{{ $badgeClass }} text-uppercase">{{ $status }}</span>
              </td>
              <td>{{ number_format($report->total_records ?? 0) }}</td>
              <td>{{ $report->generated_by ?? 'System' }}</td>
              <td>{{ $generatedAt ? $generatedAt->format('M d, Y g:i A') : 'Not generated' }}</td>
              <td>
                <div class="btn-group report-action-buttons" role="group">
                  <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline-primary" title="View Report">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-warning report-edit-btn" title="Edit Report"
                          data-bs-toggle="modal" data-bs-target="#report-edit-modal"
                          data-update-url="{{ route('reports.update', $report) }}"
                          data-title="{{ e($report->title) }}"
                          data-category="{{ e($report->category) }}"
                          data-period-start="{{ optional($report->period_start)->format('Y-m-d') }}"
                          data-period-end="{{ optional($report->period_end)->format('Y-m-d') }}"
                          data-summary="{{ e($report->summary) }}"
                          data-status="{{ $status }}">
                    <i class="fas fa-pen"></i>
                  </button>
                  @if($report->file_path)
                    <a href="{{ asset('storage/' . $report->file_path) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" title="Open File">
                      <i class="fas fa-file-arrow-up"></i>
                    </a>
                  @else
                    <button class="btn btn-sm btn-outline-secondary" disabled title="No File">
                      <i class="fas fa-file-arrow-up"></i>
                    </button>
                  @endif
                  <form method="POST" action="{{ route('reports.destroy', $report) }}" class="d-inline" onsubmit="return confirm('Delete this report?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Report">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                No reports found. Create your first report to get started.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    document.querySelectorAll('.modal.show').forEach(modal => modal.classList.remove('show'));
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');

    const filterSelect = document.getElementById('status-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function () {
            const filterValue = this.value;
            const tableRows = document.querySelectorAll('#reports-tbody tr');

            tableRows.forEach(row => {
                if (row.cells.length < 4) return;

                const statusCell = row.cells[3];
                const status = statusCell.textContent.trim().toLowerCase();
                row.style.display = filterValue && !status.includes(filterValue) ? 'none' : '';
            });
        });
    }

    const editModal = document.getElementById('report-edit-modal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const form = editModal.querySelector('form');
            form.action = button.getAttribute('data-update-url') || '';

            editModal.querySelector('[name="title"]').value = button.getAttribute('data-title') || '';
            editModal.querySelector('[name="category"]').value = button.getAttribute('data-category') || '';
            editModal.querySelector('[name="period_start"]').value = button.getAttribute('data-period-start') || '';
            editModal.querySelector('[name="period_end"]').value = button.getAttribute('data-period-end') || '';
            editModal.querySelector('[name="summary"]').value = button.getAttribute('data-summary') || '';
            editModal.querySelector('[name="status"]').value = button.getAttribute('data-status') || 'draft';
        });
    }
});

function refreshReportsTable() {
    location.reload();
}
</script>
@endpush

@push('styles')
<style>
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

.leave-balance-box {
  background: #ffffff;
  border-radius: 16px;
  padding: 18px;
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
  height: 100%;
}

.report-highlight {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-view-balance {
  background: linear-gradient(135deg, #1f5fbf, #193f91);
  color: #ffffff;
  border: none;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 0.85rem;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(25, 63, 145, 0.3);
}

.btn-view-balance:hover,
.btn-view-balance:focus {
  color: #ffffff;
  background: linear-gradient(135deg, #235fce, #1a4fb5);
  box-shadow: 0 6px 14px rgba(25, 63, 145, 0.35);
}

.report-action-buttons .btn {
  background-color: transparent !important;
  box-shadow: none !important;
}

.report-action-buttons .btn:hover,
.report-action-buttons .btn:focus {
  box-shadow: none !important;
}

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
}
</style>
@endpush

@endsection
