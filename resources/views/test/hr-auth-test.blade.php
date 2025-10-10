<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Authentication Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>HR Authentication System Test</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Test the HR Authentication Modal System</strong><br>
                            This page allows you to test the HR authentication modal without going through the full leave management interface.
                        </div>

                        <h5>Test Actions:</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Leave Request Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-success btn-sm mb-2 w-100" onclick="testAuthModal('approve', 'leave_request', 1)">
                                            <i class="fas fa-check me-2"></i>Test Approve Request
                                        </button>
                                        <button class="btn btn-warning btn-sm mb-2 w-100" onclick="testAuthModal('reject', 'leave_request', 1)">
                                            <i class="fas fa-times me-2"></i>Test Reject Request
                                        </button>
                                        <button class="btn btn-danger btn-sm w-100" onclick="testAuthModal('delete', 'leave_request', 1)">
                                            <i class="fas fa-trash me-2"></i>Test Delete Request
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Leave Type Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-primary btn-sm mb-2 w-100" onclick="testAuthModal('edit', 'leave_type', 1, {name: 'Annual Leave', code: 'AL', max_days: 21})">
                                            <i class="fas fa-edit me-2"></i>Test Edit Type
                                        </button>
                                        <button class="btn btn-danger btn-sm w-100" onclick="testAuthModal('delete', 'leave_type', 1)">
                                            <i class="fas fa-trash me-2"></i>Test Delete Type
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>Test Credentials:</h5>
                            <div class="alert alert-warning">
                                <strong>Authorized Roles:</strong> HR Manager, SuperAdmin, Admin, HR Scheduler, System Administrator<br>
                                <strong>Test with:</strong> Any employee email and password with one of the above roles
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('leave-management') }}" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Leave Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HR Authentication Modal -->
    <div class="modal fade" id="hr-auth-modal" tabindex="-1" aria-labelledby="hrAuthModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hrAuthModalLabel">HR Authorization Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="hr-auth-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Authorization Required:</strong> Only HR Manager, System Administrator, HR Scheduler, Admin, or HR Administrator can perform this action.
                        </div>
                        <div class="mb-3">
                            <label for="auth-email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="auth-email" name="email" required placeholder="Enter your email address">
                        </div>
                        <div class="mb-3">
                            <label for="auth-password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="auth-password" name="password" required placeholder="Enter your password">
                        </div>
                        <input type="hidden" id="auth-action" name="action" value="">
                        <input type="hidden" id="auth-type" name="type" value="">
                        <input type="hidden" id="auth-item-id" name="item_id" value="">
                        <input type="hidden" id="auth-extra-data" name="extra_data" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock me-2"></i>Authenticate & Proceed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set up CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Test HR Authentication Modal Functions
        function testAuthModal(action, type, itemId, extraData = null) {
            // Set the form data
            document.getElementById('auth-action').value = action;
            document.getElementById('auth-type').value = type;
            document.getElementById('auth-item-id').value = itemId;
            document.getElementById('auth-extra-data').value = extraData ? JSON.stringify(extraData) : '';
            
            // Clear previous form data
            document.getElementById('auth-email').value = '';
            document.getElementById('auth-password').value = '';
            
            // Update modal title based on action
            const modalTitle = document.getElementById('hrAuthModalLabel');
            const actionText = {
                'approve': 'Approve Leave Request',
                'reject': 'Reject Leave Request', 
                'delete': 'Delete Record',
                'edit': 'Edit Record'
            };
            modalTitle.textContent = `HR Authorization Required - ${actionText[action] || 'Perform Action'}`;
            
            // Set form action URL
            const form = document.getElementById('hr-auth-form');
            form.action = '/leave/hr-auth';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('hr-auth-modal'));
            modal.show();
        }

        // Handle HR authentication form submission
        document.getElementById('hr-auth-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('hr-auth-modal'));
                    modal.hide();
                    
                    // Show success message
                    showAlert('success', data.message || 'Action completed successfully!');
                } else {
                    showAlert('error', data.message || 'Authentication failed. Please check your credentials.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Helper function to show alerts
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Insert alert at the top of the container
            const container = document.querySelector('.container');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>




