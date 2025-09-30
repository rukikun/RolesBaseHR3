// Leave Management JavaScript
class LeaveManagement {
    constructor() {
        this.leaveRequests = [];
        this.employees = [];
        this.leaveBalance = {};
        this.init();
    }

    init() {
        this.loadEmployees();
        this.loadLeaveRequests();
        this.loadLeaveBalance();
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

    async loadLeaveRequests() {
        try {
            const response = await APIService.get('leaves/list.php');
            this.leaveRequests = response.data;
            this.renderLeaveTable();
            this.renderPendingLeaves();
            this.updateStats();
        } catch (error) {
            console.error('Error loading leave requests:', error);
        }
    }

    async loadLeaveBalance() {
        try {
            const response = await APIService.get('leaves/balance.php');
            this.leaveBalance = response.data;
            this.updateLeaveBalance();
        } catch (error) {
            console.error('Error loading leave balance:', error);
        }
    }

    populateEmployeeDropdowns() {
        const select = document.getElementById('leave-employee');
        if (select) {
            select.innerHTML = '<option value="">Select Employee</option>';
            this.employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.full_name;
                select.appendChild(option);
            });
        }
    }

    updateLeaveBalance() {
        const balance = this.leaveBalance;
        
        document.getElementById('annual-leave-balance').textContent = `${balance.annual || 0} days remaining`;
        document.getElementById('sick-leave-balance').textContent = `${balance.sick || 0} days remaining`;
        document.getElementById('personal-leave-balance').textContent = `${balance.personal || 0} days remaining`;
        
        // Update progress bars
        const annualProgress = Math.max(0, (balance.annual || 0) / 25 * 100);
        const sickProgress = Math.max(0, (balance.sick || 0) / 10 * 100);
        const personalProgress = Math.max(0, (balance.personal || 0) / 5 * 100);
        
        document.getElementById('annual-leave-progress').style.width = `${annualProgress}%`;
        document.getElementById('sick-leave-progress').style.width = `${sickProgress}%`;
        document.getElementById('personal-leave-progress').style.width = `${personalProgress}%`;
    }

    renderPendingLeaves() {
        const container = document.getElementById('pending-leave-list');
        if (!container) return;

        const pendingLeaves = this.leaveRequests.filter(leave => leave.status === 'pending');
        
        if (pendingLeaves.length === 0) {
            container.innerHTML = '<p class="text-muted">No pending leave requests</p>';
            return;
        }

        let html = '';
        pendingLeaves.forEach(leave => {
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>${leave.employee_name}</strong><br>
                        <small class="text-muted">${leave.leave_type} - ${leave.days_requested} days</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success me-1" onclick="leaveManagement.approveLeave(${leave.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="leaveManagement.rejectLeave(${leave.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    renderLeaveTable() {
        const tableBody = document.querySelector('#leave-history-table tbody');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        if (this.leaveRequests.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No leave requests found</td></tr>';
            return;
        }

        this.leaveRequests.forEach(leave => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${leave.employee_name}</td>
                <td><span class="badge bg-secondary">${leave.leave_type}</span></td>
                <td>${HRUtils.formatDate(leave.start_date)}</td>
                <td>${HRUtils.formatDate(leave.end_date)}</td>
                <td>${leave.days_requested}</td>
                <td>${leave.reason}</td>
                <td>${HRUtils.getStatusBadge(leave.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="leaveManagement.viewLeave(${leave.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${leave.status === 'pending' ? `
                        <button class="btn btn-sm btn-success" onclick="leaveManagement.approveLeave(${leave.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="leaveManagement.rejectLeave(${leave.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    updateStats() {
        const totalRequests = this.leaveRequests.length;
        const approvedRequests = this.leaveRequests.filter(leave => leave.status === 'approved').length;
        const pendingRequests = this.leaveRequests.filter(leave => leave.status === 'pending').length;
        const rejectedRequests = this.leaveRequests.filter(leave => leave.status === 'rejected').length;

        document.getElementById('total-requests').textContent = totalRequests;
        document.getElementById('approved-requests').textContent = approvedRequests;
        document.getElementById('pending-requests').textContent = pendingRequests;
        document.getElementById('rejected-requests').textContent = rejectedRequests;
    }

    setupEventListeners() {
        // Request leave button
        const requestLeaveBtn = document.getElementById('request-leave-btn');
        if (requestLeaveBtn) {
            requestLeaveBtn.addEventListener('click', () => this.showLeaveModal());
        }

        // Leave form
        const leaveForm = document.getElementById('leave-form');
        if (leaveForm) {
            leaveForm.addEventListener('submit', (e) => this.handleLeaveSubmit(e));
        }

        // Date change calculation
        const startDateInput = document.getElementById('leave-start-date');
        const endDateInput = document.getElementById('leave-end-date');
        
        if (startDateInput && endDateInput) {
            [startDateInput, endDateInput].forEach(input => {
                input.addEventListener('change', () => this.calculateLeaveDays());
            });
        }

        // Leave filter
        const leaveFilter = document.getElementById('leave-filter');
        if (leaveFilter) {
            leaveFilter.addEventListener('change', () => this.applyFilter());
        }
    }

    calculateLeaveDays() {
        const startDate = document.getElementById('leave-start-date').value;
        const endDate = document.getElementById('leave-end-date').value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = end.getTime() - start.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
            
            document.getElementById('leave-days-count').textContent = daysDiff > 0 ? daysDiff : 0;
        }
    }

    showLeaveModal(leaveId = null) {
        const modal = new bootstrap.Modal(document.getElementById('leave-modal'));
        const form = document.getElementById('leave-form');
        
        if (leaveId) {
            // View/Edit mode
            const leave = this.leaveRequests.find(l => l.id === leaveId);
            if (leave) {
                document.getElementById('leave-id').value = leave.id;
                document.getElementById('leave-employee').value = leave.employee_id;
                document.getElementById('leave-type').value = leave.leave_type;
                document.getElementById('leave-start-date').value = leave.start_date;
                document.getElementById('leave-end-date').value = leave.end_date;
                document.getElementById('leave-reason').value = leave.reason;
                document.getElementById('leave-modal-title').textContent = 'View Leave Request';
                
                // Show approve/reject buttons for pending requests
                if (leave.status === 'pending') {
                    document.getElementById('approve-leave-btn').style.display = 'inline-block';
                    document.getElementById('reject-leave-btn').style.display = 'inline-block';
                }
                
                this.calculateLeaveDays();
            }
        } else {
            // Create mode
            form.reset();
            document.getElementById('leave-id').value = '';
            document.getElementById('leave-modal-title').textContent = 'Request Leave';
            document.getElementById('approve-leave-btn').style.display = 'none';
            document.getElementById('reject-leave-btn').style.display = 'none';
        }
        
        modal.show();
    }

    async handleLeaveSubmit(e) {
        e.preventDefault();
        
        const formData = {
            employee_id: document.getElementById('leave-employee').value,
            leave_type: document.getElementById('leave-type').value,
            start_date: document.getElementById('leave-start-date').value,
            end_date: document.getElementById('leave-end-date').value,
            reason: document.getElementById('leave-reason').value,
            days_requested: parseInt(document.getElementById('leave-days-count').textContent)
        };

        const errors = FormValidator.validateRequired([
            { id: 'leave-employee', name: 'Employee' },
            { id: 'leave-type', name: 'Leave Type' },
            { id: 'leave-start-date', name: 'Start Date' },
            { id: 'leave-end-date', name: 'End Date' },
            { id: 'leave-reason', name: 'Reason' }
        ]);

        if (!FormValidator.validateDate(formData.start_date, formData.end_date)) {
            errors.push('End date must be after start date');
        }

        if (!FormValidator.showErrors(errors)) return;

        try {
            const leaveId = document.getElementById('leave-id').value;
            
            if (leaveId) {
                await APIService.put(`leaves/update.php?id=${leaveId}`, formData);
                HRUtils.showNotification('Leave request updated successfully!', 'success');
            } else {
                await APIService.post('leaves/create.php', formData);
                HRUtils.showNotification('Leave request submitted successfully!', 'success');
            }

            bootstrap.Modal.getInstance(document.getElementById('leave-modal')).hide();
            this.loadLeaveRequests();
        } catch (error) {
            HRUtils.showNotification('Error saving leave request', 'danger');
        }
    }

    async approveLeave(id) {
        try {
            await APIService.put(`leaves/approve.php?id=${id}`, { status: 'approved' });
            HRUtils.showNotification('Leave request approved!', 'success');
            this.loadLeaveRequests();
        } catch (error) {
            HRUtils.showNotification('Error approving leave request', 'danger');
        }
    }

    async rejectLeave(id) {
        try {
            await APIService.put(`leaves/reject.php?id=${id}`, { status: 'rejected' });
            HRUtils.showNotification('Leave request rejected!', 'success');
            this.loadLeaveRequests();
        } catch (error) {
            HRUtils.showNotification('Error rejecting leave request', 'danger');
        }
    }

    viewLeave(id) {
        this.showLeaveModal(id);
    }

    applyFilter() {
        const filter = document.getElementById('leave-filter').value;
        // Implementation for filtering leave requests
        this.renderLeaveTable();
    }
}

// Initialize leave management when DOM is loaded
let leaveManagement;
document.addEventListener('DOMContentLoaded', function() {
    leaveManagement = new LeaveManagement();
});
