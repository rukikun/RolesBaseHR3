/**
 * Employee Management JavaScript
 * Handles employee CRUD operations and timesheet integration
 */

class EmployeeManager {
    constructor() {
        this.hrApi = new HRApi();
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadEmployees();
    }

    bindEvents() {
        // Add employee form submission
        const employeeForm = document.getElementById('employee-form');
        if (employeeForm) {
            employeeForm.addEventListener('submit', (e) => this.handleEmployeeSubmit(e));
        }

        // Search functionality
        const searchInput = document.getElementById('employee-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }

        // Department filter
        const deptFilter = document.getElementById('department-filter');
        if (deptFilter) {
            deptFilter.addEventListener('change', (e) => this.handleDepartmentFilter(e.target.value));
        }

        // Status filter
        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => this.handleStatusFilter(e.target.value));
        }
    }

    async loadEmployees(filters = {}) {
        try {
            HRUIHelper.showLoading('employees-table-body');
            
            const response = await this.hrApi.getEmployees(filters);
            
            if (response.success) {
                this.renderEmployeesTable(response.data);
                this.updateEmployeeStats(response.data);
            } else {
                HRUIHelper.showAlert('Error loading employees: ' + response.message, 'danger');
            }
        } catch (error) {
            console.error('Error loading employees:', error);
            HRUIHelper.showAlert('Failed to load employees', 'danger');
        } finally {
            HRUIHelper.hideLoading('employees-table-body');
        }
    }

    renderEmployeesTable(employees) {
        const tbody = document.getElementById('employees-table-body');
        if (!tbody) return;

        if (employees.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                        No employees found
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = employees.map(employee => `
            <tr>
                <td>#${String(employee.id).padStart(4, '0')}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" 
                             alt="${employee.first_name} ${employee.last_name}" class="rounded-circle me-2" width="40" height="40">
                        ${employee.first_name} ${employee.last_name}
                    </div>
                </td>
                <td>${employee.email}</td>
                <td>${employee.position}</td>
                <td><span class="badge bg-secondary">${employee.department}</span></td>
                <td>${employee.phone}</td>
                <td>${HRUIHelper.formatDate(employee.hire_date)}</td>
                <td>
                    <span class="badge bg-${employee.status === 'active' ? 'success' : 'secondary'}">
                        ${employee.status.charAt(0).toUpperCase() + employee.status.slice(1)}
                    </span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="employeeManager.viewEmployee(${employee.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="employeeManager.viewTimesheets(${employee.id})" title="Timesheets">
                            <i class="fas fa-clock"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="employeeManager.editEmployee(${employee.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="employeeManager.deleteEmployee(${employee.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updateEmployeeStats(employees) {
        // Update stat cards
        const totalCount = document.getElementById('total-employees-count');
        const activeCount = document.getElementById('active-employees-count');
        const deptCount = document.getElementById('departments-count');
        const newCount = document.getElementById('new-employees-count');

        if (totalCount) totalCount.textContent = employees.length;
        if (activeCount) activeCount.textContent = employees.filter(e => e.status === 'active').length;
        if (deptCount) deptCount.textContent = [...new Set(employees.map(e => e.department))].length;
        
        // New employees this year
        const currentYear = new Date().getFullYear();
        const newThisYear = employees.filter(e => new Date(e.hire_date).getFullYear() === currentYear).length;
        if (newCount) newCount.textContent = newThisYear;
    }

    async handleEmployeeSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const employeeData = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            position: formData.get('position'),
            department: formData.get('department'),
            hire_date: formData.get('hire_date'),
            salary: formData.get('salary'),
            status: formData.get('status') || 'active'
        };

        const employeeId = formData.get('employee_id');
        
        try {
            let response;
            if (employeeId) {
                response = await this.hrApi.updateEmployee(employeeId, employeeData);
            } else {
                response = await this.hrApi.createEmployee(employeeData);
            }

            if (response.success) {
                HRUIHelper.showAlert(employeeId ? 'Employee updated successfully!' : 'Employee created successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('employee-modal')).hide();
                this.loadEmployees();
            } else {
                HRUIHelper.showAlert('Error: ' + response.message, 'danger');
            }
        } catch (error) {
            console.error('Error saving employee:', error);
            HRUIHelper.showAlert('Failed to save employee', 'danger');
        }
    }

    async viewEmployee(employeeId) {
        try {
            const response = await this.hrApi.getEmployee(employeeId);
            if (response.success) {
                this.showEmployeeModal(response.data);
            }
        } catch (error) {
            console.error('Error loading employee:', error);
            HRUIHelper.showAlert('Failed to load employee details', 'danger');
        }
    }

    async viewTimesheets(employeeId) {
        try {
            // Get employee details first
            const empResponse = await this.hrApi.getEmployee(employeeId);
            if (!empResponse.success) {
                HRUIHelper.showAlert('Failed to load employee details', 'danger');
                return;
            }

            // Get employee timesheets
            const timesheetResponse = await this.hrApi.getTimesheets({ employee_id: employeeId });
            
            if (timesheetResponse.success) {
                this.showEmployeeTimesheets(empResponse.data, timesheetResponse.data);
            } else {
                HRUIHelper.showAlert('Failed to load timesheets', 'danger');
            }
        } catch (error) {
            console.error('Error loading timesheets:', error);
            HRUIHelper.showAlert('Failed to load employee timesheets', 'danger');
        }
    }

    showEmployeeTimesheets(employee, timesheets) {
        const modalHtml = `
            <div class="modal fade" id="employee-timesheets-modal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-clock me-2"></i>
                                Timesheets - ${employee.first_name} ${employee.last_name}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>${timesheets.length}</h4>
                                            <small>Total Entries</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>${timesheets.filter(t => t.status === 'approved').length}</h4>
                                            <small>Approved</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>${timesheets.filter(t => t.status === 'pending').length}</h4>
                                            <small>Pending</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>${timesheets.reduce((sum, t) => sum + parseFloat(t.hours_worked || 0), 0).toFixed(1)}</h4>
                                            <small>Total Hours</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Clock In</th>
                                            <th>Clock Out</th>
                                            <th>Hours</th>
                                            <th>Overtime</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${timesheets.length > 0 ? timesheets.map(timesheet => `
                                            <tr>
                                                <td>${HRUIHelper.formatDate(timesheet.work_date)}</td>
                                                <td>${timesheet.clock_in_time || '-'}</td>
                                                <td>${timesheet.clock_out_time || '-'}</td>
                                                <td>${timesheet.hours_worked || 0}h</td>
                                                <td>${timesheet.overtime_hours || 0}h</td>
                                                <td>
                                                    <span class="badge bg-${timesheet.status === 'approved' ? 'success' : timesheet.status === 'pending' ? 'warning' : 'danger'}">
                                                        ${timesheet.status.charAt(0).toUpperCase() + timesheet.status.slice(1)}
                                                    </span>
                                                </td>
                                                <td>${timesheet.description || '-'}</td>
                                            </tr>
                                        `).join('') : `
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No timesheet entries found for this employee
                                                </td>
                                            </tr>
                                        `}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="window.location.href='/timesheet-management'">
                                <i class="fas fa-plus me-1"></i>Add Timesheet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('employee-timesheets-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('employee-timesheets-modal'));
        modal.show();

        // Clean up when modal is hidden
        document.getElementById('employee-timesheets-modal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    showEmployeeModal(employee) {
        // Implementation for showing employee details modal
        // This would populate the existing employee view modal
        console.log('Show employee modal:', employee);
    }

    editEmployee(employeeId) {
        // Implementation for editing employee
        console.log('Edit employee:', employeeId);
    }

    async deleteEmployee(employeeId) {
        if (!confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await this.hrApi.deleteEmployee(employeeId);
            if (response.success) {
                HRUIHelper.showAlert('Employee deleted successfully!', 'success');
                this.loadEmployees();
            } else {
                HRUIHelper.showAlert('Error: ' + response.message, 'danger');
            }
        } catch (error) {
            console.error('Error deleting employee:', error);
            HRUIHelper.showAlert('Failed to delete employee', 'danger');
        }
    }

    handleSearch(searchTerm) {
        this.loadEmployees({ search: searchTerm });
    }

    handleDepartmentFilter(department) {
        this.loadEmployees({ department: department });
    }

    handleStatusFilter(status) {
        this.loadEmployees({ status: status });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('employees-table-body')) {
        window.employeeManager = new EmployeeManager();
    }
});
