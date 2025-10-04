<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('schedule_cron');
            $table->timestamp('next_run_at')->nullable();
            $table->string('status')->default('active'); // active, paused
            $table->timestamps();
        });

        Schema::create('standing_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standing_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standing_order_items');
        Schema::dropIfExists('standing_orders');
    }
};

