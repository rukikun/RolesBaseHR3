/**
 * HR System API JavaScript Library
 * Comprehensive CRUD operations for all HR modules
 */

class HRApi {
    constructor() {
        this.baseUrl = '/api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    // Generic API request method
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    // Employee API methods
    async getEmployees(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/employees?${params}`);
    }

    async createEmployee(employeeData) {
        return this.request('/employees', {
            method: 'POST',
            body: JSON.stringify(employeeData)
        });
    }

    async getEmployee(id) {
        return this.request(`/employees/${id}`);
    }

    async updateEmployee(id, employeeData) {
        return this.request(`/employees/${id}`, {
            method: 'PUT',
            body: JSON.stringify(employeeData)
        });
    }

    async deleteEmployee(id) {
        return this.request(`/employees/${id}`, {
            method: 'DELETE'
        });
    }

    async getEmployeeStats() {
        return this.request('/employees/stats/summary');
    }

    async getDepartments() {
        return this.request('/employees/departments/list');
    }

    // Timesheet API methods
    async getTimesheets(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/timesheets?${params}`);
    }

    async createTimesheet(timesheetData) {
        return this.request('/timesheets', {
            method: 'POST',
            body: JSON.stringify(timesheetData)
        });
    }

    async getTimesheet(id) {
        return this.request(`/timesheets/${id}`);
    }

    async updateTimesheet(id, timesheetData) {
        return this.request(`/timesheets/${id}`, {
            method: 'PUT',
            body: JSON.stringify(timesheetData)
        });
    }

    async deleteTimesheet(id) {
        return this.request(`/timesheets/${id}`, {
            method: 'DELETE'
        });
    }

    async getTimesheetStats() {
        return this.request('/timesheets/stats');
    }

    // Shift API methods
    async getShifts(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/shifts?${params}`);
    }

    async createShift(shiftData) {
        return this.request('/shifts', {
            method: 'POST',
            body: JSON.stringify(shiftData)
        });
    }

    async getShift(id) {
        return this.request(`/shifts/${id}`);
    }

    async updateShift(id, shiftData) {
        return this.request(`/shifts/${id}`, {
            method: 'PUT',
            body: JSON.stringify(shiftData)
        });
    }

    async deleteShift(id) {
        return this.request(`/shifts/${id}`, {
            method: 'DELETE'
        });
    }

    async getShiftStats() {
        return this.request('/shifts/stats');
    }

    async getShiftTypes() {
        return this.request('/shifts/types');
    }

    async getSchedule(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/shifts/schedule?${params}`);
    }

    // Leave API methods
    async getLeaveRequests(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/leave/requests?${params}`);
    }

    async createLeaveRequest(leaveData) {
        return this.request('/leave/requests', {
            method: 'POST',
            body: JSON.stringify(leaveData)
        });
    }

    async getLeaveRequest(id) {
        return this.request(`/leave/requests/${id}`);
    }

    async updateLeaveRequest(id, leaveData) {
        return this.request(`/leave/requests/${id}`, {
            method: 'PUT',
            body: JSON.stringify(leaveData)
        });
    }

    async deleteLeaveRequest(id) {
        return this.request(`/leave/requests/${id}`, {
            method: 'DELETE'
        });
    }

    async approveLeaveRequest(id) {
        return this.request(`/leave/requests/${id}/approve`, {
            method: 'POST'
        });
    }

    async rejectLeaveRequest(id, reason) {
        return this.request(`/leave/requests/${id}/reject`, {
            method: 'POST',
            body: JSON.stringify({ rejection_reason: reason })
        });
    }

    async getLeaveBalances(employeeId = null, year = null) {
        const params = new URLSearchParams();
        if (employeeId) params.append('employee_id', employeeId);
        if (year) params.append('year', year);
        return this.request(`/leave/balances?${params}`);
    }

    // Claims API methods
    async getClaims(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/claims?${params}`);
    }

    async createClaim(claimData) {
        // Handle file upload
        const formData = new FormData();
        Object.keys(claimData).forEach(key => {
            formData.append(key, claimData[key]);
        });

        return this.request('/claims', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
                // Don't set Content-Type for FormData
            },
            body: formData
        });
    }

    async getClaim(id) {
        return this.request(`/claims/${id}`);
    }

    async updateClaim(id, claimData) {
        // Handle file upload
        const formData = new FormData();
        Object.keys(claimData).forEach(key => {
            formData.append(key, claimData[key]);
        });
        formData.append('_method', 'PUT');

        return this.request(`/claims/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
    }

    async deleteClaim(id) {
        return this.request(`/claims/${id}`, {
            method: 'DELETE'
        });
    }

    async approveClaim(id, notes = '') {
        return this.request(`/claims/${id}/approve`, {
            method: 'POST',
            body: JSON.stringify({ notes })
        });
    }

    async rejectClaim(id, reason, notes = '') {
        return this.request(`/claims/${id}/reject`, {
            method: 'POST',
            body: JSON.stringify({ rejection_reason: reason, notes })
        });
    }

    async markClaimPaid(id, notes = '') {
        return this.request(`/claims/${id}/mark-paid`, {
            method: 'POST',
            body: JSON.stringify({ notes })
        });
    }

    // Dashboard API methods
    async getDashboardStats() {
        return this.request('/dashboard/stats');
    }

    async clockIn() {
        return this.request('/dashboard/clock-in', {
            method: 'POST'
        });
    }

    async clockOut() {
        return this.request('/dashboard/clock-out', {
            method: 'POST'
        });
    }

    async getRecentEntries() {
        return this.request('/dashboard/recent-entries');
    }
}

// Utility functions for UI interactions
class HRUIHelper {
    static showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    static showLoading(element) {
        if (element) {
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            element.disabled = true;
        }
    }

    static hideLoading(element, originalText) {
        if (element) {
            element.innerHTML = originalText;
            element.disabled = false;
        }
    }

    static formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    static formatDateTime(dateString) {
        return new Date(dateString).toLocaleString();
    }

    static formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    static getStatusBadge(status) {
        const statusClasses = {
            'pending': 'bg-warning text-dark',
            'approved': 'bg-success',
            'rejected': 'bg-danger',
            'paid': 'bg-info',
            'active': 'bg-success',
            'inactive': 'bg-secondary',
            'terminated': 'bg-danger'
        };
        
        const className = statusClasses[status] || 'bg-secondary';
        return `<span class="badge ${className}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    }

    static confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    static validateForm(formElement) {
        const requiredFields = formElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
}

// Initialize global instances
window.hrApi = new HRApi();
window.hrUI = HRUIHelper;

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { HRApi, HRUIHelper };
}
