<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class Shift extends Model
{
    use HasFactory;

    // Explicitly specify the database connection
    protected $connection = 'mysql';

    protected $fillable = [
        'employee_id',
        'shift_type_id',
        'shift_date',
        'date', // Keep for backward compatibility
        'start_time',
        'end_time',
        'location',
        'break_duration',
        'notes',
        'status'
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using hr3systemdb
        Config::set('database.connections.mysql.database', 'hr3systemdb');
        DB::purge('mysql');
    }
    
    // Specify the table name explicitly
    protected $table = 'shifts';

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class);
    }
}
