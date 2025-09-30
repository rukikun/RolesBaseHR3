// NUCLEAR FORCE INTERACTIVE - Ultimate solution for blocking elements

(function() {
    'use strict';
    
    // ULTIMATE NUCLEAR CLEANUP FUNCTION
    function ultimateForceInteractive() {
        console.log('ULTIMATE FORCE: Starting nuclear cleanup...');
        
        // STEP 1: Remove ALL possible blocking elements
        const blockingSelectors = [
            '.modal-backdrop', '.backdrop', '[class*="backdrop"]',
            '.overlay', '[class*="overlay"]', '.modal-open',
            '.fade.show', '.show', '.in',
            'div[style*="position: fixed"]',
            'div[style*="position: absolute"]',
            'div[style*="z-index"]',
            '[style*="pointer-events: none"]',
            '[style*="display: block"]',
            '.blockUI', '.ui-widget-overlay',
            '.loading-overlay', '.spinner-overlay'
        ];
        
        blockingSelectors.forEach(selector => {
            try {
                document.querySelectorAll(selector).forEach(element => {
                    // Keep mobile sidebar overlay but remove everything else
                    if (element.id !== 'overlay' && element.id !== 'mobileOverlay') {
                        element.remove();
                        console.log('REMOVED:', selector, element);
                    }
                });
            } catch(e) {
                console.log('Error removing:', selector, e);
            }
        });
        
        // STEP 2: NUCLEAR reset of body and html
        const htmlElement = document.documentElement;
        const bodyElement = document.body;
        
        // Reset ALL body classes
        bodyElement.className = bodyElement.className.replace(/modal-open|no-scroll|overflow-hidden/g, '');
        
        // FORCE body styles
        bodyElement.style.cssText = 'overflow: visible !important; overflow-x: auto !important; overflow-y: auto !important; padding: 0 !important; margin: 0 !important; position: static !important; pointer-events: auto !important; z-index: auto !important;';
        
        // FORCE html styles
        htmlElement.style.cssText = 'overflow: visible !important; overflow-x: auto !important; overflow-y: auto !important; position: static !important; pointer-events: auto !important;';
        
        // STEP 3: Hide and disable ALL modals
        document.querySelectorAll('.modal, [class*="modal"]').forEach(modal => {
            modal.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important; z-index: -9999 !important;';
            modal.classList.remove('show', 'fade', 'in', 'active');
            modal.setAttribute('aria-hidden', 'true');
        });
        
        // STEP 4: FORCE ALL elements to be interactive
        document.querySelectorAll('*').forEach(element => {
            // Don't mess with the mobile sidebar overlay
            if (element.id !== 'overlay' && element.id !== 'mobileOverlay') {
                element.style.pointerEvents = 'auto';
                
                // Remove any blocking inline styles
                if (element.style.position === 'fixed' || element.style.position === 'absolute') {
                    if (!element.classList.contains('sidebar') && !element.classList.contains('nav')) {
                        element.style.position = 'relative';
                    }
                }
            }
        });
        
        // STEP 5: Force specific interactive elements
        const interactiveSelectors = [
            'button', 'a', 'input', 'select', 'textarea',
            '.btn', '.nav-link', '.form-control', '.form-select',
            '.table', '.card', '.ess-card', '.stats-card',
            '.badge', '.alert', '.dropdown'
        ];
        
        interactiveSelectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                element.style.cssText += 'pointer-events: auto !important; position: relative !important; z-index: 1 !important;';
            });
        });
        
        console.log('ULTIMATE FORCE: Nuclear cleanup completed');
        return true;
    }
    
    // Make function globally available
    window.ultimateForceInteractive = ultimateForceInteractive;
    window.forceInteractive = ultimateForceInteractive; // Alias
    
    // IMMEDIATE execution
    ultimateForceInteractive();
    
    // Multiple execution strategies
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ultimateForceInteractive);
    }
    
    window.addEventListener('load', ultimateForceInteractive);
    window.addEventListener('focus', ultimateForceInteractive);
    
    // Aggressive timing
    setTimeout(ultimateForceInteractive, 50);
    setTimeout(ultimateForceInteractive, 100);
    setTimeout(ultimateForceInteractive, 250);
    setTimeout(ultimateForceInteractive, 500);
    setTimeout(ultimateForceInteractive, 1000);
    
    // Continuous monitoring - run every 2 seconds
    setInterval(ultimateForceInteractive, 2000);
    
    // Emergency shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+Shift+F for emergency cleanup
        if (e.ctrlKey && e.shiftKey && e.key === 'F') {
            e.preventDefault();
            ultimateForceInteractive();
            alert('EMERGENCY CLEANUP ACTIVATED!');
        }
        
        // Escape key cleanup
        if (e.key === 'Escape') {
            ultimateForceInteractive();
        }
    });
    
    // Click cleanup
    document.addEventListener('click', function() {
        // Quick cleanup on every click
        document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
        document.body.style.overflow = 'auto';
        document.body.style.pointerEvents = 'auto';
    });
    
    // AGGRESSIVE mutation observer
    const observer = new MutationObserver(function(mutations) {
        let needsCleanup = false;
        
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const classList = node.classList || [];
                    const className = node.className || '';
                    
                    if (classList.contains('modal-backdrop') ||
                        classList.contains('backdrop') ||
                        classList.contains('overlay') ||
                        className.includes('backdrop') ||
                        className.includes('overlay')) {
                        
                        if (node.id !== 'overlay' && node.id !== 'mobileOverlay') {
                            node.remove();
                            needsCleanup = true;
                        }
                    }
                }
            });
        });
        
        if (needsCleanup) {
            setTimeout(ultimateForceInteractive, 10);
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    console.log('ULTIMATE FORCE INTERACTIVE: Loaded and monitoring');
    
})();
