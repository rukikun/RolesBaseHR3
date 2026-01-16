<?php
// Script to remove ESS-related assets

$essAssets = [
    __DIR__ . '/public/assets/js/ess-modal-system.js',
    __DIR__ . '/public/assets/css/working-modal-ess.css',
    __DIR__ . '/public/assets/css/ess-modal-system.css',
    __DIR__ . '/public/assets/css/employee-ess-clean.css'
];

foreach ($essAssets as $asset) {
    if (file_exists($asset)) {
        if (unlink($asset)) {
            echo "Removed: " . basename($asset) . "\n";
        } else {
            echo "Failed to remove: " . basename($asset) . "\n";
        }
    } else {
        echo "Not found: " . basename($asset) . "\n";
    }
}

echo "ESS assets cleanup completed.\n";
?>
