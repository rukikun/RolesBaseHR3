@extends('layouts.hr')

@section('title', 'Payroll Management - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Payroll Management</h2>
        <p class="text-muted mb-0">Manage employee payroll and process approved timesheets</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payroll Management</li>
      </ol>
    </nav>
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

@php
$totalPayrollItems = $payrollItems->count() ?? 0;
$totalAmount = $payrollItems->sum('total_amount') ?? 0;
$pendingPayroll = $payrollItems->where('status', 'pending')->count() ?? 0;
$processedPayroll = $payrollItems->where('status', 'processed')->count() ?? 0;
@endphp

<!-- Payroll Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-list text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $totalPayrollItems }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Payroll Items</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-dollar-sign text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">₱{{ number_format($totalAmount, 2) }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Amount</p>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $pendingPayroll }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-check-circle text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $processedPayroll }}</h3>
          <p class="text-muted mb-0 small stat-label">Processed</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payroll Actions -->
<div class="row mb-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-cogs me-2"></i>Payroll Actions
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-success mb-2 me-2" id="process-all-btn">
          <i class="fas fa-play me-2"></i>Process All Pending
        </button>
        <button class="btn btn-primary mb-2 me-2" id="generate-report-btn">
          <i class="fas fa-file-alt me-2"></i>Generate Payroll Report
        </button>
        <button class="btn btn-info mb-2" id="export-payroll-btn">
          <i class="fas fa-download me-2"></i>Export to Excel
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Payroll Items Table -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-list me-2"></i>Payroll Items
    </h5>
    <div>
      <select id="status-filter" class="form-select form-select-sm d-inline-block w-auto me-2">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="processed">Processed</option>
        <option value="paid">Paid</option>
      </select>
      <button class="btn btn-sm btn-outline-secondary" onclick="refreshPayrollTable()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="payroll-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Department</th>
            <th>Week Period</th>
            <th>Total Hours</th>
            <th>Overtime</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Processed Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="payroll-tbody">
          @forelse($payrollItems as $payroll)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar-sm me-2">
                    @php
                      $employeeName = $payroll->employee_name ?? 'Unknown Employee';
                      $nameParts = explode(' ', $employeeName);
                      $firstName = $nameParts[0] ?? 'Unknown';
                      $lastName = $nameParts[1] ?? 'Employee';
                      $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                      $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                      $colorIndex = crc32($payroll->employee_id ?? 0) % count($colors);
                      $bgColor = $colors[$colorIndex];
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($initials) }}&background={{ $bgColor }}&color=ffffff&size=32&bold=true" 
                         class="rounded-circle" width="32" height="32" alt="Avatar">
                  </div>
                  <div>
                    <div class="fw-medium">{{ $employeeName }}</div>
                    <small class="text-muted">ID: {{ $payroll->employee_id ?? 'N/A' }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $payroll->department ?? 'N/A' }}</td>
              <td>{{ $payroll->week_period ?? 'N/A' }}</td>
              <td>
                <span class="badge bg-info">{{ $payroll->total_hours ?? 0 }} hrs</span>
              </td>
              <td>
                <span class="badge bg-warning">{{ $payroll->overtime_hours ?? 0 }} hrs</span>
              </td>
              <td>
                <strong class="text-success">₱{{ number_format($payroll->total_amount ?? 0, 2) }}</strong>
              </td>
              <td>
                @php
                  $status = $payroll->status ?? 'pending';
                  $badgeClass = match($status) {
                    'pending' => 'warning',
                    'processed' => 'success',
                    'paid' => 'primary',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst($status) }}
                </span>
              </td>
              <td>
                {{ isset($payroll->processed_at) ? date('M d, Y', strtotime($payroll->processed_at)) : 'Not processed' }}
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewPayrollDetails({{ $payroll->id ?? 0 }})" title="View Details">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if($status === 'pending')
                    <form method="POST" action="{{ route('payroll.process', $payroll->id ?? 0) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to process this payroll item?')">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Process Payroll">
                        <i class="fas fa-play"></i>
                      </button>
                    </form>
                  @endif
                  @if($status === 'processed')
                    <form method="POST" action="{{ route('payroll.mark-paid', $payroll->id ?? 0) }}" style="display: inline;" onsubmit="return confirm('Mark this payroll as paid?')">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-outline-info" title="Mark as Paid">
                        <i class="fas fa-dollar-sign"></i>
                      </button>
                    </form>
                  @endif
                  <form method="POST" action="{{ route('payroll.destroy', $payroll->id ?? 0) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payroll item? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Payroll">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No payroll items found. <a href="{{ route('timesheet-management') }}" class="text-primary">Go to Timesheet Management</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Validated Attachments Table -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-check-circle me-2"></i>Validated Attachments
    </h5>
    <div>
      <select id="attachment-status-filter" class="form-select form-select-sm d-inline-block w-auto me-2">
        <option value="">All Status</option>
        <option value="validated">Validated</option>
        <option value="sent_to_payroll">Sent to Payroll</option>
        <option value="processed">Processed</option>
      </select>
      <button class="btn btn-sm btn-outline-secondary" onclick="refreshAttachmentTable()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="validated-attachments-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Claim Type</th>
            <th>Amount</th>
            <th>Claim Date</th>
            <th>Description</th>
            <th>Attachment</th>
            <th>Status</th>
            <th>Validated Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="validated-attachments-tbody">
          @forelse($validatedAttachments as $attachment)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar-sm me-2">
                    @php
                      $employeeName = $attachment->employee_name ?? 'Unknown Employee';
                      $nameParts = explode(' ', $employeeName);
                      $firstName = $nameParts[0] ?? 'Unknown';
                      $lastName = $nameParts[1] ?? 'Employee';
                      $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                      $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                      $colorIndex = crc32($attachment->employee_id ?? 0) % count($colors);
                      $bgColor = $colors[$colorIndex];
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($initials) }}&background={{ $bgColor }}&color=ffffff&size=32&bold=true" 
                         class="rounded-circle" width="32" height="32" alt="Avatar">
                  </div>
                  <div>
                    <div class="fw-medium">{{ $employeeName }}</div>
                    <small class="text-muted">ID: {{ $attachment->employee_id ?? 'N/A' }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $attachment->claim_type ?? 'N/A' }}</td>
              <td>
                <strong class="text-success">₱{{ number_format($attachment->amount ?? 0, 2) }}</strong>
              </td>
              <td>
                {{ isset($attachment->claim_date) ? date('M d, Y', strtotime($attachment->claim_date)) : 'N/A' }}
              </td>
              <td>{{ Str::limit($attachment->description ?? 'N/A', 30) }}</td>
              <td>
                @if($attachment->attachment_path)
                  <div class="d-flex align-items-center">
                    <i class="fas fa-paperclip text-success me-2" title="Has attachment"></i>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewAttachment('{{ $attachment->attachment_path }}')">
                      <i class="fas fa-eye"></i> View
                    </button>
                  </div>
                @else
                  <i class="fas fa-times text-muted" title="No attachment"></i>
                @endif
              </td>
              <td>
                @php
                  $status = $attachment->status ?? 'validated';
                  $badgeClass = match($status) {
                    'validated' => 'success',
                    'sent_to_payroll' => 'info',
                    'processed' => 'primary',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
              </td>
              <td>
                {{ isset($attachment->validated_at) ? date('M d, Y', strtotime($attachment->validated_at)) : 'Not validated' }}
              </td>
              <td>
                <div class="btn-group" role="group">
                  @if($status === 'validated')
                    <form method="POST" action="{{ route('payroll.process-attachment', $attachment->id ?? 0) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to process this validated attachment?')">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Process Attachment">
                        <i class="fas fa-play"></i>
                      </button>
                    </form>
                  @endif
                  @if($status === 'sent_to_payroll')
                    <form method="POST" action="{{ route('payroll.mark-attachment-paid', $attachment->id ?? 0) }}" style="display: inline;" onsubmit="return confirm('Mark this attachment as processed?')">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-outline-info" title="Mark as Processed">
                        <i class="fas fa-dollar-sign"></i>
                      </button>
                    </form>
                  @endif
                  <form method="POST" action="{{ route('payroll.delete-attachment', $attachment->id ?? 0) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this validated attachment? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Validated Attachment">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No validated attachments found. <a href="{{ route('validate-attachment') }}" class="text-primary">Go to Validate Attachment</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- View Attachment Modal -->
<div class="working-modal" id="view-attachment-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-attachment-modal')"></div>
    <div class="working-modal-dialog" style="max-width: 800px;">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">View Attachment</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-attachment-modal')">&times;</button>
            </div>
            <div class="working-modal-body text-center">
                <div id="attachment-content">
                    <!-- Attachment content will be loaded here -->
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-attachment-modal')">Close</button>
                <button type="button" class="btn btn-primary" id="open-new-tab-btn">
                    <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Payroll Details Modal -->
<div class="working-modal" id="view-payroll-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-payroll-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Payroll Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-payroll-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-payroll-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Department:</strong>
                        <p id="view-payroll-department" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Week Period:</strong>
                        <p id="view-payroll-period" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Hours:</strong>
                        <p id="view-payroll-hours" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Overtime Hours:</strong>
                        <p id="view-payroll-overtime" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Amount:</strong>
                        <p id="view-payroll-amount" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Status:</strong>
                        <p id="view-payroll-status" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-payroll-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Simple modal functions
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// View attachment
function viewAttachment(attachmentPath) {
    if (!attachmentPath) {
        alert('❌ No attachment found for this validated attachment');
        return;
    }
    
    const attachmentContent = document.getElementById('attachment-content');
    const openNewTabBtn = document.getElementById('open-new-tab-btn');
    
    // Clear previous content
    attachmentContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i><p>Loading attachment...</p></div>';
    
    // Get file extension
    const extension = attachmentPath.split('.').pop().toLowerCase();
    const fullPath = `/storage/${attachmentPath}`;
    
    // Set up the "Open in New Tab" button
    openNewTabBtn.onclick = function() {
        window.open(fullPath, '_blank');
    };
    
    // Show debug info
    console.log('Attachment Path:', attachmentPath);
    console.log('Full Path:', fullPath);
    console.log('Extension:', extension);
    
    if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(extension)) {
        // Display image with better error handling
        const img = new Image();
        img.onload = function() {
            attachmentContent.innerHTML = `<img src="${fullPath}" class="img-fluid" style="max-height: 500px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" alt="Attachment">`;
        };
        img.onerror = function() {
            attachmentContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p><strong>Unable to load image</strong></p>
                    <p class="text-muted">Path: ${attachmentPath}</p>
                    <p class="text-muted">Full URL: ${fullPath}</p>
                    <p class="text-muted">Use the "Open in New Tab" button below to view the file directly.</p>
                </div>
            `;
        };
        img.src = fullPath;
    } else if (extension === 'pdf') {
        // Display PDF
        attachmentContent.innerHTML = `<embed src="${fullPath}" type="application/pdf" width="100%" height="500px" style="border-radius: 8px;">`;
    } else {
        // Display file info for other file types
        attachmentContent.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-file fa-3x text-muted mb-3"></i>
                <p><strong>File:</strong> ${attachmentPath.split('/').pop()}</p>
                <p class="text-muted">File type: ${extension.toUpperCase()}</p>
                <p class="text-muted">Preview not available for this file type</p>
                <p class="text-muted">Use the "Open in New Tab" button below to view the file.</p>
            </div>
        `;
    }
    
    openWorkingModal('view-attachment-modal');
}

// View payroll details
function viewPayrollDetails(payrollId) {
    if (!payrollId || payrollId === 0) {
        alert('❌ Unable to view payroll details - invalid ID');
        return;
    }
    
    const payrollRow = document.querySelector(`button[onclick="viewPayrollDetails(${payrollId})"]`)?.closest('tr');
    
    if (payrollRow && payrollRow.cells.length >= 8) {
        try {
            document.getElementById('view-payroll-employee').textContent = payrollRow.cells[0]?.textContent?.trim() || 'N/A';
            document.getElementById('view-payroll-department').textContent = payrollRow.cells[1]?.textContent?.trim() || 'N/A';
            document.getElementById('view-payroll-period').textContent = payrollRow.cells[2]?.textContent?.trim() || 'N/A';
            document.getElementById('view-payroll-hours').textContent = payrollRow.cells[3]?.textContent?.trim() || 'N/A';
            document.getElementById('view-payroll-overtime').textContent = payrollRow.cells[4]?.textContent?.trim() || 'N/A';
            document.getElementById('view-payroll-amount').textContent = payrollRow.cells[5]?.textContent?.trim() || 'N/A';
            document.getElementById('view-payroll-status').textContent = payrollRow.cells[6]?.querySelector('.badge')?.textContent?.trim() || 'Unknown';
            openWorkingModal('view-payroll-modal');
        } catch (error) {
            console.error('Error viewing payroll details:', error);
            alert('❌ Error loading payroll details. Please try again.');
        }
    } else {
        alert('❌ Unable to find payroll details. Please refresh the page and try again.');
    }
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('status-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const filterValue = this.value;
            const tableRows = document.querySelectorAll('#payroll-tbody tr');
            
            tableRows.forEach(row => {
                if (row.cells.length < 6) return; // Skip empty rows
                
                const statusCell = row.cells[6];
                const status = statusCell.textContent.trim().toLowerCase();
                
                let showRow = true;
                
                if (filterValue && !status.includes(filterValue)) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        });
    }
});

// Refresh table
function refreshPayrollTable() {
    location.reload();
}

// Action buttons
document.getElementById('process-all-btn')?.addEventListener('click', function() {
    if (confirm('Are you sure you want to process all pending payroll items?')) {
        // Implementation for processing all pending items
        alert('Process all feature coming soon!');
    }
});

document.getElementById('generate-report-btn')?.addEventListener('click', function() {
    // Implementation for generating payroll report
    alert('Generate report feature coming soon!');
});

document.getElementById('export-payroll-btn')?.addEventListener('click', function() {
    // Implementation for exporting to Excel
    alert('Export to Excel feature coming soon!');
});
</script>
@endpush

@push('styles')
<!-- Working Modal CSS -->
<style>
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2000;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.working-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.working-modal-dialog {
    position: relative;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    z-index: 2001;
    margin: 0;
}

.working-modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.working-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
}

.working-modal-title {
    margin: 0;
    color: #212529;
    font-weight: 600;
}

.working-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.working-modal-close:hover {
    color: #000;
}

.working-modal-body {
    padding: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
}

.working-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    background-color: #f8f9fa;
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
  background: linear-gradient(90deg, #007bff, #6f42c1);
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
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
}
</style>
@endpush

@endsection
