<?php

namespace App\Enum\Order;

enum StatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case ACCEPTED = 'accepted';
    case CANCELLED = 'cancelled';
}
