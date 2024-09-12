<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AvailableOrdersResource
{
    public function __construct(protected LengthAwarePaginator $orders)
    {

    }

    public function build(): array
    {
        $mapped = $this->orders->getCollection()->map(function (Order $order) {
            return (new AvailableOrderResource($order))->build();
        });

        $this->orders->setCollection($mapped);

        return $this->orders->toArray();
    }
}
