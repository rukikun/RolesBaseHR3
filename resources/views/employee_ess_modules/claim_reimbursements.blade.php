@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Claim & Reimbursement')

@section('content')
<!-- Page Header -->
<div class="page-header-container fade-in">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="height: 40px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Claim & Reimbursement</h2>
        <p class="text-muted mb-0">Submit and track your expense claims</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Claims</li>
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
  </div>
  <div class="ess-card-body">
    <div class="d-flex gap-3 flex-wrap">
      <button class="btn btn-jetlouge" data-bs-toggle="modal" data-bs-target="#submitClaimModal">
        <i class="bi bi-plus-circle me-2"></i>Submit New Claim
      </button>
      <button class="btn btn-jetlouge-outline" onclick="refreshClaimData()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh Data
      </button>
    </div>
  </div>
</div>

<!-- Claim Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="stats-card slide-up">
      <div class="stats-icon primary">
        <i class="bi bi-receipt"></i>
      </div>
      <div class="stats-value">{{ $stats['totalClaims'] ?? 8 }}</div>
      <p class="stats-label">Total Claims</p>
      <p class="stats-sublabel">All time</p>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stats-card slide-up" style="animation-delay: 0.1s;">
      <div class="stats-icon warning">
        <i class="bi bi-clock-history"></i>
      </div>
      <div class="stats-value">{{ $stats['pendingClaims'] ?? 3 }}</div>
      <p class="stats-label">Pending Claims</p>
      <p class="stats-sublabel">Awaiting review</p>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stats-card slide-up" style="animation-delay: 0.2s;">
      <div class="stats-icon success">
        <i class="bi bi-check-circle"></i>
      </div>
      <div class="stats-value">{{ $stats['approvedClaims'] ?? 4 }}</div>
      <p class="stats-label">Approved Claims</p>
      <p class="stats-sublabel">Ready for payment</p>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stats-card slide-up" style="animation-delay: 0.3s;">
      <div class="stats-icon info">
        <i class="bi bi-currency-dollar"></i>
      </div>
      <div class="stats-value">₱{{ number_format($stats['totalAmount'] ?? 15750, 2) }}</div>
      <p class="stats-label">Total Amount</p>
      <p class="stats-sublabel">This year</p>
    </div>
  </div>
</div>

<!-- Claims List -->
<div class="ess-card slide-up" style="animation-delay: 0.4s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-list-ul"></i>
      My Claims
    </h5>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" id="statusFilter" onchange="filterClaims()">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="paid">Paid</option>
      </select>
    </div>
  </div>
  <div class="ess-card-body p-0">
    <div class="table-responsive">
      <table class="table table-clean mb-0">
                  <thead>
                    <tr>
                      <th>Claim ID</th>
                      <th>Type</th>
                      <th>Date</th>
                      <th>Amount</th>
                      <th>Description</th>
                      <th>Status</th>
                      <th>Submitted</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="claimsTableBody">
                    @if(isset($claims) && $claims->count() > 0)
                      @foreach($claims as $claim)
                      <tr>
                        <td>{{ $claim->claim_number ?? 'CLM-' . str_pad($claim->id ?? 1, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $claim->claim_type ?? 'Travel' }}</td>
                        <td>{{ isset($claim->claim_date) ? date('M d, Y', strtotime($claim->claim_date)) : 'Dec 01, 2024' }}</td>
                        <td>₱{{ number_format($claim->amount ?? 2500, 2) }}</td>
                        <td>{{ $claim->description ?? 'Business travel expenses' }}</td>
                        <td>
                          @php
                            $status = $claim->status ?? 'pending';
                            $badgeClass = match($status) {
                              'approved' => 'bg-success',
                              'rejected' => 'bg-danger',
                              'paid' => 'bg-info',
                              default => 'bg-warning'
                            };
                          @endphp
                          @php
                            $badgeClass = match($status) {
                              'approved' => 'badge-success',
                              'rejected' => 'badge-danger',
                              'paid' => 'badge-info',
                              default => 'badge-warning'
                            };
                          @endphp
                          <span class="badge-clean {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td>{{ isset($claim->created_at) ? date('M d, Y', strtotime($claim->created_at)) : 'Nov 28, 2024' }}</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-action btn-outline-primary" onclick="viewClaim({{ $claim->id ?? 1 }})">
                              <i class="bi bi-eye"></i>
                            </button>
                            @if(($claim->status ?? 'pending') === 'pending')
                            <button class="btn btn-action btn-outline-secondary" onclick="editClaim({{ $claim->id ?? 1 }})">
                              <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                      @endforeach
                    @else
                      <!-- Sample data when no claims exist -->
                      <tr>
                        <td>CLM-0001</td>
                        <td>Travel</td>
                        <td>Dec 01, 2024</td>
                        <td>₱2,500.00</td>
                        <td>Business travel expenses</td>
                        <td><span class="badge-clean badge-success">Approved</span></td>
                        <td>Nov 28, 2024</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-action btn-outline-primary" onclick="viewClaim(1)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>CLM-0002</td>
                        <td>Meals</td>
                        <td>Nov 30, 2024</td>
                        <td>₱850.00</td>
                        <td>Client meeting lunch</td>
                        <td><span class="badge-clean badge-warning">Pending</span></td>
                        <td>Nov 30, 2024</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-action btn-outline-primary" onclick="viewClaim(2)">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-action btn-outline-secondary" onclick="editClaim(2)">
                              <i class="bi bi-pencil"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>CLM-0003</td>
                        <td>Office Supplies</td>
                        <td>Nov 25, 2024</td>
                        <td>₱1,200.00</td>
                        <td>Stationery and office materials</td>
                        <td><span class="badge-clean badge-info">Paid</span></td>
                        <td>Nov 25, 2024</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-action btn-outline-primary" onclick="viewClaim(3)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
          </div>

<!-- Submit Claim Modal -->
<div class="modal fade" id="submitClaimModal" tabindex="-1" aria-labelledby="submitClaimModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submitClaimModalLabel">Submit New Claim</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="submitClaimForm" action="{{ route('employee.claims.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="claimType" class="form-label">Claim Type <span class="text-danger">*</span></label>
              <select class="form-select" id="claimType" name="claim_type" required>
                <option value="">Select claim type</option>
                <option value="Travel">Travel</option>
                <option value="Meals">Meals & Entertainment</option>
                <option value="Office Supplies">Office Supplies</option>
                <option value="Communication">Communication</option>
                <option value="Training">Training & Development</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="claimDate" class="form-label">Claim Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="claimDate" name="claim_date" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="claimAmount" class="form-label">Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="claimAmount" name="amount" step="0.01" min="0" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="claimReceipt" class="form-label">Receipt/Attachment</label>
              <input type="file" class="form-control" id="claimReceipt" name="receipt" accept="image/*,.pdf">
              <div class="form-text">Upload receipt or supporting document (optional)</div>
            </div>
          </div>
          <div class="mb-3">
            <label for="claimDescription" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="claimDescription" name="description" rows="3" required placeholder="Provide details about the expense..."></textarea>
          </div>
          <div class="mb-3">
            <label for="claimPurpose" class="form-label">Business Purpose</label>
            <textarea class="form-control" id="claimPurpose" name="business_purpose" rows="2" placeholder="Explain the business purpose of this expense..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-jetlouge-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-jetlouge">Submit Claim</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Claim Modal -->
<div class="modal fade" id="viewClaimModal" tabindex="-1" aria-labelledby="viewClaimModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewClaimModalLabel">Claim Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewClaimContent">
        <!-- Claim details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-jetlouge-outline" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Set today's date as default for claim date
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('claimDate').value = today;
});

