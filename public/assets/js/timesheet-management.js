/**
 * Timesheet Management JavaScript
 * Complete CRUD functionality with API integration
 */

class TimesheetManager {
    constructor() {
        this.currentFilters = {};
        this.timesheets = [];
        this.employees = [];
        this.init();
    }

    async init() {
        await this.loadEmployees();
        await this.loadTimesheets();
        await this.loadStats();
        this.setupEventListeners();
        this.setupAutoRefresh();
    }

    async loadEmployees() {
        try {
            const response = await hrApi.request('/employees/dropdown/list');
            if (response.success) {
                this.employees = response.data;
                this.populateEmployeeDropdowns();
            }
        } catch (error) {
            console.error('Failed to load employees:', error);
        }
    }

    populateEmployeeDropdowns() {
        const dropdowns = document.querySelectorAll('.employee-dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.innerHTML = '<option value="">All Employees</option>';
            this.employees.forEach(employee => {
                dropdown.innerHTML += `<option value="${employee.id}">${employee.full_name}</option>`;
            });
        });
    }

    async loadTimesheets(filters = {}) {
        try {
            const response = await hrApi.getTimesheets(filters);
            if (response.success) {
                this.timesheets = response.data;
                this.renderTimesheets();
            } else {
                hrUI.showAlert(response.message, 'warning');
            }
        } catch (error) {
            hrUI.showAlert('Failed to load timesheets: ' + error.message, 'danger');
        }
    }

    async loadStats() {
        try {
            const response = await hrApi.getTimesheetStats();
            if (response.success) {
                this.updateStatsDisplay(response.data);
            }
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    }

    updateStatsDisplay(stats) {
        document.getElementById('total-timesheets').textContent = stats.total || 0;
        document.getElementById('pending-timesheets').textContent = stats.pending || 0;
        document.getElementById('approved-timesheets').textContent = stats.approved || 0;
        document.getElementById('total-hours').textContent = (stats.total_hours || 0).toFixed(1);
    }

    renderTimesheets() {
        const tbody = document.getElementById('timesheets-tbody');
        if (!tbody) return;

        if (this.timesheets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No timesheets found</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.timesheets.map(timesheet => `
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input timesheet-checkbox" 
                           value="${timesheet.id}" data-status="${timesheet.status}">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-jetlouge-light rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="fas fa-user text-jetlouge-primary"></i>
                        </div>
                        <div>
                            <div class="fw-medium">${timesheet.employee_name || 'Unknown Employee'}</div>
                            <small class="text-muted">ID: ${timesheet.employee_id}</small>
                        </div>
                    </div>
                </td>
                <td>${hrUI.formatDate(timesheet.work_date)}</td>
                <td>
                    <span class="badge bg-primary">${timesheet.hours_worked}h</span>
                    ${timesheet.overtime_hours > 0 ? `<span class="badge bg-warning ms-1">+${timesheet.overtime_hours}h OT</span>` : ''}
                </td>
                <td>
                    <small class="text-muted">${timesheet.description || 'No description'}</small>
                </td>
                <td>${hrUI.getStatusBadge(timesheet.status)}</td>
                <td><small class="text-muted">${hrUI.formatDateTime(timesheet.created_at)}</small></td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="timesheetManager.viewTimesheet(${timesheet.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="timesheetManager.editTimesheet(${timesheet.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${timesheet.status === 'pending' ? `
                            <button class="btn btn-sm btn-outline-success" onclick="timesheetManager.approveTimesheet(${timesheet.id})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="timesheetManager.rejectTimesheet(${timesheet.id})" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-outline-danger" onclick="timesheetManager.deleteTimesheet(${timesheet.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        this.updateBulkActions();
    }

    setupEventListeners() {
        // Filter form
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }

        // Add timesheet form
        const addForm = document.getElementById('add-timesheet-form');
        if (addForm) {
            addForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleAddTimesheet();
            });
        }

        // Edit timesheet form
        const editForm = document.getElementById('edit-timesheet-form');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleEditTimesheet();
            });
        }

        // Bulk actions
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('timesheet-checkbox')) {
                this.updateBulkActions();
            }
        });

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all-timesheets');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.selectAllTimesheets(e.target.checked);
            });
        }

        // Bulk approve button
        const bulkApproveBtn = document.getElementById('bulk-approve-btn');
        if (bulkApproveBtn) {
            bulkApproveBtn.addEventListener('click', () => {
                this.bulkApproveTimesheets();
            });
        }

        // Export button
        const exportBtn = document.getElementById('export-timesheets-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportTimesheets();
            });
        }
    }

    applyFilters() {
        const formData = new FormData(document.getElementById('filter-form'));
        this.currentFilters = Object.fromEntries(formData.entries());
        this.loadTimesheets(this.currentFilters);
    }

    async handleAddTimesheet() {
        const form = document.getElementById('add-timesheet-form');
        if (!hrUI.validateForm(form)) {
            hrUI.showAlert('Please fill in all required fields', 'danger');
            return;
        }

        const formData = new FormData(form);
        const timesheetData = Object.fromEntries(formData.entries());

        try {
            const response = await hrApi.createTimesheet(timesheetData);
            if (response.success) {
                hrUI.showAlert('Timesheet created successfully!', 'success');
                form.reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('addTimesheetModal'));
                modal.hide();
                await this.loadTimesheets(this.currentFilters);
                await this.loadStats();
            } else {
                hrUI.showAlert(response.message, 'danger');
            }
        } catch (error) {
            hrUI.showAlert('Failed to create timesheet: ' + error.message, 'danger');
        }
    }

    async viewTimesheet(id) {
        try {
            const response = await hrApi.getTimesheet(id);
            if (response.success) {
                this.showTimesheetDetails(response.data);
            }
        } catch (error) {
            hrUI.showAlert('Failed to load timesheet details: ' + error.message, 'danger');
        }
    }

    showTimesheetDetails(timesheet) {
        const modal = document.getElementById('viewTimesheetModal');
        if (!modal) return;

        document.getElementById('view-employee-name').textContent = timesheet.employee_name || 'Unknown Employee';
        document.getElementById('view-work-date').textContent = hrUI.formatDate(timesheet.work_date);
        document.getElementById('view-hours-worked').textContent = timesheet.hours_worked + ' hours';
        document.getElementById('view-overtime-hours').textContent = (timesheet.overtime_hours || 0) + ' hours';
        document.getElementById('view-description').textContent = timesheet.description || 'No description';
        document.getElementById('view-status').innerHTML = hrUI.getStatusBadge(timesheet.status);
        document.getElementById('view-created-at').textContent = hrUI.formatDateTime(timesheet.created_at);

        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }

    async editTimesheet(id) {
        try {
            const response = await hrApi.getTimesheet(id);
            if (response.success) {
                this.populateEditForm(response.data);
                const modal = new bootstrap.Modal(document.getElementById('editTimesheetModal'));
                modal.show();
            }
        } catch (error) {
            hrUI.showAlert('Failed to load timesheet for editing: ' + error.message, 'danger');
        }
    }

    populateEditForm(timesheet) {
        document.getElementById('edit-timesheet-id').value = timesheet.id;
        document.getElementById('edit-employee-id').value = timesheet.employee_id;
        document.getElementById('edit-work-date').value = timesheet.work_date;
        document.getElementById('edit-hours-worked').value = timesheet.hours_worked;
        document.getElementById('edit-overtime-hours').value = timesheet.overtime_hours || '';
        document.getElementById('edit-description').value = timesheet.description || '';
        document.getElementById('edit-status').value = timesheet.status;
    }

    async handleEditTimesheet() {
        const form = document.getElementById('edit-timesheet-form');
        if (!hrUI.validateForm(form)) {
            hrUI.showAlert('Please fill in all required fields', 'danger');
            return;
        }

        const formData = new FormData(form);
        const timesheetData = Object.fromEntries(formData.entries());
        const id = timesheetData.id;
        delete timesheetData.id;

        try {
            const response = await hrApi.updateTimesheet(id, timesheetData);
            if (response.success) {
                hrUI.showAlert('Timesheet updated successfully!', 'success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('editTimesheetModal'));
                modal.hide();
                await this.loadTimesheets(this.currentFilters);
                await this.loadStats();
            } else {
                hrUI.showAlert(response.message, 'danger');
            }
        } catch (error) {
            hrUI.showAlert('Failed to update timesheet: ' + error.message, 'danger');
        }
    }

    async approveTimesheet(id) {
        hrUI.confirmAction('Are you sure you want to approve this timesheet?', async () => {
            try {
                const response = await hrApi.updateTimesheet(id, { status: 'approved' });
                if (response.success) {
                    hrUI.showAlert('Timesheet approved successfully!', 'success');
                    await this.loadTimesheets(this.currentFilters);
                    await this.loadStats();
                }
            } catch (error) {
                hrUI.showAlert('Failed to approve timesheet: ' + error.message, 'danger');
            }
        });
    }

    async rejectTimesheet(id) {
        hrUI.confirmAction('Are you sure you want to reject this timesheet?', async () => {
            try {
                const response = await hrApi.updateTimesheet(id, { status: 'rejected' });
                if (response.success) {
                    hrUI.showAlert('Timesheet rejected!', 'warning');
                    await this.loadTimesheets(this.currentFilters);
                    await this.loadStats();
                }
            } catch (error) {
                hrUI.showAlert('Failed to reject timesheet: ' + error.message, 'danger');
            }
        });
    }

    async deleteTimesheet(id) {
        hrUI.confirmAction('Are you sure you want to delete this timesheet? This action cannot be undone.', async () => {
            try {
                const response = await hrApi.deleteTimesheet(id);
                if (response.success) {
                    hrUI.showAlert('Timesheet deleted successfully!', 'success');
                    await this.loadTimesheets(this.currentFilters);
                    await this.loadStats();
                }
            } catch (error) {
                hrUI.showAlert('Failed to delete timesheet: ' + error.message, 'danger');
            }
        });
    }

    selectAllTimesheets(checked) {
        const checkboxes = document.querySelectorAll('.timesheet-checkbox');
        checkboxes.forEach(checkbox => {
            if (checkbox.dataset.status === 'pending') {
                checkbox.checked = checked;
            }
        });
        this.updateBulkActions();
    }

    updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.timesheet-checkbox:checked');
        const bulkActionsDiv = document.getElementById('bulk-actions');
        const bulkApproveBtn = document.getElementById('bulk-approve-btn');
        
        if (selectedCheckboxes.length > 0) {
            if (bulkActionsDiv) bulkActionsDiv.style.display = 'block';
            if (bulkApproveBtn) bulkApproveBtn.textContent = `Approve Selected (${selectedCheckboxes.length})`;
        } else {
            if (bulkActionsDiv) bulkActionsDiv.style.display = 'none';
        }
    }

    async bulkApproveTimesheets() {
        const selectedIds = Array.from(document.querySelectorAll('.timesheet-checkbox:checked'))
            .map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            hrUI.showAlert('Please select timesheets to approve', 'warning');
            return;
        }

        hrUI.confirmAction(`Are you sure you want to approve ${selectedIds.length} timesheet(s)?`, async () => {
            try {
                const promises = selectedIds.map(id => 
                    hrApi.updateTimesheet(id, { status: 'approved' })
                );
                
                await Promise.all(promises);
                hrUI.showAlert(`${selectedIds.length} timesheet(s) approved successfully!`, 'success');
                await this.loadTimesheets(this.currentFilters);
                await this.loadStats();
                
                // Clear selections
                document.querySelectorAll('.timesheet-checkbox:checked').forEach(cb => cb.checked = false);
                this.updateBulkActions();
            } catch (error) {
                hrUI.showAlert('Failed to approve timesheets: ' + error.message, 'danger');
            }
        });
    }

    exportTimesheets() {
        const csvContent = this.generateCSV();
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `timesheets_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        hrUI.showAlert('Timesheets exported successfully!', 'success');
    }

    generateCSV() {
        const headers = ['Employee Name', 'Work Date', 'Hours Worked', 'Overtime Hours', 'Description', 'Status', 'Created At'];
        const rows = this.timesheets.map(timesheet => [
            timesheet.employee_name || 'Unknown Employee',
            timesheet.work_date,
            timesheet.hours_worked,
            timesheet.overtime_hours || 0,
            (timesheet.description || '').replace(/"/g, '""'),
            timesheet.status,
            timesheet.created_at
        ]);
        
        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');
            
        return csvContent;
    }

    setupAutoRefresh() {
        // Auto-refresh every 30 seconds
        setInterval(() => {
            this.loadStats();
        }, 30000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.timesheetManager = new TimesheetManager();
});
