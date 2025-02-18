<?php

namespace App\Console\Commands;

use App\Services\CashReportService;
use Illuminate\Console\Command;

class CalculateBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cashReportService = new CashReportService;
        $cashReportService->calculateAndSaveDailyData();
        $this->info('Balance calculated and saved successfully.');
    }
}
