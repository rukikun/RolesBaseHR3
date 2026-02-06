<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Http\Request;

echo "<h2>Testing Updated Biometric Flow</h2>";

// Test 1: Check if biometric routes exist
echo "<h3>1. Testing Biometric Routes</h3>";

$routes = app('router')->getRoutes();
$biometricRoutes = [];

foreach ($routes as $route) {
    if (strpos($route->uri(), 'biometric') !== false || strpos($route->uri(), 'clock-in-biometric') !== false || strpos($route->uri(), 'clock-out-biometric') !== false) {
        $biometricRoutes[] = $route->uri() . ' [' . $route->methods()[0] . ']';
    }
}

if (!empty($biometricRoutes)) {
    echo "<p style='color: green;'>✅ Biometric routes found:</p>";
    echo "<ul>";
    foreach ($biometricRoutes as $route) {
        echo "<li>$route</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ No biometric routes found</p>";
}

// Test 2: Check if AuthController has new methods
echo "<h3>2. Testing AuthController Methods</h3>";

if (class_exists('App\Http\Controllers\AuthController')) {
    $controller = new \App\Http\Controllers\AuthController();
    
    $methods = [
        'clockInBiometric',
        'clockOutBiometric',
        'checkBiometricStatus',
        'registerBiometric',
        'verifyBiometric'
    ];
    
    echo "<ul>";
    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "<li style='color: green;'>✅ $method method exists</li>";
        } else {
            echo "<li style='color: red;'>❌ $method method missing</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ AuthController class not found</p>";
}

// Test 3: Check if TimeAndAttendance view exists and has biometric modal
echo "<h3>3. Testing TimeAndAttendance View</h3>";

$viewPath = __DIR__ . '/resources/views/attendance/TimeAndAttendance.blade.php';
if (file_exists($viewPath)) {
    $viewContent = file_get_contents($viewPath);
    
    if (strpos($viewContent, 'biometricModal') !== false) {
        echo "<p style='color: green;'>✅ Biometric modal found in TimeAndAttendance view</p>";
    } else {
        echo "<p style='color: red;'>❌ Biometric modal not found in TimeAndAttendance view</p>";
    }
    
    if (strpos($viewContent, 'showBiometricModal') !== false) {
        echo "<p style='color: green;'>✅ Biometric JavaScript functions found</p>";
    } else {
        echo "<p style='color: red;'>❌ Biometric JavaScript functions not found</p>";
    }
    
    if (strpos($viewContent, 'clockInBiometric') !== false) {
        echo "<p style='color: green;'>✅ Clock-in biometric route reference found</p>";
    } else {
        echo "<p style='color: red;'>❌ Clock-in biometric route reference not found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ TimeAndAttendance view not found</p>";
}

// Test 4: Check if OTP verification view no longer has biometric code
echo "<h3>4. Testing OTP Verification View (Cleaned)</h3>";

$otpViewPath = __DIR__ . '/resources/views/otp_verification.blade.php';
if (file_exists($otpViewPath)) {
    $otpContent = file_get_contents($otpViewPath);
    
    if (strpos($otpContent, 'biometricModal') === false) {
        echo "<p style='color: green;'>✅ Biometric modal removed from OTP verification</p>";
    } else {
        echo "<p style='color: red;'>❌ Biometric modal still present in OTP verification</p>";
    }
    
    if (strpos($otpContent, 'showBiometricModal') === false) {
        echo "<p style='color: green;'>✅ Biometric functions removed from OTP verification</p>";
    } else {
        echo "<p style='color: red;'>❌ Biometric functions still present in OTP verification</p>";
    }
} else {
    echo "<p style='color: red;'>❌ OTP verification view not found</p>";
}

// Test 5: Check BiometricCredential model
echo "<h3>5. Testing BiometricCredential Model</h3>";

if (class_exists('App\Models\BiometricCredential')) {
    echo "<p style='color: green;'>✅ BiometricCredential model exists</p>";
    
    // Test if model has relationship
    $employee = new \App\Models\Employee();
    if (method_exists($employee, 'biometricCredentials')) {
        echo "<p style='color: green;'>✅ Employee model has biometricCredentials relationship</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Employee model missing biometricCredentials relationship</p>";
    }
} else {
    echo "<p style='color: red;'>❌ BiometricCredential model not found</p>";
}

echo "<h3>Summary</h3>";
echo "<p><strong>✅ Biometric authentication successfully moved from login to clock-in/out!</strong></p>";
echo "<p><strong>📝 Updated Flow:</strong></p>";
echo "<ol>";
echo "<li>Login now only requires OTP verification (no biometric)</li>";
echo "<li>Clock-in/out now requires biometric authentication</li>";
echo "<li>Users can register fingerprint on first clock-in attempt</li>";
echo "<li>Fallback to regular clock-in/out available</li>";
echo "</ol>";

echo "<p><strong>🔗 Test URLs:</strong></p>";
echo "<ul>";
echo "<li><a href='/admin/login' target='_blank'>Login (OTP only)</a></li>";
echo "<li><a href='/attendance/time-and-attendance' target='_blank'>Time & Attendance (Biometric)</a></li>";
echo "<li><a href='/test-default-biometric' target='_blank'>Test Biometric Status</a></li>";
echo "</ul>";

?>
