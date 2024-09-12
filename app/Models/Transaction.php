<?php

namespace App\Models;

use App\Cast\DecimalCast;
use App\Enum\TransactionDirectionEnum;
use App\Enum\TransactionStatusEnum;
use App\Enum\TransactionTypeEnum;
use Decimal\Decimal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property string $uuid
 * @property-read int $account_id
 * @property-read int|null $order_id
 * @property string $type
 * @property string $direction
 * @property string $status
 * @property Decimal $amount
 *
 * @property-read Order $order
 * @see Transaction::order()
 *
 * @property-read Account $account
 * @see Transaction::account()
 */
class Transaction extends BaseModel
{
    protected $table = 'transactions';

    protected $casts = [
        'amount' => DecimalCast::class
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getTypeEnum(): TransactionTypeEnum
    {
        return TransactionTypeEnum::from($this->type);
    }

    public function getDirectionEnum(): TransactionDirectionEnum
    {
        return TransactionDirectionEnum::from($this->direction);
    }

    public function getStatusEnum(): TransactionStatusEnum
    {
        return TransactionStatusEnum::from($this->status);
    }


}
