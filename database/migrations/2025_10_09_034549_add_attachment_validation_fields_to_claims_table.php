<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Add attachment validation fields
            $table->boolean('attachment_validated')->default(false)->after('receipt_path');
            $table->timestamp('validated_at')->nullable()->after('attachment_validated');
            $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
            
            // Add alternative attachment path field
            $table->string('attachment_path')->nullable()->after('receipt_path');
            
            // Add index for performance
            $table->index(['status', 'attachment_validated']);
            $table->index('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex(['status', 'attachment_validated']);
            $table->dropIndex(['validated_at']);
            $table->dropColumn([
                'attachment_validated',
                'validated_at', 
                'validated_by',
                'attachment_path'
            ]);
        });
    }
};
