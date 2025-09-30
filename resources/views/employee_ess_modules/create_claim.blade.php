@extends('layouts.employee_layout')

@section('title', 'Submit New Claim')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Submit New Claim
                    </h4>
                    <a href="{{ route('employee.claims') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Claims
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('employee.claims.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="claim_type_id" class="form-label">Claim Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="claim_type_id" name="claim_type_id" required>
                                        <option value="">Select claim type</option>
                                        @foreach($claimTypes as $claimType)
                                        <option value="{{ $claimType->id }}" {{ old('claim_type_id') == $claimType->id ? 'selected' : '' }}>
                                            {{ $claimType->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="amount" name="amount" 
                                               step="0.01" min="0" value="{{ old('amount') }}" required 
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="claim_date" class="form-label">Claim Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="claim_date" name="claim_date" 
                                           value="{{ old('claim_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="receipt" class="form-label">Receipt/Attachment</label>
                                    <input type="file" class="form-control" id="receipt" name="receipt" 
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    <div class="form-text">Accepted formats: JPG, PNG, PDF (Max: 2MB)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      required placeholder="Please provide details about your claim...">{{ old('description') }}</textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                        </div>

                        <!-- Claim Guidelines -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Claim Guidelines:</h6>
                            <ul class="mb-0">
                                <li>Ensure all receipts are clear and legible</li>
                                <li>Claims must be submitted within 30 days of expense</li>
                                <li>Provide detailed description of the expense</li>
                                <li>Attach original receipts or invoices when possible</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Submit Claim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Reference -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>Quick Reference
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Common Claim Types:</h6>
                    <ul class="list-unstyled">
                        @foreach($claimTypes as $claimType)
                        <li class="mb-2">
                            <span class="badge bg-light text-dark">{{ $claimType->name }}</span>
                            @if(isset($claimType->description))
                            <br><small class="text-muted">{{ $claimType->description }}</small>
                            @endif
                        </li>
                        @endforeach
                    </ul>

                    <hr>

                    <h6>Processing Time:</h6>
                    <p class="small text-muted">
                        Claims are typically processed within 5-7 business days. 
                        You will receive email notifications about status updates.
                    </p>

                    <h6>Need Help?</h6>
                    <p class="small text-muted">
                        Contact HR at <strong>hr@jetlouge.com</strong> for assistance 
                        with your claims.
                    </p>
                </div>
            </div>

            <!-- Recent Claims -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Your Recent Claims
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <a href="{{ route('employee.claims') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>View All Claims
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.querySelector('form').reset();
    }
}

// Auto-calculate and format amount
document.getElementById('amount').addEventListener('input', function(e) {
    let value = parseFloat(e.target.value);
    if (!isNaN(value)) {
        e.target.value = value.toFixed(2);
    }
});

// Character counter for description
document.getElementById('description').addEventListener('input', function(e) {
    const maxLength = 1000;
    const currentLength = e.target.value.length;
    const remaining = maxLength - currentLength;
    
    // Find or create character counter
    let counter = document.getElementById('char-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'char-counter';
        counter.className = 'form-text';
        e.target.parentNode.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} characters`;
    counter.className = remaining < 100 ? 'form-text text-warning' : 'form-text text-muted';
});

// File upload preview
document.getElementById('receipt').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
        const fileName = file.name;
        
        // Show file info
        let fileInfo = document.getElementById('file-info');
        if (!fileInfo) {
            fileInfo = document.createElement('div');
            fileInfo.id = 'file-info';
            fileInfo.className = 'form-text';
            e.target.parentNode.appendChild(fileInfo);
        }
        
        fileInfo.innerHTML = `<i class="fas fa-file me-1"></i>${fileName} (${fileSize} MB)`;
        fileInfo.className = fileSize > 2 ? 'form-text text-danger' : 'form-text text-success';
    }
});
</script>
<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection
