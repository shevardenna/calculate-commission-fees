<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CalculatorService implements CalculatorServiceInterface
{
    protected const DEPOSIT_COMMISSION_RATE = 0.0003;
    protected const PRIVATE_WITHDRAWAL_COMMISSION_RATE = 0.003;
    protected const BUSINESS_WITHDRAWAL_COMMISSION_RATE = 0.005;
    protected const MAX_FREE_WITHDRAWAL_AMOUNT = 1000;
    protected const MAX_FREE_WITHDRAWALS = 3;

    protected Collection $rates;
    protected array $userData = [];

    public function __construct()
    {
        $this->rates = $this->getCurrencyRates();
    }

    /**
     * Gets currency rates from API and puts it into collection
     * @return Collection
     */
    public function getCurrencyRates(): Collection
    {
        return collect(Http::get(config('currency.api_url'))->json());
    }

    /**
     * Reads csv file and returns collection of clients
     * @param string $url
     * @return Collection
     */
    public function readCsvData(string $url): Collection
    {
        $csvData = Http::get($url)->body();
        $rows = explode("\n", $csvData);

        return collect($rows)->filter(function ($row) {
            return !empty(array_filter(str_getcsv($row)));
        })->map(function ($row) {
            return str_getcsv($row);
        });
    }

    /**
     * Calculates commissions for an array of clients
     * @param Collection $clients
     * @param Collection|null $rates
     * @return Collection
     */
    public function calculateCommissions(Collection $clients, Collection $rates = null): Collection
    {
        if ($rates) {
            $this->rates = $rates;
        }

        return $clients->map(function ($client) {
            return $this->calculateCommissionForClient($client);
        });
    }

    /**
     * Calculates commission for a single client
     * @param array $client
     * @return float
     */
    private function calculateCommissionForClient(array $client): float
    {
        list($date, $userId, $userType, $operationType, $amount, $currency) = $client;
        $currentWeek = Carbon::parse($date)->startOfWeek()->format('Y-m-d');

        if (!isset($this->userData[$userId][$currentWeek])) {
            $this->initializeUserData($userId, $currentWeek, $userType);
        }

        $amountInEur = $this->convertToEur($amount, $currency);

        if ($operationType === 'deposit') {
            $commission = $amount * self::DEPOSIT_COMMISSION_RATE;
        } else {
            $commission = $this->calculateWithdrawalCommission(
                $userType,
                $amount,
                $currency,
                $userId,
                $currentWeek,
                $amountInEur
            );
        }

        return $this->roundUp($commission, $currency);
    }

    /**
     * Initializes user data array for a given user and week
     * @param string $userId
     * @param string $currentWeek
     * @param string $userType
     */
    private function initializeUserData(string $userId, string $currentWeek, string $userType): void
    {
        $this->userData[$userId][$currentWeek] = [
            'userType' => $userType,
            'withdrawals' => [
                'count' => 0,
                'amount' => 0,
            ],
        ];
    }

    /**
     * Calculates commission for a withdrawal operation
     * @param string $userType
     * @param float $amount
     * @param string $currency
     * @param string $userId
     * @param string $currentWeek
     * @param float $amountInEur
     * @return float
     */
    private function calculateWithdrawalCommission(
        string $userType,
        float $amount,
        string $currency,
        string $userId,
        string $currentWeek,
        float $amountInEur
    ): float {
        if ($userType === 'private') {
            $remainingFreeAmount = self::MAX_FREE_WITHDRAWAL_AMOUNT - $this->userData[$userId][$currentWeek]['withdrawals']['amount'];
            if ($this->userData[$userId][$currentWeek]['withdrawals']['count'] < self::MAX_FREE_WITHDRAWALS && $remainingFreeAmount > 0) {
                $freeAmountInEur = min($amountInEur, $remainingFreeAmount);
                $commissionableAmountInEur = $amountInEur - $freeAmountInEur;
                $commission = $this->convertFromEur(
                    $commissionableAmountInEur * self::PRIVATE_WITHDRAWAL_COMMISSION_RATE,
                    $currency
                );
            } else {
                $commission = $amount * self::PRIVATE_WITHDRAWAL_COMMISSION_RATE;
            }
            $this->userData[$userId][$currentWeek]['withdrawals']['count']++;
            $this->userData[$userId][$currentWeek]['withdrawals']['amount'] += $amountInEur;
        } else { // business
            $commission = $amount * self::BUSINESS_WITHDRAWAL_COMMISSION_RATE;
        }

        return $commission;
    }

    /**
     * Converts the given amount to EUR currency based on the exchange rate obtained from the API
     * @param float $amount
     * @param string $currency
     * @return float
     */
    private function convertToEur(float $amount, string $currency): float
    {
        return $amount / $this->rates['rates'][$currency];
    }

    /**
     * Converts the given amount from EUR currency based on the exchange rate obtained from the API
     * @param float $amount
     * @param string $currency
     * @return float
     */
    private function convertFromEur(float $amount, string $currency): float
    {
        return $amount * $this->rates['rates'][$currency];
    }

    /**
     * Rounds up the given value to the nearest integer or two decimal places depending on the currency
     * @param float $value
     * @param string $currency
     * @return float
     */
    private function roundUp(float $value, string $currency): float
    {
        $decimalPlaces = $currency === 'JPY' ? 0 : 2;
        return ceil($value * pow(10, $decimalPlaces)) / pow(10, $decimalPlaces);
    }
}
