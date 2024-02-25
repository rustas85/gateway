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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description'); // Описание подписки
            $table->integer('price'); // Целочисленная цена подписки
            $table->integer('request_limit'); // Лимит запросов в день
            $table->date('start_date')->nullable(); // Дата начала подписки
            $table->date('end_date')->nullable(); // Дата окончания подписки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
