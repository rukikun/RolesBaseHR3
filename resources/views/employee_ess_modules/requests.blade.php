@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Request Forms')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-header mb-4">
        <h2 class="fw-bold">Request Forms</h2>
        <p class="text-muted">Submit various employee requests</p>
      </div>

      <!-- Quick Actions -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-3">Quick Actions</h5>
              <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitRequestModal">
                  <i class="bi bi-plus-circle me-2"></i>Submit New Request
                </button>
                <button class="btn btn-outline-secondary" onclick="refreshRequestData()">
                  <i class="bi bi-arrow-clockwise me-2"></i>Refresh Data
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Request Statistics -->
      <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
          <div class="card text-center">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-file-earmark-text text-primary fs-3 me-2"></i>
                <h3 class="mb-0 text-primary">{{ $stats['totalRequests'] ?? 12 }}</h3>
              </div>
              <p class="card-text text-muted mb-0">Total Requests</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
          <div class="card text-center">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-clock-history text-warning fs-3 me-2"></i>
                <h3 class="mb-0 text-warning">{{ $stats['pendingRequests'] ?? 4 }}</h3>
              </div>
              <p class="card-text text-muted mb-0">Pending Requests</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
          <div class="card text-center">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-check-circle text-success fs-3 me-2"></i>
                <h3 class="mb-0 text-success">{{ $stats['approvedRequests'] ?? 7 }}</h3>
              </div>
              <p class="card-text text-muted mb-0">Approved Requests</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
          <div class="card text-center">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-calendar-week text-info fs-3 me-2"></i>
                <h3 class="mb-0 text-info">{{ $stats['thisMonth'] ?? 3 }}</h3>
              </div>
              <p class="card-text text-muted mb-0">This Month</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Request Types -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Available Request Types</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                      <i class="bi bi-laptop text-primary fs-2 mb-3"></i>
                      <h6 class="card-title">Equipment Request</h6>
                      <p class="card-text text-muted">Request laptops, monitors, or office equipment</p>
                      <button class="btn btn-outline-primary btn-sm" onclick="openRequestModal('Equipment')">
                        Request Equipment
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="card h-100 border-success">
                    <div class="card-body text-center">
                      <i class="bi bi-award text-success fs-2 mb-3"></i>
                      <h6 class="card-title">Certificate Request</h6>
                      <p class="card-text text-muted">Request employment certificates or documents</p>
                      <button class="btn btn-outline-success btn-sm" onclick="openRequestModal('Certificate')">
                        Request Certificate
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="card h-100 border-info">
                    <div class="card-body text-center">
                      <i class="bi bi-tools text-info fs-2 mb-3"></i>
                      <h6 class="card-title">IT Support</h6>
                      <p class="card-text text-muted">Request technical support or software access</p>
                      <button class="btn btn-outline-info btn-sm" onclick="openRequestModal('IT Support')">
                        Request Support
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Requests List -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">My Requests</h5>
              <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterRequests()">
                  <option value="">All Status</option>
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Request ID</th>
                      <th>Type</th>
                      <th>Subject</th>
                      <th>Priority</th>
                      <th>Status</th>
                      <th>Submitted</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="requestsTableBody">
                    @if(isset($requests) && $requests->count() > 0)
                      @foreach($requests as $request)
                      <tr>
                        <td>{{ $request->request_number ?? 'REQ-' . str_pad($request->id ?? 1, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $request->request_type ?? 'Equipment' }}</td>
                        <td>{{ $request->subject ?? 'Laptop Request' }}</td>
                        <td>
                          @php
                            $priority = $request->priority ?? 'medium';
                            $priorityClass = match($priority) {
                              'high' => 'bg-danger',
                              'low' => 'bg-secondary',
                              default => 'bg-warning'
                            };
                          @endphp
                          <span class="badge {{ $priorityClass }}">{{ ucfirst($priority) }}</span>
                        </td>
                        <td>
                          @php
                            $status = $request->status ?? 'pending';
                            $badgeClass = match($status) {
                              'approved' => 'bg-success',
                              'rejected' => 'bg-danger',
                              'completed' => 'bg-info',
                              default => 'bg-warning'
                            };
                          @endphp
                          <span class="badge {{ $badgeClass }} status-badge">{{ ucfirst($status) }}</span>
                        </td>
                        <td>{{ isset($request->created_at) ? date('M d, Y', strtotime($request->created_at)) : 'Dec 01, 2024' }}</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewRequest({{ $request->id ?? 1 }})">
                              <i class="bi bi-eye"></i>
                            </button>
                            @if(($request->status ?? 'pending') === 'pending')
                            <button class="btn btn-outline-secondary" onclick="editRequest({{ $request->id ?? 1 }})">
                              <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                      @endforeach
                    @else
                      <!-- Sample data when no requests exist -->
                      <tr>
                        <td>REQ-0001</td>
                        <td>Equipment</td>
                        <td>Laptop Request</td>
                        <td><span class="badge bg-warning">Medium</span></td>
                        <td><span class="badge bg-success status-badge">Approved</span></td>
                        <td>Nov 28, 2024</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewRequest(1)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>REQ-0002</td>
                        <td>Certificate</td>
                        <td>Employment Certificate</td>
                        <td><span class="badge bg-danger">High</span></td>
                        <td><span class="badge bg-warning status-badge">Pending</span></td>
                        <td>Dec 01, 2024</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewRequest(2)">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="editRequest(2)">
                              <i class="bi bi-pencil"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>REQ-0003</td>
                        <td>IT Support</td>
                        <td>Software Access Request</td>
                        <td><span class="badge bg-secondary">Low</span></td>
                        <td><span class="badge bg-info status-badge">Completed</span></td>
                        <td>Nov 25, 2024</td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewRequest(3)">
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
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Submit Request Modal -->
<div class="modal fade" id="submitRequestModal" tabindex="-1" aria-labelledby="submitRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submitRequestModalLabel">Submit New Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="submitRequestForm" action="{{ route('employee.requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="requestType" class="form-label">Request Type <span class="text-danger">*</span></label>
              <select class="form-select" id="requestType" name="request_type" required>
                <option value="">Select request type</option>
                <option value="Equipment">Equipment Request</option>
                <option value="Certificate">Certificate Request</option>
                <option value="IT Support">IT Support</option>
                <option value="Access">Access Request</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
              <select class="form-select" id="priority" name="priority" required>
                <option value="">Select priority</option>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="subject" name="subject" required placeholder="Brief description of your request">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Provide detailed information about your request..."></textarea>
          </div>
          <div class="mb-3">
            <label for="justification" class="form-label">Business Justification</label>
            <textarea class="form-control" id="justification" name="justification" rows="2" placeholder="Explain why this request is needed..."></textarea>
          </div>
          <div class="mb-3">
            <label for="attachment" class="form-label">Attachment</label>
            <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png">
            <div class="form-text">Upload supporting documents if needed (optional)</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewRequestModalLabel">Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewRequestContent">
        <!-- Request details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Open request modal with pre-selected type
function openRequestModal(type) {
    document.getElementById('requestType').value = type;
    new bootstrap.Modal(document.getElementById('submitRequestModal')).show();
}

// Filter requests by status
function filterRequests() {
    const filter = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#requestsTableBody tr');
    
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

// View request details
function viewRequest(requestId) {
    const requestDetails = `
        <div class="row">
            <div class="col-md-6">
                <h6>Request Information</h6>
                <p><strong>Request ID:</strong> REQ-${String(requestId).padStart(4, '0')}</p>
                <p><strong>Type:</strong> Equipment Request</p>
                <p><strong>Subject:</strong> Laptop Request</p>
                <p><strong>Priority:</strong> <span class="badge bg-warning">Medium</span></p>
                <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
            </div>
            <div class="col-md-6">
                <h6>Submission Details</h6>
                <p><strong>Submitted:</strong> Nov 28, 2024</p>
                <p><strong>Reviewed:</strong> Nov 30, 2024</p>
                <p><strong>Reviewer:</strong> IT Manager</p>
                <p><strong>Expected Completion:</strong> Dec 05, 2024</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Description</h6>
                <p>Requesting a new laptop for development work. Current laptop is experiencing performance issues and affecting productivity.</p>
                <h6>Business Justification</h6>
                <p>Need reliable hardware for software development tasks and client presentations.</p>
            </div>
        </div>
    `;
    
    document.getElementById('viewRequestContent').innerHTML = requestDetails;
    new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
}

// Edit request (for pending requests only)
function editRequest(requestId) {
    // Pre-populate the form with existing data
    document.getElementById('requestType').value = 'Certificate';
    document.getElementById('priority').value = 'high';
    document.getElementById('subject').value = 'Employment Certificate';
    document.getElementById('description').value = 'Need employment certificate for visa application';
    document.getElementById('justification').value = 'Required for travel visa processing';
    
    // Change modal title and form action for editing
    document.getElementById('submitRequestModalLabel').textContent = 'Edit Request';
    document.getElementById('submitRequestForm').action = `/employee/requests/${requestId}`;
    
    // Add method spoofing for PUT request
    let methodInput = document.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        document.getElementById('submitRequestForm').appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    new bootstrap.Modal(document.getElementById('submitRequestModal')).show();
}

// Refresh request data
function refreshRequestData() {
    location.reload();
}

// Reset form when modal is closed
document.getElementById('submitRequestModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('submitRequestModalLabel').textContent = 'Submit New Request';
    document.getElementById('submitRequestForm').action = '{{ route("employee.requests.store") }}';
    document.getElementById('submitRequestForm').reset();
    
    // Remove method spoofing input if exists
    const methodInput = document.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }
    
    // Reset priority to medium
    document.getElementById('priority').value = 'medium';
});

// Form validation
document.getElementById('submitRequestForm').addEventListener('submit', function(e) {
    const subject = document.getElementById('subject').value.trim();
    const description = document.getElementById('description').value.trim();
    
    if (subject.length < 5) {
        e.preventDefault();
        alert('Please provide a more descriptive subject (at least 5 characters).');
        return;
    }
    
    if (description.length < 20) {
        e.preventDefault();
        alert('Please provide a more detailed description (at least 20 characters).');
        return;
    }
});
</script>
@endsection
