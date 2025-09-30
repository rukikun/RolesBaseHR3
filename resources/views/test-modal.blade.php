<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Modal Test Page</h1>
        <p>This page tests the modal functionality with direct inline functions.</p>
        
        <button class="btn btn-primary" onclick="openWorkingModal('test-modal')">
            Open Test Modal
        </button>
        
        <button class="btn btn-success ms-2" onclick="openWorkingModal('leave-modal')">
            Open Leave Modal
        </button>
        
        <div class="mt-3">
            <button class="btn btn-warning" onclick="testConsole()">
                Test Console Log
            </button>
        </div>
    </div>

    <!-- Test Modal -->
    <div id="test-modal" class="working-modal" style="display: none;">
        <div class="working-modal-content bg-white rounded p-4" style="max-width: 500px; width: 90%;">
            <div class="working-modal-header d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Test Modal</h5>
                <button type="button" class="btn-close" onclick="closeWorkingModal('test-modal')"></button>
            </div>
            <div class="working-modal-body">
                <p>This is a test modal to verify functionality.</p>
                <input type="text" class="form-control" placeholder="Test input">
    <div class="working-modal" id="create-leave-modal">
        <div class="working-modal-backdrop" onclick="closeWorkingModal('create-leave-modal')"></div>
        <div class="working-modal-dialog">
            <div class="working-modal-content">
                <div class="working-modal-header">
                    <h5 class="working-modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
                    </h5>
                    <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-leave-modal')">&times;</button>
                </div>
                <div class="working-modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="leave-type" class="form-label">Leave Type</label>
                            <select class="form-select" id="leave-type" required>
                                <option value="">Select Leave Type</option>
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="emergency">Emergency Leave</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start-date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start-date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end-date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end-date" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" rows="3" placeholder="Please provide a reason for your leave request..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-leave-modal')">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="alert('Leave request would be submitted!'); closeWorkingModal('create-leave-modal')">
                        <i class="bi bi-paper-plane me-1"></i>Submit Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/simple-modal-fix.js') }}"></script>
    
    <script>
        // Additional debugging
        console.log('Test page loaded');
        
        // Test if functions are available
        setTimeout(() => {
            console.log('openWorkingModal function available:', typeof openWorkingModal);
            console.log('closeWorkingModal function available:', typeof closeWorkingModal);
        }, 1000);
    </script>
</body>
</html>
