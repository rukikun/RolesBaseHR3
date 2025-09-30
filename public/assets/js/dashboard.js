// Dashboard JavaScript
class Dashboard {
    constructor() {
        this.init();
    }

    init() {
        this.updateClock();
        this.loadDashboardStats();
        this.loadTodaysSchedule();
        this.loadRecentTimeEntries();
        this.setupEventListeners();
        
        // Update clock every second
        setInterval(() => this.updateClock(), 1000);
    }

    updateClock() {
        const clockElement = document.getElementById('live-clock');
        if (clockElement) {
            const now = new Date();
            clockElement.textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }

    async loadDashboardStats() {
        try {
            const stats = await APIService.get('dashboard/stats.php');
            
            document.getElementById('total-employees').textContent = stats.totalEmployees || 0;
            document.getElementById('present-today').textContent = stats.presentToday || 0;
            document.getElementById('pending-timesheets').textContent = stats.pendingTimesheets || 0;
            document.getElementById('leave-requests').textContent = stats.leaveRequests || 0;
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    async loadTodaysSchedule() {
        const scheduleContainer = document.getElementById('todays-schedule');
        if (!scheduleContainer) return;

        try {
            HRUtils.showLoading(scheduleContainer);
            const schedule = await APIService.get('schedule/today.php');
            
            if (schedule.length === 0) {
                scheduleContainer.innerHTML = '<p class="text-muted">No shifts scheduled for today.</p>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Shift</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            schedule.forEach(shift => {
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${shift.employee_avatar || 'https://via.placeholder.com/32'}" 
                                     class="rounded-circle me-2" width="32" height="32">
                                <span>${shift.employee_name}</span>
                            </div>
                        </td>
                        <td>${shift.shift_name}</td>
                        <td>${HRUtils.formatTime(shift.start_time)} - ${HRUtils.formatTime(shift.end_time)}</td>
                        <td>${HRUtils.getStatusBadge(shift.status)}</td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            scheduleContainer.innerHTML = html;
        } catch (error) {
            scheduleContainer.innerHTML = '<p class="text-danger">Error loading schedule</p>';
        }
    }

    async loadRecentTimeEntries() {
        const tableBody = document.querySelector('#recent-entries-table tbody');
        if (!tableBody) return;

        try {
            const entries = await APIService.get('timesheet/recent.php');
            
            tableBody.innerHTML = '';
            
            if (entries.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No recent entries</td></tr>';
                return;
            }

            entries.forEach(entry => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${HRUtils.formatDate(entry.date)}</td>
                    <td>${HRUtils.formatTime(entry.clock_in)}</td>
                    <td>${HRUtils.formatTime(entry.clock_out)}</td>
                    <td>${HRUtils.calculateHours(entry.clock_in, entry.clock_out)}</td>
                    <td>${HRUtils.getStatusBadge(entry.status)}</td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading entries</td></tr>';
        }
    }

    setupEventListeners() {
        const clockInBtn = document.getElementById('clock-in-btn');
        const clockOutBtn = document.getElementById('clock-out-btn');

        if (clockInBtn) {
            clockInBtn.addEventListener('click', () => this.clockIn());
        }

        if (clockOutBtn) {
            clockOutBtn.addEventListener('click', () => this.clockOut());
        }

        // Check current clock status
        this.checkClockStatus();
    }

    async checkClockStatus() {
        try {
            const status = await APIService.get('timesheet/clock-status.php');
            const clockInBtn = document.getElementById('clock-in-btn');
            const clockOutBtn = document.getElementById('clock-out-btn');

            if (status.isClockedIn) {
                clockInBtn.disabled = true;
                clockOutBtn.disabled = false;
            } else {
                clockInBtn.disabled = false;
                clockOutBtn.disabled = true;
            }
        } catch (error) {
            console.error('Error checking clock status:', error);
        }
    }

    async clockIn() {
        try {
            const result = await APIService.post('timesheet/clock-in.php', {
                timestamp: new Date().toISOString()
            });

            if (result.success) {
                HRUtils.showNotification('Clocked in successfully!', 'success');
                document.getElementById('clock-in-btn').disabled = true;
                document.getElementById('clock-out-btn').disabled = false;
                this.loadRecentTimeEntries();
            }
        } catch (error) {
            HRUtils.showNotification('Error clocking in', 'danger');
        }
    }

    async clockOut() {
        try {
            const result = await APIService.post('timesheet/clock-out.php', {
                timestamp: new Date().toISOString()
            });

            if (result.success) {
                HRUtils.showNotification(`Clocked out successfully! Total hours: ${result.totalHours}`, 'success');
                document.getElementById('clock-in-btn').disabled = false;
                document.getElementById('clock-out-btn').disabled = true;
                this.loadRecentTimeEntries();
            }
        } catch (error) {
            HRUtils.showNotification('Error clocking out', 'danger');
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new Dashboard();
});
