/* Working Modal System JavaScript - Based on leave_management.blade.php */

// Core modal functions
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first input in modal
        setTimeout(() => {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Reset form if it exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset form action to create mode
            const originalAction = form.getAttribute('data-original-action') || form.action;
            form.action = originalAction;
            form.method = 'POST';
            
            // Remove method override if it exists
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            
            // Reset modal title
            const title = modal.querySelector('.working-modal-title');
            if (title && title.getAttribute('data-original-title')) {
                title.textContent = title.getAttribute('data-original-title');
            }
        }
    }
}

// Close modal when clicking backdrop
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('working-modal-backdrop')) {
        const modal = e.target.closest('.working-modal');
        if (modal) {
            closeWorkingModal(modal.id);
        }
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.working-modal[style*="flex"]');
        if (openModal) {
            closeWorkingModal(openModal.id);
        }
    }
});

// Auto-calculate functions for different modules
function calculateLeaveDays() {
    const startDateInput = document.getElementById('start-date') || document.getElementById('leave-start-date');
    const endDateInput = document.getElementById('end-date') || document.getElementById('leave-end-date');
    const daysDisplay = document.getElementById('calculated-days');
    
    if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate >= startDate) {
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            if (daysDisplay) {
                daysDisplay.textContent = `${diffDays} days`;
            }
            
            // Update hidden input if exists
            const daysInput = document.getElementById('days-requested');
            if (daysInput) {
                daysInput.value = diffDays;
            }
            
            return diffDays;
        }
    }
    return 0;
}

function calculateTotalHours() {
    const startTimeInput = document.getElementById('start-time') || document.getElementById('time-start');
    const endTimeInput = document.getElementById('end-time') || document.getElementById('time-end');
    const totalHoursDisplay = document.getElementById('total-hours');
    
    if (startTimeInput && endTimeInput && startTimeInput.value && endTimeInput.value) {
        const startTime = new Date(`1970-01-01T${startTimeInput.value}:00`);
        const endTime = new Date(`1970-01-01T${endTimeInput.value}:00`);
        
        if (endTime > startTime) {
            const diffMs = endTime - startTime;
            const diffHours = diffMs / (1000 * 60 * 60);
            
            if (totalHoursDisplay) {
                totalHoursDisplay.textContent = `${diffHours.toFixed(2)} hours`;
            }
            
            // Update hidden input if exists
            const hoursInput = document.getElementById('hours-worked');
            if (hoursInput) {
                hoursInput.value = diffHours.toFixed(2);
            }
            
            return diffHours;
        }
    }
    return 0;
}

// Form validation functions
function validateClaimForm() {
    const claimType = document.getElementById('claim-type-id') || document.getElementById('claim-type');
    const amount = document.getElementById('claim-amount') || document.getElementById('amount');
    const description = document.getElementById('claim-description') || document.getElementById('description');
    
    if (!claimType || !claimType.value) {
        alert('Please select a claim type.');
        return false;
    }
    
    if (!amount || !amount.value || parseFloat(amount.value) <= 0) {
        alert('Please enter a valid amount.');
        return false;
    }
    
    if (!description || !description.value.trim()) {
        alert('Please provide a description.');
        return false;
    }
    
    return true;
}

function validateLeaveForm() {
    const employeeId = document.getElementById('employee-id') || document.getElementById('request-employee');
    const leaveTypeId = document.getElementById('leave-type-id') || document.getElementById('request-leave-type');
    const startDate = document.getElementById('start-date') || document.getElementById('leave-start-date');
    const endDate = document.getElementById('end-date') || document.getElementById('leave-end-date');
    const reason = document.getElementById('leave-reason') || document.getElementById('reason');
    
    if (employeeId && !employeeId.value) {
        alert('Please select an employee.');
        return false;
    }
    
    if (!leaveTypeId || !leaveTypeId.value) {
        alert('Please select a leave type.');
        return false;
    }
    
    if (!startDate || !startDate.value) {
        alert('Please select a start date.');
        return false;
    }
    
    if (!endDate || !endDate.value) {
        alert('Please select an end date.');
        return false;
    }
    
    if (new Date(endDate.value) < new Date(startDate.value)) {
        alert('End date cannot be before start date.');
        return false;
    }
    
    if (!reason || !reason.value.trim()) {
        alert('Please provide a reason for the leave request.');
        return false;
    }
    
    return true;
}

