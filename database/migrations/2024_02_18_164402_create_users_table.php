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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('email')->unique();
            $table->text('password');
            $table->integer('balance');
            $table->text('type');
            $table->string('subscription_type')->default('base');
            $table->unsignedInteger('daily_requests_count')->default(0);
            $table->boolean('email_verified_at')->default(false);
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
