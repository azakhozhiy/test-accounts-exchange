<?php

namespace App\Enum;

enum TransactionStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
