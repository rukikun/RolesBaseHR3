<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_days_per_year',
        'carry_forward',
        'requires_approval',
        'is_active'
    ];

    protected $casts = [
        'carry_forward' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'max_days_per_year' => 'integer'
    ];

    // Relationships
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
