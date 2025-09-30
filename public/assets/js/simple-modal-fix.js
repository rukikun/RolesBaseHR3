/**
 * Simple Modal Fix for ESS System
 * Direct implementation to make modals work immediately
 */

// Simple modal functions that work
function openWorkingModal(modalId) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.zIndex = '9999';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        
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

// Make functions globally available
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;

// Auto-setup when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Simple Modal Fix loaded');
    
    // Hide all modals initially
    const modals = document.querySelectorAll('[id*="modal"]');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
    
    // Add click handlers for backdrop closing
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('working-modal') || 
            e.target.classList.contains('modal-backdrop') ||
            e.target.classList.contains('working-modal-backdrop')) {
            const modal = e.target;
            if (modal.id) {
                closeWorkingModal(modal.id);
            }
        }
    });
    
    // Escape key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleModals = document.querySelectorAll('[id*="modal"][style*="flex"]');
            visibleModals.forEach(modal => {
                closeWorkingModal(modal.id);
            });
        }
    });
});

console.log('Simple Modal Fix script loaded successfully!');
