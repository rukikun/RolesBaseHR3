<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create claim_types table if it doesn't exist
        if (!Schema::hasTable('claim_types')) {
            Schema::create('claim_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 10)->unique();
                $table->text('description')->nullable();
                $table->decimal('max_amount', 10, 2)->nullable();
                $table->boolean('requires_attachment')->default(false);
                $table->boolean('auto_approve')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Indexes
                $table->index(['is_active', 'name']);
            });
        }
        
        // Insert default claim types
        $defaultClaimTypes = [
            [
                'name' => 'Travel Expenses',
                'code' => 'TRAVEL',
                'description' => 'Business travel related expenses including transportation, accommodation, and meals',
                'max_amount' => 1000.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFICE',
                'description' => 'Office supplies and equipment purchases',
                'max_amount' => 500.00,
                'requires_attachment' => true,
                'auto_approve' => true,
                'is_active' => true
            ],
            [
                'name' => 'Meal Allowance',
                'code' => 'MEAL',
                'description' => 'Daily meal allowances and business dining expenses',
                'max_amount' => 50.00,
                'requires_attachment' => false,
                'auto_approve' => true,
                'is_active' => true
            ],
            [
                'name' => 'Transportation',
                'code' => 'TRANSPORT',
                'description' => 'Local transportation expenses including taxi, bus, and parking fees',
                'max_amount' => 100.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true
            ],
            [
                'name' => 'Training & Development',
                'code' => 'TRAINING',
                'description' => 'Professional development courses, certifications, and training materials',
                'max_amount' => 2000.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true
            ],
            [
                'name' => 'Medical Expenses',
                'code' => 'MEDICAL',
                'description' => 'Medical and health-related expenses covered by company policy',
                'max_amount' => 500.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true
            ],
            [
                'name' => 'Phone & Internet',
                'code' => 'TELECOM',
                'description' => 'Business phone and internet expenses',
                'max_amount' => 150.00,
                'requires_attachment' => true,
                'auto_approve' => true,
                'is_active' => true
            ]
        ];
        
        foreach ($defaultClaimTypes as $claimType) {
            DB::table('claim_types')->updateOrInsert(
                ['code' => $claimType['code']],
                array_merge($claimType, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_types');
    }
};
