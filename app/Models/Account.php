<?php

namespace App\Models;

use App\Cast\DecimalCast;
use App\Enum\CurrencyEnum;
use Decimal\Decimal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property-read int $user_id
 * @property string $currency
 * @property Decimal $current_balance
 * @property Decimal $available_balance
 *
 * @property string|null $updated_at
 */
class Account extends BaseModel
{
    protected $table = 'accounts';

    protected $casts = [
        'current_balance' => DecimalCast::class,
        'available_balance' => DecimalCast::class,
    ];

    public function getCurrencyEnum(): CurrencyEnum
    {
        return CurrencyEnum::from($this->currency);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
