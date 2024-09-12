<?php

namespace App\Models;

use App\Cast\DecimalCast;
use App\Enum\CurrencyEnum;
use Decimal\Decimal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property string $uuid
 * @property-read int $initiator_user_id
 * @property-read int $initiator_give_account_id
 * @property-read int $initiator_receive_account_id
 * @property-read int|null $acceptor_user_id
 * @property-read int|null $acceptor_give_account_id
 * @property-read int|null $acceptor_receive_account_id
 * @property Decimal $amount_initiator
 * @property Decimal $amount_acceptor
 * @property string $currency_initiator
 * @property string $currency_acceptor
 * @property Decimal $service_amount
 * @property Decimal $service_percent
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property-read User $initiatorUser
 * @see Order::initiatorUser()
 */
class Order extends BaseModel
{
    protected $table = 'orders';

    protected $casts = [
        'amount_initiator' => DecimalCast::class,
        'amount_acceptor' => DecimalCast::class,
        'service_amount' => DecimalCast::class
    ];

    public function getAcceptorFullAmount(): Decimal
    {
        return $this->amount_acceptor->add($this->service_amount);
    }

    public function getCurrencyInitiatorEnum(): CurrencyEnum
    {
        return CurrencyEnum::from($this->currency_initiator);
    }

    public function getCurrencyAcceptorEnum(): CurrencyEnum
    {
        return CurrencyEnum::from($this->currency_acceptor);
    }

    public function initiatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_user_id');
    }

    public function initiatorGiveAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_give_account_id');
    }

    public function initiatorReceiveAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_receive_account_id');
    }

    public function acceptorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acceptor_user_id');
    }

    public function acceptorGiveAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acceptor_give_account_id');
    }

    public function acceptorReceiveAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acceptor_receive_account_id');
    }
}
