// Timesheet JavaScript
class Timesheet {
    constructor() {
        this.timeEntries = [];
        this.employees = [];
        this.init();
    }

    init() {
        this.loadEmployees();
        this.loadTimeEntries();
        this.setupEventListeners();
        this.updateStats();
    }

    async loadEmployees() {
        try {
            const response = await APIService.get('employees/list.php');
            this.employees = response.data;
            this.populateEmployeeDropdowns();
        } catch (error) {
            console.error('Error loading employees:', error);
        }
    }

    async loadTimeEntries() {
        try {
            const response = await APIService.get('timesheet/list.php');
            this.timeEntries = response.data;
            this.renderTimeEntriesTable();
            this.updateStats();
        } catch (error) {
            console.error('Error loading time entries:', error);
        }
    }

    populateEmployeeDropdowns() {
        const selects = ['timesheet-employee', 'time-entry-employee'];
        
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Select Employee</option>';
                
                this.employees.forEach(employee => {
                    const option = document.createElement('option');
                    option.value = employee.id;
                    option.textContent = employee.full_name;
                    select.appendChild(option);
                });
                
                if (currentValue) select.value = currentValue;
            }
        });
    }

    renderTimeEntriesTable() {
        const tableBody = document.querySelector('#timesheet-table tbody');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        if (this.timeEntries.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No time entries found</td></tr>';
            return;
        }

        this.timeEntries.forEach(entry => {
            const employee = this.employees.find(e => e.id == entry.employee_id);
            const totalHours = entry.total_hours || HRUtils.calculateHours(entry.clock_in, entry.clock_out);
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${HRUtils.formatDate(entry.date)}</td>
                <td>${employee ? employee.full_name : 'Unknown'}</td>
                <td>${HRUtils.formatTime(entry.clock_in)}</td>
                <td>${HRUtils.formatTime(entry.clock_out)}</td>
                <td>${entry.break_minutes || 0} min</td>
                <td>${totalHours}</td>
                <td>${entry.overtime_hours || '0h'}</td>
                <td>${HRUtils.getStatusBadge(entry.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="timesheet.editTimeEntry(${entry.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="timesheet.deleteTimeEntry(${entry.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    updateStats() {
        const totalHours = this.timeEntries.reduce((sum, entry) => sum + (parseFloat(entry.total_hours) || 0), 0);
        const approvedEntries = this.timeEntries.filter(entry => entry.status === 'approved').length;
        const pendingEntries = this.timeEntries.filter(entry => entry.status === 'pending').length;
        const workingDays = new Set(this.timeEntries.map(entry => entry.date)).size;

        document.getElementById('total-hours').textContent = Math.round(totalHours);
        document.getElementById('approved-entries').textContent = approvedEntries;
        document.getElementById('pending-entries').textContent = pendingEntries;
        document.getElementById('working-days').textContent = workingDays;
    }

    setupEventListeners() {
        // Add time entry button
        const addTimeEntryBtn = document.getElementById('add-time-entry-btn');
        if (addTimeEntryBtn) {
            addTimeEntryBtn.addEventListener('click', () => this.showTimeEntryModal());
        }

        // Filter button
        const filterBtn = document.getElementById('filter-timesheet-btn');
        if (filterBtn) {
            filterBtn.addEventListener('click', () => this.applyFilters());
        }

        // Period change
        const periodSelect = document.getElementById('timesheet-period');
        if (periodSelect) {
            periodSelect.addEventListener('change', () => this.handlePeriodChange());
        }

        // Time entry form
        const timeEntryForm = document.getElementById('time-entry-form');
        if (timeEntryForm) {
            timeEntryForm.addEventListener('submit', (e) => this.handleTimeEntrySubmit(e));
        }

        // Export button
        const exportBtn = document.getElementById('generate-timesheet-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportTimesheet());
        }
    }

    handlePeriodChange() {
        const period = document.getElementById('timesheet-period').value;
        const customRange = document.getElementById('custom-date-range');
        
        if (period === 'custom') {
            customRange.style.display = 'block';
        } else {
            customRange.style.display = 'none';
            this.applyFilters();
        }
    }

    async applyFilters() {
        const employeeId = document.getElementById('timesheet-employee').value;
        const period = document.getElementById('timesheet-period').value;
        let startDate = '';
        let endDate = '';

        if (period === 'custom') {
            startDate = document.getElementById('timesheet-start-date').value;
            endDate = document.getElementById('timesheet-end-date').value;
        }

        try {
            const params = new URLSearchParams({
                employee_id: employeeId,
                period: period,
                start_date: startDate,
                end_date: endDate
            });

            const response = await APIService.get(`timesheet/list.php?${params}`);
            this.timeEntries = response.data;
            this.renderTimeEntriesTable();
            this.updateStats();
        } catch (error) {
            HRUtils.showNotification('Error applying filters', 'danger');
        }
    }

    showTimeEntryModal(entryId = null) {
        const modal = new bootstrap.Modal(document.getElementById('time-entry-modal'));
        const form = document.getElementById('time-entry-form');
        
        if (entryId) {
            // Edit mode
            const entry = this.timeEntries.find(e => e.id === entryId);
            if (entry) {
                document.getElementById('time-entry-id').value = entry.id;
                document.getElementById('time-entry-employee').value = entry.employee_id;
                document.getElementById('time-entry-date').value = entry.date;
                document.getElementById('time-entry-clock-in').value = entry.clock_in;
                document.getElementById('time-entry-clock-out').value = entry.clock_out;
                document.getElementById('time-entry-break').value = entry.break_minutes || 0;
                document.getElementById('time-entry-notes').value = entry.notes || '';
                document.getElementById('time-entry-modal-title').textContent = 'Edit Time Entry';
                document.getElementById('delete-time-entry-btn').style.display = 'inline-block';
            }
        } else {
            // Create mode
            form.reset();
            document.getElementById('time-entry-id').value = '';
            document.getElementById('time-entry-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('time-entry-modal-title').textContent = 'Add Time Entry';
            document.getElementById('delete-time-entry-btn').style.display = 'none';
        }
        
        modal.show();
    }

    async handleTimeEntrySubmit(e) {
        e.preventDefault();
        
        const formData = {
            employee_id: document.getElementById('time-entry-employee').value,
            date: document.getElementById('time-entry-date').value,
            clock_in: document.getElementById('time-entry-clock-in').value,
            clock_out: document.getElementById('time-entry-clock-out').value,
            break_minutes: document.getElementById('time-entry-break').value || 0,
            notes: document.getElementById('time-entry-notes').value
        };

        const errors = FormValidator.validateRequired([
            { id: 'time-entry-employee', name: 'Employee' },
            { id: 'time-entry-date', name: 'Date' },
            { id: 'time-entry-clock-in', name: 'Clock In Time' },
            { id: 'time-entry-clock-out', name: 'Clock Out Time' }
        ]);

        if (!FormValidator.showErrors(errors)) return;

        try {
            const entryId = document.getElementById('time-entry-id').value;
            
            if (entryId) {
                await APIService.put(`timesheet/update.php?id=${entryId}`, formData);
                HRUtils.showNotification('Time entry updated successfully!', 'success');
            } else {
                await APIService.post('timesheet/create.php', formData);
                HRUtils.showNotification('Time entry created successfully!', 'success');
            }

            bootstrap.Modal.getInstance(document.getElementById('time-entry-modal')).hide();
            this.loadTimeEntries();
        } catch (error) {
            HRUtils.showNotification('Error saving time entry', 'danger');
        }
    }

    editTimeEntry(id) {
        this.showTimeEntryModal(id);
    }

    async deleteTimeEntry(id) {
        if (!confirm('Are you sure you want to delete this time entry?')) return;

        try {
            await APIService.delete(`timesheet/delete.php?id=${id}`);
            HRUtils.showNotification('Time entry deleted successfully!', 'success');
            this.loadTimeEntries();
        } catch (error) {
            HRUtils.showNotification('Error deleting time entry', 'danger');
        }
    }

    async exportTimesheet() {
        try {
            const response = await APIService.get('timesheet/export.php');
            // Handle file download
            const blob = new Blob([response], { type: 'application/vnd.ms-excel' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `timesheet_${new Date().toISOString().split('T')[0]}.xlsx`;
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (error) {
            HRUtils.showNotification('Error exporting timesheet', 'danger');
        }
    }
}

// Initialize timesheet when DOM is loaded
let timesheet;
document.addEventListener('DOMContentLoaded', function() {
    timesheet = new Timesheet();
});
