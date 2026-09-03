<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method'); // cash, bkash, nagad, rocket, card
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference')->nullable();
            $table->string('status')->default('completed'); // completed, pending, refunded
            $table->timestamp('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payment_date', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
