<?php
/**
 * ESS Modal Verification Script
 * Checks that all ESS files have proper working-modal implementation
 */

$essDirectory = __DIR__ . '/../resources/views/employee_ess_modules/';
$results = [];

// Files to check
$filesToCheck = [
    'employee_dashboard.blade.php',
    'claims_reimbursement.blade.php', 
    'leave_management.blade.php',
    'shift_request.blade.php',
    'timesheet_management.blade.php',
    'timesheet_history.blade.php',
    'employee_schedule.blade.php',
    'create_claim.blade.php',
    'leave_balance.blade.php'
];

foreach ($filesToCheck as $filename) {
    $filepath = $essDirectory . $filename;
    
    if (!file_exists($filepath)) {
        $results[$filename] = ['status' => 'missing', 'issues' => ['File not found']];
        continue;
    }
    
    $content = file_get_contents($filepath);
    $issues = [];
    $features = [];
    
    // Check for CSS include
    if (strpos($content, 'working-modal-ess.css') !== false) {
        $features[] = '✅ CSS included';
    } else {
        $issues[] = '❌ Missing working-modal-ess.css';
    }
    
    // Check for JS include
    if (strpos($content, 'working-modal-ess.js') !== false) {
        $features[] = '✅ JS included';
    } else {
        $issues[] = '❌ Missing working-modal-ess.js';
    }
    
    // Check for working-modal classes
    if (strpos($content, 'working-modal') !== false) {
        $features[] = '✅ Working modal classes found';
    } else {
        $issues[] = '❌ No working-modal classes';
    }
    
    // Check for proper modal structure
    if (strpos($content, 'working-modal-backdrop') !== false) {
        $features[] = '✅ Modal backdrop implemented';
    } else {
        $issues[] = '❌ Missing modal backdrop';
    }
    
    // Check for close functions
    if (strpos($content, 'closeWorkingModal') !== false) {
        $features[] = '✅ Close functions implemented';
    } else {
        $issues[] = '❌ Missing close functions';
    }
    
    // Check for Bootstrap modal remnants
    if (strpos($content, 'data-bs-toggle="modal"') !== false) {
        $issues[] = '⚠️ Bootstrap modal triggers still present';
    }
    
    if (strpos($content, 'class="modal fade"') !== false) {
        $issues[] = '⚠️ Bootstrap modal classes still present';
    }
    
    // Determine overall status
    $status = empty($issues) ? 'perfect' : (count($issues) <= 2 ? 'good' : 'needs_work');
    
    $results[$filename] = [
        'status' => $status,
        'features' => $features,
        'issues' => $issues
    ];
}

// Generate report
echo "ESS Modal Verification Report\n";
echo "============================\n\n";

$perfectCount = 0;
$goodCount = 0;
$needsWorkCount = 0;
$missingCount = 0;

foreach ($results as $filename => $result) {
    $statusIcon = [
        'perfect' => '🟢',
        'good' => '🟡', 
        'needs_work' => '🔴',
        'missing' => '⚫'
    ][$result['status']];
    
    echo "$statusIcon $filename\n";
    
    if (isset($result['features'])) {
        foreach ($result['features'] as $feature) {
            echo "   $feature\n";
        }
    }
    
    if (!empty($result['issues'])) {
        foreach ($result['issues'] as $issue) {
            echo "   $issue\n";
        }
    }
    
    echo "\n";
    
    // Count statuses
    switch ($result['status']) {
        case 'perfect': $perfectCount++; break;
        case 'good': $goodCount++; break;
        case 'needs_work': $needsWorkCount++; break;
        case 'missing': $missingCount++; break;
    }
}

// Summary
echo "Summary:\n";
echo "--------\n";
echo "🟢 Perfect: $perfectCount files\n";
echo "🟡 Good: $goodCount files\n"; 
echo "🔴 Needs Work: $needsWorkCount files\n";
echo "⚫ Missing: $missingCount files\n";
echo "\nTotal Files Checked: " . count($results) . "\n";

// Overall assessment
$totalGood = $perfectCount + $goodCount;
$totalFiles = count($results);
$percentage = round(($totalGood / $totalFiles) * 100);

echo "\nOverall Status: $percentage% of ESS files have working modals\n";

if ($percentage >= 90) {
    echo "🎉 Excellent! ESS modal system is ready for production.\n";
} elseif ($percentage >= 70) {
    echo "👍 Good progress! Minor fixes needed.\n";
} else {
    echo "⚠️ More work needed to complete modal fixes.\n";
}

// Check core files exist
echo "\nCore Files Check:\n";
echo "-----------------\n";

$coreFiles = [
    'public/assets/css/working-modal-ess.css',
    'public/assets/js/working-modal-ess.js'
];

foreach ($coreFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $size = round(filesize($fullPath) / 1024, 1);
        echo "✅ $file ({$size}KB)\n";
    } else {
        echo "❌ $file (missing)\n";
    }
}

echo "\n🔧 Emergency cleanup available with Ctrl+Shift+M in browser\n";
?>
