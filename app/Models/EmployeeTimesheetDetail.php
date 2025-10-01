<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeTimesheetDetail extends Model
{
    use HasFactory;

    protected $table = 'employee_timesheet_details';

    protected $fillable = [
        'employee_id',
        'week_start_date',
        'week_end_date',
        'monday_date',
        'monday_time_in',
        'monday_break',
        'monday_time_out',
        'monday_total_hours',
        'monday_actual_time',
        'tuesday_date',
        'tuesday_time_in',
        'tuesday_break',
        'tuesday_time_out',
        'tuesday_total_hours',
        'tuesday_actual_time',
        'wednesday_date',
        'wednesday_time_in',
        'wednesday_break',
        'wednesday_time_out',
        'wednesday_total_hours',
        'wednesday_actual_time',
        'thursday_date',
        'thursday_time_in',
        'thursday_break',
        'thursday_time_out',
        'thursday_total_hours',
        'thursday_actual_time',
        'friday_date',
        'friday_time_in',
        'friday_break',
        'friday_time_out',
        'friday_total_hours',
        'friday_actual_time',
        'saturday_date',
        'saturday_time_in',
        'saturday_break',
        'saturday_time_out',
        'saturday_total_hours',
        'saturday_actual_time',
        'sunday_date',
        'sunday_time_in',
        'sunday_break',
        'sunday_time_out',
        'sunday_total_hours',
        'sunday_actual_time',
        'total_week_hours',
        'overtime_hours',
        'status',
        'supervisor_id',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'monday_date' => 'date',
        'tuesday_date' => 'date',
        'wednesday_date' => 'date',
        'thursday_date' => 'date',
        'friday_date' => 'date',
        'saturday_date' => 'date',
        'sunday_date' => 'date',
        'approved_at' => 'datetime',
        'total_week_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'monday_total_hours' => 'decimal:2',
        'monday_actual_time' => 'decimal:2',
        'tuesday_total_hours' => 'decimal:2',
        'tuesday_actual_time' => 'decimal:2',
        'wednesday_total_hours' => 'decimal:2',
        'wednesday_actual_time' => 'decimal:2',
        'thursday_total_hours' => 'decimal:2',
        'thursday_actual_time' => 'decimal:2',
        'friday_total_hours' => 'decimal:2',
        'friday_actual_time' => 'decimal:2',
        'saturday_total_hours' => 'decimal:2',
        'saturday_actual_time' => 'decimal:2',
        'sunday_total_hours' => 'decimal:2',
        'sunday_actual_time' => 'decimal:2'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    // Accessors
    public function getWeeklyDataAttribute()
    {
        return [
            'Monday' => [
                'date' => $this->monday_date ? $this->monday_date->format('m-d-y') : null,
                'time_in' => $this->monday_time_in,
                'break' => $this->monday_break,
                'time_out' => $this->monday_time_out,
                'total_hours' => $this->monday_total_hours . ' hrs.',
                'actual_time' => $this->monday_actual_time . ' hrs.'
            ],
            'Tuesday' => [
                'date' => $this->tuesday_date ? $this->tuesday_date->format('m-d-y') : null,
                'time_in' => $this->tuesday_time_in,
                'break' => $this->tuesday_break,
                'time_out' => $this->tuesday_time_out,
                'total_hours' => $this->tuesday_total_hours . ' hrs.',
                'actual_time' => $this->tuesday_actual_time . ' hrs.'
            ],
            'Wednesday' => [
                'date' => $this->wednesday_date ? $this->wednesday_date->format('m-d-y') : null,
                'time_in' => $this->wednesday_time_in,
                'break' => $this->wednesday_break,
                'time_out' => $this->wednesday_time_out,
                'total_hours' => $this->wednesday_total_hours . ' hrs.',
                'actual_time' => $this->wednesday_actual_time . ' hrs.'
            ],
            'Thursday' => [
                'date' => $this->thursday_date ? $this->thursday_date->format('m-d-y') : null,
                'time_in' => $this->thursday_time_in,
                'break' => $this->thursday_break,
                'time_out' => $this->thursday_time_out,
                'total_hours' => $this->thursday_total_hours . ' hrs.',
                'actual_time' => $this->thursday_actual_time . ' hrs.'
            ],
            'Friday' => [
                'date' => $this->friday_date ? $this->friday_date->format('m-d-y') : null,
                'time_in' => $this->friday_time_in,
                'break' => $this->friday_break,
                'time_out' => $this->friday_time_out,
                'total_hours' => $this->friday_total_hours . ' hrs.',
                'actual_time' => $this->friday_actual_time . ' hrs.'
            ]
        ];
    }

    // Scopes
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForWeek($query, $weekStartDate)
    {
        return $query->where('week_start_date', $weekStartDate);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper methods
    public function calculateTotalHours()
    {
        $total = 0;
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $total += $this->{$day . '_actual_time'} ?? 0;
        }
        
        $this->total_week_hours = $total;
        $this->overtime_hours = max(0, $total - 40); // Assuming 40 hours is standard work week
        
        return $this;
    }

    public function approve($supervisorId)
    {
        $this->status = 'approved';
        $this->supervisor_id = $supervisorId;
        $this->approved_at = now();
        $this->save();
        
        return $this;
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
        
        return $this;
    }

    // Generate timesheet from attendance data
    public static function generateFromAttendance($employeeId, $weekStartDate)
    {
        $weekStart = Carbon::parse($weekStartDate)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        // Check if timesheet already exists
        $existing = self::where('employee_id', $employeeId)
                       ->where('week_start_date', $weekStart->format('Y-m-d'))
                       ->first();
        
        if ($existing) {
            return $existing;
        }
        
        $timesheet = new self();
        $timesheet->employee_id = $employeeId;
        $timesheet->week_start_date = $weekStart;
        $timesheet->week_end_date = $weekEnd;
        $timesheet->status = 'pending';
        
        // Get attendance data for the week
        $attendances = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('l'); // Day name
            });
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        foreach ($days as $day) {
            $dayLower = strtolower($day);
            $currentDate = $weekStart->copy()->next($day === 'Monday' ? 'Monday' : $day);
            
            $timesheet->{$dayLower . '_date'} = $currentDate;
            
            if (isset($attendances[$day])) {
                $attendance = $attendances[$day];
                $timesheet->{$dayLower . '_time_in'} = $attendance->clock_in_time ? 
                    Carbon::parse($attendance->clock_in_time)->format('g:i A') : null;
                $timesheet->{$dayLower . '_time_out'} = $attendance->clock_out_time ? 
                    Carbon::parse($attendance->clock_out_time)->format('g:i A') : null;
                $timesheet->{$dayLower . '_break'} = '12:00 PM - 1:00 PM'; // Default break
                $timesheet->{$dayLower . '_total_hours'} = $attendance->total_hours ?? 8;
                $timesheet->{$dayLower . '_actual_time'} = $attendance->total_hours ?? 8;
            }
        }
        
        $timesheet->calculateTotalHours();
        $timesheet->save();
        
        return $timesheet;
    }
}
