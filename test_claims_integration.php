<?php
/**
 * Test Claims Integration
 * This script tests the claims system integration with employees
 */

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\ClaimController;
use Illuminate\Http\Request;

try {
    echo "🧪 Testing Claims Integration...\n\n";
    
    // Create controller instance
    $controller = new ClaimController();
    
    // Test the index method
    echo "📊 Testing ClaimController::index()...\n";
    $response = $controller->index();
    
    // Check if response is a view
    if ($response instanceof \Illuminate\View\View) {
        $data = $response->getData();
        
        echo "✅ Controller returned view successfully\n";
        echo "📋 Data passed to view:\n";
        
        // Check employees data
        if (isset($data['employees'])) {
            $employees = $data['employees'];
            echo "   👥 Employees: " . $employees->count() . " found\n";
            
            if ($employees->count() > 0) {
                echo "   📝 Sample employees:\n";
                foreach ($employees->take(3) as $employee) {
                    echo "      - {$employee->first_name} {$employee->last_name} (ID: {$employee->id})\n";
                }
            } else {
                echo "   ⚠️  No employees found in controller data\n";
            }
        } else {
            echo "   ❌ No employees data passed to view\n";
        }
        
        // Check claim types data
        if (isset($data['claimTypes'])) {
            $claimTypes = $data['claimTypes'];
            echo "   🏷️  Claim Types: " . $claimTypes->count() . " found\n";
            
            if ($claimTypes->count() > 0) {
                echo "   📝 Sample claim types:\n";
                foreach ($claimTypes->take(3) as $claimType) {
                    echo "      - {$claimType->name} ({$claimType->code})\n";
                }
            }
        } else {
            echo "   ❌ No claim types data passed to view\n";
        }
        
        // Check claims data
        if (isset($data['claims'])) {
            $claims = $data['claims'];
            echo "   💰 Claims: " . $claims->count() . " found\n";
        } else {
            echo "   ❌ No claims data passed to view\n";
        }
        
        // Check statistics
        if (isset($data['totalClaims'])) {
            echo "   📈 Statistics:\n";
            echo "      - Total Claims: " . $data['totalClaims'] . "\n";
            echo "      - Pending Claims: " . $data['pendingClaims'] . "\n";
            echo "      - Approved Claims: " . $data['approvedClaims'] . "\n";
            echo "      - Total Amount: $" . number_format($data['totalAmount'], 2) . "\n";
        }
        
    } else {
        echo "❌ Controller did not return a view\n";
    }
    
    // Test direct database connection
    echo "\n🔗 Testing Direct Database Connection...\n";
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test employees table
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
        $employeeCount = $stmt->fetchColumn();
        echo "✅ Direct DB - Active employees: {$employeeCount}\n";
        
        // Test claim_types table
        $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types WHERE is_active = 1");
        $claimTypeCount = $stmt->fetchColumn();
        echo "✅ Direct DB - Active claim types: {$claimTypeCount}\n";
        
        // Test claims table
        $stmt = $pdo->query("SELECT COUNT(*) FROM claims");
        $claimsCount = $stmt->fetchColumn();
        echo "✅ Direct DB - Total claims: {$claimsCount}\n";
        
        // Test foreign key relationship
        $stmt = $pdo->query("
            SELECT c.id, e.first_name, e.last_name, ct.name as claim_type_name 
            FROM claims c 
            LEFT JOIN employees e ON c.employee_id = e.id 
            LEFT JOIN claim_types ct ON c.claim_type_id = ct.id 
            LIMIT 3
        ");
        $sampleClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($sampleClaims) > 0) {
            echo "✅ Foreign key relationships working:\n";
            foreach ($sampleClaims as $claim) {
                echo "   - Claim #{$claim['id']}: {$claim['first_name']} {$claim['last_name']} - {$claim['claim_type_name']}\n";
            }
        } else {
            echo "ℹ️  No claims found to test relationships\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 Integration Test Summary:\n";
    echo "   - Controller: " . (isset($employees) && $employees->count() > 0 ? "✅ Working" : "❌ Issues") . "\n";
    echo "   - Database: " . (isset($employeeCount) && $employeeCount > 0 ? "✅ Working" : "❌ Issues") . "\n";
    echo "   - Employee Integration: " . (isset($employees) && $employees->count() > 0 ? "✅ Fixed" : "❌ Needs Fix") . "\n";
    
    if (isset($employees) && $employees->count() > 0) {
        echo "\n🎉 Claims Employee Integration is working properly!\n";
        echo "💡 The dropdown should now show employees in the claims form.\n";
    } else {
        echo "\n⚠️  Claims Employee Integration needs attention.\n";
        echo "💡 Run fix_claims_employee_integration.php to resolve issues.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test Error: " . $e->getMessage() . "\n";
    echo "📍 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
