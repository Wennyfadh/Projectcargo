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
        Schema::table('shipments', function (Blueprint $table) {
            if (! Schema::hasColumn('shipments', 'transport_mode')) {
                $table->enum('transport_mode', ['SEA', 'AIR', 'ROAD'])
                    ->default('SEA')
                    ->after('voyage_number')
                    ->index()
                    ->comment('Mode of transport: SEA (Kapal), AIR (Pesawat), ROAD (Mobil Truk)');
            }

            if (! Schema::hasColumn('shipments', 'vehicle_identifier')) {
                $table->string('vehicle_identifier', 100)
                    ->nullable()
                    ->after('transport_mode')
                    ->comment('Specific vehicle identifier: IMO vessel name, Flight No, or Truck Plate No');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'vehicle_identifier')) {
                $table->dropColumn('vehicle_identifier');
            }
            if (Schema::hasColumn('shipments', 'transport_mode')) {
                $table->dropColumn('transport_mode');
            }
        });
    }
};
