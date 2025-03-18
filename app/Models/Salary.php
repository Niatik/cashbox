<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Listeners\SalaryCreated;
use App\Listeners\SalaryDeleted;
use App\Listeners\SalaryUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_date',
        'employee_id',
        'description',
        'salary_amount',
        'is_cash',
    ];

    protected $casts = [
        'salary_amount' => MoneyCast::class,
        'is_cash' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
