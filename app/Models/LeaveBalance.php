<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
        'remaining_days',
        'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'allocated_days' => 'integer',
        'used_days' => 'integer',
        'remaining_days' => 'integer'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    // Accessors
    public function getRemainingDaysAttribute($value)
    {
        if ($value !== null) {
            return (int) $value;
        }

        $allocated = (int) ($this->attributes['allocated_days'] ?? 0);
        $used = (int) ($this->attributes['used_days'] ?? 0);

        return max($allocated - $used, 0);
    }
}
