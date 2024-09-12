<?php

namespace App\Http\Resources;

use App\Models\Order;
use Carbon\Carbon;

class AvailableOrderResource
{
    public function __construct(protected Order $order)
    {

    }

    public function build(): array
    {
        return [
            'id' => $this->order->uuid,
            'status' => $this->order->status,
            'give' => [
                'amount' => $this->order->getAcceptorFullAmount(),
                'currency' => $this->order->currency_acceptor,
            ],
            'receive' => [
                'amount' => $this->order->amount_initiator,
                'currency' => $this->order->currency_initiator,
            ],
            'initiator' => [
                'email' => $this->order->initiatorUser->email
            ],
            'created_at' => Carbon::parse($this->order->created_at)->toIso8601ZuluString(),
            'updated_at' => $this->order->updated_at
                ? Carbon::parse($this->order->updated_at)->toIso8601ZuluString()
                : null
        ];
    }
}
