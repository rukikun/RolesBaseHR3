@extends('layouts.hr')

@section('title', 'Validate Attachment - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Validate Attachment</h2>
        <p class="text-muted mb-0">Review and validate claim attachments for approved claims</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('claims-reimbursement') }}" class="text-decoration-none">Claims Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Validate Attachment</li>
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
$totalApprovedClaims = $totalApprovedClaims ?? 0;
$totalApprovedAmount = $totalApprovedAmount ?? 0;
$claimsWithAttachments = $claimsWithAttachments ?? 0;
$pendingValidation = $pendingValidation ?? 0;
@endphp

<!-- Validation Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-check-circle text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $totalApprovedClaims }}</h3>
          <p class="text-muted mb-0 small stat-label">Approved Claims</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-paperclip text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $claimsWithAttachments }}</h3>
          <p class="text-muted mb-0 small stat-label">With Attachments</p>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $pendingValidation }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending Validation</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-dollar-sign text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">${{ number_format($totalApprovedAmount, 2) }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Amount</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Validation Actions -->
<div class="row mb-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-tasks me-2"></i>Attachment Validation Actions
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-success mb-2 me-2" id="bulk-validate-btn">
          <i class="fas fa-check-double me-2"></i>Bulk Validate
        </button>
        <button class="btn btn-info mb-2" id="export-report-btn">
          <i class="fas fa-file-export me-2"></i>Export Validation Report
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Approved Claims Table -->
<div class="card mb-4" id="approved-claims-section">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-check-circle me-2"></i>Approved Claims
    </h5>
    <div>
      <select id="attachment-filter" class="form-select form-select-sm d-inline-block w-auto me-2">
        <option value="">All Claims</option>
        <option value="with-attachment">With Attachment</option>
        <option value="no-attachment">No Attachment</option>
        <option value="validated">Validated</option>
        <option value="pending-validation">Pending Validation</option>
      </select>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="approved-claims-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Claim Type</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Description</th>
            <th>Attachments</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="approved-claims-tbody">
          @forelse($approvedClaims as $claim)
            <tr>
              <td>{{ $claim->employee_name ?? 'Unknown Employee' }}</td>
              <td>{{ $claim->claim_type_name ?? 'Unknown Type' }}</td>
              <td>${{ number_format($claim->amount ?? 0, 2) }}</td>
              <td>{{ isset($claim->claim_date) ? date('M d, Y', strtotime($claim->claim_date)) : 'N/A' }}</td>
              <td>{{ isset($claim->description) ? Str::limit($claim->description, 30) : 'N/A' }}</td>
              <td>
                @if((isset($claim->receipt_path) && $claim->receipt_path) || (isset($claim->attachment_path) && $claim->attachment_path))
                  <div class="d-flex align-items-center">
                    <i class="fas fa-paperclip text-success me-2" title="Has attachment"></i>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewAttachment('{{ $claim->receipt_path ?? $claim->attachment_path ?? '' }}')">
                      <i class="fas fa-eye"></i> View
                    </button>
                  </div>
                @else
                  <i class="fas fa-times text-muted" title="No attachment"></i>
                @endif
              </td>
              <td>
                @php
                  $status = $claim->status ?? 'approved';
                  $badgeClass = match($status) {
                    'approved' => 'success',
                    'ready_for_payroll' => 'info',
                    'paid' => 'primary',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewClaimDetails({{ isset($claim->id) ? $claim->id : 0 }})" title="View Details">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if($status === 'approved' && isset($claim->id))
                    @php
                      // Check if this claim has already been validated
                      $isValidated = false;
                      try {
                        $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM validated_attachments WHERE claim_id = ?");
                        $stmt->execute([$claim->id]);
                        $isValidated = $stmt->fetchColumn() > 0;
                      } catch (\Exception $e) {
                        // If check fails, assume not validated
                      }
                    @endphp
                    
                    @if(!$isValidated)
                      <form method="POST" action="{{ route('validate-attachment.validate', $claim->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to validate this attachment and send to payroll?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Validate Attachment">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                    @else
                      <span class="badge bg-success">Validated</span>
                    @endif
                    
                    <form method="POST" action="{{ route('validate-attachment.delete', $claim->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this claim? This action cannot be undone.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Claim">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No approved claims found. <a href="{{ route('claims-reimbursement') }}" class="text-primary">Go to Claims Management</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- View Claim Details Modal -->
<div class="working-modal" id="view-claim-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-claim-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Claim Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-claim-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-claim-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Claim Type:</strong>
                        <p id="view-claim-type" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Amount:</strong>
                        <p id="view-claim-amount" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <p id="view-claim-date" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Description:</strong>
                        <p id="view-claim-description" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-claim-status" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Attachment:</strong>
                        <p id="view-claim-attachment" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-claim-modal')">Close</button>
            </div>
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

// View claim details
function viewClaimDetails(claimId) {
    // Handle case where claimId is 0 or invalid
    if (!claimId || claimId === 0) {
        alert('❌ Unable to view claim details - invalid claim ID');
        return;
    }
    
    const claimRow = document.querySelector(`button[onclick="viewClaimDetails(${claimId})"]`)?.closest('tr');
    
    if (claimRow && claimRow.cells.length >= 7) {
        try {
            document.getElementById('view-claim-employee').textContent = claimRow.cells[0]?.textContent?.trim() || 'N/A';
            document.getElementById('view-claim-type').textContent = claimRow.cells[1]?.textContent?.trim() || 'N/A';
            document.getElementById('view-claim-amount').textContent = claimRow.cells[2]?.textContent?.trim() || 'N/A';
            document.getElementById('view-claim-date').textContent = claimRow.cells[3]?.textContent?.trim() || 'N/A';
            document.getElementById('view-claim-description').textContent = claimRow.cells[4]?.textContent?.trim() || 'N/A';
            document.getElementById('view-claim-attachment').textContent = claimRow.cells[5]?.querySelector('.fa-paperclip') ? 'Yes' : 'No';
            document.getElementById('view-claim-status').textContent = claimRow.cells[6]?.querySelector('.badge')?.textContent?.trim() || 'Unknown';
            openWorkingModal('view-claim-modal');
        } catch (error) {
            console.error('Error viewing claim details:', error);
            alert('❌ Error loading claim details. Please try again.');
        }
    } else {
        alert('❌ Unable to find claim details. Please refresh the page and try again.');
    }
}

// View attachment
function viewAttachment(attachmentPath) {
    if (!attachmentPath) {
        alert('❌ No attachment found for this claim');
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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('attachment-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const filterValue = this.value;
            const tableRows = document.querySelectorAll('#approved-claims-tbody tr');
            
            tableRows.forEach(row => {
                if (row.cells.length < 6) return; // Skip empty rows
                
                const attachmentCell = row.cells[5];
                const statusCell = row.cells[6];
                const hasAttachment = attachmentCell.querySelector('.fa-paperclip');
                const status = statusCell.textContent.trim().toLowerCase();
                
                let showRow = true;
                
                switch (filterValue) {
                    case 'with-attachment':
                        showRow = hasAttachment !== null;
                        break;
                    case 'no-attachment':
                        showRow = hasAttachment === null;
                        break;
                    case 'validated':
                        showRow = status.includes('ready') || status.includes('paid');
                        break;
                    case 'pending-validation':
                        showRow = status.includes('approved') && !status.includes('ready');
                        break;
                    default:
                        showRow = true;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        });
    }
});

// Bulk actions
document.getElementById('bulk-validate-btn')?.addEventListener('click', function() {
    if (confirm('Are you sure you want to validate all approved claims with attachments?')) {
        // Implementation for bulk validation
        alert('Bulk validation feature coming soon!');
    }
});


document.getElementById('export-report-btn')?.addEventListener('click', function() {
    // Implementation for exporting validation report
    alert('Export report feature coming soon!');
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
</style>
@endpush

@endsection
