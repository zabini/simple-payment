<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payer_wallet_id');
            $table->uuid('payee_wallet_id');
            $table->float('amount');
            $table->enum('status', ['pending', 'completed', 'failed', 'reverted']);
            $table->text('failed_reason')->nullable();
            $table->datetimes();

            $table->foreign('payer_wallet_id')
                ->references('id')
                ->on('wallets');

            $table->foreign('payee_wallet_id')
                ->references('id')
                ->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
