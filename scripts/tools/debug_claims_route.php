<?php
/**
 * Debug Claims Route
 */

echo "🔍 Debugging Claims Route...\n\n";

// Check if the simple controller file exists
$controllerPath = __DIR__ . '/app/Http/Controllers/ClaimControllerSimple.php';
if (file_exists($controllerPath)) {
    echo "✅ ClaimControllerSimple.php exists\n";
} else {
    echo "❌ ClaimControllerSimple.php NOT found\n";
}

// Check routes file
$routesPath = __DIR__ . '/routes/web.php';
if (file_exists($routesPath)) {
    echo "✅ routes/web.php exists\n";
    
    $routesContent = file_get_contents($routesPath);
    
    if (strpos($routesContent, 'ClaimControllerSimple') !== false) {
        echo "✅ ClaimControllerSimple found in routes\n";
    } else {
        echo "❌ ClaimControllerSimple NOT found in routes\n";
    }
    
    if (strpos($routesContent, "Route::get('/claims-reimbursement', [ClaimControllerSimple::class, 'index'])") !== false) {
        echo "✅ Claims route uses ClaimControllerSimple\n";
    } else {
        echo "❌ Claims route does NOT use ClaimControllerSimple\n";
        
        // Show what it actually uses
        if (preg_match("/Route::get\('\/claims-reimbursement', \[([^:]+)::class/", $routesContent, $matches)) {
            echo "📋 Current controller: " . $matches[1] . "\n";
        }
    }
} else {
    echo "❌ routes/web.php NOT found\n";
}

echo "\n💡 Next steps:\n";
echo "1. Ensure ClaimControllerSimple is imported in routes/web.php\n";
echo "2. Ensure the route uses ClaimControllerSimple::class\n";
echo "3. Clear cache: php artisan cache:clear\n";
echo "4. Clear routes: php artisan route:clear\n";
echo "5. Test in browser\n";
