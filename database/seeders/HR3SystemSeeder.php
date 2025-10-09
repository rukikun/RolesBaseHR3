<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HR3SystemSeeder extends Seeder
{
    public function run(): void
    {
        // Insert sample employees
        DB::table('employees')->insert([
            [
                'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@jetlouge.com',
                'phone' => '+63 912 345 6789', 'position' => 'Customer Service Representative',
                'department' => 'Operations', 'hire_date' => '2024-01-15', 'salary' => 45000.00,
                'status' => 'active', 'online_status' => 'online',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane.smith@jetlouge.com',
                'phone' => '+63 917 234 5678', 'position' => 'Travel Consultant',
                'department' => 'Sales', 'hire_date' => '2024-02-01', 'salary' => 55000.00,
                'status' => 'active', 'online_status' => 'online',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'first_name' => 'Mike', 'last_name' => 'Johnson', 'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+63 918 345 6789', 'position' => 'Operations Manager',
                'department' => 'Operations', 'hire_date' => '2023-11-10', 'salary' => 75000.00,
                'status' => 'active', 'online_status' => 'offline',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah', 'last_name' => 'Wilson', 'email' => 'sarah.wilson@jetlouge.com',
                'phone' => '+63 919 456 7890', 'position' => 'Marketing Specialist',
                'department' => 'Marketing', 'hire_date' => '2024-03-05', 'salary' => 58000.00,
                'status' => 'active', 'online_status' => 'online',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'first_name' => 'David', 'last_name' => 'Brown', 'email' => 'david.brown@jetlouge.com',
                'phone' => '+63 920 567 8901', 'position' => 'IT Support',
                'department' => 'IT', 'hire_date' => '2024-01-20', 'salary' => 62000.00,
                'status' => 'active', 'online_status' => 'offline',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // Insert shift types
        DB::table('shift_types')->insert([
            ['name' => 'Morning Shift', 'description' => 'Standard morning work shift', 'default_start_time' => '08:00:00', 'default_end_time' => '16:00:00', 'color_code' => '#28a745', 'type' => 'morning', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Evening Shift', 'description' => 'Standard evening work shift', 'default_start_time' => '16:00:00', 'default_end_time' => '00:00:00', 'color_code' => '#fd7e14', 'type' => 'evening', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Night Shift', 'description' => 'Overnight work shift', 'default_start_time' => '00:00:00', 'default_end_time' => '08:00:00', 'color_code' => '#6f42c1', 'type' => 'night', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Weekend Day', 'description' => 'Weekend daytime shift', 'default_start_time' => '09:00:00', 'default_end_time' => '17:00:00', 'color_code' => '#20c997', 'type' => 'weekend', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Part-time Morning', 'description' => 'Part-time morning shift', 'default_start_time' => '08:00:00', 'default_end_time' => '12:00:00', 'color_code' => '#17a2b8', 'type' => 'morning', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert leave types
        DB::table('leave_types')->insert([
            ['name' => 'Annual Leave', 'description' => 'Yearly vacation leave', 'days_allowed' => 15, 'carry_forward' => true, 'requires_approval' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sick Leave', 'description' => 'Medical leave for illness', 'days_allowed' => 10, 'carry_forward' => false, 'requires_approval' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Emergency Leave', 'description' => 'Urgent personal matters', 'days_allowed' => 5, 'carry_forward' => false, 'requires_approval' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maternity Leave', 'description' => 'Maternity leave for mothers', 'days_allowed' => 90, 'carry_forward' => false, 'requires_approval' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Study Leave', 'description' => 'Educational purposes', 'days_allowed' => 5, 'carry_forward' => true, 'requires_approval' => true, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert claim types
        DB::table('claim_types')->insert([
            ['name' => 'Travel Expenses', 'description' => 'Business travel related expenses', 'max_amount' => 15000.00, 'requires_receipt' => true, 'approval_required' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Meal Allowance', 'description' => 'Meal expenses during business hours', 'max_amount' => 2000.00, 'requires_receipt' => true, 'approval_required' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Office Supplies', 'description' => 'Office equipment and supplies', 'max_amount' => 5000.00, 'requires_receipt' => true, 'approval_required' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Training Fees', 'description' => 'Professional development and training', 'max_amount' => 25000.00, 'requires_receipt' => true, 'approval_required' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Communication', 'description' => 'Phone and internet expenses', 'max_amount' => 2000.00, 'requires_receipt' => true, 'approval_required' => false, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert sample time entries
        DB::table('time_entries')->insert([
            ['employee_id' => 1, 'work_date' => now()->format('Y-m-d'), 'clock_in_time' => '08:00:00', 'clock_out_time' => '17:00:00', 'hours_worked' => 8.00, 'overtime_hours' => 1.00, 'description' => 'Regular work day with overtime', 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'work_date' => now()->format('Y-m-d'), 'clock_in_time' => '08:30:00', 'clock_out_time' => '17:30:00', 'hours_worked' => 8.00, 'overtime_hours' => 1.00, 'description' => 'Extended work for project deadline', 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 3, 'work_date' => now()->subDay()->format('Y-m-d'), 'clock_in_time' => '08:00:00', 'clock_out_time' => '17:00:00', 'hours_worked' => 8.00, 'overtime_hours' => 1.00, 'description' => 'Operations management tasks', 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 4, 'work_date' => now()->subDay()->format('Y-m-d'), 'clock_in_time' => '08:00:00', 'clock_out_time' => '16:00:00', 'hours_worked' => 8.00, 'overtime_hours' => 0.00, 'description' => 'Marketing campaign work', 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 5, 'work_date' => now()->subDays(2)->format('Y-m-d'), 'clock_in_time' => '08:00:00', 'clock_out_time' => '17:00:00', 'hours_worked' => 8.00, 'overtime_hours' => 1.00, 'description' => 'IT support and maintenance', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert sample shifts
        DB::table('shifts')->insert([
            ['employee_id' => 1, 'shift_type_id' => 1, 'date' => now()->addDay()->format('Y-m-d'), 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'shift_type_id' => 1, 'date' => now()->addDay()->format('Y-m-d'), 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 3, 'shift_type_id' => 2, 'date' => now()->addDay()->format('Y-m-d'), 'start_time' => '16:00:00', 'end_time' => '00:00:00', 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 4, 'shift_type_id' => 1, 'date' => now()->addDays(2)->format('Y-m-d'), 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 5, 'shift_type_id' => 4, 'date' => now()->addDays(3)->format('Y-m-d'), 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert sample leave requests
        DB::table('leave_requests')->insert([
            ['employee_id' => 1, 'leave_type_id' => 1, 'start_date' => now()->addWeek()->format('Y-m-d'), 'end_date' => now()->addWeek()->addDays(2)->format('Y-m-d'), 'days_requested' => 3, 'reason' => 'Family vacation', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'leave_type_id' => 2, 'start_date' => now()->addDays(2)->format('Y-m-d'), 'end_date' => now()->addDays(2)->format('Y-m-d'), 'days_requested' => 1, 'reason' => 'Medical appointment', 'status' => 'approved', 'approved_by' => 3, 'approved_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 4, 'leave_type_id' => 1, 'start_date' => now()->addWeeks(2)->format('Y-m-d'), 'end_date' => now()->addWeeks(3)->format('Y-m-d'), 'days_requested' => 7, 'reason' => 'Annual vacation', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert sample claims
        DB::table('claims')->insert([
            ['employee_id' => 1, 'claim_type_id' => 1, 'amount' => 1500.00, 'claim_date' => now()->subDays(3)->format('Y-m-d'), 'description' => 'Business trip to Manila', 'status' => 'approved', 'approved_by' => 3, 'approved_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'claim_type_id' => 2, 'amount' => 250.00, 'claim_date' => now()->subDay()->format('Y-m-d'), 'description' => 'Client lunch meeting', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 4, 'claim_type_id' => 3, 'amount' => 800.00, 'claim_date' => now()->subDays(5)->format('Y-m-d'), 'description' => 'Office equipment purchase', 'status' => 'approved', 'approved_by' => 3, 'approved_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 5, 'claim_type_id' => 4, 'amount' => 5000.00, 'claim_date' => now()->subWeek()->format('Y-m-d'), 'description' => 'IT certification course', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert sample leave balances
        $currentYear = now()->year;
        DB::table('leave_balances')->insert([
            ['employee_id' => 1, 'leave_type_id' => 1, 'year' => $currentYear, 'allocated_days' => 15, 'used_days' => 2, 'remaining_days' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 1, 'leave_type_id' => 2, 'year' => $currentYear, 'allocated_days' => 10, 'used_days' => 1, 'remaining_days' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'leave_type_id' => 1, 'year' => $currentYear, 'allocated_days' => 15, 'used_days' => 5, 'remaining_days' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'leave_type_id' => 2, 'year' => $currentYear, 'allocated_days' => 10, 'used_days' => 1, 'remaining_days' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 3, 'leave_type_id' => 1, 'year' => $currentYear, 'allocated_days' => 15, 'used_days' => 0, 'remaining_days' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 4, 'leave_type_id' => 1, 'year' => $currentYear, 'allocated_days' => 15, 'used_days' => 3, 'remaining_days' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 5, 'leave_type_id' => 1, 'year' => $currentYear, 'allocated_days' => 15, 'used_days' => 1, 'remaining_days' => 14, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Insert sample notifications
        DB::table('employee_notifications')->insert([
            ['employee_id' => 1, 'type' => 'info', 'title' => 'Welcome to HR System', 'message' => 'Welcome to the Jetlouge HR Management System. Please update your profile information.', 'sent_at' => now(), 'priority' => 'medium', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 1, 'type' => 'reminder', 'title' => 'Timesheet Reminder', 'message' => 'Please submit your timesheet for this week.', 'sent_at' => now()->subDay(), 'priority' => 'high', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'type' => 'success', 'title' => 'Leave Approved', 'message' => 'Your sick leave request has been approved.', 'sent_at' => now()->subHours(2), 'priority' => 'medium', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 4, 'type' => 'info', 'title' => 'Training Available', 'message' => 'New digital marketing training is available for enrollment.', 'sent_at' => now()->subDay(), 'priority' => 'low', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 5, 'type' => 'warning', 'title' => 'Pending Timesheet', 'message' => 'Your timesheet is still pending approval.', 'sent_at' => now()->subHours(3), 'priority' => 'medium', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
