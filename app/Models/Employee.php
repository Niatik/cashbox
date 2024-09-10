<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'phone',
        'salary',
        'employment_date',
    ];

    protected $casts = [
        'salary' => MoneyCast::class,
    ];
}
