<?php

namespace App\Models;

use App\Events\WorkSessionDeleting;
use App\Services\WorkSessionService;
use Database\Factories\WorkSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSession extends Model
{
    /** @use HasFactory<WorkSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time',
        'salary_rate_id',
        'rate_id',
    ];

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'deleting' => WorkSessionDeleting::class,
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

    public function expenseWorkSessions(): HasMany
    {
        return $this->hasMany(ExpenseWorkSession::class);
    }

    public function salaryWorkSessions(): HasMany
    {
        return $this->hasMany(SalaryWorkSession::class);
    }

    /**
     * Get the salary total attribute.
     * Returns saved value if SalaryWorkSession exists, otherwise calculates dynamically.
     */
    public function getSalaryTotalAttribute(): float
    {
        $salaryWorkSession = $this->salaryWorkSessions->first();

        if ($salaryWorkSession) {
            return $salaryWorkSession->salary_total;
        }

        return app(WorkSessionService::class)->calculateSalaryTotal($this);
    }
}
