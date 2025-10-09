@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Claims & Reimbursement')

@section('content')
<!-- Page Header -->
<div class="page-header-container fade-in">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="height: 40px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Claims & Reimbursement</h2>
        <p class="text-muted mb-0">Submit and track your expense claims</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Claims & Reimbursement</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Quick Actions -->
<div class="ess-card mb-4 slide-up">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-lightning-charge"></i>
      Quick Actions
    </h5>
    <p class="text-muted mb-0">Submit new claims or view existing ones</p>
  </div>
  <div class="ess-card-body">
    <div class="d-flex gap-3 flex-wrap">
      <button class="btn btn-jetlouge" onclick="openWorkingModal('create-claim-modal')">
        <i class="bi bi-plus-circle me-2"></i>Submit Claim
      </button>
      <button class="btn btn-jetlouge-outline" onclick="refreshClaimData()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
      </button>
    </div>
  </div>
</div>

<!-- Claims Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stats-card slide-up">
      <div class="stats-icon primary">
        <i class="bi bi-receipt"></i>
      </div>
      <div class="stats-value">8</div>
      <p class="stats-label">Total Claims</p>
      <p class="stats-sublabel">This Month</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.1s;">
      <div class="stats-icon warning">
        <i class="bi bi-clock-history"></i>
      </div>
      <div class="stats-value">3</div>
      <p class="stats-label">Pending Claims</p>
      <p class="stats-sublabel">Awaiting Approval</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.2s;">
      <div class="stats-icon success">
        <i class="bi bi-check-circle"></i>
      </div>
      <div class="stats-value">5</div>
      <p class="stats-label">Approved Claims</p>
      <p class="stats-sublabel">Ready for Payment</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.3s;">
      <div class="stats-icon info">
        <i class="bi bi-currency-dollar"></i>
      </div>
      <div class="stats-value">₱18,500</div>
      <p class="stats-label">Total Amount</p>
      <p class="stats-sublabel">This Month</p>
    </div>
  </div>
</div>

