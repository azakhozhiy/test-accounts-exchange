<?php

namespace App\Repository;

use App\Enum\Order\StatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository
{


    public function query(): Builder
    {
        return Order::query();
    }

    public function findByUuidForAccept(User $user, string $uuid): Order{
        /** @var Order $order */
        $order = $this->query()
            ->where('uuid', $uuid)
            ->where('initiator_user_id', '!=', $user->getKey())
            ->where('status', StatusEnum::PENDING->value)
            ->lockForUpdate()
            ->firstOrFail();

        return $order;
    }

    public function findByUuidForComplete(User $user, string $uuid): Order{
        /** @var Order $order */
        $order = $this->query()
            ->where('uuid', $uuid)
            ->where('status', StatusEnum::ACCEPTED->value)
            ->lockForUpdate()
            ->firstOrFail();

        return $order;
    }

    public function findByUuidForCancel(User $initiator, string $uuid): Order{
        /** @var Order $order */
        $order = $this->query()
            ->where('uuid', $uuid)
            ->where('initiator_user_id', $initiator->getKey())
            ->where('status', StatusEnum::PENDING->value)
            ->lockForUpdate()
            ->firstOrFail();

        return $order;
    }
}
