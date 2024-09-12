<?php

namespace App\DTO;

use App\Enum\CurrencyEnum;
use Decimal\Decimal;

class AmountDTO
{
    private string $amount;
    private string $currency;

    public function __construct(string $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAmount(): Decimal
    {
        return new Decimal($this->amount);
    }

    public function getCurrencyEnum(): CurrencyEnum
    {
        return CurrencyEnum::from($this->currency);
    }
}
