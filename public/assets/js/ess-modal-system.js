/**
 * Complete ESS Modal System
 * Provides fully functional modal system for Employee Self-Service modules
 */

// Global modal state
let activeModal = null;

// Initialize modal system
document.addEventListener('DOMContentLoaded', function() {
    console.log('ESS Modal System initializing...');
    
    // Force hide all modals on page load
    hideAllModals();
    
    // Setup modal triggers
    setupModalTriggers();
    
    // Setup global event listeners
    setupGlobalEventListeners();
    
    console.log('ESS Modal System initialized successfully!');
});

// Hide all modals function
function hideAllModals() {
    const modals = document.querySelectorAll('.working-modal, .modal, [id*="modal"]');
    modals.forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('show');
    });
    
    // Clean up body state
    document.body.classList.remove('modal-open');
    document.body.style.overflow = 'auto';
    document.body.style.paddingRight = '0';
    
    // Remove any modal backdrops
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.remove();
    });
}

// Setup modal triggers
function setupModalTriggers() {
    // Convert all modal trigger buttons
    const triggers = document.querySelectorAll('[onclick*="openWorkingModal"], [data-bs-toggle="modal"], .btn[onclick*="modal"]');
    
    triggers.forEach(trigger => {
        // Remove existing onclick handlers
        trigger.removeAttribute('onclick');
        trigger.removeAttribute('data-bs-toggle');
        trigger.removeAttribute('data-bs-target');
        
        // Add new click handler
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find modal ID from various sources
            let modalId = this.getAttribute('data-modal-id') || 
                         this.getAttribute('data-target') ||
                         this.getAttribute('data-bs-target');
            
            if (modalId) {
                modalId = modalId.replace('#', '');
            } else {
                // Try to extract from onclick attribute or text content
                const text = this.textContent.toLowerCase();
                if (text.includes('leave')) modalId = 'create-leave-modal';
                else if (text.includes('claim')) modalId = 'create-claim-modal';
                else if (text.includes('shift')) modalId = 'create-shift-modal';
                else if (text.includes('timesheet')) modalId = 'create-timesheet-modal';
                else if (text.includes('request')) modalId = 'create-request-modal';
            }
            
            if (modalId) {
                console.log('Opening modal:', modalId);
                openModal(modalId);
            } else {
                console.warn('No modal ID found for trigger:', this);
            }
        });
    });
}

// Setup global event listeners
function setupGlobalEventListeners() {
    // Escape key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && activeModal) {
            closeModal(activeModal);
        }
    });
    
    // Click outside modal handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('working-modal') || 
            e.target.classList.contains('modal-backdrop')) {
            if (activeModal) {
                closeModal(activeModal);
            }
        }
    });
    
    // Emergency cleanup shortcut (Ctrl+Shift+M)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'M') {
            e.preventDefault();
            emergencyCleanup();
        }
    });
}

// Open modal function
function openModal(modalId) {
    console.log('Attempting to open modal:', modalId);
    
    // Close any existing modal first
    if (activeModal) {
        closeModal(activeModal);
    }
    
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error('Modal not found:', modalId);
        showAlert('Modal not found: ' + modalId, 'error');
        return false;
    }
    
    // Show modal
    modal.style.display = 'flex';
    modal.classList.add('show');
    activeModal = modalId;
    
    // Lock body scroll
    document.body.style.overflow = 'hidden';
    
    // Focus first input
    setTimeout(() => {
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            firstInput.focus();
        }
    }, 100);
    
    console.log('Modal opened successfully:', modalId);
    return true;
}

// Close modal function
function closeModal(modalId) {
    console.log('Closing modal:', modalId);
    
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.warn('Modal not found for closing:', modalId);
        return;
    }
    
    // Hide modal
    modal.style.display = 'none';
    modal.classList.remove('show');
    
    // Reset form if exists
    const form = modal.querySelector('form');
    if (form && !form.hasAttribute('data-keep-values')) {
        form.reset();
        // Clear validation classes
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
        });
    }
    
    // Unlock body scroll
    document.body.style.overflow = 'auto';
    
    // Clear active modal
    if (activeModal === modalId) {
        activeModal = null;
    }
    
    console.log('Modal closed successfully:', modalId);
}

// Global functions for backward compatibility
window.openWorkingModal = openModal;
window.closeWorkingModal = closeModal;
window.openModal = openModal;
window.closeModal = closeModal;

// Show alert function
function showAlert(message, type = 'info', duration = 5000) {
    // Remove existing alerts
    document.querySelectorAll('.ess-alert').forEach(alert => alert.remove());
    
    const alertTypes = {
        'success': { class: 'alert-success', icon: 'bi-check-circle-fill' },
        'error': { class: 'alert-danger', icon: 'bi-exclamation-triangle-fill' },
        'warning': { class: 'alert-warning', icon: 'bi-exclamation-triangle-fill' },
        'info': { class: 'alert-info', icon: 'bi-info-circle-fill' }
    };
    
    const alertConfig = alertTypes[type] || alertTypes.info;
    
    const alertHtml = `
        <div class="alert ${alertConfig.class} alert-dismissible fade show ess-alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
            <i class="bi ${alertConfig.icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-dismiss
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.querySelector('.ess-alert');
            if (alert) {
                alert.remove();
            }
        }, duration);
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        const value = field.type === 'checkbox' ? field.checked : field.value.trim();
        
        if (!value) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    if (!isValid) {
        showAlert('Please fill in all required fields.', 'warning');
    }
    
    return isValid;
}

// Submit form with AJAX
function submitForm(formId, options = {}) {
    const form = document.getElementById(formId);
    if (!form) {
        showAlert('Form not found: ' + formId, 'error');
        return;
    }
    
    if (!validateForm(formId)) {
        return;
    }
    
    const formData = new FormData(form);
    const submitBtn = form.querySelector('[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    
    // Show loading state
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
        submitBtn.disabled = true;
    }
    
    fetch(form.action, {
        method: form.method || 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message || 'Operation completed successfully!', 'success');
            closeModal(form.closest('.working-modal, .modal').id);
            
            if (options.onSuccess) {
                options.onSuccess(data);
            } else {
                // Default: reload page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        } else {
            showAlert(data.message || 'An error occurred.', 'error');
            if (options.onError) options.onError(data);
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        showAlert('An error occurred while submitting the form.', 'error');
        if (options.onError) options.onError(error);
    })
    .finally(() => {
        // Reset button state
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Emergency cleanup
function emergencyCleanup() {
    console.log('Emergency cleanup initiated...');
    
    // Hide all modals
    hideAllModals();
    
    // Force reset all pointer events
    document.querySelectorAll('*').forEach(element => {
        element.style.pointerEvents = '';
    });
    
    // Clear active modal
    activeModal = null;
    
    showAlert('Emergency cleanup completed!', 'success');
}

// Export functions globally
window.showAlert = showAlert;
window.validateForm = validateForm;
window.submitForm = submitForm;
window.emergencyCleanup = emergencyCleanup;

console.log('ESS Modal System loaded successfully!');
