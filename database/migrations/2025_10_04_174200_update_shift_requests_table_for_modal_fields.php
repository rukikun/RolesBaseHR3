<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_type_id')->nullable()->after('employee_id');
            $table->date('shift_date')->nullable()->after('requested_date');
            $table->decimal('hours', 5, 2)->nullable()->after('requested_end_time');
            $table->time('start_time')->nullable()->after('requested_end_time');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('location')->nullable()->after('end_time');
            $table->text('notes')->nullable()->after('location');
            $table->text('reason')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shift_requests', function (Blueprint $table) {
            $table->dropColumn(['shift_type_id', 'shift_date', 'hours', 'start_time', 'end_time', 'location', 'notes']);
            // $table->text('reason')->nullable(false)->change();
        });
    }
};
