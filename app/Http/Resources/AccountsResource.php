<?php

namespace App\Http\Resources;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

class AccountsResource
{
    public function __construct(protected Collection $accounts)
    {

    }

    public function build(): array
    {
        return $this->accounts->map(function (Account $account) {
            return (new AccountResource($account))->build();
        })->toArray();
    }
}
