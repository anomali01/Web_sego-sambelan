<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('manual_enabled')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('qris_image')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_payment_settings');
    }
};
