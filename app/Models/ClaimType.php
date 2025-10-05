<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ClaimType extends Model
{
    use HasFactory;

    // Explicitly specify the database connection
    protected $connection = 'mysql';
    protected $table = 'claim_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_amount',
        'requires_attachment',
        'auto_approve',
        'auto_approve_limit',
        'approval_levels',
        'approval_rules',
        'category',
        'color_code',
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

    protected $casts = [
        'max_amount' => 'decimal:2',
        'requires_attachment' => 'boolean',
        'auto_approve' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
}
