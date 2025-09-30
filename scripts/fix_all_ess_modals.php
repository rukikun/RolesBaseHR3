<?php
/**
 * Fix All ESS Modal Files
 * Applies working-modal CSS and JavaScript to all Employee Self-Service files
 */

$essDirectory = __DIR__ . '/../resources/views/employee_ess_modules/';
$cssInclude = '<link rel="stylesheet" href="{{ asset(\'assets/css/working-modal-ess.css\') }}">';
$jsInclude = '<script src="{{ asset(\'assets/js/working-modal-ess.js\') }}"></script>';

// Files that need modal fixes
$filesToFix = [
    'leave_management.blade.php',
    'shift_request.blade.php', 
    'timesheet_management.blade.php',
    'timesheet_history.blade.php',
    'employee_schedule.blade.php',
    'create_claim.blade.php',
    'leave_balance.blade.php',
    'shift_schedule.blade.php',
    'employee_profile.blade.php'
];

$fixedFiles = [];
$skippedFiles = [];

foreach ($filesToFix as $filename) {
    $filepath = $essDirectory . $filename;
    
    if (!file_exists($filepath)) {
        $skippedFiles[] = $filename . ' (file not found)';
        continue;
    }
    
    $content = file_get_contents($filepath);
    $originalContent = $content;
    
    // Add CSS include if not present
    if (strpos($content, 'working-modal-ess.css') === false) {
        // Find head section and add CSS
        if (strpos($content, '</head>') !== false) {
            $content = str_replace('</head>', "  $cssInclude\n</head>", $content);
        } elseif (strpos($content, '@section(\'styles\')') !== false) {
            $content = str_replace('@section(\'styles\')', "@section('styles')\n$cssInclude", $content);
        }
    }
    
    // Add JS include if not present
    if (strpos($content, 'working-modal-ess.js') === false) {
        // Find end of body or scripts section
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', "  $jsInclude\n</body>", $content);
        } elseif (strpos($content, '@endsection') !== false && strpos($content, '@section(\'scripts\')') !== false) {
            $content = str_replace('@endsection', "$jsInclude\n@endsection", $content);
        } elseif (strpos($content, '</script>') !== false) {
            // Add after last script tag
            $lastScriptPos = strrpos($content, '</script>');
            if ($lastScriptPos !== false) {
                $content = substr_replace($content, "</script>\n$jsInclude", $lastScriptPos, 9);
            }
        }
    }
    
    // Convert Bootstrap modals to working modals
    $modalPatterns = [
        // Modal container
        '/class="modal fade"/' => 'class="working-modal" style="display: none;"',
        '/class="modal"/' => 'class="working-modal" style="display: none;"',
        
        // Modal dialog
        '/class="modal-dialog([^"]*)"/' => 'class="working-modal-dialog$1"',
        
        // Modal content
        '/class="modal-content"/' => 'class="working-modal-content"',
        
        // Modal header
        '/class="modal-header"/' => 'class="working-modal-header"',
        
        // Modal title
        '/class="modal-title"/' => 'class="working-modal-title"',
        
        // Modal body
        '/class="modal-body"/' => 'class="working-modal-body"',
        
        // Modal footer
        '/class="modal-footer"/' => 'class="working-modal-footer"',
        
        // Close button
        '/data-bs-dismiss="modal"/' => 'onclick="closeWorkingModal(this.closest(\'.working-modal\').id)"',
        '/class="btn-close"/' => 'class="working-modal-close"',
    ];
    
    foreach ($modalPatterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Add backdrop div after modal opening tag
    $content = preg_replace(
        '/(<div class="working-modal"[^>]*>)/',
        '$1' . "\n" . '    <div class="working-modal-backdrop" onclick="closeWorkingModal(this.closest(\'.working-modal\').id)"></div>',
        $content
    );
    
    // Convert Bootstrap modal triggers
    $content = preg_replace(
        '/data-bs-toggle="modal" data-bs-target="#([^"]+)"/',
        'onclick="openWorkingModal(\'$1\')"',
        $content
    );
    
    // Only save if content changed
    if ($content !== $originalContent) {
        file_put_contents($filepath, $content);
        $fixedFiles[] = $filename;
    } else {
        $skippedFiles[] = $filename . ' (no changes needed)';
    }
}

// Output results
echo "ESS Modal Fix Results:\n";
echo "=====================\n\n";

if (!empty($fixedFiles)) {
    echo "✅ Fixed Files (" . count($fixedFiles) . "):\n";
    foreach ($fixedFiles as $file) {
        echo "   - $file\n";
    }
    echo "\n";
}

if (!empty($skippedFiles)) {
    echo "⏭️  Skipped Files (" . count($skippedFiles) . "):\n";
    foreach ($skippedFiles as $file) {
        echo "   - $file\n";
    }
    echo "\n";
}

echo "🎉 ESS Modal fix completed!\n";
echo "\nFiles now include:\n";
echo "   - working-modal-ess.css (comprehensive modal styling)\n";
echo "   - working-modal-ess.js (modal functionality)\n";
echo "\nAll Bootstrap modals converted to working-modal format.\n";
echo "Emergency cleanup available with Ctrl+Shift+M\n";
?>
