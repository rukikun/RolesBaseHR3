/**
 * HR Database Integration JavaScript
 * Ensures all HR modules properly handle database CRUD operations
 */

// Global configuration
const HR_CONFIG = {
    baseUrl: '/api',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    defaultHeaders: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
};

// Add CSRF token to headers if available
if (HR_CONFIG.csrfToken) {
    HR_CONFIG.defaultHeaders['X-CSRF-TOKEN'] = HR_CONFIG.csrfToken;
}

/**
 * Enhanced API utility functions
 */
const HRApi = {
    // Generic API request handler
    async request(method, url, data = null) {
        const config = {
            method: method.toUpperCase(),
            headers: { ...HR_CONFIG.defaultHeaders }
        };

        if (data && method.toUpperCase() !== 'GET') {
            if (data instanceof FormData) {
                // Remove Content-Type for FormData (browser sets it with boundary)
                delete config.headers['Content-Type'];
                config.body = data;
            } else {
                config.body = JSON.stringify(data);
            }
        }

        try {
            const response = await fetch(`${HR_CONFIG.baseUrl}${url}`, config);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || `HTTP ${response.status}`);
            }
            
            return result;
        } catch (error) {
            console.error('API Request Error:', error);
            throw error;
        }
    },

    // Convenience methods
    get(url) { return this.request('GET', url); },
    post(url, data) { return this.request('POST', url, data); },
    put(url, data) { return this.request('PUT', url, data); },
    delete(url) { return this.request('DELETE', url); }
};

/**
 * Notification system
 */
const HRNotifications = {
    show(message, type = 'info', duration = 5000) {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;

        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${this.getIcon(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        alertContainer.insertAdjacentHTML('beforeend', alertHtml);

        // Auto-dismiss after duration
        if (duration > 0) {
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, duration);
        }
    },

    getIcon(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'danger'); },
    warning(message) { this.show(message, 'warning'); },
    info(message) { this.show(message, 'info'); }
};

/**
 * Timesheet Management Functions
 */
