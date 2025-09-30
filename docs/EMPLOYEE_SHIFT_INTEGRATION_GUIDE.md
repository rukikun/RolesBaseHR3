# Employee-Shift Integration Guide

## Overview
This guide documents the complete integration between Employee Management and Shift Schedule Management modules in the HR3 system.

## Database Integration

### Foreign Key Relationships
```sql
-- Shifts table references employees
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE

-- Complete relationship chain:
employees -> shifts -> shift_types
employees -> time_entries (timesheet integration)
```

### Database Tables Involved
- **employees**: Core employee data
- **shifts**: Shift assignments linking employees to shift types
- **shift_types**: Shift configuration templates
- **time_entries**: Timesheet data (separate integration)

## Integration Features

### 1. Tabbed Interface
**Location**: `shift_schedule_management.blade.php`

```html
<ul class="nav nav-tabs mb-4" id="shiftTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="shifts-tab">Shifts & Schedule</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="employees-tab">Employees</button>
  </li>
</ul>
```

### 2. Employee Statistics Dashboard
**Real-time Statistics Cards:**
- Total Employees
- Active Employees  
- Employees with Shifts
- Department Count

**JavaScript Function:**
```javascript
function updateShiftEmployeeStats(employees) {
    const totalCount = employees.length;
    const activeCount = employees.filter(emp => emp.status === 'active').length;
    const departments = [...new Set(employees.map(emp => emp.department))].length;
    
    document.getElementById('emp-total-count').textContent = totalCount;
    document.getElementById('emp-active-count').textContent = activeCount;
    document.getElementById('emp-dept-count').textContent = departments;
}
```

### 3. Cross-Module Navigation

#### View Employee Shifts
**Function**: `viewEmployeeShifts(employeeId)`
- Switches from Employees tab to Shifts tab
- Filters shifts by selected employee
- Provides seamless navigation experience

```javascript
function viewEmployeeShifts(employeeId) {
    // Switch to shifts tab
    const shiftsTab = document.getElementById('shifts-tab');
    const shiftsPane = document.getElementById('shifts');
    const employeesPane = document.getElementById('employees');
    
    // Update tab states
    document.getElementById('employees-tab').classList.remove('active');
    shiftsTab.classList.add('active');
    employeesPane.classList.remove('show', 'active');
    shiftsPane.classList.add('show', 'active');
}
```

#### Assign Shift to Employee
**Function**: `assignShiftToEmployee(employeeId)`
- Opens shift assignment modal
- Pre-selects the employee
- Sets current date as default

```javascript
function assignShiftToEmployee(employeeId) {
    const employeeSelect = document.getElementById('shift-employee');
    if (employeeSelect) {
        employeeSelect.value = employeeId;
    }
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('shift-date').value = today;
    
    openWorkingModal('create-shift-modal');
}
```

### 4. Employee Management in Shift Context

#### Employee Table Actions
Each employee record has three action buttons:
1. **View Shifts** (calendar icon) - Switch to shifts view filtered by employee
2. **Assign Shift** (plus icon) - Create new shift for employee
3. **Edit** (edit icon) - Edit employee details

#### Employee Modal
- Full CRUD operations for employees
- Form validation and error handling
- Working-modal CSS for proper functionality

### 5. Search and Filtering

#### Employee Filters
- **Search**: Real-time name search
- **Department**: Filter by department
- **Status**: Filter by active/inactive/terminated

```javascript
function filterShiftEmployees() {
    const searchTerm = document.getElementById('employee-search').value.toLowerCase();
    const departmentFilter = document.getElementById('department-filter').value;
    const statusFilter = document.getElementById('employee-status-filter').value;
    
    const rows = document.querySelectorAll('#shift-employees-tbody tr');
    
    rows.forEach(row => {
        const name = row.cells[1]?.textContent.toLowerCase() || '';
        const department = row.cells[3]?.textContent || '';
        const status = row.cells[4]?.textContent.toLowerCase() || '';
        
        const matchesSearch = name.includes(searchTerm);
        const matchesDepartment = !departmentFilter || department === departmentFilter;
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        
        row.style.display = matchesSearch && matchesDepartment && matchesStatus ? '' : 'none';
    });
}
```

## API Integration

### Shared Endpoints
- **GET /api/employees** - Load employee data for both modules
- **GET /api/employees/{id}** - Get specific employee details
- **POST /api/employees** - Create new employee
- **PUT /api/employees/{id}** - Update employee
- **DELETE /api/employees/{id}** - Delete employee

### Data Flow
1. Employee data loaded via AJAX from shared API
2. Statistics calculated client-side
3. Cross-module navigation updates UI state
4. Form submissions use server-side validation

## Testing Procedures

### 1. Basic Navigation
- Switch between Shifts and Employees tabs
- Verify data loads correctly in each tab
- Check for JavaScript errors in console

### 2. Employee Statistics
- Verify counts update when employees are loaded
- Check department count accuracy
- Ensure active employee count is correct

### 3. Cross-Module Actions
- Click "View Shifts" on employee record
- Verify tab switches and employee filter applies
- Click "Assign Shift" and check modal pre-population

### 4. Employee Management
- Add new employee via modal
- Edit existing employee
- Test search and filtering functionality

### 5. Data Consistency
- Verify employee data matches between modules
- Check foreign key relationships work
- Test cascade delete functionality

## Security Considerations

### CSRF Protection
All forms include CSRF tokens:
```html
@csrf
```

### Input Validation
- Server-side validation on all employee data
- Client-side validation for required fields
- SQL injection prevention through parameterized queries

### Access Control
- Authentication required for all operations
- Proper error handling without data exposure

## Performance Optimization

### Lazy Loading
- Employee data loaded only when Employees tab is accessed
- Statistics calculated client-side to reduce server load

### Efficient Queries
- Proper database indexes on foreign keys
- Optimized queries with necessary JOINs only

### Caching Strategy
- Employee dropdown data cached client-side
- Department list updated dynamically

## Troubleshooting

### Common Issues
1. **Tab not switching**: Check Bootstrap JS is loaded
2. **Employee data not loading**: Verify API endpoints are working
3. **Modal not opening**: Ensure working-modal CSS is included
4. **Statistics not updating**: Check JavaScript console for errors

### Debug Steps
1. Check browser console for JavaScript errors
2. Verify API responses in Network tab
3. Confirm database relationships exist
4. Test with different employee records

## Future Enhancements

### Potential Improvements
1. **Shift History**: Show employee's shift history in employee view
2. **Bulk Operations**: Assign shifts to multiple employees
3. **Calendar Integration**: Visual calendar view of employee shifts
4. **Notifications**: Alert employees of shift assignments
5. **Reporting**: Generate employee shift reports

### Integration Opportunities
1. **Leave Management**: Check leave requests when assigning shifts
2. **Time Tracking**: Link shift assignments to time entries
3. **Payroll**: Calculate pay based on shift assignments

---

**Last Updated**: August 30, 2025  
**Status**: Integration Complete - Ready for Production
