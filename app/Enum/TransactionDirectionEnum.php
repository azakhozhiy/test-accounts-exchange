<?php

namespace App\Enum;

enum TransactionDirectionEnum: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';
}
