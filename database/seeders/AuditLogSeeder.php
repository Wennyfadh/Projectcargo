<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Comprehensive immutable audit trail capturing events across global export shipments.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.mayora.co.id')->first();
        $siti = User::where('email', 'siti.rahmawati@cargotrace.mayora.co.id')->first();

        $s1 = Shipment::where('shipment_number', 'CT-MYR-2026-001')->first();
        $s3 = Shipment::where('shipment_number', 'CT-MYR-2026-003')->first();
        $s7 = Shipment::where('shipment_number', 'CT-MYR-2026-007')->first();
        $s11 = Shipment::where('shipment_number', 'CT-MYR-2026-011')->first();
        $s12 = Shipment::where('shipment_number', 'CT-MYR-2026-012')->first();

        $logs = [];

        // Shipment 1 Logs
        if ($s1) {
            $logs[] = [
                'user_id' => $operator->id,
                'entity_type' => 'App\Models\Shipment',
                'entity_id' => $s1->id,
                'action' => 'CREATED',
                'old_values' => null,
                'new_values' => ['shipment_number' => 'CT-MYR-2026-001', 'bill_of_lading_number' => 'EGLV0419283710', 'status' => 'CREATED'],
                'ip_address' => '192.168.10.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0 Safari/537.36',
                'created_at' => Carbon::parse('2026-01-28 10:15:00'),
            ];
            $logs[] = [
                'user_id' => $operator->id,
                'entity_type' => 'App\Models\Shipment',
                'entity_id' => $s1->id,
                'action' => 'STATUS_TRANSITION',
                'old_values' => ['status' => 'IN_TRANSIT'],
                'new_values' => ['status' => 'DELIVERED', 'actual_arrival_at' => '2026-02-08 14:15:00'],
                'ip_address' => '192.168.10.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::parse('2026-02-08 14:20:00'),
            ];
        }

        // Shipment 3 ETA Recalculation Log
        if ($s3) {
            $logs[] = [
                'user_id' => $operator->id,
                'entity_type' => 'App\Models\Shipment',
                'entity_id' => $s3->id,
                'action' => 'ETA_RECALCULATION',
                'old_values' => ['estimated_arrival_at' => '2026-02-28 08:00:00', 'delay_status' => 'ON_TIME', 'delay_duration_hours' => 0],
                'new_values' => ['estimated_arrival_at' => '2026-03-01 22:00:00', 'delay_status' => 'HIGH_DELAY', 'delay_duration_hours' => 38, 'delay_reason' => 'Port of Los Angeles terminal congestion.'],
                'ip_address' => '192.168.10.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::parse('2026-02-27 18:05:00'),
            ];
        }

        // Shipment 7 Shanghai Creation
        if ($s7) {
            $logs[] = [
                'user_id' => $operator->id,
                'entity_type' => 'App\Models\Shipment',
                'entity_id' => $s7->id,
                'action' => 'CREATED',
                'old_values' => null,
                'new_values' => ['shipment_number' => 'CT-MYR-2026-007', 'destination' => 'CNSHA (Shanghai)', 'carrier' => 'COSCO'],
                'ip_address' => '192.168.10.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::parse('2026-02-14 09:30:00'),
            ];
        }

        // Shipment 11 UK Delivered
        if ($s11) {
            $logs[] = [
                'user_id' => $operator->id,
                'entity_type' => 'App\Models\Shipment',
                'entity_id' => $s11->id,
                'action' => 'STATUS_TRANSITION',
                'old_values' => ['status' => 'IN_TRANSIT'],
                'new_values' => ['status' => 'DELIVERED', 'actual_arrival_at' => '2026-02-14 16:00:00'],
                'ip_address' => '192.168.10.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::parse('2026-02-14 16:05:00'),
            ];
        }

        // Shipment 12 Lagos Exception Log
        if ($s12) {
            $logs[] = [
                'user_id' => $operator->id,
                'entity_type' => 'App\Models\Shipment',
                'entity_id' => $s12->id,
                'action' => 'EXCEPTION_TRIGGERED',
                'old_values' => ['delay_status' => 'ON_TIME'],
                'new_values' => ['delay_status' => 'HIGH_DELAY', 'delay_duration_hours' => 42, 'exception' => 'PORT_CONGESTION'],
                'ip_address' => '192.168.10.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::parse('2026-02-25 16:10:00'),
            ];
        }

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
