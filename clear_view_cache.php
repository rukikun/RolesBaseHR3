<?php
// Script to clear cached view files

$viewCachePath = __DIR__ . '/storage/framework/views';

if (is_dir($viewCachePath)) {
    $files = glob($viewCachePath . '/*.php');
    $deletedCount = 0;
    
    foreach ($files as $file) {
        if (unlink($file)) {
            $deletedCount++;
        }
    }
    
    echo "Cleared {$deletedCount} cached view files.\n";
} else {
    echo "View cache directory not found.\n";
}
?>
