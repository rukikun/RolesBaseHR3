// Universal Modal Utilities for HR System
// Prevents modal backdrop issues and ensures proper cleanup

// Initialize all modals on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals with proper settings
    const modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(modalEl => {
        // Ensure modal is properly initialized
        if (!bootstrap.Modal.getInstance(modalEl)) {
            new bootstrap.Modal(modalEl, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }
        
        // Add event listeners for proper cleanup
        modalEl.addEventListener('hidden.bs.modal', function() {
            // Clean up any stuck backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                if (backdrop && !document.querySelector('.modal.show')) {
                    backdrop.remove();
                }
            });
            
            // Reset body styles
            if (!document.querySelector('.modal.show')) {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });
    });
});

// Properly close modal and cleanup
function closeModal(modalId = null) {
    let modal;
    
    if (modalId) {
        modal = document.getElementById(modalId);
    } else {
        // Find any open modal
        modal = document.querySelector('.modal.show');
    }
    
    if (!modal) return;
    
    const modalInstance = bootstrap.Modal.getInstance(modal);
    
    if (modalInstance) {
        modalInstance.hide();
    }
    
    // Force cleanup of modal backdrop if it gets stuck
    setTimeout(() => {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }, 300);
}

// Show alert with auto-dismiss
function showAlert(message, type = 'info', containerId = 'alert-container') {
    const alertContainer = document.getElementById(containerId);
    if (!alertContainer) {
        console.warn('Alert container not found:', containerId);
        return;
    }
    
    // Clear existing alerts of the same type
    const existingAlerts = alertContainer.querySelectorAll(`.alert-${type}`);
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 10000;
        width: 280px;
        margin: 0;
        font-size: 0.875rem;
    `;
    alert.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert && alert.parentNode) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    }, 5000);
}

// Get appropriate icon for alert type
function getAlertIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'danger': return 'exclamation-triangle';
        case 'warning': return 'exclamation-circle';
        case 'info': return 'info-circle';
        default: return 'info-circle';
    }
}

// Disable/enable form submission button
function toggleSubmitButton(buttonSelector, isDisabled, loadingText = 'Saving...', originalText = 'Save') {
    const button = document.querySelector(buttonSelector);
    if (!button) return;
    
    button.disabled = isDisabled;
    button.textContent = isDisabled ? loadingText : originalText;
}

// Validate required form fields
function validateRequiredFields(formId, requiredFields) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    for (const fieldId of requiredFields) {
        const field = document.getElementById(fieldId);
        if (!field || !field.value.trim()) {
            showAlert(`Please fill in all required fields`, 'warning');
            field?.focus();
            return false;
        }
    }
    return true;
}

// Reset form and clear validation
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        // Clear any validation states
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
        
        // Reset any hidden ID fields
        const hiddenIdFields = form.querySelectorAll('input[type="hidden"][id$="-id"]');
        hiddenIdFields.forEach(field => field.value = '');
    }
}

// Enhanced modal show function
function showModal(modalId, title = null) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
        console.error('Modal not found:', modalId);
        return;
    }
    
    // Set title if provided
    if (title) {
        const titleElement = modalElement.querySelector('.modal-title');
        if (titleElement) {
            titleElement.textContent = title;
        }
    }
    
    // Get or create modal instance
    let modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (!modalInstance) {
        modalInstance = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
    }
    
    modalInstance.show();
}

// Enhanced form submission with loading state
function submitForm(formId, url, method = 'POST', successCallback = null, errorCallback = null) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error('Form not found:', formId);
        return;
    }
    
    const formData = new FormData(form);
    const jsonData = {};
    
    // Convert FormData to JSON
    for (let [key, value] of formData.entries()) {
        jsonData[key] = value;
    }
    
    // Find submit button and set loading state
    const submitBtn = form.querySelector('button[type="submit"], .btn-primary');
    const originalText = submitBtn ? submitBtn.textContent : '';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(jsonData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message || 'Operation completed successfully!', 'success');
            if (successCallback) successCallback(data);
        } else {
            showAlert(data.message || 'An error occurred', 'danger');
            if (errorCallback) errorCallback(data);
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        showAlert('Network error occurred', 'danger');
        if (errorCallback) errorCallback(error);
    })
    .finally(() => {
        // Reset button state
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// Global error handler for uncaught errors
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    showAlert('An unexpected error occurred. Please try again or contact support if the issue persists.', 'danger');
});

// Global handler for unhandled promise rejections
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showAlert('A network error occurred. Please try again.', 'warning');
});