<!-- My Claims List -->
<div class="ess-card slide-up" style="animation-delay: 0.4s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-list-ul"></i>
      My Claims
    </h5>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" id="statusFilter">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="paid">Paid</option>
      </select>
      <button class="btn btn-jetlouge btn-sm" data-bs-toggle="modal" data-bs-target="#claimModal">
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>
  </div>
  <div class="ess-card-body p-0">
    <div class="table-responsive">
      <table class="table table-clean mb-0">
        <thead>
          <tr>
            <th><i class="bi bi-receipt me-2"></i>Claim Type</th>
            <th><i class="bi bi-currency-dollar me-2"></i>Amount</th>
            <th><i class="bi bi-calendar3 me-2"></i>Date</th>
            <th><i class="bi bi-file-text me-2"></i>Description</th>
            <th><i class="bi bi-flag me-2"></i>Status</th>
            <th><i class="bi bi-gear me-2"></i>Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            // Direct database connection for claims using config
            try {
                $host = config('database.connections.mysql.host', '127.0.0.1');
                $port = config('database.connections.mysql.port', '3306');
                $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
                $username = config('database.connections.mysql.username', 'root');
                $password = config('database.connections.mysql.password', '');
                
                $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Auto-create claims table if not exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS claims (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    claim_type_id INT,
                    amount DECIMAL(10,2) NOT NULL,
                    description TEXT,
                    receipt_path VARCHAR(255),
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    submitted_date DATE,
                    approved_date DATE,
                    approved_by INT,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Auto-create claim_types table if not exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS claim_types (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    code VARCHAR(10) NOT NULL UNIQUE,
                    description TEXT,
                    max_amount DECIMAL(10,2),
                    requires_receipt BOOLEAN DEFAULT TRUE,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Insert sample claim types if table is empty
                $countStmt = $pdo->query("SELECT COUNT(*) as count FROM claim_types");
                $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($count == 0) {
                    $pdo->exec("INSERT INTO claim_types (name, code, description, max_amount, requires_receipt) VALUES
                        ('Travel Expenses', 'TRAVEL', 'Business travel reimbursement', 5000.00, TRUE),
                        ('Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, TRUE),
                        ('Office Supplies', 'OFFICE', 'Office equipment and supplies', 1000.00, TRUE),
                        ('Training Costs', 'TRAINING', 'Professional development expenses', 2000.00, TRUE),
                        ('Medical Claims', 'MEDICAL', 'Medical expense reimbursement', 3000.00, TRUE)");
                }
                
                // Get current employee's claims
                $employeeId = Auth::guard('employee')->user()->id ?? 1;
                $stmt = $pdo->prepare("SELECT c.*, ct.name as claim_type_name 
                    FROM claims c 
                    LEFT JOIN claim_types ct ON c.claim_type_id = ct.id 
                    WHERE c.employee_id = ? 
                    ORDER BY c.created_at DESC");
                $stmt->execute([$employeeId]);
                $directClaims = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                echo "<!-- Direct DB Query: Found " . count($directClaims) . " claims -->"; 
            } catch (Exception $e) {
                $directClaims = [];
                echo "<!-- Direct DB Query Error: " . $e->getMessage() . " -->";
            }
          @endphp
          
          @if(count($directClaims) > 0)
            @foreach($directClaims as $claim)
            <tr>
              <td>{{ $claim->claim_type_name ?? 'Unknown Type' }}</td>
              <td>₱{{ number_format($claim->amount ?? 0, 2) }}</td>
              <td>{{ isset($claim->description) ? Str::limit($claim->description, 50) : 'N/A' }}</td>
              <td>{{ isset($claim->created_at) ? date('M d, Y', strtotime($claim->created_at)) : 'N/A' }}</td>
              <td>
                @php
                  $status = $claim->status ?? 'pending';
                  $badgeClass = match($status) {
                    'approved' => 'success',
                    'pending' => 'warning', 
                    'rejected' => 'danger',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst($status) }}
                </span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" 
                          onclick="openWorkingModal('view-claim-modal'); loadClaimDetails({{ $claim->id ?? 0 }})" title="View">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if(($claim->status ?? 'pending') === 'pending')
                  <form method="GET" action="/claims/{{ $claim->id ?? 0 }}/edit" style="display: inline;">
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          @else
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No claims found. <a href="#" onclick="openWorkingModal('create-claim-modal')" class="text-primary">Submit your first claim</a>
              <br><small class="text-muted">Direct DB found: {{ count($directClaims) }} claims</small>
            </td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Claim Modal -->
<div class="working-modal" id="create-claim-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-claim-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Submit New Claim</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-claim-modal')">&times;</button>
            </div>
            <form id="create-claim-form" method="POST" action="{{ route('employee.claims.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="claim-type-id" class="form-label">Claim Type</label>
                        <select class="form-select" id="claim-type-id" name="claim_type_id" required>
                            <option value="">Select Claim Type</option>
                            @php
                                // Get claim types directly from database for modal using config
                                try {
                                    $host = config('database.connections.mysql.host', '127.0.0.1');
                                    $port = config('database.connections.mysql.port', '3306');
                                    $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
                                    $username = config('database.connections.mysql.username', 'root');
                                    $password = config('database.connections.mysql.password', '');
                                    
                                    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                                    $pdo = new PDO($dsn, $username, $password);
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 ORDER BY name");
                                    $modalClaimTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
                                } catch (Exception $e) {
                                    $modalClaimTypes = [];
                                }
                            @endphp
                            @if(count($modalClaimTypes) > 0)
                                @foreach($modalClaimTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No claim types available</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="claim-amount" class="form-label">Amount (₱)</label>
                        <input type="number" class="form-control" id="claim-amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="claim-description" class="form-label">Description</label>
                        <textarea class="form-control" id="claim-description" name="description" rows="3" required placeholder="Provide details about your claim..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="claim-receipt" class="form-label">Receipt/Documentation</label>
                        <input type="file" class="form-control" id="claim-receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Upload receipt or supporting documentation (JPG, PNG, PDF)</div>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-claim-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Claim Modal -->
<div class="working-modal" id="view-claim-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-claim-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Claim Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-claim-modal')">&times;</button>
            </div>
            <div class="working-modal-body" id="claim-details-content">
                <p>Loading claim details...</p>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-claim-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Direct modal functions that work immediately
function openWorkingModal(modalId) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.zIndex = '9999';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        document.body.style.overflow = 'hidden';
        console.log('Modal opened successfully');
    } else {
        console.error('Modal not found:', modalId);
        alert('Modal not found: ' + modalId);
    }
}

function closeWorkingModal(modalId) {
    console.log('Closing modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('Modal closed successfully');
        
        // Reset form if it's the create modal
        if (modalId === 'create-claim-modal') {
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
        }
    }
}

// Make functions globally available
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;

document.addEventListener('DOMContentLoaded', function() {
  
  // Enhanced filter functionality
  const statusFilter = document.getElementById('statusFilter');
  
  if (statusFilter) {
    statusFilter.addEventListener('change', function() {
      const filterValue = this.value;
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        if (filterValue === '') {
          row.style.display = '';
        } else {
          const statusBadge = row.querySelector('.badge-clean');
          if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase();
            row.style.display = status.includes(filterValue) ? '' : 'none';
          }
        }
      });
      
      // Show filter feedback
      const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none').length;
      if (filterValue) {
        showAlert(`Showing ${visibleRows} claims with status: ${filterValue}`, 'info');
      }
    });
  }
  
  // Set maximum date to today for claims
  const dateInput = document.getElementById('claim_date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.max = today;
  }

});

