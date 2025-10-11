<?php

echo "=== SIMPLE SERVER TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n\n";

// Test 1: Check if .env file exists
echo "1. ENVIRONMENT FILE CHECK:\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists\n";
    
    // Read .env file
    $envContent = file_get_contents($envFile);
    $envLines = explode("\n", $envContent);
    
    $mailSettings = [];
    foreach ($envLines as $line) {
        $line = trim($line);
        if (strpos($line, 'MAIL_') === 0) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = $parts[0];
                $value = $parts[1];
                if ($key === 'MAIL_PASSWORD') {
                    $value = $value ? 'SET (' . strlen($value) . ' chars)' : 'NOT SET';
                }
                $mailSettings[$key] = $value;
            }
        }
    }
    
    if (!empty($mailSettings)) {
        echo "Email settings found:\n";
        foreach ($mailSettings as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    } else {
        echo "❌ No email settings found in .env file\n";
    }
    
} else {
    echo "❌ .env file not found\n";
    echo "Expected location: {$envFile}\n";
}

// Test 2: Check PHP extensions
echo "\n2. PHP EXTENSIONS CHECK:\n";
$requiredExtensions = ['openssl', 'curl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ {$ext} extension loaded\n";
    } else {
        echo "❌ {$ext} extension missing\n";
    }
}

// Test 3: Check if vendor directory exists
echo "\n3. COMPOSER DEPENDENCIES:\n";
$vendorDir = __DIR__ . '/vendor';
if (is_dir($vendorDir)) {
    echo "✅ Vendor directory exists\n";
    
    // Check for PHPMailer
    $phpmailerPath = $vendorDir . '/phpmailer/phpmailer';
    if (is_dir($phpmailerPath)) {
        echo "✅ PHPMailer installed\n";
    } else {
        echo "❌ PHPMailer not found\n";
    }
} else {
    echo "❌ Vendor directory missing\n";
    echo "Run: composer install\n";
}

// Test 4: Test basic SMTP connection
echo "\n4. SMTP CONNECTION TEST:\n";
if (!empty($mailSettings['MAIL_HOST'])) {
    $host = str_replace('"', '', $mailSettings['MAIL_HOST']);
    $port = str_replace('"', '', $mailSettings['MAIL_PORT'] ?? '587');
    
    echo "Testing connection to {$host}:{$port}...\n";
    
    $connection = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($connection) {
        echo "✅ SMTP server reachable\n";
        fclose($connection);
    } else {
        echo "❌ Cannot connect to SMTP server\n";
        echo "Error: {$errstr} ({$errno})\n";
    }
} else {
    echo "❌ No MAIL_HOST configured\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
if (!file_exists($envFile)) {
    echo "1. Create .env file with email configuration\n";
}
if (empty($mailSettings)) {
    echo "2. Add email settings to .env file\n";
}
if (!is_dir($vendorDir)) {
    echo "3. Run 'composer install' to install dependencies\n";
}

echo "\n=== TEST COMPLETED ===\n";

?>
