<?php

namespace App\Enum;

enum CurrencyEnum: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case RUB = 'RUB';

    public static function getCodes(): array{
        $data = [];

        foreach(self::cases() as $case){
            $data[] = $case->value;
        }

        return $data;
    }
}
