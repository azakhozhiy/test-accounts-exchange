<?php

namespace App\Http\Resources;

use App\Models\Order;
use Carbon\Carbon;

class OrderCreatedResource
{
    public function __construct(protected Order $order){

    }

    public function build(): array{
        return [
            'id' => $this->order->uuid,
            'status' => $this->order->status,
            'created_at' => Carbon::parse($this->order->created_at)->toIso8601ZuluString(),
            'updated_at' => $this->order->updated_at
                ? Carbon::parse($this->order->updated_at)->toIso8601ZuluString()
                : null
        ];
    }
}
