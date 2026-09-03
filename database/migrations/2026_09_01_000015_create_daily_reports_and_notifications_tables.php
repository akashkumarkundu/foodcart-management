<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('total_customers')->default(0);
            $table->decimal('total_sales', 12, 2)->default(0.00);
            $table->decimal('cash_sales', 12, 2)->default(0.00);
            $table->decimal('bkash_sales', 12, 2)->default(0.00);
            $table->decimal('nagad_sales', 12, 2)->default(0.00);
            $table->decimal('rocket_sales', 12, 2)->default(0.00);
            $table->decimal('card_sales', 12, 2)->default(0.00);
            $table->decimal('total_expenses', 12, 2)->default(0.00);
            $table->decimal('total_waste', 12, 2)->default(0.00);
            $table->decimal('net_profit', 12, 2)->default(0.00);
            $table->decimal('profit_margin', 5, 2)->default(0.00);
            $table->foreignId('closed_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_closed')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('system'); // low_stock, high_waste, pending_order, supplier_due, daily_closing, sales_milestone, system
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points'); // positive for earned, negative for redeemed
            $table->string('type')->default('earned'); // earned, redeemed, adjusted
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('daily_reports');
    }
};
