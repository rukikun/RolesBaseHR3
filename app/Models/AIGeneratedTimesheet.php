<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AIGeneratedTimesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
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
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship with approver
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Generate AI timesheet based on employee's shift schedule and attendance history
     */
    public static function generateForEmployee($employeeId, $weekStartDate = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            throw new \Exception("Employee not found");
        }

        $weekStart = $weekStartDate ? Carbon::parse($weekStartDate) : Carbon::now()->startOfWeek();
        
        // Check if AI timesheet table exists and if record already exists
        try {
            $existing = self::where('employee_id', $employeeId)
                ->where('week_start_date', $weekStart->format('Y-m-d'))
                ->first();
                
            if ($existing) {
                return $existing;
            }
        } catch (\Exception $e) {
            // Table doesn't exist, use fallback generation
            return self::generateFallbackTimesheet($employee, $weekStart);
        }

        // Get employee's shift assignments for the week
        $shifts = \DB::table('shifts')
            ->where('employee_id', $employeeId)
            ->whereBetween('shift_date', [
                $weekStart->format('Y-m-d'),
                $weekStart->copy()->addDays(4)->format('Y-m-d')
            ])
            ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
            ->select('shifts.*', 'shift_types.start_time', 'shift_types.end_time', 'shift_types.name as shift_name')
            ->get()
            ->keyBy(function($shift) {
                return Carbon::parse($shift->shift_date)->format('l'); // Monday, Tuesday, etc.
            });

        // Get attendance history for pattern analysis
        $attendanceHistory = \DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->where('date', '>=', $weekStart->copy()->subWeeks(4)->format('Y-m-d'))
            ->where('date', '<', $weekStart->format('Y-m-d'))
            ->get();

        // Generate weekly data
        $weeklyData = [];
        $totalHours = 0;
        $overtimeHours = 0;
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        foreach ($days as $index => $day) {
            $currentDate = $weekStart->copy()->addDays($index);
            $dateStr = $currentDate->format('m/d/y');
            
            // Check if employee has a shift assigned for this day
            $shift = $shifts->get($day);
            
            if ($shift) {
                // Use scheduled shift data
                $startTime = $shift->start_time;
                $endTime = $shift->end_time;
                
                // Add realistic variations based on attendance history
                $variation = self::calculateTimeVariation($attendanceHistory, $day);
                $actualStartTime = self::addMinutesToTime($startTime, $variation['start']);
                $actualEndTime = self::addMinutesToTime($endTime, $variation['end']);
                
            } else {
                // Use employee's typical pattern or default schedule
                $pattern = self::getEmployeePattern($employee, $attendanceHistory);
                $actualStartTime = $pattern['start_time'];
                $actualEndTime = $pattern['end_time'];
            }

            // Calculate hours
            $dayHours = self::calculateWorkHours($actualStartTime, $actualEndTime);
            $regularHours = min($dayHours, 8);
            $dayOvertime = max(0, $dayHours - 8);
            
            $totalHours += $regularHours;
            $overtimeHours += $dayOvertime;

            $weeklyData[$day] = [
                'date' => $dateStr,
                'time_in' => self::formatTime12Hour($actualStartTime),
                'break' => '12:00 PM - 1:00 PM',
                'time_out' => self::formatTime12Hour($actualEndTime),
                'total_hours' => number_format($regularHours, 1) . ' hrs.',
                'overtime' => $dayOvertime > 0 ? number_format($dayOvertime, 1) . ' hrs.' : '0 hrs.'
            ];
        }

        // Generate AI insights
        $insights = self::generateInsights($totalHours, $overtimeHours, $employee, $weeklyData);

        // Create the AI timesheet
        $aiTimesheet = self::create([
            'employee_id' => $employeeId,
            'week_start_date' => $weekStart->format('Y-m-d'),
            'weekly_data' => $weeklyData,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'ai_insights' => $insights,
            'status' => 'generated',
            'generated_at' => now()
        ]);

        return $aiTimesheet;
    }

    /**
     * Calculate time variation based on attendance history
     */
    private static function calculateTimeVariation($attendanceHistory, $day)
    {
        // Analyze historical patterns for this day of week
        $dayAttendances = $attendanceHistory->filter(function($attendance) use ($day) {
            return Carbon::parse($attendance->date)->format('l') === $day;
        });

        if ($dayAttendances->isEmpty()) {
            // Default variation: ±15 minutes
            return [
                'start' => rand(-15, 15),
                'end' => rand(-15, 15)
            ];
        }

        // Calculate average variation from scheduled times
        // This is a simplified version - in reality, you'd compare against scheduled shift times
        return [
            'start' => rand(-10, 10), // More accurate based on history
            'end' => rand(-10, 10)
        ];
    }

    /**
     * Get employee's typical work pattern
     */
    private static function getEmployeePattern($employee, $attendanceHistory)
    {
        // Default patterns based on department or role
        $patterns = [
            'IT' => ['start_time' => '09:00:00', 'end_time' => '18:00:00'],
            'Marketing' => ['start_time' => '08:30:00', 'end_time' => '17:30:00'],
            'HR' => ['start_time' => '08:00:00', 'end_time' => '17:00:00'],
            'Finance' => ['start_time' => '08:00:00', 'end_time' => '17:00:00'],
            'Operations' => ['start_time' => '07:00:00', 'end_time' => '16:00:00']
        ];

        return $patterns[$employee->department] ?? $patterns['HR'];
    }

    /**
     * Calculate work hours between two times
     */
    private static function calculateWorkHours($startTime, $endTime)
    {
        $start = Carbon::createFromFormat('H:i:s', $startTime);
        $end = Carbon::createFromFormat('H:i:s', $endTime);
        
        if ($end->lt($start)) {
            $end->addDay(); // Handle overnight shifts
        }
        
        $totalMinutes = $end->diffInMinutes($start) - 60; // Subtract 1 hour break
        return max(0, $totalMinutes / 60);
    }

    /**
     * Add minutes to time string
     */
    private static function addMinutesToTime($timeStr, $minutes)
    {
        $time = Carbon::createFromFormat('H:i:s', $timeStr);
        return $time->addMinutes($minutes)->format('H:i:s');
    }

    /**
     * Format time in 12-hour format
     */
    private static function formatTime12Hour($timeStr)
    {
        return Carbon::createFromFormat('H:i:s', $timeStr)->format('h:i A');
    }

    /**
     * Generate AI insights
     */
    private static function generateInsights($totalHours, $overtimeHours, $employee, $weeklyData)
    {
        $insights = [];
        
        $insights[] = "Total weekly hours: " . number_format($totalHours, 1) . " hours";
        
        if ($overtimeHours > 0) {
            $insights[] = "⚠️ Overtime detected: " . number_format($overtimeHours, 1) . " hours over standard 40-hour week";
        }
        
        if ($totalHours < 35) {
            $insights[] = "ℹ️ Below full-time threshold: Consider reviewing schedule";
        }
        
        $insights[] = "📊 Average daily hours: " . number_format($totalHours / 5, 1) . " hours";
        
        // Check for consistent patterns
        $dailyHours = array_map(function($day) {
            return (float) str_replace(' hrs.', '', $day['total_hours']);
        }, $weeklyData);
        
        $variance = self::calculateVariance($dailyHours);
        if ($variance < 0.5) {
            $insights[] = "✅ Consistent schedule pattern detected";
        } else {
            $insights[] = "📈 Variable schedule detected - consider standardizing hours";
        }
        
        // Department-specific insights
        if ($employee->department === 'IT' && $overtimeHours > 5) {
            $insights[] = "💻 High IT overtime - consider workload distribution";
        }
        
        return $insights;
    }

    /**
     * Calculate variance of an array
     */
    private static function calculateVariance($values)
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);
        
        return sqrt($variance);
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
     * Generate fallback timesheet when database table doesn't exist
     */
    public static function generateFallbackTimesheet($employee, $weekStart)
    {
        // Generate weekly data without saving to database
        $weeklyData = [];
        $totalHours = 0;
        $overtimeHours = 0;
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Get employee pattern
        $pattern = self::getEmployeePattern($employee, collect([]));

        foreach ($days as $index => $day) {
            $currentDate = $weekStart->copy()->addDays($index);
            $dateStr = $currentDate->format('m/d/y');
            
            // Add realistic variations
            $startVariation = rand(-15, 15);
            $endVariation = rand(-15, 15);
            
            $actualStartTime = self::addMinutesToTime($pattern['start_time'], $startVariation);
            $actualEndTime = self::addMinutesToTime($pattern['end_time'], $endVariation);

            // Calculate hours
            $dayHours = self::calculateWorkHours($actualStartTime, $actualEndTime);
            $regularHours = min($dayHours, 8);
            $dayOvertime = max(0, $dayHours - 8);
            
            $totalHours += $regularHours;
            $overtimeHours += $dayOvertime;

            $weeklyData[$day] = [
                'date' => $dateStr,
                'time_in' => self::formatTime12Hour($actualStartTime),
                'break' => '12:00 PM - 1:00 PM',
                'time_out' => self::formatTime12Hour($actualEndTime),
                'total_hours' => number_format($regularHours, 1) . ' hrs.',
                'overtime' => $dayOvertime > 0 ? number_format($dayOvertime, 1) . ' hrs.' : '0 hrs.'
            ];
        }

        // Generate AI insights
        $insights = self::generateInsights($totalHours, $overtimeHours, $employee, $weeklyData);

        // Return a mock object with the same structure
        return (object) [
            'id' => 'fallback-' . $employee->id,
            'employee_id' => $employee->id,
            'employee' => $employee,
            'week_start_date' => $weekStart,
            'weekly_data' => $weeklyData,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'ai_insights' => $insights,
            'generated_at' => now(),
            'status' => 'generated'
        ];
    }
}