function validateShiftForm() {
    const employeeId = document.getElementById('employee-id') || document.getElementById('shift-employee');
    const shiftDate = document.getElementById('shift-date');
    const startTime = document.getElementById('start-time') || document.getElementById('shift-start-time');
    const endTime = document.getElementById('end-time') || document.getElementById('shift-end-time');
    
    if (employeeId && !employeeId.value) {
        alert('Please select an employee.');
        return false;
    }
    
    if (!shiftDate || !shiftDate.value) {
        alert('Please select a shift date.');
        return false;
    }
    
    if (!startTime || !startTime.value) {
        alert('Please select a start time.');
        return false;
    }
    
    if (!endTime || !endTime.value) {
        alert('Please select an end time.');
        return false;
    }
    
    if (startTime.value >= endTime.value) {
        alert('End time must be after start time.');
        return false;
    }
    
    return true;
}

function validateTimeEntryForm() {
    const date = document.getElementById('entry-date') || document.getElementById('time-date');
    const startTime = document.getElementById('start-time') || document.getElementById('time-start');
    const endTime = document.getElementById('end-time') || document.getElementById('time-end');
    const description = document.getElementById('time-description') || document.getElementById('description');
    
    if (!date || !date.value) {
        alert('Please select a date.');
        return false;
    }
    
    if (!startTime || !startTime.value) {
        alert('Please select a start time.');
        return false;
    }
    
    if (!endTime || !endTime.value) {
        alert('Please select an end time.');
        return false;
    }
    
    if (startTime.value >= endTime.value) {
        alert('End time must be after start time.');
        return false;
    }
    
    if (!description || !description.value.trim()) {
        alert('Please provide a description.');
        return false;
    }
    
    return true;
}

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate days when dates change
    const startDateInputs = document.querySelectorAll('#start-date, #leave-start-date');
    const endDateInputs = document.querySelectorAll('#end-date, #leave-end-date');
    
    startDateInputs.forEach(input => {
        input.addEventListener('change', calculateLeaveDays);
    });
    
    endDateInputs.forEach(input => {
        input.addEventListener('change', calculateLeaveDays);
    });
    
    // Auto-calculate hours when times change
    const startTimeInputs = document.querySelectorAll('#start-time, #time-start, #shift-start-time');
    const endTimeInputs = document.querySelectorAll('#end-time, #time-end, #shift-end-time');
    
    startTimeInputs.forEach(input => {
        input.addEventListener('change', calculateTotalHours);
    });
    
    endTimeInputs.forEach(input => {
        input.addEventListener('change', calculateTotalHours);
    });
    
    // Add form validation to all working modal forms
    const forms = document.querySelectorAll('.working-modal form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Determine form type and validate accordingly
            if (form.id.includes('claim')) {
                isValid = validateClaimForm();
            } else if (form.id.includes('leave')) {
                isValid = validateLeaveForm();
            } else if (form.id.includes('shift')) {
                isValid = validateShiftForm();
            } else if (form.id.includes('time')) {
                isValid = validateTimeEntryForm();
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Set minimum date to today for future date inputs
    const futureDateInputs = document.querySelectorAll('input[type="date"][id*="date"], input[type="date"][id*="start"], input[type="date"][id*="end"]');
    const today = new Date().toISOString().split('T')[0];
    
    futureDateInputs.forEach(input => {
        if (!input.hasAttribute('min')) {
            input.setAttribute('min', today);
        }
    });
});

// Global functions for ESS modules
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;
window.calculateLeaveDays = calculateLeaveDays;
window.calculateTotalHours = calculateTotalHours;
window.validateClaimForm = validateClaimForm;
window.validateLeaveForm = validateLeaveForm;
window.validateShiftForm = validateShiftForm;
window.validateTimeEntryForm = validateTimeEntryForm;
