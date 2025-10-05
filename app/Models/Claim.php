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
    protected $table = 'claims';
    
    protected $fillable = [
        'employee_id',
        'claim_type_id',
        'amount',
        'claim_date',
        'description',
        'business_purpose',
        'receipt_path',
        'receipt_metadata',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'approved_amount',
        'reference_number',
        'payment_date',
        'payment_method',
        'notes'
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
