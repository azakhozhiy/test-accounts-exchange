<?php

namespace App\Services;

use App\DTO\AccountSelectionFilter;
use App\Enum\TransactionDirectionEnum;
use App\Enum\TransactionTypeEnum;
use App\Exceptions\Account\InsufficientAvailableBalanceException;
use App\Exceptions\Account\UpdateBalanceException;
use App\Models\Account;
use App\Models\User;
use App\Repository\AccountRepository;
use Decimal\Decimal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class AccountService
{
    protected int $cacheTtl = 3600;

    public function __construct(protected AccountRepository $accountRepository)
    {

    }

    public function attemptToUpdateBalance(
        AccountSelectionFilter   $accountSelectFilter,
        Decimal                  $amount,
        TransactionDirectionEnum $direction,
        TransactionTypeEnum      $transactionType,
        int                      $maxAttempts = 5
    ): Account
    {
        $attempts = 0;

        do {
            $giveAccount = $this->findUserAccount($accountSelectFilter);
            $updated = $this->changeBalance($giveAccount, $amount, $direction, $transactionType);
            $attempts++;

            if (!$updated && $attempts >= $maxAttempts) {
                throw new UpdateBalanceException("Failed to update available balance after multiple attempts.");
            }
        } while (!$updated);

        return $giveAccount;
    }

    public function findUserAccount(AccountSelectionFilter $filter): Account
    {
        return $this->accountRepository->findBySelectFilter($filter);
    }

    public function changeBalance(
        Account                  $account,
        Decimal                  $amount,
        TransactionDirectionEnum $directionEnum,
        TransactionTypeEnum      $typeEnum
    ): int
    {
        $balance = $account->available_balance;

        $newBalance = match ($directionEnum) {
            TransactionDirectionEnum::DEBIT => $balance->sub($amount),
            TransactionDirectionEnum::CREDIT => $balance->add($amount),
        };

        if ($newBalance->isNegative()) {
            throw new InsufficientAvailableBalanceException();
        }

        $updateData = [
            'available_balance' => $newBalance->toString()
        ];

        if ($typeEnum->isFinal()) {
            $newCurrentBalance = match ($directionEnum) {
                TransactionDirectionEnum::DEBIT => $account->current_balance->sub($amount),
                TransactionDirectionEnum::CREDIT => $account->current_balance->add($amount),
            };

            $updateData['current_balance'] = $newCurrentBalance->toString();
        }

        return Account::query()
            ->whereKey($account->getKey())
            ->where('updated_at', '=', $account->updated_at)
            ->update($updateData);
    }

    public function clearCacheAccounts(int $userId): void
    {
        Cache::forget("user.{$userId}.accounts");
    }

    public function getUserAccounts(User $user): Collection
    {
        $cacheKey = "user.{$user->getKey()}.accounts";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user) {
            return $this->accountRepository->getQueryByUserId($user->getKey())
                ->orderBy('id', 'desc')
                ->get();
        });
    }
}
