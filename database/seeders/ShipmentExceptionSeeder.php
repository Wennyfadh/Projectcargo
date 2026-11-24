<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShipmentExceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Realistic international logistics exceptions across Global Trade Routes.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.mayora.co.id')->first();
        $siti = User::where('email', 'siti.rahmawati@cargotrace.mayora.co.id')->first();

        // 1. CUSTOMS_HOLD on Shipment 1 (Tanjung Priok - Resolved)
        $s1 = Shipment::where('shipment_number', 'CT-MYR-2026-001')->first();
        if ($s1) {
            ShipmentException::create([
                'shipment_id' => $s1->id,
                'exception_type' => 'CUSTOMS_HOLD',
                'severity' => 'MEDIUM',
                'status' => 'RESOLVED',
                'delay_impact_hours' => 6,
                'description' => 'Bea Cukai Tanjung Priok issued Jalur Merah (Red Channel) physical examination notice for Container EGLU9182341.',
                'root_cause' => 'Periodic random risk algorithm selection in Indonesian Customs CEISA 4.0 system for HS Code 1704.90.10 export.',
                'action_taken' => 'Mayora Port Logistics Officer submitted COA, Halal Certificate, and BPOM PEB. Physical container inspection cleared. SPPB Ekspor issued.',
                'detected_at' => Carbon::parse('2026-01-31 08:30:00'),
                'resolved_at' => Carbon::parse('2026-01-31 14:45:00'),
                'logged_by' => $siti->id,
                'resolved_by' => $siti->id,
            ]);
        }

        // 2. PORT_CONGESTION on Shipment 3 (Port of Los Angeles - High Delay 38 Hours)
        $s3 = Shipment::where('shipment_number', 'CT-MYR-2026-003')->first();
        if ($s3) {
            ShipmentException::create([
                'shipment_id' => $s3->id,
                'exception_type' => 'PORT_CONGESTION',
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'delay_impact_hours' => 38,
                'description' => 'Vessel MSC ISABELLA queued at San Pedro Bay outer anchorage awaiting available berth at Pier 400 Port of Los Angeles.',
                'root_cause' => 'Severe shortage of intermodal railcars and chassis turnaround bottlenecks in Southern California.',
                'action_taken' => 'MSC Operations in coordination with Pacific Maritime Association for prioritized berthing window. Importer notified of revised ETA.',
                'detected_at' => Carbon::parse('2026-02-27 18:00:00'),
                'resolved_at' => null,
                'logged_by' => $operator->id,
                'resolved_by' => null,
            ]);
        }

        // 3. WEATHER_DELAY on Shipment 4 (Luzon Strait / Philippines - Medium Delay 16 Hours)
        $s4 = Shipment::where('shipment_number', 'CT-MYR-2026-004')->first();
        if ($s4) {
            ShipmentException::create([
                'shipment_id' => $s4->id,
                'exception_type' => 'WEATHER_DELAY',
                'severity' => 'MEDIUM',
                'status' => 'INVESTIGATING',
                'delay_impact_hours' => 16,
                'description' => 'Vessel EVER BOOM caught in tropical depression weather front approaching Luzon Strait, Philippines.',
                'root_cause' => 'Sustained 40-knot headwinds and 4.2-meter swell threatening container lashing security.',
                'action_taken' => 'Captain reduced cruising speed to 10 knots and diverted 35 NM south along sheltered coastal corridor.',
                'detected_at' => Carbon::parse('2026-02-24 09:30:00'),
                'resolved_at' => null,
                'logged_by' => $operator->id,
                'resolved_by' => null,
            ]);
        }

        // 4. DOCUMENTATION_ISSUE / SECURITY DELAY on Shipment 9 (Jeddah / Red Sea - Medium Delay 14 Hours)
        $s9 = Shipment::where('shipment_number', 'CT-MYR-2026-009')->first();
        if ($s9) {
            ShipmentException::create([
                'shipment_id' => $s9->id,
                'exception_type' => 'DOCUMENTATION_ISSUE',
                'severity' => 'MEDIUM',
                'status' => 'INVESTIGATING',
                'delay_impact_hours' => 14,
                'description' => 'Mandatory maritime transit clearance and naval convoy registration check at Bab el-Mandeb entrance.',
                'root_cause' => 'Enhanced international security protocol for commercial container vessels transiting the Southern Red Sea.',
                'action_taken' => 'Vessel master submitted UKMTO/EMASOH transit manifests. Speed reduced while waiting for scheduled convoy formation.',
                'detected_at' => Carbon::parse('2026-02-23 14:00:00'),
                'resolved_at' => null,
                'logged_by' => $operator->id,
                'resolved_by' => null,
            ]);
        }

        // 5. PORT_CONGESTION on Shipment 12 (Lagos / Apapa, Nigeria - High Delay 42 Hours)
        $s12 = Shipment::where('shipment_number', 'CT-MYR-2026-012')->first();
        if ($s12) {
            ShipmentException::create([
                'shipment_id' => $s12->id,
                'exception_type' => 'PORT_CONGESTION',
                'severity' => 'HIGH',
                'status' => 'OPEN',
                'delay_impact_hours' => 42,
                'description' => 'Vessel MSC LORETTO held at Lagos Fairway Buoy anchorage due to severe berth waiting times at Apapa Container Terminal.',
                'root_cause' => 'Customs scanning backlog and sluggish evacuation of import containers into Lagos hinterland.',
                'action_taken' => 'Local MSC Lagos agent coordinating with Nigerian Ports Authority (NPA) for terminal allocation.',
                'detected_at' => Carbon::parse('2026-02-25 16:00:00'),
                'resolved_at' => null,
                'logged_by' => $operator->id,
                'resolved_by' => null,
            ]);
        }
    }
}
