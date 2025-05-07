<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Paymob transaction ID
            $table->string('order_id'); // Your order ID
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->string('status'); // success, failed, pending
            $table->json('payment_data')->nullable(); // Store all payment data
            $table->json('callback_data')->nullable(); // Store callback data
            $table->string('payment_method')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
};
