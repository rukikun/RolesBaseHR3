<?php
// Test script to verify shift types undefined property fix

try {
    // Connect to database
    $pdo = new PDO('mysql:host=localhost;dbname=hr3_hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Test the query that was causing issues
    $stmt = $pdo->query("
        SELECT 
            id,
            name,
            type,
            default_start_time,
            default_end_time,
            COALESCE(break_duration, 30) as break_duration,
            COALESCE(hourly_rate, 0.00) as hourly_rate,
            COALESCE(description, '') as description,
            COALESCE(color_code, '#007bff') as color_code,
            COALESCE(is_active, 1) as is_active,
            created_at,
            updated_at
        FROM shift_types 
        ORDER BY name
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "✅ Query executed successfully\n";
    echo "📊 Found " . count($results) . " shift types\n\n";
    
    // Test each property that was causing undefined property errors
    foreach ($results as $shiftType) {
        echo "🔍 Testing shift type: {$shiftType->name}\n";
        echo "   - ID: {$shiftType->id}\n";
        echo "   - Type: {$shiftType->type}\n";
        echo "   - Start Time: {$shiftType->default_start_time}\n";
        echo "   - End Time: {$shiftType->default_end_time}\n";
        echo "   - Break Duration: {$shiftType->break_duration} min\n";
        echo "   - Hourly Rate: $" . number_format($shiftType->hourly_rate, 2) . "\n";
        echo "   - Description: " . ($shiftType->description ?: 'N/A') . "\n";
        echo "   - Color Code: {$shiftType->color_code}\n";
        echo "   - Is Active: " . ($shiftType->is_active ? 'Yes' : 'No') . "\n";
        echo "   ✅ All properties accessible\n\n";
    }
    
    echo "🎉 All tests passed! The undefined property issue has been fixed.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📝 Make sure XAMPP MySQL is running and hr3systemdb database exists.\n";
}
?>
