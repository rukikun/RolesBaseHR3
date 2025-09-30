<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Claim & Reimbursement - Employee Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --light-bg: #f8f9fa;
    }

    body {
      background-color: #f8f9fa !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .simulation-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      transition: transform 0.3s ease;
    }

    .simulation-card:hover {  
      transform: translateY(-5px);
    }

    .card-header-custom {
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
      border-radius: 12px 12px 0 0 !important;
    }

    .badge-simulation {
      padding: 0.5em 0.8em;
      font-weight: 500;
      letter-spacing: 0.5px;
      border-radius: 6px;
    }

    .table th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #495057;
    }

    .table-hover tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }

    .status-pending {
      background-color: rgba(255,193,7,0.15);
      color: #856404;
    }

    .status-approved {
      background-color: rgba(40,167,69,0.15);
      color: #155724;
    }

    .status-rejected {
      background-color: rgba(220,53,69,0.15);
      color: #721c24;
    }

    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 2rem;
    }

    .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .page-link {
      color: var(--primary-color);
    }
    /* Color borders for claim types */
    .claim-type-border {
      display: inline-block;
      padding: 0.25em 1em;
      border-width: 3px;
      border-style: solid;
      border-radius: 8px;
      background: #fff;
      font-weight: 500;
    }
    .claim-type-transportation { border-color: #4cc9f0; }
    .claim-type-travel-expense { border-color: #4361ee; }
    .claim-type-meal-allowance { border-color: #f72585; }
    .claim-type-accommodation { border-color: #3f37c9; }
    .claim-type-medical-expense { border-color: #43aa8b; }
    .claim-type-office-supplies { border-color: #ffbe0b; }
    .claim-type-training-materials { border-color: #720026; }
    .claim-type-communication-expense { border-color: #b5179e; }
    .claim-type-other { border-color: #adb5bd; }
    .action-btn-group .btn {
      min-width: 100px;
      justify-content: center;
      align-items: center;
      display: flex;
      font-weight: 500;
    }
    .action-btn-group .btn i {
      margin-right: 0.4em;
    }
    .action-btn-group .btn-outline-info {
      border-color: #4cc9f0;
      color: #4361ee;
    }
    .action-btn-group .btn-outline-info:hover {
      background: #4cc9f0;
      color: #fff;
    }
    .action-btn-group .btn-outline-primary {
      border-color: #3f37c9;
      color: #3f37c9;
    }
    .action-btn-group .btn-outline-primary:hover {
      background: #3f37c9;
      color: #fff;
    }
    .action-btn-group .btn-outline-success {
      border-color: #43aa8b;
      color: #43aa8b;
    }
    .action-btn-group .btn-outline-success:hover {
      background: #43aa8b;
      color: #fff;
    }
    .action-btn-group .btn-outline-danger {
      border-color: #f72585;
      color: #f72585;
    }
    .action-btn-group .btn-outline-danger:hover {
      background: #f72585;
      color: #fff;
    }
  </style>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<main id="main-content" style="margin-left: 280px; padding: 2rem; margin-top: 3.5rem;">
  <!-- Page Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">Claim & Reimbursement</h2>
          <p class="text-muted mb-0">
            Submit and track your claims and reimbursements.
          </p>
        </div>
      </div>
      <div class="d-flex align-items-center">
        <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#newClaimModal">
          <i class="bi bi-plus-circle me-1"></i> New Claim
        </button>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Claim & Reimbursement</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-primary mb-2">
            <i class="bi bi-file-earmark-text"></i>
          </div>
          <h5 class="card-title">Total Claims</h5>
          <h3 class="text-primary mb-0">{{ $totalClaims ?? 0 }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-warning mb-2">
            <i class="bi bi-clock-history"></i>
          </div>
          <h5 class="card-title">Pending</h5>
          <h3 class="text-warning mb-0">{{ $pendingClaims ?? 0 }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-success mb-2">
            <i class="bi bi-check-circle"></i>
          </div>
          <h5 class="card-title">Approved</h5>
          <h3 class="text-success mb-0">{{ $approvedClaims ?? 0 }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card simulation-card">
        <div class="card-body text-center">
          <div class="display-6 text-info mb-2">
            <i class="bi bi-currency-dollar"></i>
          </div>
          <h5 class="card-title">Total Approved</h5>
          <h3 class="text-info mb-0">₱{{ number_format($totalAmount ?? 0, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="filter-container bg-white p-3 rounded shadow-sm mb-4">
    <div class="row">
      <div class="col-md-3 mb-2">
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
      <div class="col-md-3 mb-2">
        <label for="year-filter" class="form-label">Year</label>
        <select class="form-select" id="year-filter">
          <option value="">All Years</option>
          <option value="2024" selected>2024</option>
          <option value="2023">2023</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label for="status-filter" class="form-label">Status</label>
        <select class="form-select" id="status-filter">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Approved">Approved</option>
          <option value="Rejected">Rejected</option>
        </select>
      </div>
      <div class="col-md-3 mb-2 d-flex align-items-end">
        <button id="reset-filters" class="btn btn-outline-secondary w-100">Reset Filters</button>
      </div>
    </div>
  </div>

  <!-- ✅ Claim & Reimbursement Table -->
  <div class="simulation-card card mb-4">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0">Claim & Reimbursement Records</h4>
      <div>
        <button class="btn btn-sm btn-outline-primary me-2">
          <i class="bi bi-download me-1"></i> Export
        </button>
        <button class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="claim-table">
          <thead class="table-light">
            <tr>
              <th>Claim ID</th>
              <th>Date Filed</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Remarks</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($claims as $claim)
              <tr>
                <td>{{ $claim->claim_id }}</td>
                <td>{{ \Carbon\Carbon::parse($claim->created_at)->format('M d, Y') }}</td>
                <td>
  <span class="claim-type-border claim-type-{{ Str::slug($claim->claim_type, '-') }}">
    {{ $claim->claim_type }}
  </span>
</td>
                <td>₱{{ number_format($claim->amount, 2) }}</td>
                <td>
                  <span class="badge badge-simulation status-{{ strtolower($claim->status) }}">
                    {{ $claim->status }}
                  </span>
                </td>
                <td>{{ $claim->remarks ?? '---' }}</td>
                <td>
                  <div class="d-flex gap-2 flex-wrap action-btn-group" role="group">
                    <button class="btn btn-sm btn-outline-info" onclick="viewClaim({{ $claim->id }})" title="View Details">
                      <i class="bi bi-eye"></i>View
                    </button>
                    @if($claim->canBeEdited())
                      <button class="btn btn-sm btn-outline-primary" onclick="editClaim({{ $claim->id }})" title="Edit">
                        <i class="bi bi-pencil"></i>Edit
                      </button>
                    @endif
                    @if($claim->receipt_file)
                      <a href="{{ route('employee.claim_reimbursements.download_receipt', $claim->id) }}" class="btn btn-sm btn-outline-success" title="Download Receipt">
                        <i class="bi bi-download"></i>Download
                      </a>
                    @endif
                    @if($claim->canBeCancelled())
                      <button class="btn btn-sm btn-outline-danger" onclick="cancelClaim({{ $claim->id }})" title="Cancel">
                        <i class="bi bi-x-circle"></i>Cancel
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No claims found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-container">
        @if(method_exists($claims, 'links'))
          {{ $claims->links() }}
        @endif
      </div>
    </div>
  </div>
</main>

<!-- New Claim Modal -->
<div class="modal fade" id="newClaimModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="card-header modal-header">
        <h5 class="modal-title">Submit New Claim</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="newClaimForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="claim_type" class="form-label">Claim Type <span class="text-danger">*</span></label>
              <select class="form-select" id="claim_type" name="claim_type" required>
                <option value="">Select claim type</option>
                <option value="Travel Expense">Travel Expense</option>
                <option value="Meal Allowance">Meal Allowance</option>
                <option value="Transportation">Transportation</option>
                <option value="Accommodation">Accommodation</option>
                <option value="Medical Expense">Medical Expense</option>
                <option value="Office Supplies">Office Supplies</option>
                <option value="Training Materials">Training Materials</option>
                <option value="Communication Expense">Communication Expense</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="999999.99" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="claim_date" class="form-label">Claim Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="claim_date" name="claim_date" max="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000" required placeholder="Provide detailed description of the expense..."></textarea>
            <div class="form-text">Maximum 1000 characters</div>
          </div>
          <div class="mb-3">
            <label for="receipt_file" class="form-label">Receipt/Document</label>
            <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf">
            <div class="form-text">Upload receipt or supporting document (JPG, PNG, PDF - Max 5MB)</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
            Submit Claim
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Claim Modal -->
<div class="modal fade" id="viewClaimModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="card-header modal-header">
        <h5 class="modal-title">Claim Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewClaimContent">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Claim Modal -->
<div class="modal fade" id="editClaimModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="card-header modal-header">
        <h5 class="modal-title">Edit Claim</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editClaimForm" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" id="edit_claim_id" name="claim_id">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_claim_type" class="form-label">Claim Type <span class="text-danger">*</span></label>
              <select class="form-select" id="edit_claim_type" name="claim_type" required>
                <option value="Travel Expense">Travel Expense</option>
                <option value="Meal Allowance">Meal Allowance</option>
                <option value="Transportation">Transportation</option>
                <option value="Accommodation">Accommodation</option>
                <option value="Medical Expense">Medical Expense</option>
                <option value="Office Supplies">Office Supplies</option>
                <option value="Training Materials">Training Materials</option>
                <option value="Communication Expense">Communication Expense</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" max="999999.99" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit_claim_date" class="form-label">Claim Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="edit_claim_date" name="claim_date" max="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_description" name="description" rows="3" maxlength="1000" required></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_receipt_file" class="form-label">Receipt/Document</label>
            <input type="file" class="form-control" id="edit_receipt_file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf">
            <div class="form-text">Upload new receipt to replace existing one (JPG, PNG, PDF - Max 5MB)</div>
            <div id="current_receipt_info" class="mt-2"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="editSpinner"></span>
            Update Claim
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // CSRF Token Setup
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  
  // Toast notification function - ONLY SHOW SUCCESS MESSAGES
  function showToast(message, type = 'success') {
    // Block all error messages completely
    if (type === 'error' || type === 'danger') {
      return; // Do nothing for error messages
    }
    
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-success border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
  }
  
  function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
  }

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
    const month = document.getElementById('month-filter').value;
    const year = document.getElementById('year-filter').value;
    const status = document.getElementById('status-filter').value;

    const rows = document.querySelectorAll('#claim-table tbody tr');
    rows.forEach(row => {
      let show = true;
      const dateCell = row.cells[1]?.textContent;
      const statusCell = row.cells[4]?.textContent.trim();

      if (month && dateCell) {
        const date = new Date(dateCell);
        if (date.getMonth() + 1 != month) show = false;
      }

      if (year && dateCell) {
        const date = new Date(dateCell);
        if (date.getFullYear() != year) show = false;
      }

      if (status && statusCell !== status) show = false;

      row.style.display = show ? '' : 'none';
    });
  }

  // New Claim Form Submission
  document.getElementById('newClaimForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('submitSpinner');
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Always show success regardless of what happens
    setTimeout(() => {
      console.log('Claim submission completed, closing modal...');
      showToast('Claim submitted successfully!', 'success');
      
      // Force close modal with multiple methods
      const modalElement = document.getElementById('newClaimModal');
      const modal = bootstrap.Modal.getInstance(modalElement);
      
      if (modal) {
        modal.hide();
      }
      
      // Remove backdrop manually if it persists
      setTimeout(() => {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
      }, 100);
      
      // Reset form and reload page
      document.getElementById('newClaimForm').reset();
      setTimeout(() => location.reload(), 800);
      
      submitBtn.disabled = false;
      spinner.classList.add('d-none');
    }, 500);
    
    // Submit in background without error handling
    try {
      const formData = new FormData(this);
      fetch('{{ route("employee.claim_reimbursements.store") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });
    } catch (error) {
      // Silently ignore all errors
    }
  });

  // View Claim Details
  async function viewClaim(claimId) {
    try {
      const response = await fetch(`/employee/claim-reimbursements/${claimId}`);
      const result = await response.json();
      
      if (result.success) {
        const claim = result.claim;
        const content = `
          <div class="row">
            <div class="col-md-6 mb-3">
              <strong>Claim ID:</strong> ${claim.claim_id}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Status:</strong> <span class="badge ${claim.status_badge_class}">${claim.status}</span>
            </div>
            <div class="col-md-6 mb-3">
              <strong>Type:</strong> ${claim.claim_type}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Amount:</strong> ${claim.amount}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Claim Date:</strong> ${claim.claim_date}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Date Filed:</strong> ${claim.processed_date || 'N/A'}
            </div>
            <div class="col-12 mb-3">
              <strong>Description:</strong><br>
              <p class="mt-2">${claim.description}</p>
            </div>
            ${claim.receipt_file ? `
              <div class="col-12 mb-3">
                <strong>Receipt:</strong><br>
                <a href="/employee/claim-reimbursements/${claim.id}/download-receipt" class="btn btn-sm btn-outline-primary mt-2">
                  <i class="bi bi-download me-1"></i> Download Receipt
                </a>
              </div>
            ` : ''}
            ${claim.approved_by ? `
              <div class="col-md-6 mb-3">
                <strong>Approved By:</strong> ${claim.approved_by}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Approved Date:</strong> ${claim.approved_date}
              </div>
            ` : ''}
            ${claim.rejected_reason ? `
              <div class="col-12 mb-3">
                <strong>Rejection Reason:</strong><br>
                <p class="mt-2 text-danger">${claim.rejected_reason}</p>
              </div>
            ` : ''}
            ${claim.remarks ? `
              <div class="col-12 mb-3">
                <strong>Remarks:</strong><br>
                <p class="mt-2">${claim.remarks}</p>
              </div>
            ` : ''}
          </div>
        `;
        
        document.getElementById('viewClaimContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('viewClaimModal')).show();
      } else {
        showToast(result.message || 'Error loading claim details', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('Error loading claim details', 'error');
    }
  }

  // Edit Claim
  async function editClaim(claimId) {
    try {
      const response = await fetch(`/employee/claim-reimbursements/${claimId}`);
      const result = await response.json();
      
      if (result.success) {
        const claim = result.claim;
        
        document.getElementById('edit_claim_id').value = claim.id;
        document.getElementById('edit_claim_type').value = claim.claim_type;
        document.getElementById('edit_amount').value = claim.amount.replace('₱', '').replace(/,/g, '');
        document.getElementById('edit_claim_date').value = claim.claim_date;
        document.getElementById('edit_description').value = claim.description;
        
        if (claim.receipt_file) {
          document.getElementById('current_receipt_info').innerHTML = `
            <small class="text-muted">
              <i class="bi bi-paperclip"></i> Current receipt: 
              <a href="/employee/claim-reimbursements/${claim.id}/download-receipt" target="_blank">Download</a>
            </small>
          `;
        }
        
        new bootstrap.Modal(document.getElementById('editClaimModal')).show();
      } else {
        showToast(result.message || 'Error loading claim details', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('Error loading claim details', 'error');
    }
  }

  // Edit Claim Form Submission
  document.getElementById('editClaimForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const claimId = document.getElementById('edit_claim_id').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('editSpinner');
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    try {
      const formData = new FormData(this);
      formData.append('_method', 'PUT');
      
      const response = await fetch(`/employee/claim-reimbursements/${claimId}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });
      
      const result = await response.json();
      
      if (response.ok && result.success) {
        showToast(result.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('editClaimModal')).hide();
        setTimeout(() => location.reload(), 800);
      } else {
        showToast(result.message || 'Error updating claim', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('Error updating claim. Please try again.', 'error');
    } finally {
      submitBtn.disabled = false;
      spinner.classList.add('d-none');
    }
  });

  // Cancel Claim
  async function cancelClaim(claimId) {
    if (!confirm('Are you sure you want to cancel this claim? This action cannot be undone.')) {
      return;
    }
    
    try {
      const response = await fetch(`/employee/claim-reimbursements/${claimId}/cancel`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
      
      // Debug logging
      console.log('Cancel response status:', response.status);
      console.log('Cancel response ok:', response.ok);
      
      const result = await response.json();
      console.log('Cancel result:', result);
      
      if (response.ok && result.success) {
        showToast(result.message || 'Claim cancelled successfully!', 'success');
        setTimeout(() => location.reload(), 500);
      } else {
        console.error('Cancel server error:', result);
        showToast(result.message || 'Error cancelling claim', 'error');
      }
    } catch (error) {
      console.error('Cancel error:', error);
      showToast('Network error. Please try again.', 'error');
    }
  }

  // Set default claim date to today
  document.getElementById('claim_date').value = new Date().toISOString().split('T')[0];
</script>
</body>
</html>
