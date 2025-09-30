<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{
    use HasFactory;
    
    protected $connection = 'mysql';
    protected $table = 'shift_requests';

    protected $fillable = [
        'employee_id',
        'shift_type_id',
        'shift_date',
        'start_time',
        'end_time',
        'hours',
        'location',
        'notes',
        'status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'shift_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'hours' => 'decimal:2'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
