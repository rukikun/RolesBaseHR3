<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'period_start',
        'period_end',
        'status',
        'generated_by',
        'generated_at',
        'summary',
        'total_records',
        'file_path'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'total_records' => 'integer'
    ];

    public function getPeriodLabelAttribute(): string
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('M d, Y') . ' - ' . $this->period_end->format('M d, Y');
        }

        if ($this->period_start) {
            return $this->period_start->format('M d, Y');
        }

        return 'N/A';
    }
}
