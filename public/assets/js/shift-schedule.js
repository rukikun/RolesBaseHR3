// Shift Schedule JavaScript
class ShiftSchedule {
    constructor() {
        this.shifts = [];
        this.employees = [];
        this.employeeShifts = [];
        this.currentMonth = new Date().getMonth();
        this.currentYear = new Date().getFullYear();
        this.init();
    }

    init() {
        this.loadShifts();
        this.loadEmployees();
        this.loadEmployeeShifts();
        this.setupEventListeners();
        this.generateCalendar();
    }

    async loadShifts() {
        try {
            const response = await APIService.get('shifts/list.php');
            this.shifts = response.data;
            this.populateShiftDropdowns();
        } catch (error) {
            console.error('Error loading shifts:', error);
        }
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

    async loadEmployeeShifts() {
        try {
            const response = await APIService.get('shifts/employee-shifts.php');
            this.employeeShifts = response.data;
            this.renderEmployeeShiftsTable();
        } catch (error) {
            console.error('Error loading employee shifts:', error);
        }
    }

    populateShiftDropdowns() {
        const assignShiftSelect = document.getElementById('assign-shift');
        if (assignShiftSelect) {
            assignShiftSelect.innerHTML = '<option value="">Select Shift</option>';
            this.shifts.forEach(shift => {
                const option = document.createElement('option');
                option.value = shift.id;
                option.textContent = `${shift.name} (${HRUtils.formatTime(shift.start_time)} - ${HRUtils.formatTime(shift.end_time)})`;
                assignShiftSelect.appendChild(option);
            });
        }
    }

    populateEmployeeDropdowns() {
        const assignEmployeeSelect = document.getElementById('assign-employee');
        if (assignEmployeeSelect) {
            assignEmployeeSelect.innerHTML = '<option value="">Select Employee</option>';
            this.employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.full_name;
                assignEmployeeSelect.appendChild(option);
            });
        }
    }

    generateCalendar() {
        const calendarBody = document.getElementById('calendar-body');
        if (!calendarBody) return;

        const firstDay = new Date(this.currentYear, this.currentMonth, 1);
        const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        calendarBody.innerHTML = '';

        for (let week = 0; week < 6; week++) {
            const row = document.createElement('tr');
            
            for (let day = 0; day < 7; day++) {
                const currentDate = new Date(startDate);
                currentDate.setDate(startDate.getDate() + (week * 7) + day);
                
                const cell = document.createElement('td');
                cell.innerHTML = `
                    <div class="calendar-date">${currentDate.getDate()}</div>
                    <div class="shifts-container" id="shifts-${currentDate.toISOString().split('T')[0]}"></div>
                `;
                
                if (currentDate.getMonth() !== this.currentMonth) {
                    cell.classList.add('text-muted');
                }
                
                row.appendChild(cell);
            }
            
            calendarBody.appendChild(row);
        }

        this.populateCalendarShifts();
    }

    populateCalendarShifts() {
        this.employeeShifts.forEach(shift => {
            const container = document.getElementById(`shifts-${shift.shift_date}`);
            if (container) {
                const employee = this.employees.find(e => e.id === shift.employee_id);
                const shiftInfo = this.shifts.find(s => s.id === shift.shift_id);
                
                if (employee && shiftInfo) {
                    const shiftElement = document.createElement('div');
                    shiftElement.className = 'shift-item';
                    shiftElement.textContent = `${employee.first_name} - ${shiftInfo.name}`;
                    shiftElement.onclick = () => this.editShiftAssignment(shift.id);
                    container.appendChild(shiftElement);
                }
            }
        });
    }

    renderEmployeeShiftsTable() {
        const tableBody = document.querySelector('#employee-shifts-table tbody');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        this.employeeShifts.forEach(shift => {
            const employee = this.employees.find(e => e.id === shift.employee_id);
            const shiftInfo = this.shifts.find(s => s.id === shift.shift_id);
            
            if (employee && shiftInfo) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${employee.full_name}</td>
                    <td>${employee.position}</td>
                    <td>${HRUtils.formatDate(shift.shift_date)}</td>
                    <td>${shiftInfo.name}</td>
                    <td>${HRUtils.formatTime(shiftInfo.start_time)} - ${HRUtils.formatTime(shiftInfo.end_time)}</td>
                    <td>${HRUtils.getStatusBadge(shift.status)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="shiftSchedule.editShiftAssignment(${shift.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="shiftSchedule.deleteShiftAssignment(${shift.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            }
        });
    }

    setupEventListeners() {
        // Create shift button
        const createShiftBtn = document.getElementById('create-shift-btn');
        if (createShiftBtn) {
            createShiftBtn.addEventListener('click', () => this.showShiftModal());
        }

        // Assign shift button
        const assignShiftBtn = document.getElementById('assign-shift-btn');
        if (assignShiftBtn) {
            assignShiftBtn.addEventListener('click', () => this.showAssignShiftModal());
        }

        // Shift form submission
        const shiftForm = document.getElementById('shift-form');
        if (shiftForm) {
            shiftForm.addEventListener('submit', (e) => this.handleShiftSubmit(e));
        }

        // Assign shift form submission
        const assignShiftForm = document.getElementById('assign-shift-form');
        if (assignShiftForm) {
            assignShiftForm.addEventListener('submit', (e) => this.handleAssignShiftSubmit(e));
        }
    }

    showShiftModal(shiftId = null) {
        const modal = new bootstrap.Modal(document.getElementById('shift-modal'));
        const form = document.getElementById('shift-form');
        
        if (shiftId) {
            // Edit mode
            const shift = this.shifts.find(s => s.id === shiftId);
            if (shift) {
                document.getElementById('shift-id').value = shift.id;
                document.getElementById('shift-name').value = shift.name;
                document.getElementById('shift-start-time').value = shift.start_time;
                document.getElementById('shift-end-time').value = shift.end_time;
                document.getElementById('shift-description').value = shift.description;
                document.getElementById('shift-modal-title').textContent = 'Edit Shift';
                document.getElementById('delete-shift-btn').style.display = 'inline-block';
            }
        } else {
            // Create mode
            form.reset();
            document.getElementById('shift-id').value = '';
            document.getElementById('shift-modal-title').textContent = 'Create New Shift';
            document.getElementById('delete-shift-btn').style.display = 'none';
        }
        
        modal.show();
    }

    showAssignShiftModal() {
        const modal = new bootstrap.Modal(document.getElementById('assign-shift-modal'));
        const form = document.getElementById('assign-shift-form');
        
        form.reset();
        document.getElementById('assign-date').value = new Date().toISOString().split('T')[0];
        
        modal.show();
    }

    async handleShiftSubmit(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('shift-name').value,
            start_time: document.getElementById('shift-start-time').value,
            end_time: document.getElementById('shift-end-time').value,
            description: document.getElementById('shift-description').value
        };

        const errors = FormValidator.validateRequired([
            { id: 'shift-name', name: 'Shift Name' },
            { id: 'shift-start-time', name: 'Start Time' },
            { id: 'shift-end-time', name: 'End Time' }
        ]);

        if (!FormValidator.showErrors(errors)) return;

        try {
            const shiftId = document.getElementById('shift-id').value;
            
            if (shiftId) {
                await APIService.put(`shifts/update.php?id=${shiftId}`, formData);
                HRUtils.showNotification('Shift updated successfully!', 'success');
            } else {
                await APIService.post('shifts/create.php', formData);
                HRUtils.showNotification('Shift created successfully!', 'success');
            }

            bootstrap.Modal.getInstance(document.getElementById('shift-modal')).hide();
            this.loadShifts();
        } catch (error) {
            HRUtils.showNotification('Error saving shift', 'danger');
        }
    }

    async handleAssignShiftSubmit(e) {
        e.preventDefault();
        
        const formData = {
            employee_id: document.getElementById('assign-employee').value,
            shift_id: document.getElementById('assign-shift').value,
            shift_date: document.getElementById('assign-date').value,
            notes: document.getElementById('assign-notes').value
        };

        const errors = FormValidator.validateRequired([
            { id: 'assign-employee', name: 'Employee' },
            { id: 'assign-shift', name: 'Shift' },
            { id: 'assign-date', name: 'Date' }
        ]);

        if (!FormValidator.showErrors(errors)) return;

        try {
            await APIService.post('shifts/assign.php', formData);
            HRUtils.showNotification('Shift assigned successfully!', 'success');
            
            bootstrap.Modal.getInstance(document.getElementById('assign-shift-modal')).hide();
            this.loadEmployeeShifts();
            this.generateCalendar();
        } catch (error) {
            HRUtils.showNotification('Error assigning shift', 'danger');
        }
    }

    async deleteShiftAssignment(id) {
        if (!confirm('Are you sure you want to delete this shift assignment?')) return;

        try {
            await APIService.delete(`shifts/delete-assignment.php?id=${id}`);
            HRUtils.showNotification('Shift assignment deleted successfully!', 'success');
            this.loadEmployeeShifts();
            this.generateCalendar();
        } catch (error) {
            HRUtils.showNotification('Error deleting shift assignment', 'danger');
        }
    }
}

// Initialize shift schedule when DOM is loaded
let shiftSchedule;
document.addEventListener('DOMContentLoaded', function() {
    shiftSchedule = new ShiftSchedule();
});
