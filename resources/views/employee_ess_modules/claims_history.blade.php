@extends('layouts.employee_layout')

@section('title', 'Claims History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Claims History
                    </h4>
                    <div>
                        <a href="{{ route('employee.claims.create') }}" class="btn btn-success me-2">
                            <i class="fas fa-plus me-1"></i>New Claim
                        </a>
                        <a href="{{ route('employee.claims') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Claims
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Claims</h6>
                                            <h3 class="mb-0">{{ $stats['totalClaims'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Amount</h6>
                                            <h3 class="mb-0">₱{{ number_format($stats['totalAmount'] ?? 0, 2) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-money-bill-wave fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Approved</h6>
                                            <h3 class="mb-0">{{ $stats['approvedClaims'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Pending</h6>
                                            <h3 class="mb-0">{{ $stats['pendingClaims'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-hourglass-half fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Rejected</h6>
                                            <h3 class="mb-0">{{ $stats['rejectedClaims'] ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Approved ₱</h6>
                                            <h3 class="mb-0">{{ number_format($stats['approvedAmount'] ?? 0, 0) }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-coins fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Options -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="typeFilter">
                                <option value="">All Types</option>
                                @foreach($claims->unique('claim_type_name') as $claim)
                                    @if($claim->claim_type_name)
                                    <option value="{{ $claim->claim_type_name }}">{{ $claim->claim_type_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="month" class="form-control" id="monthFilter" placeholder="Filter by month">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </button>
                        </div>
                    </div>

                    <!-- Claims History Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="claimsHistoryTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Claim Date</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Receipt</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                <tr data-status="{{ $claim->status ?? 'pending' }}" 
                                    data-type="{{ $claim->claim_type_name ?? '' }}" 
                                    data-date="{{ $claim->claim_date ?? '' }}">
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ str_pad($claim->id ?? 0, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>{{ $claim->claim_type_name ?? 'N/A' }}</td>
                                    <td>
                                        <strong class="text-success">₱{{ number_format($claim->amount ?? 0, 2) }}</strong>
                                    </td>
                                    <td>{{ isset($claim->claim_date) ? date('M d, Y', strtotime($claim->claim_date)) : 'N/A' }}</td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="{{ $claim->description ?? 'No description' }}">
                                            {{ $claim->description ?? 'No description' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $status = $claim->status ?? 'pending';
                                            $badgeClass = match($status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'paid' => 'bg-info',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>{{ isset($claim->created_at) ? date('M d, Y', strtotime($claim->created_at)) : 'N/A' }}</td>
                                    <td>
                                        @if($claim->receipt_path ?? false)
                                        <a href="{{ asset('storage/' . $claim->receipt_path) }}" target="_blank" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        @else
                                        <span class="text-muted">No receipt</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewClaim({{ $claim->id ?? 0 }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No claims found
                                        <br>
                                        <a href="{{ route('employee.claims.create') }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus me-1"></i>Submit Your First Claim
                                        </a>
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
</div>

<!-- View Claim Modal -->
<div class="modal fade" id="viewClaimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Claim Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="claimDetails">
                <!-- Claim details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const monthFilter = document.getElementById('monthFilter');
    const table = document.getElementById('claimsHistoryTable');
    const rows = table.querySelectorAll('tbody tr[data-status]');

    function filterTable() {
        const statusValue = statusFilter.value.toLowerCase();
        const typeValue = typeFilter.value.toLowerCase();
        const monthValue = monthFilter.value;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status').toLowerCase();
            const rowType = row.getAttribute('data-type').toLowerCase();
            const rowDate = row.getAttribute('data-date');
            const rowMonth = rowDate ? rowDate.substring(0, 7) : '';

            const statusMatch = !statusValue || rowStatus === statusValue;
            const typeMatch = !typeValue || rowType.includes(typeValue);
            const monthMatch = !monthValue || rowMonth === monthValue;

            if (statusMatch && typeMatch && monthMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    statusFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);
    monthFilter.addEventListener('change', filterTable);

    window.clearFilters = function() {
        statusFilter.value = '';
        typeFilter.value = '';
        monthFilter.value = '';
        filterTable();
    };

    window.viewClaim = function(claimId) {
        // Find the claim data from the table
        const claimRow = document.querySelector(`tr[data-status] button[onclick="viewClaim(${claimId})"]`).closest('tr');
        const cells = claimRow.querySelectorAll('td');
        
        const claimDetails = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Claim Information</h6>
                    <p><strong>Claim ID:</strong> ${cells[0].textContent.trim()}</p>
                    <p><strong>Type:</strong> ${cells[1].textContent.trim()}</p>
                    <p><strong>Amount:</strong> ${cells[2].textContent.trim()}</p>
                    <p><strong>Claim Date:</strong> ${cells[3].textContent.trim()}</p>
                    <p><strong>Status:</strong> ${cells[5].innerHTML}</p>
                    <p><strong>Submitted:</strong> ${cells[6].textContent.trim()}</p>
                </div>
                <div class="col-md-6">
                    <h6>Description</h6>
                    <p>${cells[4].getAttribute('title') || cells[4].textContent.trim()}</p>
                    
                    <h6>Receipt</h6>
                    ${cells[7].innerHTML.includes('href') ? 
                        `<a href="${cells[7].querySelector('a').href}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i>Download Receipt
                        </a>` : 
                        '<p class="text-muted">No receipt attached</p>'
                    }
                </div>
            </div>
        `;
        
        document.getElementById('claimDetails').innerHTML = claimDetails;
        const modal = new bootstrap.Modal(document.getElementById('viewClaimModal'));
        modal.show();
    };
});
</script>
@endsection