// Filter claims by status
function filterClaims() {
    const filter = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#claimsTableBody tr');
    
    rows.forEach(row => {
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase();
            if (filter === '' || status.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

// View claim details
function viewClaim(claimId) {
    const claimDetails = `
        <div class="row">
            <div class="col-md-6">
                <h6>Claim Information</h6>
                <p><strong>Claim ID:</strong> CLM-${String(claimId).padStart(4, '0')}</p>
                <p><strong>Type:</strong> Travel</p>
                <p><strong>Amount:</strong> ₱2,500.00</p>
                <p><strong>Date:</strong> Dec 01, 2024</p>
                <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
            </div>
            <div class="col-md-6">
                <h6>Submission Details</h6>
                <p><strong>Submitted:</strong> Nov 28, 2024</p>
                <p><strong>Reviewed:</strong> Nov 29, 2024</p>
                <p><strong>Reviewer:</strong> HR Manager</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Description</h6>
                <p>Business travel expenses for client meeting in Cebu. Includes transportation and accommodation costs.</p>
                <h6>Business Purpose</h6>
                <p>Client presentation and contract negotiation meeting with potential new partner.</p>
            </div>
        </div>
    `;
    
    document.getElementById('viewClaimContent').innerHTML = claimDetails;
    new bootstrap.Modal(document.getElementById('viewClaimModal')).show();
}

// Edit claim (for pending claims only)
function editClaim(claimId) {
    // Pre-populate the form with existing data
    document.getElementById('claimType').value = 'Meals';
    document.getElementById('claimDate').value = '2024-11-30';
    document.getElementById('claimAmount').value = '850';
    document.getElementById('claimDescription').value = 'Client meeting lunch';
    document.getElementById('claimPurpose').value = 'Business lunch with potential client';
    
    // Change modal title and form action for editing
    document.getElementById('submitClaimModalLabel').textContent = 'Edit Claim';
    document.getElementById('submitClaimForm').action = `/employee/claims/${claimId}`;
    
    // Add method spoofing for PUT request
    let methodInput = document.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        document.getElementById('submitClaimForm').appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    new bootstrap.Modal(document.getElementById('submitClaimModal')).show();
}

// Refresh claim data
function refreshClaimData() {
    location.reload();
}

// Reset form when modal is closed
document.getElementById('submitClaimModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('submitClaimModalLabel').textContent = 'Submit New Claim';
    document.getElementById('submitClaimForm').action = '{{ route("employee.claims.store") }}';
    document.getElementById('submitClaimForm').reset();
    
    // Remove method spoofing input if exists
    const methodInput = document.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }
    
    // Reset date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('claimDate').value = today;
});

// Form validation
document.getElementById('submitClaimForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('claimAmount').value);
    const description = document.getElementById('claimDescription').value.trim();
    
    if (amount <= 0) {
        e.preventDefault();
        alert('Please enter a valid amount greater than 0.');
        return;
    }
    
    if (description.length < 10) {
        e.preventDefault();
        alert('Please provide a more detailed description (at least 10 characters).');
        return;
    }
});
</script>
@endsection
