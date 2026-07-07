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
            $table->string('status')->default('pending'); // pending, sent, delivered, failed
            $table->string('gateway')->nullable(); // twilio, msg91, etc.
            $table->string('message_id')->nullable(); // Provider message ID
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('status');
            $table->index('number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_histories');
    }
};