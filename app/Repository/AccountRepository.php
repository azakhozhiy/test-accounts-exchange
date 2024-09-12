<?php

namespace App\Repository;

use App\DTO\AccountSelectionFilter;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AccountRepository
{
    public function query(): Builder
    {
        return Account::query();
    }

    public function findById(int $id): ?Account
    {
        /** @var Account $account */
        $account = Account::query()->findOrFail($id);

        return $account;
    }

    public function getQueryByUserId(int $userId): Builder
    {
        return $this->query()
            ->where('user_id', $userId);
    }

    public function findBySelectFilter(AccountSelectionFilter $filter): Account
    {
        $query = $this->getQueryByUserId($filter->getUserId());

        if ($filter->getCurrencyEnum()) {
            $query = $query->where('currency', $filter->getCurrencyEnum()->value);
        }

        /** @var Account $account */
        $account = $query->where($filter->getIdColumn(), $filter->getIdValue())
            ->firstOrFail();

        return $account;
    }

    public function findByUserAndUuidAndCurrency(User $user, string $uuid, string $currency): Account
    {
        /** @var Account $account */
        $account = $this->getQueryByUserId($user)
            ->where('uuid', $uuid)
            ->where('currency', $currency)
            ->firstOrFail();

        return $account;
    }
}
