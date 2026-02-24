<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSession extends Model
{
    /** @use HasFactory<\Database\Factories\WorkSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time',
        'salary_rate_id',
        'rate_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryRate(): BelongsTo
    {
        return $this->belongsTo(SalaryRate::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}