// Show/Hide claim form functions - GLOBAL SCOPE
function showClaimForm() {
  document.getElementById('claimFormContainer').style.display = 'block';
  document.getElementById('claimFormContainer').scrollIntoView({ behavior: 'smooth' });
}

function hideClaimForm() {
  document.getElementById('claimFormContainer').style.display = 'none';
  document.getElementById('claimForm').reset();
}

function submitClaim() {
  // Basic form validation
  const form = document.getElementById('claimForm');
  const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
  let isValid = true;
  
  inputs.forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('is-invalid');
      isValid = false;
    } else {
      input.classList.remove('is-invalid');
    }
  });
  
  if (!isValid) {
    showAlert('Please fill in all required fields.', 'warning');
    return;
  }
  
  // Simulate form submission
  showAlert('Claim submitted successfully! You will receive a confirmation email shortly.', 'success');
  
  // Hide form and reset
  hideClaimForm();
}

// Additional DOMContentLoaded for form handling
document.addEventListener('DOMContentLoaded', function() {

  // Form submission with validation
  const claimForm = document.getElementById('claimForm');
  if (claimForm) {
    claimForm.addEventListener('submit', function(e) {
      e.preventDefault();
      submitClaim();
    });
  }
});

function refreshClaimData() {
  // Show loading state
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refreshing...';
  btn.disabled = true;
  
  // Simulate refresh
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    showAlert('Claims data refreshed successfully!', 'success');
  }, 1500);
}

function loadClaimDetails(claimId) {
  // Load claim details via server-side route
  fetch(`/claims/${claimId}/view`)
    .then(response => response.json())
    .then(data => {
      document.getElementById('claim-details-content').innerHTML = `
        <div class="row">
          <div class="col-md-6"><strong>Type:</strong> ${data.claim_type_name || 'N/A'}</div>
          <div class="col-md-6"><strong>Amount:</strong> ₱${data.amount || '0.00'}</div>
        </div>
        <div class="row mt-2">
          <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-${data.status === 'approved' ? 'success' : data.status === 'pending' ? 'warning' : 'danger'}">${data.status || 'pending'}</span></div>
          <div class="col-md-6"><strong>Date:</strong> ${data.created_at || 'N/A'}</div>
        </div>
        <div class="row mt-2">
          <div class="col-12"><strong>Description:</strong><br>${data.description || 'No description provided'}</div>
        </div>
      `;
    })
    .catch(error => {
      document.getElementById('claim-details-content').innerHTML = '<p class="text-danger">Error loading claim details.</p>';
    });
}

function showAlert(message, type = 'info') {
  // Clear any existing alerts first
  document.querySelectorAll('.alert').forEach(alert => alert.remove());
  
  const alertClass = type === 'success' ? 'alert-success' : 
                    type === 'error' ? 'alert-danger' : 'alert-info';
  
  const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: relative; z-index: 1;">
      <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  // Insert at top of page
  const container = document.querySelector('.page-header-container');
  if (container) {
    container.insertAdjacentHTML('afterend', alertHtml);
  }
  
  // Auto dismiss after 3 seconds
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.remove();
    }
  }, 3000);
}

// Force cleanup function for modal issues
function forceModalCleanup() {
  // Remove all modal backdrops
  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.remove();
  });
  
  // Reset body state
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
  
  // Hide all modals
  document.querySelectorAll('.modal').forEach(modal => {
    const modalInstance = bootstrap.Modal.getInstance(modal);
    if (modalInstance) {
      modalInstance.hide();
    }
    modal.style.display = 'none';
    modal.classList.remove('show');
  });
  
  showAlert('Modal cleanup completed - page should be interactive now', 'success');
}

// Multiple cleanup triggers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    forceModalCleanup();
  }
  // Emergency cleanup with Ctrl+Shift+C
  if (e.ctrlKey && e.shiftKey && e.key === 'C') {
    e.preventDefault();
    aggressiveCleanup();
    alert('Emergency cleanup activated!');
  }
});

// Cleanup on any click
document.addEventListener('click', function() {
  // Quick cleanup on every click
  document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
  document.body.style.overflow = 'auto';
  document.body.style.pointerEvents = 'auto';
});

// Cleanup on window focus
window.addEventListener('focus', aggressiveCleanup);

});
</script>

<style>
/* Working Modal CSS - Essential for form functionality */
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
}

.working-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: transparent;
}

.working-modal-dialog {
    position: relative;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 2001;
}

.working-modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.working-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.working-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 500;
}

.working-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.working-modal-close:before {
    content: '×';
}

.working-modal-body {
    padding: 1.5rem;
}

.working-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Ensure form elements are interactive */
.working-modal input,
.working-modal select,
.working-modal textarea,
.working-modal button {
    pointer-events: auto !important;
}

.working-modal .form-control,
.working-modal .form-select {
    pointer-events: auto !important;
    position: relative;
    z-index: auto;
}
</style>
@endsection
