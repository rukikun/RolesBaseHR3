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
        'start_time',
        'end_time',
        'location',
        'break_duration',
        'notes',
        'status',
        'is_active'
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using the correct database from config
        $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
    }
    
    // Specify the table name explicitly
    protected $table = 'shifts';

    protected $casts = [
        'shift_date' => 'date',
        'is_active' => 'boolean',
        'break_duration' => 'integer'
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
