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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 50)->unique()->index()->comment('Tracking number identifier e.g. CT-MYR-2026-001');
            $table->string('booking_reference', 50)->nullable()->comment('Carrier ocean booking confirmation number');
            $table->string('bill_of_lading_number', 50)->unique()->index()->comment('Ocean B/L Number');
            $table->string('container_number', 20)->index()->comment('ISO 6346 BIC container number (e.g. EGLU9182341)');
            $table->string('seal_number', 30)->nullable()->comment('Customs high-security bolt seal ID');

            // Foreign keys
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade')->comment('Consignee / buyer company user');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('restrict')->comment('Ocean carrier / shipping line');
            $table->foreignId('origin_location_id')->constrained('locations')->onDelete('restrict')->comment('Point of origin / port of loading');
            $table->foreignId('destination_location_id')->constrained('locations')->onDelete('restrict')->comment('Final port of discharge / delivery site');
            $table->foreignId('current_location_id')->nullable()->constrained('locations')->onDelete('set null')->comment('Last known physical port / transit checkpoint');

            // Maritime voyage attributes
            $table->string('vessel_name', 100)->nullable()->comment('Container ship vessel name (e.g. EVER BLINK)');
            $table->string('voyage_number', 50)->nullable()->comment('Voyage identification code');
            $table->enum('cargo_type', ['FCL', 'LCL'])->default('FCL')->comment('Full Container Load / Less than Container Load');
            $table->enum('container_type', ['20GP', '40GP', '40HC', '45HC'])->default('40HC');

            // Lifecycle & Delay states
            $table->enum('status', ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'])->default('CREATED')->index();
            $table->enum('delay_status', ['ON_TIME', 'LOW_DELAY', 'MEDIUM_DELAY', 'HIGH_DELAY'])->default('ON_TIME')->index();

            // Schedules and ETA Tracking
            $table->dateTime('expected_departure_at')->comment('ETD from Origin');
            $table->dateTime('actual_departure_at')->nullable()->comment('ATD from Origin');
            $table->dateTime('expected_arrival_at')->comment('Baseline contractual ETA at Destination');
            $table->dateTime('estimated_arrival_at')->comment('Dynamic real-time updated ETA');
            $table->dateTime('actual_arrival_at')->nullable()->comment('ATA / Final Delivery timestamp');

            // Delay calculations
            $table->integer('delay_duration_hours')->default(0)->comment('Calculated delay: max(0, estimated_arrival - expected_arrival) in hours');
            $table->string('delay_reason')->nullable()->comment('Primary cause of delay');

            // Cargo conditions & logistics notes
            $table->string('temperature_control', 50)->default('AMBIENT_DRY')->comment('Dry confectionery storage parameter');
            $table->text('special_instructions')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
