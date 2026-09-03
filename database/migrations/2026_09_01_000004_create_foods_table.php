<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('bengali_name')->nullable();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('selling_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->integer('preparation_time')->default(10)->comment('minutes');
            $table->integer('current_stock')->default(50);
            $table->integer('min_stock')->default(10);
            $table->string('unit')->default('plate');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
