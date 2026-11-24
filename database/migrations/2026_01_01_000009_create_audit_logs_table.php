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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('entity_type', 100)->index()->comment('Morph class or model name (e.g. Shipment, ShipmentException)');
            $table->unsignedBigInteger('entity_id')->index()->comment('Primary key of targeted entity');
            $table->string('action', 50)->comment('Action executed: CREATED, STATUS_TRANSITION, EXCEPTION_TRIGGERED, RESOLVED, etc.');
            $table->json('old_values')->nullable()->comment('State before change');
            $table->json('new_values')->nullable()->comment('State after change');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
