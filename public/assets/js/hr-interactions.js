/**
 * HR System Interactive Enhancements
 * Advanced JavaScript for professional user interactions
 */

class HRInteractions {
    constructor() {
        this.init();
    }

    init() {
        this.initializeAnimations();
        this.initializeTooltips();
        this.initializeCounters();
        this.initializeParallax();
        this.initializeProgressBars();
        this.initializeNotifications();
        this.initializeFormEnhancements();
        this.initializeTableEnhancements();
        this.initializeModalEnhancements();
        this.initializeThemeToggle();
    }

    // Smooth animations for page elements
    initializeAnimations() {
        // Fade in elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.card, .dashboard-section, .stat-card').forEach(el => {
            el.classList.add('animate-on-scroll');
            observer.observe(el);
        });

        // Add stagger animation to stat cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Enhanced tooltips
    initializeTooltips() {
        // Initialize Bootstrap tooltips with custom styling
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                customClass: 'hr-tooltip',
                delay: { show: 300, hide: 100 }
            });
        });

        // Add dynamic tooltips to buttons without titles
        document.querySelectorAll('.btn:not([title]):not([data-bs-toggle])').forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                const action = this.getActionFromIcon(icon.className);
                if (action) {
                    btn.setAttribute('title', action);
                    new bootstrap.Tooltip(btn, {
                        customClass: 'hr-tooltip'
                    });
                }
            }
        });
    }

    // Animated counters for statistics
    initializeCounters() {
        const counters = document.querySelectorAll('[id$="-claims"], [id$="-timesheets"], [id$="-employees"], [id$="-shifts"]');
        
        const animateCounter = (element, target) => {
            const duration = 2000;
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 16);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.textContent) || Math.floor(Math.random() * 100) + 10;
                    animateCounter(entry.target, target);
                    observer.unobserve(entry.target);
                }
            });
        });

        counters.forEach(counter => observer.observe(counter));
    }

    // Subtle parallax effects
    initializeParallax() {
        let ticking = false;

        const updateParallax = () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.dashboard-logo, .page-header-container::before');
            
            parallaxElements.forEach(element => {
                const speed = 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });
            
            ticking = false;
        };

        const requestTick = () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        };

        window.addEventListener('scroll', requestTick, { passive: true });
    }

    // Animated progress bars
    initializeProgressBars() {
        const createProgressBar = (container, percentage, color = 'var(--jetlouge-primary)') => {
            const progressBar = document.createElement('div');
            progressBar.className = 'progress-bar-enhanced';
            progressBar.innerHTML = `
                <div class="progress-track">
                    <div class="progress-fill" style="--progress-color: ${color}; --progress-width: ${percentage}%"></div>
                </div>
                <span class="progress-text">${percentage}%</span>
            `;
            container.appendChild(progressBar);
            
            // Animate on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.querySelector('.progress-fill').style.width = `${percentage}%`;
                        observer.unobserve(entry.target);
                    }
                });
            });
            observer.observe(progressBar);
        };

        // Add progress bars to relevant sections
        document.querySelectorAll('.leave-balance, .attendance-rate').forEach(container => {
            const percentage = Math.floor(Math.random() * 100) + 1;
            createProgressBar(container, percentage);
        });
    }

    // Enhanced notifications
    initializeNotifications() {
        this.notificationQueue = [];
        this.maxNotifications = 3;

        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        const id = 'notification-' + Date.now();
        
        notification.id = id;
        notification.className = `alert alert-${type} notification-enhanced alert-dismissible fade show`;
        notification.style.minWidth = '350px';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${this.getNotificationIcon(type)} me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Add to queue and manage display
        this.notificationQueue.push(notification);
        if (this.notificationQueue.length > this.maxNotifications) {
            const oldest = this.notificationQueue.shift();
            oldest.remove();
        }

        container.appendChild(notification);

        // Auto-remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 150);
            }
        }, duration);

        // Add sound effect (optional)
        this.playNotificationSound(type);
    }

    // Form enhancements
    initializeFormEnhancements() {
        // Floating labels
        document.querySelectorAll('.form-floating input, .form-floating select').forEach(input => {
            const updateLabel = () => {
                const label = input.nextElementSibling;
                if (label && label.tagName === 'LABEL') {
                    if (input.value || input === document.activeElement) {
                        label.classList.add('active');
                    } else {
                        label.classList.remove('active');
                    }
                }
            };

            input.addEventListener('focus', updateLabel);
            input.addEventListener('blur', updateLabel);
            input.addEventListener('input', updateLabel);
            updateLabel(); // Initial state
        });

        // Real-time validation
        document.querySelectorAll('input[required], select[required]').forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });

        // Password strength indicator
        document.querySelectorAll('input[type="password"]').forEach(input => {
            this.addPasswordStrengthIndicator(input);
        });
    }

    // Table enhancements
    initializeTableEnhancements() {
        // Sortable headers
        document.querySelectorAll('.table-enhanced th').forEach(header => {
            if (!header.classList.contains('no-sort')) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => {
                    this.sortTable(header);
                });
            }
        });

        // Row selection
        document.querySelectorAll('.table-enhanced tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.type !== 'checkbox' && e.target.tagName !== 'BUTTON') {
                    row.classList.toggle('selected');
                }
            });
        });

        // Search functionality
        document.querySelectorAll('.table-search').forEach(searchInput => {
            searchInput.addEventListener('input', (e) => {
                this.filterTable(e.target);
            });
        });
    }

    // Modal enhancements
    initializeModalEnhancements() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', () => {
                modal.classList.add('modal-show-animation');
            });

            modal.addEventListener('hidden.bs.modal', () => {
                modal.classList.remove('modal-show-animation');
                // Reset form if present
                const form = modal.querySelector('form');
                if (form) form.reset();
            });
        });
    }

    // Theme toggle functionality
    initializeThemeToggle() {
        const themeToggle = document.createElement('button');
        themeToggle.className = 'btn btn-outline-light btn-sm theme-toggle';
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        themeToggle.title = 'Toggle Dark Mode';
        
        const navbar = document.querySelector('.navbar .container-fluid');
        if (navbar) {
            navbar.appendChild(themeToggle);
        }

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            const icon = themeToggle.querySelector('i');
            icon.className = document.body.classList.contains('dark-theme') 
                ? 'fas fa-sun' 
                : 'fas fa-moon';
        });
    }

    // Utility methods
    getActionFromIcon(iconClass) {
        const iconMap = {
            'fa-edit': 'Edit',
            'fa-trash': 'Delete',
            'fa-eye': 'View',
            'fa-download': 'Download',
            'fa-upload': 'Upload',
            'fa-plus': 'Add',
            'fa-check': 'Approve',
            'fa-times': 'Reject',
            'fa-save': 'Save',
            'fa-print': 'Print'
        };

        for (const [icon, action] of Object.entries(iconMap)) {
            if (iconClass.includes(icon)) return action;
        }
        return null;
    }

    getNotificationIcon(type) {
        const iconMap = {
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'danger': 'fa-times-circle',
            'info': 'fa-info-circle'
        };
        return iconMap[type] || 'fa-bell';
    }

    validateField(field) {
        const isValid = field.checkValidity();
        field.classList.toggle('is-valid', isValid);
        field.classList.toggle('is-invalid', !isValid);
        
        // Show/hide feedback
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!isValid && !feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = field.validationMessage;
            field.parentNode.appendChild(feedback);
        } else if (isValid && feedback) {
            feedback.remove();
        }
    }

    addPasswordStrengthIndicator(input) {
        const indicator = document.createElement('div');
        indicator.className = 'password-strength';
        indicator.innerHTML = `
            <div class="strength-bar">
                <div class="strength-fill"></div>
            </div>
            <span class="strength-text">Password strength</span>
        `;
        input.parentNode.appendChild(indicator);

        input.addEventListener('input', () => {
            const strength = this.calculatePasswordStrength(input.value);
            const fill = indicator.querySelector('.strength-fill');
            const text = indicator.querySelector('.strength-text');
            
            fill.style.width = `${strength.percentage}%`;
            fill.className = `strength-fill strength-${strength.level}`;
            text.textContent = strength.text;
        });
    }

    calculatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score += 25;
        if (/[a-z]/.test(password)) score += 25;
        if (/[A-Z]/.test(password)) score += 25;
        if (/[0-9]/.test(password)) score += 25;
        if (/[^A-Za-z0-9]/.test(password)) score += 25;

        const levels = {
            0: { level: 'weak', text: 'Very weak', percentage: 0 },
            25: { level: 'weak', text: 'Weak', percentage: 25 },
            50: { level: 'medium', text: 'Medium', percentage: 50 },
            75: { level: 'strong', text: 'Strong', percentage: 75 },
            100: { level: 'very-strong', text: 'Very strong', percentage: 100 }
        };

        return levels[Math.min(score, 100)] || levels[0];
    }

    sortTable(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const isAscending = !header.classList.contains('sort-asc');

        rows.sort((a, b) => {
            const aText = a.children[columnIndex].textContent.trim();
            const bText = b.children[columnIndex].textContent.trim();
            
            const aValue = isNaN(aText) ? aText.toLowerCase() : parseFloat(aText);
            const bValue = isNaN(bText) ? bText.toLowerCase() : parseFloat(bText);
            
            return isAscending ? 
                (aValue > bValue ? 1 : -1) : 
                (aValue < bValue ? 1 : -1);
        });

        // Update header classes
        header.parentNode.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }

    filterTable(searchInput) {
        const table = searchInput.closest('.card').querySelector('table');
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const searchTerm = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    playNotificationSound(type) {
        // Create audio context for subtle notification sounds
        if (typeof AudioContext !== 'undefined') {
            const audioContext = new AudioContext();
            const frequencies = {
                'success': 800,
                'info': 600,
                'warning': 400,
                'danger': 300
            };
            
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(frequencies[type] || 600, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        }
    }
}

// Additional CSS animations
const additionalStyles = `
    .animate-on-scroll {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .animate-fade-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }

    .progress-bar-enhanced {
        margin: 1rem 0;
    }

    .progress-track {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }

    .progress-fill {
        height: 100%;
        background: var(--progress-color);
        width: 0;
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .hr-tooltip {
        --bs-tooltip-bg: var(--jetlouge-primary);
        --bs-tooltip-color: white;
        font-size: 0.875rem;
    }

    .password-strength {
        margin-top: 0.5rem;
    }

    .strength-bar {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }

    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
    }

    .strength-weak { background: #ef4444; }
    .strength-medium { background: #f59e0b; }
    .strength-strong { background: #10b981; }
    .strength-very-strong { background: #059669; }

    .strength-text {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .table-enhanced th.sort-asc::after {
        content: ' ↑';
        color: var(--jetlouge-primary);
    }

    .table-enhanced th.sort-desc::after {
        content: ' ↓';
        color: var(--jetlouge-primary);
    }

    .table-enhanced tbody tr.selected {
        background: rgba(30, 58, 138, 0.1) !important;
    }

    .modal-show-animation .modal-dialog {
        animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px) scale(0.95);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .theme-toggle {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .dark-theme {
        --jetlouge-light: #1e293b;
        background-color: #0f172a !important;
        color: #f1f5f9;
    }

    .dark-theme .card {
        background: linear-gradient(145deg, #1e293b 0%, #334155 100%) !important;
        border-color: #475569;
    }

    .dark-theme .navbar {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    }
`;

// Inject additional styles
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.hrInteractions = new HRInteractions();
});

// Export for global access
window.HRInteractions = HRInteractions;
