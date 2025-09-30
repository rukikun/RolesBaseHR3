// Common HR System JavaScript Functions

// API Base URL
const API_BASE_URL = '../api';

// Common utility functions
class HRUtils {
    static formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString();
    }

    static formatTime(timeStr) {
        if (!timeStr) return "--:--";
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }

    static calculateHours(clockIn, clockOut) {
        if (!clockIn || !clockOut) return "-";

        const [inH, inM] = clockIn.split(':').map(Number);
        const [outH, outM] = clockOut.split(':').map(Number);

        let hours = outH - inH;
        let minutes = outM - inM;

        if (minutes < 0) {
            hours--;
            minutes += 60;
        }

        if (hours < 0) hours += 24;

        return `${hours}h ${minutes}m`;
    }

    static formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    static getStatusBadge(status) {
        const statusClasses = {
            'pending': 'bg-warning',
            'approved': 'bg-success',
            'rejected': 'bg-danger',
            'paid': 'bg-primary'
        };
        
        const className = statusClasses[status] || 'bg-secondary';
        return `<span class="badge ${className}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    }

    static showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show notification`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    static showLoading(element) {
        element.innerHTML = '<div class="text-center"><div class="loading-spinner"></div></div>';
    }

    static hideLoading(element, content = '') {
        element.innerHTML = content;
    }
}

// API Service Class
class APIService {
    static async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}/${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            HRUtils.showNotification(error.message, 'danger');
            throw error;
        }
    }

    static async get(endpoint) {
        return this.request(endpoint);
    }

    static async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }

    static async uploadFile(endpoint, formData) {
        return this.request(endpoint, {
            method: 'POST',
            body: formData,
            headers: {} // Let browser set Content-Type for FormData
        });
    }
}

// Sidebar Management
class SidebarManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.restoreState();
    }

    setupEventListeners() {
        const menuBtn = document.getElementById('menu-btn');
        const desktopToggle = document.getElementById('desktop-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('main-content');

        // Mobile sidebar toggle
        if (menuBtn && sidebar && overlay) {
            menuBtn.addEventListener('click', (e) => {
                e.preventDefault();
                sidebar.classList.toggle('active');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            });
        }

        // Desktop sidebar toggle
        if (desktopToggle && sidebar && mainContent) {
            desktopToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const isCollapsed = sidebar.classList.contains('collapsed');
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', !isCollapsed);

                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            });
        }

        // Close mobile sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            });
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    restoreState() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        
        if (savedState === 'true' && sidebar && mainContent) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    }
}

// Form Validation
class FormValidator {
    static validateRequired(fields) {
        const errors = [];
        
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element || !element.value.trim()) {
                errors.push(field.message || `${field.name} is required`);
                this.addErrorClass(element);
            } else {
                this.removeErrorClass(element);
            }
        });
        
        return errors;
    }

    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static validateDate(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return start <= end;
    }

    static addErrorClass(element) {
        if (element) {
            element.classList.add('is-invalid');
        }
    }

    static removeErrorClass(element) {
        if (element) {
            element.classList.remove('is-invalid');
        }
    }

    static showErrors(errors) {
        if (errors.length > 0) {
            HRUtils.showNotification(errors.join('<br>'), 'danger');
            return false;
        }
        return true;
    }
}

// Data Table Manager
class DataTableManager {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.tbody = this.table?.querySelector('tbody');
        this.options = {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 10,
            ...options
        };
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
    }

    setData(data) {
        this.data = data;
        this.filteredData = [...data];
        this.render();
    }

    render() {
        if (!this.tbody) return;

        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = startIndex + this.options.pageSize;
        const pageData = this.filteredData.slice(startIndex, endIndex);

        this.tbody.innerHTML = '';
        
        if (pageData.length === 0) {
            this.tbody.innerHTML = '<tr><td colspan="100%" class="text-center">No data available</td></tr>';
            return;
        }

        pageData.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = this.options.renderRow(row);
            this.tbody.appendChild(tr);
        });

        if (this.options.pagination) {
            this.renderPagination();
        }
    }

    filter(searchTerm) {
        if (!searchTerm) {
            this.filteredData = [...this.data];
        } else {
            this.filteredData = this.data.filter(row => 
                Object.values(row).some(value => 
                    value.toString().toLowerCase().includes(searchTerm.toLowerCase())
                )
            );
        }
        this.currentPage = 1;
        this.render();
    }

    sort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }

        this.filteredData.sort((a, b) => {
            const aValue = a[column];
            const bValue = b[column];
            
            if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
            if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        });

        this.render();
    }

    renderPagination() {
        // Implementation for pagination controls
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        // Add pagination HTML here if needed
    }
}

// Initialize common functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar
    new SidebarManager();
    
    // Set current year in footer if exists
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Export for use in other modules
window.HRUtils = HRUtils;
window.APIService = APIService;
window.FormValidator = FormValidator;
window.DataTableManager = DataTableManager;