const TimesheetManager = {
    async loadTimesheets(filters = {}) {
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const url = `/timesheets${queryParams ? '?' + queryParams : ''}`;
            const response = await HRApi.get(url);
            
            if (response.success) {
                this.displayTimesheets(response.data);
                this.updateStats();
            }
        } catch (error) {
            HRNotifications.error('Failed to load timesheets: ' + error.message);
        }
    },

    displayTimesheets(timesheets) {
        const tbody = document.querySelector('#timesheets-table tbody');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        if (timesheets.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No timesheets found</td></tr>';
            return;
        }

        timesheets.forEach(timesheet => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="checkbox" class="form-check-input timesheet-checkbox" value="${timesheet.id}">
                </td>
                <td>${timesheet.id}</td>
                <td>${timesheet.employee_name || 'Unknown'}</td>
                <td>${timesheet.work_date}</td>
                <td>${timesheet.hours_worked}</td>
                <td>${timesheet.overtime_hours || 0}</td>
                <td>
                    <span class="badge bg-${this.getStatusColor(timesheet.status)}">${timesheet.status}</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="TimesheetManager.viewTimesheet(${timesheet.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="TimesheetManager.editTimesheet(${timesheet.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${timesheet.status === 'pending' ? `
                            <button class="btn btn-outline-success" onclick="TimesheetManager.approveTimesheet(${timesheet.id})">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="TimesheetManager.rejectTimesheet(${timesheet.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button class="btn btn-outline-danger" onclick="TimesheetManager.deleteTimesheet(${timesheet.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    },

    getStatusColor(status) {
        const colors = {
            pending: 'warning',
            approved: 'success',
            rejected: 'danger'
        };
        return colors[status] || 'secondary';
    },

    async createTimesheet(formData) {
        try {
            const response = await HRApi.post('/timesheets', formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadTimesheets();
                this.closeModal('add-timesheet-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to create timesheet: ' + error.message);
        }
    },

    async updateTimesheet(id, formData) {
        try {
            const response = await HRApi.put(`/timesheets/${id}`, formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadTimesheets();
                this.closeModal('edit-timesheet-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to update timesheet: ' + error.message);
        }
    },

    async deleteTimesheet(id) {
        if (!confirm('Are you sure you want to delete this timesheet?')) return;
        
        try {
            const response = await HRApi.delete(`/timesheets/${id}`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadTimesheets();
            }
        } catch (error) {
            HRNotifications.error('Failed to delete timesheet: ' + error.message);
        }
    },

    async approveTimesheet(id) {
        try {
            const response = await HRApi.post(`/timesheets/${id}/approve`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadTimesheets();
            }
        } catch (error) {
            HRNotifications.error('Failed to approve timesheet: ' + error.message);
        }
    },

    async rejectTimesheet(id) {
        try {
            const response = await HRApi.post(`/timesheets/${id}/reject`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadTimesheets();
            }
        } catch (error) {
            HRNotifications.error('Failed to reject timesheet: ' + error.message);
        }
    },

    async updateStats() {
        try {
            const response = await HRApi.get('/timesheets/stats');
            if (response.success) {
                const stats = response.data;
                document.getElementById('total-timesheets').textContent = stats.total;
                document.getElementById('pending-timesheets').textContent = stats.pending;
                document.getElementById('approved-timesheets').textContent = stats.approved;
                document.getElementById('total-hours').textContent = stats.total_hours.toFixed(1);
            }
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }
};

/**
 * Employee Management Functions
 */
const EmployeeManager = {
    async loadEmployees(filters = {}) {
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const url = `/employees${queryParams ? '?' + queryParams : ''}`;
            const response = await HRApi.get(url);
            
            if (response.success) {
                this.displayEmployees(response.data);
            }
        } catch (error) {
            HRNotifications.error('Failed to load employees: ' + error.message);
        }
    },

    loadEmployees: function(selectElement) {
        this.apiCall('/api/timesheets/employees', 'GET')
            .then(response => {
                if (response.success && response.data) {
                    selectElement.innerHTML = '<option value="">Select Employee</option>';
                    response.data.forEach(employee => {
                        const option = document.createElement('option');
                        option.value = employee.id;
                        option.textContent = employee.name + (employee.employee_number ? ` (${employee.employee_number})` : '');
                        selectElement.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading employees:', error);
                this.showNotification('Failed to load employees', 'error');
            });
    },

    displayEmployees(employees) {
        const tbody = document.querySelector('#employees-table tbody');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        if (employees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No employees found</td></tr>';
            return;
        }

        employees.forEach(employee => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${employee.id}</td>
                <td>${employee.first_name} ${employee.last_name}</td>
                <td>${employee.email}</td>
                <td>${employee.position}</td>
                <td>${employee.department}</td>
                <td>${employee.hire_date}</td>
                <td>
                    <span class="badge bg-${this.getStatusColor(employee.status)}">${employee.status}</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="EmployeeManager.viewEmployee(${employee.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="EmployeeManager.editEmployee(${employee.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="EmployeeManager.deleteEmployee(${employee.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    },

    getStatusColor(status) {
        const colors = {
            active: 'success',
            inactive: 'warning',
            terminated: 'danger'
        };
        return colors[status] || 'secondary';
    },

    async createEmployee(formData) {
        try {
            const response = await HRApi.post('/employees', formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadEmployees();
                this.closeModal('add-employee-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to create employee: ' + error.message);
        }
    },

    async updateEmployee(id, formData) {
        try {
            const response = await HRApi.put(`/employees/${id}`, formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadEmployees();
                this.closeModal('edit-employee-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to update employee: ' + error.message);
        }
    },

    async deleteEmployee(id) {
        if (!confirm('Are you sure you want to delete this employee?')) return;
        
        try {
            const response = await HRApi.delete(`/employees/${id}`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadEmployees();
            }
        } catch (error) {
            HRNotifications.error('Failed to delete employee: ' + error.message);
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }
};

/**
 * Leave Management Functions
 */
const LeaveManager = {
    async loadLeaveRequests(filters = {}) {
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const url = `/leave/requests${queryParams ? '?' + queryParams : ''}`;
            const response = await HRApi.get(url);
            
            if (response.success) {
                this.displayLeaveRequests(response.data);
            }
        } catch (error) {
            HRNotifications.error('Failed to load leave requests: ' + error.message);
        }
    },

    async createLeaveRequest(formData) {
        try {
            const response = await HRApi.post('/leave/requests', formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadLeaveRequests();
                this.closeModal('add-leave-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to create leave request: ' + error.message);
        }
    },

    async deleteLeaveRequest(id) {
        if (!confirm('Are you sure you want to delete this leave request?')) return;
        
        try {
            const response = await HRApi.delete(`/leave/requests/${id}`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadLeaveRequests();
            }
        } catch (error) {
            HRNotifications.error('Failed to delete leave request: ' + error.message);
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }
};

/**
 * Claims Management Functions
 */
const ClaimManager = {
    async loadClaims(filters = {}) {
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const url = `/claims${queryParams ? '?' + queryParams : ''}`;
            const response = await HRApi.get(url);
            
            if (response.success) {
                this.displayClaims(response.data);
            }
        } catch (error) {
            HRNotifications.error('Failed to load claims: ' + error.message);
        }
    },

    async createClaim(formData) {
        try {
            const response = await HRApi.post('/claims', formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadClaims();
                this.closeModal('add-claim-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to create claim: ' + error.message);
        }
    },

    async deleteClaim(id) {
        if (!confirm('Are you sure you want to delete this claim?')) return;
        
        try {
            const response = await HRApi.delete(`/claims/${id}`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadClaims();
            }
        } catch (error) {
            HRNotifications.error('Failed to delete claim: ' + error.message);
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }
};

/**
 * Shift Management Functions
 */
const ShiftManager = {
    async loadShifts(filters = {}) {
        try {
            const queryParams = new URLSearchParams(filters).toString();
            const url = `/shifts/schedule${queryParams ? '?' + queryParams : ''}`;
            const response = await HRApi.get(url);
            
            if (response.success) {
                this.displayShifts(response.data);
            }
        } catch (error) {
            HRNotifications.error('Failed to load shifts: ' + error.message);
        }
    },

    async createShift(formData) {
        try {
            const response = await HRApi.post('/shifts/create', formData);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadShifts();
                this.closeModal('create-shift-modal');
            }
        } catch (error) {
            HRNotifications.error('Failed to create shift: ' + error.message);
        }
    },

    async deleteShift(id) {
        if (!confirm('Are you sure you want to delete this shift?')) return;
        
        try {
            const response = await HRApi.delete(`/shifts/${id}`);
            if (response.success) {
                HRNotifications.success(response.message);
                this.loadShifts();
            }
        } catch (error) {
            HRNotifications.error('Failed to delete shift: ' + error.message);
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }
};

/**
 * Form handling utilities
 */
const FormHandler = {
    serializeForm(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    },

    setupFormSubmission(formId, submitHandler) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = this.serializeForm(form);
            await submitHandler(formData);
        });
    }
};

/**
 * Initialize all HR modules when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize based on current page
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('timesheet')) {
        TimesheetManager.loadTimesheets();
        TimesheetManager.updateStats();
        
        // Setup form handlers
        FormHandler.setupFormSubmission('add-timesheet-form', (data) => TimesheetManager.createTimesheet(data));
        FormHandler.setupFormSubmission('edit-timesheet-form', (data) => {
            const id = document.getElementById('edit-timesheet-id').value;
            return TimesheetManager.updateTimesheet(id, data);
        });
    }
    
    if (currentPath.includes('employee')) {
        EmployeeManager.loadEmployees();
        
        // Setup form handlers
        FormHandler.setupFormSubmission('add-employee-form', (data) => EmployeeManager.createEmployee(data));
        FormHandler.setupFormSubmission('edit-employee-form', (data) => {
            const id = document.getElementById('edit-employee-id').value;
            return EmployeeManager.updateEmployee(id, data);
        });
    }
    
    if (currentPath.includes('leave')) {
        LeaveManager.loadLeaveRequests();
        
        // Setup form handlers
        FormHandler.setupFormSubmission('add-leave-form', (data) => LeaveManager.createLeaveRequest(data));
    }
    
    if (currentPath.includes('claim')) {
        ClaimManager.loadClaims();
        
        // Setup form handlers
        FormHandler.setupFormSubmission('add-claim-form', (data) => ClaimManager.createClaim(data));
    }
    
    if (currentPath.includes('shift')) {
        ShiftManager.loadShifts();
        
        // Setup form handlers
        FormHandler.setupFormSubmission('create-shift-form', (data) => ShiftManager.createShift(data));
    }
});

// Export for global access
window.HRApi = HRApi;
window.HRNotifications = HRNotifications;
window.TimesheetManager = TimesheetManager;
window.EmployeeManager = EmployeeManager;
window.LeaveManager = LeaveManager;
window.ClaimManager = ClaimManager;
window.ShiftManager = ShiftManager;
