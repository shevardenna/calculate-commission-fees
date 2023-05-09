<?php

namespace App\Console\Commands;

use App\Services\CalculatorService;
use Illuminate\Console\Command;

class CalculateCommissionsCommand extends Command
{
    protected $signature = 'calculate:commissions {url}';
    protected $description = 'Calculate commissions from a CSV file at the given URL';

    private CalculatorService $calculatorService;

    public function __construct(CalculatorService $calculatorService)
    {
        parent::__construct();
        $this->calculatorService = $calculatorService;
    }

    public function handle()
    {
        $url = $this->argument('url');
        $clients = $this->calculatorService->readCsvData($url);
        $commissions = $this->calculatorService->calculateCommissions($clients);

        $this->info('Commissions:');
        $commissions->each(function ($commission) {
            $this->line($commission);
        });
    }
}
