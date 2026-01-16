<?php
// Script to remove ESS views directory

$essViewsPath = __DIR__ . '/resources/views/employee_ess_modules';

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

if (is_dir($essViewsPath)) {
    if (deleteDirectory($essViewsPath)) {
        echo "ESS views directory removed successfully.\n";
    } else {
        echo "Failed to remove ESS views directory.\n";
    }
} else {
    echo "ESS views directory not found.\n";
}
?>
