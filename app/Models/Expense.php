<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;


    protected $fillable = [
        'expense_date',
        'expense_type_id',
        'expense_amount',
    ];


    protected $casts = [
        'expense_amount' => MoneyCast::class,
    ];


    public function expense_type(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
