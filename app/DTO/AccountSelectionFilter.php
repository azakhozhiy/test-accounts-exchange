<?php

namespace App\DTO;

use App\Enum\CurrencyEnum;
use App\Models\User;

class AccountSelectionFilter
{
    public function __construct(
        protected int         $userId,
        protected string       $idColumn,
        protected string       $idValue,
        protected ?CurrencyEnum $currencyEnum
    )
    {

    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getIdColumn(): string
    {
        return $this->idColumn;
    }

    public function getIdValue(): string
    {
        return $this->idValue;
    }

    public function getCurrencyEnum(): ?CurrencyEnum
    {
        return $this->currencyEnum;
    }
}
