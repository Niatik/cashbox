<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Events\ExpenseCreated;
use App\Events\ExpenseDeleted;
use App\Events\ExpenseUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;


    protected $fillable = [
        'expense_date',
        'expense_type_id',
        'description',
        'expense_amount',
        'is_cash',
    ];


    protected $casts = [
        'expense_amount' => MoneyCast::class,
        'is_cash' => 'boolean',
    ];

    public function expense_type(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }

    protected $dispatchesEvents = [
        'created' => ExpenseCreated::class,
        'deleted' => ExpenseDeleted::class,
        'updated' => ExpenseUpdated::class,
    ];

}
