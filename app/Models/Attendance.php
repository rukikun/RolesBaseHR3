<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    
    protected $fillable = [
        'employee_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'break_start_time',
        'break_end_time',
        'total_hours',
        'overtime_hours',
        'status',
        'location',
        'ip_address',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isClockedIn()
    {
        return $this->clock_in_time && !$this->clock_out_time;
    }

    public function isClockedOut()
    {
        return $this->clock_in_time && $this->clock_out_time;
    }

    public function isOnBreak()
    {
        return $this->break_start_time && !$this->break_end_time;
    }

    public function calculateTotalHours()
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return 0;
        }

        $clockIn = Carbon::parse($this->clock_in_time);
        $clockOut = Carbon::parse($this->clock_out_time);
        
        // Handle overnight shifts
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        $totalMinutes = $clockOut->diffInMinutes($clockIn);
        
        // Subtract break time if available
        if ($this->break_start_time && $this->break_end_time) {
            $breakStart = Carbon::parse($this->break_start_time);
            $breakEnd = Carbon::parse($this->break_end_time);
            $breakMinutes = $breakEnd->diffInMinutes($breakStart);
            $totalMinutes -= $breakMinutes;
        }

        return round($totalMinutes / 60, 2);
    }

    public function calculateOvertimeHours()
    {
        $totalHours = $this->calculateTotalHours();
        $regularHours = 8; // Standard 8-hour workday
        
        return $totalHours > $regularHours ? $totalHours - $regularHours : 0;
    }

    public function getFormattedClockInAttribute()
    {
        return $this->clock_in_time ? Carbon::parse($this->clock_in_time)->format('h:i A') : null;
    }

    public function getFormattedClockOutAttribute()
    {
        return $this->clock_out_time ? Carbon::parse($this->clock_out_time)->format('h:i A') : null;
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'present':
                return '<span class="badge bg-success">Present</span>';
            case 'absent':
                return '<span class="badge bg-danger">Absent</span>';
            case 'late':
                return '<span class="badge bg-warning">Late</span>';
            case 'on_break':
                return '<span class="badge bg-info">On Break</span>';
            case 'clocked_out':
                return '<span class="badge bg-secondary">Clocked Out</span>';
            default:
                return '<span class="badge bg-light text-dark">Unknown</span>';
        }
    }

    // Static methods for quick queries
    public static function getTodayAttendance($employeeId)
    {
        return self::where('employee_id', $employeeId)
                  ->whereDate('date', Carbon::today())
                  ->first();
    }

    public static function getEmployeeAttendanceStats($employeeId, $startDate = null, $endDate = null)
    {
        $query = self::where('employee_id', $employeeId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } else {
            $query->thisMonth();
        }

        $attendances = $query->get();
        
        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'total_hours' => $attendances->sum('total_hours'),
            'overtime_hours' => $attendances->sum('overtime_hours'),
        ];
    }
}
