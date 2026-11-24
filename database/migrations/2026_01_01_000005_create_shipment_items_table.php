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
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('sku', 50)->comment('Mayora Product Internal SKU code');
            $table->string('product_name')->comment('Full commercial brand product name');
            $table->string('hs_code', 20)->comment('Harmonized System Code for international trade tariff');
            $table->enum('package_type', ['MASTER_CARTON', 'TIN', 'BOX', 'PALLET'])->default('MASTER_CARTON');
            $table->integer('quantity')->comment('Number of master cartons / shipping packages');
            $table->integer('units_per_package')->default(1)->comment('Consumer unit count per carton (e.g. 40 pouches/carton)');
            $table->decimal('net_weight_kg', 10, 2)->comment('Total net weight of commodities in kg');
            $table->decimal('gross_weight_kg', 10, 2)->comment('Total gross weight including tare packaging in kg');
            $table->decimal('volume_cbm', 10, 4)->comment('Total cubic meter cargo volume');
            $table->string('packaging_description')->nullable()->comment('Physical packaging configuration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
