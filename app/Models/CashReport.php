<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

class CashReport extends Model
{
    protected $fillable = [
        'date',
        'morning_cash_balance',
        'cash_income',
        'cashless_income',
        'cash_expense',
        'cashless_expense',
        'cash_salary',
        'cashless_salary',
    ];

    protected $casts = [
        'morning_cash_balance' => MoneyCast::class,
        'cash_income' => MoneyCast::class,
        'cashless_income' => MoneyCast::class,
        'cash_expense' => MoneyCast::class,
        'cashless_expense' => MoneyCast::class,
        'cash_salary' => MoneyCast::class,
        'cashless_salary' => MoneyCast::class,
    ];
}
