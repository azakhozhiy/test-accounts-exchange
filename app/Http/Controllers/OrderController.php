<?php

namespace App\Http\Controllers;

use App\DTO\AcceptOrderDTO;
use App\DTO\CreateExchangeOrderDTO;
use App\Http\Requests\AcceptOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\AvailableOrdersResource;
use App\Http\Resources\OrderCreatedResource;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {

    }

    public function getList(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getJwtGuard()->user();

        $paginator = $this->orderService->getAvailableOrders($user);

        return response()->json((new AvailableOrdersResource($paginator))->build());
    }

    /**
     * @throws Throwable
     */
    public function create(CreateOrderRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getJwtGuard()->user();

        $dto = CreateExchangeOrderDTO::createFromArray($request->all());
        $order = $this->orderService->create($user, $dto);

        return response()->json((new OrderCreatedResource($order))->build());
    }

    /**
     * @throws Throwable
     */
    public function accept(AcceptOrderRequest $request, string $orderUuid): JsonResponse
    {
        /** @var User $user */
        $user = $this->getJwtGuard()->user();

        $dto = AcceptOrderDTO::createFromArray($request->all());
        $this->orderService->accept($user, $dto, $orderUuid);

        return response()->json();
    }

    /**
     * @throws Throwable
     */
    public function cancel(Request $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $this->getJwtGuard()->user();
        $this->orderService->cancel($user, $uuid);

        return response()->json();
    }
}
