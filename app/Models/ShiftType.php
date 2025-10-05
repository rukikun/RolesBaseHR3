<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Traits\DatabaseConnectionTrait;

class ShiftType extends Model
{
    use DatabaseConnectionTrait;
    
    protected $table = 'shift_types';
    protected $connection = 'mysql';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using the correct database from config
        $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
    }

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_start_time',
        'default_end_time',
        'duration_hours',
        'break_duration_minutes',
        'color_code',
        'type',
        'hourly_rate',
        'is_active'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'duration_hours' => 'integer',
        'break_duration_minutes' => 'integer'
    ];

    // Relationships
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors for backward compatibility
    public function getStartTimeAttribute()
    {
        return $this->default_start_time;
    }

    public function getEndTimeAttribute()
    {
        return $this->default_end_time;
    }

    public function getBreakDurationAttribute()
    {
        return $this->break_duration_minutes;
    }

    // Helper methods
    public function getDurationInMinutes()
    {
        return $this->duration_hours * 60;
    }

    public function getFormattedDuration()
    {
        return $this->duration_hours . 'h ' . ($this->break_duration_minutes / 60) . 'h break';
    }
}
