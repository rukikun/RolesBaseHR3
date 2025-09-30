// Modal Fix - Lightweight version that works with universal modal system
document.addEventListener('DOMContentLoaded', function() {
    
    // Ensure all modals start hidden
    function ensureModalsHidden() {
        const allModals = document.querySelectorAll('.modal, .working-modal, .simple-modal');
        allModals.forEach(modal => {
            if (!modal.classList.contains('show') && !modal.style.display.includes('flex')) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Clean up any stray modal artifacts
    function cleanupModalArtifacts() {
        // Remove stray backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            if (!document.querySelector('.modal.show')) {
                backdrop.remove();
            }
        });
        
        // Reset body if no modals are open
        if (!document.querySelector('.modal.show, .working-modal[style*="display: flex"], .simple-modal[style*="display: flex"]')) {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.body.classList.remove('modal-open');
        }
    }
    
    // Force form elements to be interactive in open modals only
    function enableModalElements() {
        const openModals = document.querySelectorAll('.modal.show, .working-modal[style*="display: flex"], .simple-modal[style*="display: flex"]');
        
        openModals.forEach(modal => {
            const formElements = modal.querySelectorAll('input, select, textarea, button, .form-control, .form-select');
            formElements.forEach(el => {
                if (!el.hasAttribute('data-original-disabled')) {
                    el.removeAttribute('disabled');
                    el.removeAttribute('readonly');
                    el.style.pointerEvents = 'auto';
                    el.style.opacity = '1';
                }
            });
        });
    }
    
    // Run cleanup on page load
    ensureModalsHidden();
    cleanupModalArtifacts();
    
    // Periodic cleanup (reduced frequency)
    setInterval(() => {
        cleanupModalArtifacts();
        enableModalElements();
    }, 3000);
    
    // Cleanup on navigation
    window.addEventListener('beforeunload', cleanupModalArtifacts);
});
