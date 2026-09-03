<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('plate');
            $table->decimal('estimated_cost', 10, 2);
            $table->string('reason'); // burned, expired, overproduction, damaged, spoiled, customer_return, other
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wastes');
    }
};
