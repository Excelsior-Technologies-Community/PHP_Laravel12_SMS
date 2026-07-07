<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // twilio, msg91, infobip, vonage
            $table->string('provider_class');
            $table->json('credentials');
            $table->integer('priority')->default(1); // 1 = primary, 2 = secondary, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_fallback')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};