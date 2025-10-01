<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Optional: target a specific user (client) within the tenant. Null means tenant-wide default
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_at');
            $table->date('end_at')->nullable();
            $table->string('status')->default('active'); // active, draft, expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

