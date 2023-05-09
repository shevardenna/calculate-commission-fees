<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface CalculatorServiceInterface
{
    public function getCurrencyRates(): Collection;

    public function readCsvData(string $url): Collection;

    public function calculateCommissions(Collection $clients, Collection $rates = null): Collection;
}
