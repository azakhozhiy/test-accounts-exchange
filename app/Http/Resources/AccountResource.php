<?php

namespace App\Http\Resources;

use App\Models\Account;
use Carbon\Carbon;

class AccountResource
{
    public function __construct(protected Account $account)
    {

    }

    public function build(): array
    {
        return [
            'id' => $this->account->uuid,
            'currency' => $this->account->currency,
            'current_balance' => $this->account->current_balance,
            'available_balance' => $this->account->available_balance,
            'created_at' => Carbon::parse($this->account->created_at)->toIso8601ZuluString(),
            'updated_at' => $this->account->updated_at
                ? Carbon::parse($this->account->updated_at)->toIso8601ZuluString()
                : null
        ];
    }
}
