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
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->string('event_type', 50)->comment('Type: GATE_IN, CUSTOMS_INSPECTION, LOADED_ON_VESSEL, DEPARTED, TRANSSHIPMENT, DISCHARGED, DELIVERED, etc.');
            $table->string('status', 30)->comment('Shipment status snapshot at this event: PICKED_UP, IN_TRANSIT, DELIVERED, etc.');
            $table->dateTime('event_time')->index()->comment('Chronological occurrence timestamp');
            $table->decimal('latitude', 10, 7)->nullable()->comment('GPS latitude of the event or maritime waypoint');
            $table->decimal('longitude', 10, 7)->nullable()->comment('GPS longitude of the event or maritime waypoint');
            $table->string('location_name')->nullable()->comment('Milestone name or geographic description');
            $table->text('description')->comment('Operational activity log detail');
            $table->boolean('is_exception')->default(false)->comment('Flag indicating if event is related to a delay or customs hold');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
