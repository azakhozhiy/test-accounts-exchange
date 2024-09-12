<?php

namespace Database\Seeders;

use App\Enum\CurrencyEnum;
use App\Models\Account;
use App\Models\User;
use Decimal\Decimal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class BasicSeeder extends Seeder
{

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            foreach ($this->getUsersData() as $usersData) {
                $user = new User();
                $user->email = $usersData['email'];
                $user->password = bcrypt($usersData['password']);
                $user->save();

                foreach ($usersData['accounts'] as $accountData) {
                    $account = new Account();
                    $account->uuid = Str::orderedUuid()->toString();
                    $account->user()->associate($user);
                    $account->available_balance = $accountData['available_balance'];
                    $account->current_balance = $account->available_balance;
                    $account->currency = $accountData['currency'];
                    $account->save();
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getUsersData(): array
    {
        return [
            [
                'email' => 'a@test.com',
                'password' => '123456',
                'accounts' => [
                    [
                        'currency' => CurrencyEnum::USD->value,
                        'available_balance' => new Decimal('100'),
                    ],
                    [
                        'currency' => CurrencyEnum::RUB->value,
                        'available_balance' => new Decimal('500'),
                    ]
                ],
            ],
            [
                'email' => 'b@test.com',
                'password' => '123456',
                'accounts' => [
                    [
                        'currency' => CurrencyEnum::USD->value,
                        'available_balance' => new Decimal('10'),
                    ],
                    [
                        'currency' => CurrencyEnum::RUB->value,
                        'available_balance' => new Decimal('2500'),
                    ],
                    [
                        'currency' => CurrencyEnum::EUR->value,
                        'available_balance' => new Decimal('400'),
                    ]
                ],
            ],
        ];
    }
}
