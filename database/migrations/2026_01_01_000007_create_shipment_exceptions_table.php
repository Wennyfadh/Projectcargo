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
        Schema::create('shipment_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->enum('exception_type', [
                'CUSTOMS_HOLD',
                'WEATHER_DELAY',
                'PORT_CONGESTION',
                'DAMAGED',
                'MISROUTED',
                'DOCUMENTATION_ISSUE',
            ])->index();
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'INVESTIGATING', 'RESOLVED'])->default('OPEN')->index();
            $table->integer('delay_impact_hours')->default(0)->comment('Estimated or actual hours lost due to this exception');
            $table->text('description')->comment('Incident summary and observable manifestation');
            $table->text('root_cause')->nullable()->comment('Detailed operational or regulatory root cause');
            $table->text('action_taken')->nullable()->comment('Mitigation and corrective steps executed');
            $table->dateTime('detected_at')->comment('Timestamp when anomaly was flagged');
            $table->dateTime('resolved_at')->nullable()->comment('Timestamp when resolution was officially signed off');
            $table->foreignId('logged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_exceptions');
    }
};
