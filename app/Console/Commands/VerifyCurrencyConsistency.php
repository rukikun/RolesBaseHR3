<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerifyCurrencyConsistency extends Command
{
    protected $signature = 'verify:currency-consistency';
    protected $description = 'Verify that all view files consistently use peso signs instead of dollar signs';

    public function handle()
    {
        $this->info('🔍 Verifying currency consistency across view files...');
        $this->info('');

        $viewFiles = [
            'resources/views/claims/reimbursement.blade.php',
            'resources/views/claims/validate_attachment.blade.php',
            'resources/views/timesheets/management.blade.php',
            'resources/views/employee_ess_modules/claims_reimbursement.blade.php',
            'resources/views/employee_ess_modules/shift_schedule.blade.php',
            'resources/views/employee_ess_modules/shift_schedule_management.blade.php',
            'resources/views/landing/index.blade.php'
        ];

        $issuesFound = false;

        foreach ($viewFiles as $file) {
            $fullPath = base_path($file);
            
            if (!File::exists($fullPath)) {
                $this->warn("⚠️  File not found: {$file}");
                continue;
            }

            $content = File::get($fullPath);
            
            // Check for dollar signs in currency contexts (excluding PHP variables and JavaScript)
            $dollarMatches = [];
            preg_match_all('/\$\{\{\s*number_format\(|\$[0-9,]+\.?[0-9]*(?!\s*\})|>\$[0-9,]+\.?[0-9]*</', $content, $dollarMatches);
            
            if (!empty($dollarMatches[0])) {
                $this->error("❌ Found dollar signs in: {$file}");
                foreach ($dollarMatches[0] as $match) {
                    $this->line("   - {$match}");
                }
                $issuesFound = true;
            } else {
                $this->info("✅ {$file} - No dollar signs found");
            }

            // Check for peso signs
            $pesoCount = substr_count($content, '₱');
            if ($pesoCount > 0) {
                $this->info("   💰 Found {$pesoCount} peso signs (₱)");
            }
        }

        $this->info('');
        
        if ($issuesFound) {
            $this->error('❌ Currency consistency issues found! Please fix the dollar signs above.');
            return 1;
        } else {
            $this->info('✅ All view files are consistent - using peso signs (₱) correctly!');
            return 0;
        }
    }
}
