<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class Claim extends Model
{
    use HasFactory;

    // Explicitly specify the database connection
    protected $connection = 'mysql';
    
    protected $fillable = [
        'employee_id',
        'claim_type_id', 
        'amount',
        'claim_date',
        'description',
        'receipt_path',
        'attachment_path',
        'status',
        'rejection_reason',
        'approved_at',
        'paid_at',
        'approved_by'
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using hr3systemdb
        Config::set('database.connections.mysql.database', 'hr3systemdb');
        DB::purge('mysql');
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function claimType()
    {
        return $this->belongsTo(ClaimType::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
