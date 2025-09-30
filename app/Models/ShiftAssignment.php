<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'shift_date',
        'status',
        'notes'
    ];

    protected $casts = [
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(ShiftType::class, 'shift_id');
    }
}
