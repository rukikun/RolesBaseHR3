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
        'attachment_path',
        'receipt_metadata',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'approved_amount',
        'reference_number',
        'payment_date',
        'payment_method',
        'notes',
        'attachment_validated',
        'validated_at',
        'validated_by'
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
        'paid_at' => 'datetime',
        'validated_at' => 'datetime',
        'attachment_validated' => 'boolean'
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

    public function validator()
    {
        return $this->belongsTo(Employee::class, 'validated_by');
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

    public function scopeWithAttachment($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('receipt_path')
              ->orWhereNotNull('attachment_path');
        });
    }

    public function scopeValidated($query)
    {
        return $query->where('attachment_validated', true);
    }

    public function scopePendingValidation($query)
    {
        return $query->where('status', 'approved')
                    ->where('attachment_validated', false)
                    ->withAttachment();
    }
}
