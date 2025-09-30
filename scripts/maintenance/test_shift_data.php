<?php
// Direct test of shift data display

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Shift Data Test</h2>";
    
    // Get shift types
    $stmt = $pdo->query("SELECT * FROM shift_types ORDER BY name");
    $shiftTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Database has " . count($shiftTypes) . " shift types</p>";
    
    if (count($shiftTypes) > 0) {
        echo "<h3>Raw Database Data:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Start Time</th><th>End Time</th><th>Break</th><th>Rate</th><th>Active</th></tr>";
        
        foreach ($shiftTypes as $type) {
            echo "<tr>";
            echo "<td>{$type->id}</td>";
            echo "<td>{$type->name}</td>";
            echo "<td>{$type->type}</td>";
            echo "<td>{$type->default_start_time}</td>";
            echo "<td>{$type->default_end_time}</td>";
            echo "<td>{$type->break_duration}</td>";
            echo "<td>{$type->hourly_rate}</td>";
            echo "<td>" . ($type->is_active ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Formatted Display (Like Blade Template):</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Name</th><th>Type</th><th>Start Time</th><th>End Time</th><th>Break Duration</th><th>Status</th></tr>";
        
        foreach ($shiftTypes as $shiftType) {
            $typeColor = ($shiftType->type === 'regular') ? 'primary' : 
                        (($shiftType->type === 'overnight') ? 'dark' : 
                        (($shiftType->type === 'overtime') ? 'warning' : 'info'));
            
            $startTime = date('g:i A', strtotime($shiftType->default_start_time));
            $endTime = date('g:i A', strtotime($shiftType->default_end_time));
            $statusColor = $shiftType->is_active ? 'success' : 'danger';
            $statusText = $shiftType->is_active ? 'Active' : 'Inactive';
            
            echo "<tr>";
            echo "<td>{$shiftType->name}</td>";
            echo "<td><span style='background-color: blue; color: white; padding: 2px 6px; border-radius: 3px;'>" . ucfirst($shiftType->type) . "</span></td>";
            echo "<td>{$startTime}</td>";
            echo "<td>{$endTime}</td>";
            echo "<td>{$shiftType->break_duration} min</td>";
            echo "<td><span style='background-color: " . ($shiftType->is_active ? 'green' : 'red') . "; color: white; padding: 2px 6px; border-radius: 3px;'>{$statusText}</span></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Laravel Collection Test:</h3>";
        $collection = collect($shiftTypes);
        echo "<p>Collection count: " . $collection->count() . "</p>";
        echo "<p>Collection is empty: " . ($collection->isEmpty() ? 'YES' : 'NO') . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ No shift types found in database!</p>";
        echo "<p>Run the clean_shift_types.sql script first.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/shift-schedule-management'>Go to Shift Schedule Management Page</a></p>";
echo "<p>Check the page source to see debug comments</p>";
?>
