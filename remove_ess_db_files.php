<?php
// Script to remove ESS-related database setup files

$essDbFiles = [
    __DIR__ . '/database/setup/setup_ess_complete.sql',
    __DIR__ . '/database/setup/setup_ess_database_separate.sql',
    __DIR__ . '/database/setup/setup_ess_sample_data.sql',
    __DIR__ . '/database/setup/setup_ess_sample_data_safe.sql'
];

foreach ($essDbFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "Removed: " . basename($file) . "\n";
        } else {
            echo "Failed to remove: " . basename($file) . "\n";
        }
    } else {
        echo "Not found: " . basename($file) . "\n";
    }
}

echo "ESS database files cleanup completed.\n";
?>
