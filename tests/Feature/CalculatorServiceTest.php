<?php

namespace Tests\Feature;

use App\Services\CalculatorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CalculatorServiceTest extends TestCase
{
    protected Collection $clients;
    protected Collection $expectedCommissions;
    protected Collection $rates;
    protected CalculatorService $service;


    public function setUp(): void
    {
        parent::setUp();
        $this->clients = $this->getClients();
        $this->expectedCommissions = $this->getExpectedCommissions();
        $this->rates = $this->getRates();
        $this->service = App::make(CalculatorService::class);
    }


    /**
     * Returns collections of client data provided in task
     * @return Collection
     */
    public function getClients(): Collection
    {
        return new Collection([
            ['2014-12-31', '4', 'private', 'withdraw', '1200.00', 'EUR'],
            ['2015-01-01', '4', 'private', 'withdraw', '1000.00', 'EUR'],
            ['2016-01-05', '4', 'private', 'withdraw', '1000.00', 'EUR'],
            ['2016-01-05', '1', 'private', 'deposit', '200.00', 'EUR'],
            ['2016-01-06', '2', 'business', 'withdraw', '300.00', 'EUR'],
            ['2016-01-06', '1', 'private', 'withdraw', '30000', 'JPY'],
            ['2016-01-07', '1', 'private', 'withdraw', '1000.00', 'EUR'],
            ['2016-01-07', '1', 'private', 'withdraw', '100.00', 'USD'],
            ['2016-01-10', '1', 'private', 'withdraw', '100.00', 'EUR'],
            ['2016-01-10', '2', 'business', 'deposit', '10000.00', 'EUR'],
            ['2016-01-10', '3', 'private', 'withdraw', '1000.00', 'EUR'],
            ['2016-02-15', '1', 'private', 'withdraw', '300.00', 'EUR'],
            ['2016-02-19', '5', 'private', 'withdraw', '3000000', 'JPY'],
        ]);
    }

    /**
     * Returns collection of expected commissions provided in task
     * @return Collection
     */
    public function getExpectedCommissions(): Collection
    {
        return new Collection([
            0.6,
            3.00,
            0.00,
            0.06,
            1.50,
            0,
            0.70,
            0.30,
            0.30,
            3.00,
            0.00,
            0.00,
            8612
        ]);
    }

    /**
     * Returns rates provided in task
     * @return Collection
     */
    public function getRates(): Collection
    {
        return new Collection([
            'rates' => [
                'EUR' => config('currency.default_rate.eur'),
                'USD' => config('currency.default_rate.usd'),
                'JPY' => config('currency.default_rate.jpy')
            ]
        ]);
    }

    /**
     * Test calculateCommissions method
     *
     * @return void
     */
    public function testCalculateCommissions()
    {
        $calculatorService = new CalculatorService();
        $calculatedCommissions = $calculatorService->calculateCommissions($this->clients, $this->rates);
        $this->assertEquals($this->expectedCommissions, $calculatedCommissions);
    }
}
