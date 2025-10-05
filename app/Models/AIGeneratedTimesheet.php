<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AIGeneratedTimesheet extends Model
{
    use HasFactory;

    protected $table = 'ai_generated_timesheets';

    protected $fillable = [
        'employee_id',
        'employee_name',
        'department',
        'week_start_date',
        'weekly_data',
        'total_hours',
        'overtime_hours',
        'ai_insights',
        'status',
        'generated_at',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'weekly_data' => 'array',
        'ai_insights' => 'array',
        'week_start_date' => 'date',
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2'
    ];

    /**
     * Relationship to Employee model
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship to approver (Employee)
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Scope for current week
     */
    public function scopeCurrentWeek($query)
    {
        $weekStart = Carbon::now()->startOfWeek();
        return $query->where('week_start_date', $weekStart->format('Y-m-d'));
    }

    /**
     * Scope for specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get formatted weekly data
     */
    public function getFormattedWeeklyDataAttribute()
    {
        if (!$this->weekly_data) {
            return [];
        }

        return $this->weekly_data;
    }

    /**
     * Generate AI insights based on timesheet data
     */
    public static function generateInsights($totalHours, $overtimeHours, $employee = null, $weeklyData = [])
    {
        $insights = [];

        // Total hours analysis
        if ($totalHours >= 40) {
            $insights[] = "✅ Full-time schedule maintained ({$totalHours} hours)";
        } else {
            $insights[] = "⚠️ Below full-time threshold ({$totalHours}/40 hours)";
        }

        // Overtime analysis
        if ($overtimeHours > 0) {
            $insights[] = "💰 Overtime detected: {$overtimeHours} hours";
        }

        // Schedule consistency
        if (count($weeklyData) >= 5) {
            $insights[] = "📅 Consistent 5-day work schedule";
        }

        // Average daily hours
        $avgDaily = count($weeklyData) > 0 ? round($totalHours / count($weeklyData), 1) : 0;
        $insights[] = "📊 Average daily hours: {$avgDaily}";

        return $insights;
    }
}
