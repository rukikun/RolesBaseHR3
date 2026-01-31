<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$output = "Testing HR4 API...\n\n";

$accountsUrl = 'https://hr4.jetlougetravels-ph.com/api/accounts';

$response = Http::timeout(30)
    ->withOptions(['verify' => false])
    ->get($accountsUrl);

$responseData = $response->json();

$output .= "Top-level keys: " . implode(', ', array_keys($responseData ?? [])) . "\n\n";

// Check if message contains the data
if (isset($responseData['message']) && is_array($responseData['message'])) {
    $output .= "Message is an array with keys: " . implode(', ', array_keys($responseData['message'])) . "\n\n";
    
    if (isset($responseData['message']['system_accounts'])) {
        $accounts = $responseData['message']['system_accounts'];
        $output .= "Found system_accounts in message! Count: " . count($accounts) . "\n\n";
        
        // Find target email
        $targetEmail = 'johnkaizer19.jh@gmail.com';
        foreach ($accounts as $acc) {
            $email = isset($acc['employee']['email']) ? $acc['employee']['email'] : null;
            $password = isset($acc['password']) ? $acc['password'] : null;
            $accId = isset($acc['id']) ? $acc['id'] : 'N/A';
            $output .= "Account ID: " . $accId . " | Email: " . ($email ? $email : 'N/A') . " | Password: " . ($password ? $password : 'N/A') . "\n";
            
            if ($email && strtolower($email) === strtolower($targetEmail)) {
                $output .= ">>> FOUND TARGET ACCOUNT!\n";
            }
        }
    }
} else {
    $output .= "Message is not an array or not set\n";
}

file_put_contents('hr4_test_output.txt', $output);
echo "Output written to hr4_test_output.txt\n";
