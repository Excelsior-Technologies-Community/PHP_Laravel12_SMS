<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_histories', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->text('message');
            $table->string('status')->default('Sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_histories');
    }
};