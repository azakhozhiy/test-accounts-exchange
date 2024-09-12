<?php

namespace App\Enum;

enum TransactionTypeEnum: string
{
    case HOLD = 'hold';
    case UNHOLD = 'unhold';
    case EXCHANGE = 'exchange'; // purchase

    public function isFinal(): bool
    {
        return match ($this) {
            self::HOLD, self::UNHOLD => false,
            default => true,
        };
    }
}
