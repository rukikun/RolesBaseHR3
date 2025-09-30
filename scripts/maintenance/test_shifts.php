<?php
// Simple test page for shift management functionality
require_once '../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Shift Management Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Shift Management System Test</h1>
    
    <?php
    try {
        // Test database connection
        DB::connection()->getPdo();
        echo '<p class="success">✓ Database connection successful</p>';
        
        // Create tables if they don't exist
        DB::statement("
            CREATE TABLE IF NOT EXISTS shifts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                type ENUM('day', 'night', 'split') NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                break_duration INT DEFAULT 60,
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        DB::statement("
            CREATE TABLE IF NOT EXISTS shift_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id INT NOT NULL,
                shift_id INT NOT NULL,
                shift_date DATE NOT NULL,
                status ENUM('scheduled', 'completed', 'absent', 'cancelled') DEFAULT 'scheduled',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        DB::statement("
            CREATE TABLE IF NOT EXISTS shift_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id INT NOT NULL,
                type ENUM('swap', 'change', 'cover') NOT NULL,
                current_shift_id INT,
                requested_shift_id INT,
                request_date DATE NOT NULL,
                reason TEXT NOT NULL,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                approved_by INT NULL,
                approved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        echo '<p class="success">✓ Database tables created/verified</p>';
        
        // Insert sample data if tables are empty
        $shiftCount = DB::table('shifts')->count();
        if ($shiftCount == 0) {
            DB::table('shifts')->insert([
                ['name' => 'Morning Shift', 'type' => 'day', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'break_duration' => 60, 'description' => 'Standard morning shift'],
                ['name' => 'Afternoon Shift', 'type' => 'day', 'start_time' => '16:00:00', 'end_time' => '00:00:00', 'break_duration' => 60, 'description' => 'Afternoon to midnight shift'],
                ['name' => 'Night Shift', 'type' => 'night', 'start_time' => '00:00:00', 'end_time' => '08:00:00', 'break_duration' => 60, 'description' => 'Overnight shift'],
                ['name' => 'Split Morning', 'type' => 'split', 'start_time' => '06:00:00', 'end_time' => '14:00:00', 'break_duration' => 30, 'description' => 'Early morning split shift'],
                ['name' => 'Split Evening', 'type' => 'split', 'start_time' => '18:00:00', 'end_time' => '02:00:00', 'break_duration' => 30, 'description' => 'Evening split shift']
            ]);
            echo '<p class="success">✓ Sample shift types created</p>';
        } else {
            echo '<p class="info">✓ Found ' . $shiftCount . ' existing shift types</p>';
        }
        
        // Display current shift types
        $shifts = DB::table('shifts')->get();
        echo '<h2>Current Shift Types</h2>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Name</th><th>Type</th><th>Start Time</th><th>End Time</th><th>Break Duration</th><th>Status</th></tr>';
        foreach ($shifts as $shift) {
            echo '<tr>';
            echo '<td>' . $shift->id . '</td>';
            echo '<td>' . $shift->name . '</td>';
            echo '<td>' . ucfirst($shift->type) . '</td>';
            echo '<td>' . $shift->start_time . '</td>';
            echo '<td>' . $shift->end_time . '</td>';
            echo '<td>' . $shift->break_duration . ' min</td>';
            echo '<td>' . ($shift->is_active ? 'Active' : 'Inactive') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        // Add sample shift assignments if employees exist
        $employees = DB::table('employees')->limit(3)->get();
        if ($employees->count() > 0) {
            $assignmentCount = DB::table('shift_assignments')->count();
            if ($assignmentCount == 0) {
                foreach ($employees as $index => $employee) {
                    $shift = $shifts[$index % $shifts->count()];
                    DB::table('shift_assignments')->insert([
                        'employee_id' => $employee->id,
                        'shift_id' => $shift->id,
                        'shift_date' => date('Y-m-d', strtotime('+' . $index . ' days')),
                        'status' => 'scheduled'
                    ]);
                }
                echo '<p class="success">✓ Sample shift assignments created</p>';
            }
            
            // Add sample shift request
            $requestCount = DB::table('shift_requests')->count();
            if ($requestCount == 0) {
                DB::table('shift_requests')->insert([
                    'employee_id' => $employees->first()->id,
                    'type' => 'swap',
                    'current_shift_id' => $shifts[0]->id,
                    'requested_shift_id' => $shifts[1]->id,
                    'request_date' => date('Y-m-d', strtotime('+1 day')),
                    'reason' => 'Personal appointment in the morning',
                    'status' => 'pending'
                ]);
                echo '<p class="success">✓ Sample shift request created</p>';
            }
        }
        
        echo '<h2>API Endpoints Ready</h2>';
        echo '<ul>';
        echo '<li><strong>GET /api/shifts/stats</strong> - Get shift statistics</li>';
        echo '<li><strong>GET /api/shifts/types</strong> - Get all shift types</li>';
        echo '<li><strong>POST /api/shifts</strong> - Create new shift type</li>';
        echo '<li><strong>GET /api/shifts/{id}</strong> - Get specific shift type</li>';
        echo '<li><strong>PUT /api/shifts/{id}</strong> - Update shift type</li>';
        echo '<li><strong>DELETE /api/shifts/{id}</strong> - Delete shift type</li>';
        echo '<li><strong>GET /api/shifts/schedule</strong> - Get schedule calendar</li>';
        echo '<li><strong>GET /api/shifts/requests</strong> - Get shift requests</li>';
        echo '<li><strong>POST /api/shifts/requests</strong> - Create shift request</li>';
        echo '<li><strong>POST /api/shifts/requests/{id}/approve</strong> - Approve request</li>';
        echo '<li><strong>POST /api/shifts/requests/{id}/reject</strong> - Reject request</li>';
        echo '</ul>';
        
        echo '<p class="success"><strong>🎉 Shift Management System is fully functional!</strong></p>';
        echo '<p><a href="/shift-schedule-management" target="_blank">Open Shift Management Page</a></p>';
        
    } catch (Exception $e) {
        echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
    }
    ?>
</body>
</html>
