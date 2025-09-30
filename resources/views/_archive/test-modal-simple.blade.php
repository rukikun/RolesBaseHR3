<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Modal Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Simple Modal Test</h1>
        <p>Testing direct modal functionality</p>
        
        <button class="btn btn-primary" onclick="openWorkingModal('test-modal')">
            Open Test Modal
        </button>
        
        <button class="btn btn-success ms-2" onclick="openWorkingModal('leave-modal')">
            Open Leave Modal
        </button>
    </div>

    <!-- Test Modal -->
    <div id="test-modal" style="display: none;">
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 20px; border-radius: 8px; max-width: 500px; width: 90%;">
                <h5>Test Modal</h5>
                <p>This is a simple test modal.</p>
                <input type="text" class="form-control mb-3" placeholder="Test input">
                <button class="btn btn-secondary" onclick="closeWorkingModal('test-modal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Leave Modal -->
    <div id="leave-modal" style="display: none;">
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; width: 90%;">
                <h5>Apply for Leave</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select class="form-select">
                            <option>Annual Leave</option>
                            <option>Sick Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </form>
                <button class="btn btn-secondary" onclick="closeWorkingModal('leave-modal')">Cancel</button>
                <button class="btn btn-primary ms-2">Submit</button>
            </div>
        </div>
    </div>

    <script>
    function openWorkingModal(modalId) {
        console.log('Opening modal:', modalId);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
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
        }
    }

    window.openWorkingModal = openWorkingModal;
    window.closeWorkingModal = closeWorkingModal;
    </script>
</body>
</html>
