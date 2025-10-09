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
        Schema::table('employees', function (Blueprint $table) {
            // Add additional profile fields
            $table->string('username')->nullable()->after('email');
            $table->string('work_location')->nullable()->after('department');
            $table->unsignedBigInteger('manager_id')->nullable()->after('work_location');
            $table->date('date_of_birth')->nullable()->after('hire_date');
            $table->enum('gender', ['Male', 'Female', 'Other', 'Prefer not to say'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->enum('emergency_contact_relationship', ['Spouse', 'Parent', 'Sibling', 'Child', 'Friend', 'Other'])->nullable()->after('emergency_contact_phone');
            
            // Add foreign key constraint for manager
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            
            // Add indexes
            $table->index('username');
            $table->index('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['manager_id']);
            
            // Drop columns
            $table->dropColumn([
                'username',
                'work_location',
                'manager_id',
                'date_of_birth',
                'gender',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship'
            ]);
        });
    }
};
