<?php

namespace App\Services;

use App\AppConstants;
use App\DTO\AcceptOrderDTO;
use App\DTO\AccountSelectionFilter;
use App\DTO\CreateExchangeOrderDTO;
use App\Enum\Order\StatusEnum;
use App\Enum\TransactionDirectionEnum;
use App\Enum\TransactionStatusEnum;
use App\Enum\TransactionTypeEnum;
use App\Exceptions\Order\OrderCreationException;
use App\Exceptions\Transaction\TransactionCreationException;
use App\Helper\AmountHelper;
use App\Models\Account;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Repository\AccountRepository;
use App\Repository\OrderRepository;
use Decimal\Decimal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OrderService
{
    public function __construct(
        protected AccountRepository $accountRepository,
        protected OrderRepository   $orderRepository,
        protected AccountService    $accountService
    )
    {
    }


    public function getAvailableOrders(User $user): LengthAwarePaginator
    {
        $availableCurrencies = $this->accountRepository->getQueryByUserId($user->getKey())
            ->get()
            ->pluck('currency')
            ->unique();

        return Order::query()
            ->where('initiator_user_id', '!=', $user->getKey())
            ->whereIn('currency_acceptor', $availableCurrencies)
            ->where('status', StatusEnum::PENDING->value)
            ->orderBy('id', 'desc')
            ->with('initiatorUser')
            ->paginate(30);
    }

    /**
     * @throws Throwable
     */
    public function create(User $user, CreateExchangeOrderDTO $dto): Order
    {
        $giveCurrencyEnum = $dto->getGive()->getCurrencyEnum();
        $giveAmount = $dto->getGive()->getAmount();
        $receiveCurrencyEnum = $dto->getReceive()->getCurrencyEnum();
        $receiveAmount = $dto->getReceive()->getAmount();
        $direction = TransactionDirectionEnum::DEBIT;

        $giveAccountFilter = new AccountSelectionFilter(
            $user->getKey(), 'uuid', $dto->getAccountGiveId(), null
        );

        $receiveAccountFilter = new AccountSelectionFilter(
            $user->getKey(), 'uuid', $dto->getAccountReceiveId(), null
        );

        $receiveAccount = $this->accountService->findUserAccount($receiveAccountFilter);

        try {
            DB::beginTransaction();

            $trnType = TransactionTypeEnum::HOLD;

            $giveAccount = $this->accountService->attemptToUpdateBalance(
                $giveAccountFilter,
                $giveAmount,
                $direction,
                $trnType
            );

            // Create exchange order
            $order = new Order();
            $order->uuid = Str::orderedUuid()->toString();
            $order->initiatorUser()->associate($user);
            $order->initiatorGiveAccount()->associate($giveAccount);
            $order->initiatorReceiveAccount()->associate($receiveAccount);
            $order->currency_initiator = $giveCurrencyEnum->value;
            $order->currency_acceptor = $receiveCurrencyEnum->value;
            $order->amount_initiator = $giveAmount;
            $order->amount_acceptor = $receiveAmount;
            $order->status = StatusEnum::PENDING->value;
            $order->service_percent = new Decimal(AppConstants::SERVICE_FEE_PERCENT);
            $order->service_amount = AmountHelper::calculateServiceAmount($receiveAmount, $order->service_percent);
            if (!$order->save()) {
                throw new OrderCreationException("Error while creating the exchange order.");
            }

            // Create hold transaction
            $this->createOrderTrn(
                $order,
                $giveAccount,
                $giveAmount,
                $trnType,
                $direction
            );

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Create order ended with error: ' . $e->getMessage());

            throw $e;
        }

        // Clear cache accounts
        $this->accountService->clearCacheAccounts($user->getKey());

        return $order;
    }

    /**
     * @throws Throwable
     */
    public function accept(User $user, AcceptOrderDTO $dto, string $orderUuid): Order
    {
        try {
            DB::beginTransaction();

            $order = $this->orderRepository->findByUuidForAccept($user, $orderUuid);
            $initiatorUserId = $order->initiator_user_id;
            $acceptorUserId = $order->acceptor_user_id;
            $order->acceptorUser()->associate($user);
            $order->status = StatusEnum::ACCEPTED->value;
            if (!$order->save()) {
                throw new OrderCreationException("Error while accepting the exchange order.");
            }

            // CREDIT. INITIATOR-GIVE-ACCOUNT. UNHOLD
            $initiatorGiveAccountFilter = new AccountSelectionFilter(
                $order->initiator_user_id,
                'id',
                $order->initiator_give_account_id,
                $order->getCurrencyInitiatorEnum()
            );
            $this->createOrderTrnAndChangeBalance(
                $order,
                $initiatorGiveAccountFilter,
                $order->amount_initiator,
                TransactionTypeEnum::UNHOLD,
                TransactionDirectionEnum::CREDIT
            );

            // DEBIT. INITIATOR-GIVE-ACCOUNT. EXCHANGE
            $this->createOrderTrnAndChangeBalance(
                $order,
                $initiatorGiveAccountFilter,
                $order->amount_initiator,
                TransactionTypeEnum::EXCHANGE,
                TransactionDirectionEnum::DEBIT
            );

            // CREDIT. INITIATOR-RECEIVE-ACCOUNT. EXCHANGE
            $initiatorReceiveAccountFilter = new AccountSelectionFilter(
                $order->initiator_user_id,
                'id',
                $order->initiator_receive_account_id,
                $order->getCurrencyAcceptorEnum()
            );
            $this->createOrderTrnAndChangeBalance(
                $order,
                $initiatorReceiveAccountFilter,
                $order->amount_acceptor,
                TransactionTypeEnum::EXCHANGE,
                TransactionDirectionEnum::CREDIT
            );

            // DEBIT. ACCEPTOR-GIVE-ACCOUNT. EXCHANGE
            $acceptorGiveAccountFilter = new AccountSelectionFilter(
                $user->getKey(),
                'uuid',
                $dto->getAccountGiveAcceptorId(),
                $order->getCurrencyAcceptorEnum()
            );
            $acceptorGiveAccount = $this->createOrderTrnAndChangeBalance(
                $order,
                $acceptorGiveAccountFilter,
                $order->getAcceptorFullAmount(),
                TransactionTypeEnum::EXCHANGE,
                TransactionDirectionEnum::DEBIT
            );

            // CREDIT. ACCEPTOR-RECEIVE-ACCOUNT. EXCHANGE
            $acceptorReceiveAccountFilter = new AccountSelectionFilter(
                $user->getKey(),
                'uuid',
                $dto->getAccountReceiveAcceptorId(),
                $order->getCurrencyInitiatorEnum()
            );
            $acceptorReceiveAccount = $this->createOrderTrnAndChangeBalance(
                $order,
                $acceptorReceiveAccountFilter,
                $order->amount_initiator,
                TransactionTypeEnum::EXCHANGE,
                TransactionDirectionEnum::CREDIT
            );

            $order->status = StatusEnum::COMPLETED->value;
            $order->acceptorUser()->associate($user);
            $order->acceptorGiveAccount()->associate($acceptorGiveAccount);
            $order->acceptorGiveAccount()->associate($acceptorReceiveAccount);

            if (!$order->save()) {
                throw new OrderCreationException("Error while completing the exchange order.");
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Accept exchange order ended with error: " . $e->getMessage());

            throw $e;
        }

        $this->accountService->clearCacheAccounts($initiatorUserId);
        $this->accountService->clearCacheAccounts($acceptorUserId);

        return $order;
    }

    private function createOrderTrn(
        Order                    $order,
        Account                  $account,
        Decimal                  $amount,
        TransactionTypeEnum      $type,
        TransactionDirectionEnum $direction
    ): void
    {
        $transaction = new Transaction();
        $transaction->uuid = Str::orderedUuid()->toString();
        $transaction->order()->associate($order);
        $transaction->account()->associate($account);
        $transaction->amount = $amount;
        $transaction->type = $type;
        $transaction->direction = $direction;
        $transaction->status = TransactionStatusEnum::SUCCESS->value;
        if (!$transaction->save()) {
            throw new TransactionCreationException("Error while creating the {$type->value} transaction.");
        }
    }

    private function createOrderTrnAndChangeBalance(
        Order                    $order,
        AccountSelectionFilter   $accountFilter,
        Decimal                  $amount,
        TransactionTypeEnum      $type,
        TransactionDirectionEnum $direction,
    ): Account
    {
        $account = $this->accountService->attemptToUpdateBalance(
            $accountFilter,
            $amount,
            $direction,
            $type
        );

        $this->createOrderTrn($order, $account, $amount, $type, $direction);

        return $account;
    }

    /**
     * @throws Throwable
     */
    public function cancel(User $user, string $orderUuid): Order
    {
        try {
            DB::beginTransaction();

            $order = $this->orderRepository->findByUuidForCancel($user, $orderUuid);

            $order->status = StatusEnum::CANCELLED->value;
            $order->save();

            $accountSelectFilter = new AccountSelectionFilter(
                $user->getKey(),
                'id',
                $order->initiator_give_account_id,
                $order->getCurrencyInitiatorEnum()
            );

            $this->createOrderTrnAndChangeBalance(
                $order,
                $accountSelectFilter,
                $order->amount_initiator,
                TransactionTypeEnum::UNHOLD,
                TransactionDirectionEnum::CREDIT
            );

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Cancel order ended with error: ' . $e->getMessage());
            throw $e;
        }

        $this->accountService->clearCacheAccounts($order->initiator_user_id);
        
        return $order;
    }
}
