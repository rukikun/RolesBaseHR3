<?php
/**
 * Direct Test of Claims Controller
 */

// Set up basic Laravel environment
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the controller directly
use App\Http\Controllers\ClaimControllerSimple;

echo "🧪 Testing ClaimControllerSimple directly...\n\n";

try {
    $controller = new ClaimControllerSimple();
    $response = $controller->index();
    
    if ($response instanceof Illuminate\View\View) {
        $data = $response->getData();
        
        echo "✅ Controller works!\n";
        echo "📊 Data returned:\n";
        
        if (isset($data['employees'])) {
            $employees = $data['employees'];
            echo "   👥 Employees (" . $employees->count() . "):\n";
            foreach ($employees as $employee) {
                echo "      - ID: {$employee->id}, Name: {$employee->first_name} {$employee->last_name}\n";
            }
        }
        
        if (isset($data['claimTypes'])) {
            $claimTypes = $data['claimTypes'];
            echo "   🏷️  Claim Types (" . $claimTypes->count() . "):\n";
            foreach ($claimTypes as $claimType) {
                echo "      - ID: {$claimType->id}, Name: {$claimType->name} ({$claimType->code})\n";
            }
        }
        
        echo "\n✅ The controller is working properly!\n";
        echo "💡 The issue might be:\n";
        echo "   1. Browser cache - try hard refresh (Ctrl+F5)\n";
        echo "   2. Route cache - already cleared\n";
        echo "   3. Different route being hit\n";
        echo "   4. Middleware redirecting\n\n";
        
    } else {
        echo "❌ Controller returned: " . gettype($response) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "🎯 Try accessing: http://hr3system.test/claims-reimbursement\n";
echo "🔄 If still showing error, try hard refresh (Ctrl+F5)\n";
