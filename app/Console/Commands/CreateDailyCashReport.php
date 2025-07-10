<?php

namespace App\Console\Commands;

use App\Models\CashReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateDailyCashReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashreport:create-daily {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a daily cash report entry if it does not exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::today();
        $dateString = $date->format('Y-m-d');
        
        $existingReport = CashReport::where('date', $dateString)->first();
        
        if ($existingReport) {
            $this->info("Cash report for {$dateString} already exists.");
            return;
        }
        
        $previousDayReport = CashReport::where('date', '<', $dateString)
            ->orderBy('date', 'desc')
            ->first();
        
        $morningCashBalance = 0.00;
        
        if ($previousDayReport) {
            $morningCashBalance = $previousDayReport->morning_cash_balance 
                + $previousDayReport->cash_income 
                - $previousDayReport->cash_expense 
                - $previousDayReport->cash_salary;
        }
        
        CashReport::create([
            'date' => $dateString,
            'morning_cash_balance' => $morningCashBalance,
            'cash_income' => 0.00,
            'cashless_income' => 0.00,
            'cash_expense' => 0.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ]);
        
        $this->info("Created cash report for {$dateString} with morning balance: " . number_format($morningCashBalance, 2));
    }
}
