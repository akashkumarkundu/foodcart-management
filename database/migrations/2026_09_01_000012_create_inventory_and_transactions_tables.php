<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // purchase, sale, waste, adjustment
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->decimal('opening_stock', 10, 2)->default(0.00);
            $table->decimal('closing_stock', 10, 2)->default(0.00);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
