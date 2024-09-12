<?php

use App\Models\Account;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createAccountsTable();
        $this->createOrdersTable();
        $this->createTransactionsTable();
    }

    private function createTransactionsTable(): void
    {
        Schema::create('transactions', static function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type');
            $table->string('direction');
            $table->string('status');
            $table->unsignedDecimal('amount', 19, 4);
            $table->timestamps();

            // FKs
            $account = new Account();
            $table->foreign('account_id')
                ->references($account->getKeyName())
                ->on($account->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $order = new Order();
            $table->foreign('order_id')
                ->references($order->getKeyName())
                ->on($order->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    private function createAccountsTable(): void
    {
        Schema::create('accounts', static function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('currency');
            $table->decimal('current_balance', 19, 4)->default(0);
            $table->decimal('available_balance', 19, 4)->default(0);
            $table->timestamps();

            // FKs
            $user = new User();
            $table->foreign('user_id')
                ->references($user->getKeyName())
                ->on($user->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    private function createOrdersTable(): void
    {
        Schema::create('orders', static function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('status');
            $table->unsignedBigInteger('initiator_user_id');
            $table->unsignedBigInteger('initiator_give_account_id');
            $table->unsignedBigInteger('initiator_receive_account_id');
            $table->unsignedBigInteger('acceptor_user_id')->nullable();
            $table->unsignedBigInteger('acceptor_give_account_id')->nullable();
            $table->unsignedBigInteger('acceptor_receive_account_id')->nullable();
            $table->unsignedDecimal('amount_initiator', 19, 4);
            $table->unsignedDecimal('amount_acceptor', 19, 4);
            $table->string('currency_initiator');
            $table->string('currency_acceptor');
            $table->unsignedDecimal('service_amount', 19, 4)->default(0);
            $table->unsignedDecimal('service_percent', 6, 4)->default(0);
            $table->timestamps();

            // FKs
            $user = new User();
            $table->foreign('initiator_user_id')
                ->references($user->getKeyName())
                ->on($user->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
            $table->foreign('acceptor_user_id')
                ->references($user->getKeyName())
                ->on($user->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $account = new Account();
            $table->foreign('initiator_give_account_id')
                ->references($account->getKeyName())
                ->on($account->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
            $table->foreign('initiator_receive_account_id')
                ->references($account->getKeyName())
                ->on($account->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
            $table->foreign('acceptor_give_account_id')
                ->references($account->getKeyName())
                ->on($account->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
            $table->foreign('acceptor_receive_account_id')
                ->references($account->getKeyName())
                ->on($account->getTable())
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('accounts');
    }
};
