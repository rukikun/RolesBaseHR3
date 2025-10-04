<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ShiftType extends Model
{
    protected $table = 'shift_types';
    
    // Explicitly specify the database connection
    protected $connection = 'mysql';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using hr3systemdb
        Config::set('database.connections.mysql.database', 'hr3systemdb');
        DB::purge('mysql');
    }
    
    protected $fillable = [
        'id',
        'name',
        'code',
        'description',
        'default_start_time',
        'default_end_time',
        'break_duration',
        'hourly_rate',
        'color_code',
        'type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'break_duration' => 'integer'
    ];

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function currentShiftRequests(): HasMany
    {
        return $this->hasMany(ShiftRequest::class, 'current_shift_id');
    }

    public function requestedShiftRequests(): HasMany
    {
        return $this->hasMany(ShiftRequest::class, 'requested_shift_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessor for start_time (for backward compatibility)
    public function getStartTimeAttribute()
    {
        return $this->default_start_time;
    }

    // Accessor for end_time (for backward compatibility)
    public function getEndTimeAttribute()
    {
        return $this->default_end_time;
    }
}
