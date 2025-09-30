<?php
// Test login form submission

echo "🔍 Testing Employee Login Form Submission\n";
echo "========================================\n\n";

$baseUrl = 'http://127.0.0.1:8000';
$loginUrl = $baseUrl . '/employee/login';

// Test credentials
$credentials = [
    'email' => 'test.login@jetlouge.com',
    'password' => 'password123'
];

echo "Testing login with:\n";
echo "Email: {$credentials['email']}\n";
echo "Password: {$credentials['password']}\n\n";

// First, get the login page to extract CSRF token
echo "1. Getting login page for CSRF token...\n";

$loginPageContext = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$loginPage = file_get_contents($loginUrl, false, $loginPageContext);

if ($loginPage === false) {
    echo "   ❌ Failed to load login page\n";
    exit(1);
}

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginPage, $matches);
$csrfToken = $matches[1] ?? null;

if ($csrfToken) {
    echo "   ✅ CSRF token extracted: " . substr($csrfToken, 0, 20) . "...\n";
} else {
    echo "   ❌ CSRF token not found\n";
    exit(1);
}

// Prepare POST data
$postData = http_build_query([
    '_token' => $csrfToken,
    'email' => $credentials['email'],
    'password' => $credentials['password']
]);

// Submit login form
echo "\n2. Submitting login form...\n";

$postContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData)
        ],
        'content' => $postData,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($loginUrl, false, $postContext);
$responseHeaders = $http_response_header ?? [];

echo "   📊 Response Headers:\n";
foreach ($responseHeaders as $header) {
    echo "      {$header}\n";
    
    // Check for redirect
    if (strpos($header, 'Location:') === 0) {
        $redirectUrl = trim(substr($header, 9));
        echo "   🔄 Redirect detected to: {$redirectUrl}\n";
        
        if (strpos($redirectUrl, '/employee/dashboard') !== false) {
            echo "   ✅ Login successful - redirecting to dashboard!\n";
        } else {
            echo "   ⚠️  Unexpected redirect location\n";
        }
    }
}

// Check response content for errors
if ($response && strpos($response, 'credentials do not match') !== false) {
    echo "   ❌ Login failed - credentials do not match\n";
} elseif ($response && strpos($response, 'Sign In to Your Account') !== false) {
    echo "   ❌ Login failed - returned to login page\n";
} else {
    echo "   ✅ Login appears successful\n";
}

echo "\n🎯 MANUAL TEST:\n";
echo "==============\n";
echo "1. Open browser: http://127.0.0.1:8000/portal-selection\n";
echo "2. Click 'Employee Portal'\n";
echo "3. Use credentials:\n";
echo "   Email: test.login@jetlouge.com\n";
echo "   Password: password123\n";
echo "\nIf it still fails, check:\n";
echo "- Browser developer tools for JavaScript errors\n";
echo "- Laravel logs in storage/logs/\n";
echo "- Clear browser cookies/cache\n";

?>
