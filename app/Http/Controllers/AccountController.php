<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountsResource;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function __construct(protected AccountService $service)
    {

    }

    public function getList(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getJwtGuard()->user();
        $accounts = $this->service->getUserAccounts($user);

        return response()->json((new AccountsResource($accounts))->build());
    }
}
