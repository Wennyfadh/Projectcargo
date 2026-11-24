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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('unlocode', 10)->nullable()->index()->comment('UN/LOCODE standard port/facility code (e.g. IDTPP, PHMNL, NLRTM)');
            $table->string('name');
            $table->enum('facility_type', ['PORT', 'FACTORY', 'WAREHOUSE', 'CUSTOMS_OFFICE'])->default('PORT');
            $table->string('address');
            $table->string('city');
            $table->string('country_code', 2)->comment('ISO 3166-1 alpha-2 (e.g., ID, PH, NL, US, AU)');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone', 50)->default('UTC');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
