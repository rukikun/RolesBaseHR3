/**
 * Universal Modal Initialization and Management
 * Ensures all modals work properly with centering and sizing
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAllModals();
    setupModalEventListeners();
});

/**
 * Initialize all modal types
 */
function initializeAllModals() {
    // Initialize Bootstrap modals
    initializeBootstrapModals();
    
    // Initialize working modals
    initializeWorkingModals();
    
    // Initialize simple modals
    initializeSimpleModals();
}

/**
 * Initialize Bootstrap modals with proper configuration
 */
function initializeBootstrapModals() {
    const bootstrapModals = document.querySelectorAll('.modal');
    
    bootstrapModals.forEach(modal => {
        // Ensure proper backdrop and keyboard settings
        modal.setAttribute('data-bs-backdrop', 'static');
        modal.setAttribute('data-bs-keyboard', 'false');
        
        // Add event listeners for proper centering
        modal.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
            centerModal(this);
        });
        
        modal.addEventListener('shown.bs.modal', function() {
            centerModal(this);
            focusFirstInput(this);
        });
        
        modal.addEventListener('hide.bs.modal', function() {
            document.body.style.overflow = '';
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            clearModalForm(this);
        });
    });
}

/**
 * Initialize working modals (custom implementation)
 */
function initializeWorkingModals() {
    const workingModals = document.querySelectorAll('.working-modal');
    
    workingModals.forEach(modal => {
        // Ensure proper display and positioning
        if (modal.style.display !== 'none') {
            modal.style.display = 'none';
        }
        
        // Add close button if not present
        addCloseButtonToWorkingModal(modal);
    });
}

/**
 * Initialize simple modals
 */
function initializeSimpleModals() {
    const simpleModals = document.querySelectorAll('.simple-modal');
    
    simpleModals.forEach(modal => {
        if (modal.style.display !== 'none') {
            modal.style.display = 'none';
        }
        
        addCloseButtonToSimpleModal(modal);
    });
}

/**
 * Center a modal properly
 */
function centerModal(modal) {
    const modalDialog = modal.querySelector('.modal-dialog');
    if (modalDialog) {
        // Force recalculation of centering
        modalDialog.style.marginTop = '0';
        modalDialog.style.marginBottom = '0';
        
        // Ensure proper vertical centering
        const viewportHeight = window.innerHeight;
        const modalHeight = modalDialog.offsetHeight;
        
        if (modalHeight < viewportHeight) {
            const topMargin = Math.max(0, (viewportHeight - modalHeight) / 2);
            modalDialog.style.marginTop = topMargin + 'px';
            modalDialog.style.marginBottom = topMargin + 'px';
        }
    }
}

/**
 * Focus first input in modal
 */
function focusFirstInput(modal) {
    const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
    if (firstInput) {
        setTimeout(() => {
            firstInput.focus();
        }, 100);
    }
}

/**
 * Clear modal form when closed
 */
function clearModalForm(modal) {
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
        
        // Clear validation states
        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        const validInputs = form.querySelectorAll('.is-valid');
        validInputs.forEach(input => {
            input.classList.remove('is-valid');
        });
        
        // Clear error messages
        const errorMessages = form.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(msg => {
            msg.textContent = '';
        });
    }
}

/**
 * Add close button to working modal if not present
 */
function addCloseButtonToWorkingModal(modal) {
    const header = modal.querySelector('.working-modal-header');
    if (header && !header.querySelector('.modal-close, .working-modal-close')) {
        // Check if there's already a close button with different class
        const existingClose = header.querySelector('button[onclick*="closeWorkingModal"]');
        if (!existingClose) {
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'modal-close';
            closeButton.innerHTML = '&times;';
            closeButton.setAttribute('aria-label', 'Close');
            closeButton.onclick = () => closeWorkingModal(modal.id);
            header.appendChild(closeButton);
        }
    }
}

/**
 * Add close button to simple modal if not present
 */
function addCloseButtonToSimpleModal(modal) {
    const content = modal.querySelector('.simple-modal-content');
    if (content && !content.querySelector('.modal-close')) {
        // Check if there's already a close button
        const existingClose = content.querySelector('button[onclick*="closeModal"]');
        if (!existingClose) {
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'modal-close';
            closeButton.innerHTML = '&times;';
            closeButton.setAttribute('aria-label', 'Close');
            closeButton.onclick = () => closeModal(modal.id);
            content.appendChild(closeButton);
        }
    }
}

/**
 * Setup global modal event listeners
 */
function setupModalEventListeners() {
    // Handle escape key for all modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close Bootstrap modals
            const openBootstrapModal = document.querySelector('.modal.show');
            if (openBootstrapModal) {
                const modalInstance = bootstrap.Modal.getInstance(openBootstrapModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
                return;
            }
            
            // Close working modals
            const openWorkingModal = document.querySelector('.working-modal[style*="display: flex"], .working-modal[style*="display: block"]');
            if (openWorkingModal) {
                closeWorkingModal(openWorkingModal.id);
                return;
            }
            
            // Close simple modals
            const openSimpleModal = document.querySelector('.simple-modal[style*="display: flex"], .simple-modal[style*="display: block"]');
            if (openSimpleModal) {
                closeModal(openSimpleModal.id);
                return;
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        // Recenter all open Bootstrap modals
        const openBootstrapModals = document.querySelectorAll('.modal.show');
        openBootstrapModals.forEach(modal => {
            centerModal(modal);
        });
    });
}

/**
 * Enhanced working modal functions
 */
function openWorkingModal(modalId) {
    // Close any other open modals first
    closeAllModals();
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            focusFirstInput(modal);
        }, 100);
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        clearModalForm(modal);
    }
}

/**
 * Close all modals function
 */
function closeAllModals() {
    // Close Bootstrap modals
    const bootstrapModals = document.querySelectorAll('.modal.show');
    bootstrapModals.forEach(modal => {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });
    
    // Close working modals
    const workingModals = document.querySelectorAll('.working-modal');
    workingModals.forEach(modal => {
        modal.style.display = 'none';
    });
    
    // Close simple modals
    const simpleModals = document.querySelectorAll('.simple-modal');
    simpleModals.forEach(modal => {
        modal.style.display = 'none';
    });
    
    // Reset body
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open');
    
    // Remove any stray backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
}

/**
 * Enhanced simple modal functions
 */
function openModal(modalId) {
    // Close any other open modals first
    closeAllModals();
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            focusFirstInput(modal);
        }, 100);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        clearModalForm(modal);
    }
}

/**
 * Utility function to show Bootstrap modal
 */
function showBootstrapModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const modalInstance = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });
        modalInstance.show();
    }
}

/**
 * Utility function to hide Bootstrap modal
 */
function hideBootstrapModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
}

/**
 * Form validation helper for modals
 */
function validateModalForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    return isValid;
}

/**
 * Auto-resize modal content based on content height
 */
function autoResizeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const modalBody = modal.querySelector('.modal-body, .working-modal-body');
    if (modalBody) {
        const maxHeight = window.innerHeight * 0.8;
        const contentHeight = modalBody.scrollHeight;
        
        if (contentHeight > maxHeight) {
            modalBody.style.maxHeight = maxHeight + 'px';
            modalBody.style.overflowY = 'auto';
        } else {
            modalBody.style.maxHeight = '';
            modalBody.style.overflowY = '';
        }
    }
}

// Add navigation cleanup
document.addEventListener('beforeunload', function() {
    closeAllModals();
});

// Close modals when navigating
window.addEventListener('popstate', function() {
    closeAllModals();
});

// Make functions globally available
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;
window.openModal = openModal;
window.closeModal = closeModal;
window.showBootstrapModal = showBootstrapModal;
window.hideBootstrapModal = hideBootstrapModal;
window.validateModalForm = validateModalForm;
window.autoResizeModal = autoResizeModal;
window.closeAllModals = closeAllModals;
