<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrackingEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Realistic historical chronological tracking events across Global Trade Lanes.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.mayora.co.id')->first();
        $siti = User::where('email', 'siti.rahmawati@cargotrace.mayora.co.id')->first();

        // Location References
        $locJayanti = Location::where('unlocode', 'IDTPP-JAY')->first();
        $locPriok = Location::where('unlocode', 'IDTPP')->first();
        $locCustomsPriok = Location::where('unlocode', 'IDTPP-CUS')->first();
        $locSingapore = Location::where('unlocode', 'SGSIN')->first();
        $locManila = Location::where('unlocode', 'PHMNL')->first();
        $locPuregoldDC = Location::where('unlocode', 'PHMNL-DC1')->first();
        $locRotterdam = Location::where('unlocode', 'NLRTM')->first();
        $locLosAngeles = Location::where('unlocode', 'USLAX')->first();
        $locShanghai = Location::where('unlocode', 'CNSHA')->first();
        $locJebelAli = Location::where('unlocode', 'AEJEA')->first();
        $locJeddah = Location::where('unlocode', 'SAJED')->first();
        $locYokohama = Location::where('unlocode', 'JPYOK')->first();
        $locFelixstowe = Location::where('unlocode', 'GBFXT')->first();
        $locLagos = Location::where('unlocode', 'NGLOS')->first();

        // -------------------------------------------------------------------------
        // 1. SHIPMENT 1: CT-MYR-2026-001 (Kopiko to Manila - DELIVERED)
        // -------------------------------------------------------------------------
        $s1 = Shipment::where('shipment_number', 'CT-MYR-2026-001')->first();
        if ($s1) {
            $events = [
                ['event_type' => 'GATE_OUT', 'status' => 'PICKED_UP', 'event_time' => Carbon::parse('2026-01-30 09:00:00'), 'latitude' => $locJayanti->latitude, 'longitude' => $locJayanti->longitude, 'location_name' => 'PT Mayora Indah Jayanti Plant, Tangerang', 'description' => 'Container EGLU9182341 stuffed with 1,600 master cartons of Kopiko 100g. Bolt seal EG782011 applied.', 'is_exception' => false, 'location_id' => $locJayanti->id, 'created_by' => $operator->id],
                ['event_type' => 'GATE_IN', 'status' => 'PICKED_UP', 'event_time' => Carbon::parse('2026-01-30 14:30:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'JICT Tanjung Priok Port, Jakarta', 'description' => 'Gate-In verified at JICT In-Gate 3. Staged at Block 4B Container Yard.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $siti->id],
                ['event_type' => 'CUSTOMS_INSPECTION', 'status' => 'PICKED_UP', 'event_time' => Carbon::parse('2026-01-31 08:30:00'), 'latitude' => $locCustomsPriok->latitude, 'longitude' => $locCustomsPriok->longitude, 'location_name' => 'KPU Bea Cukai Tipe A Tanjung Priok', 'description' => 'Random physical examination notice (Jalur Merah) issued for HS Code 1704.90.10 validation.', 'is_exception' => true, 'location_id' => $locCustomsPriok->id, 'created_by' => $siti->id],
                ['event_type' => 'CUSTOMS_CLEARED', 'status' => 'PICKED_UP', 'event_time' => Carbon::parse('2026-01-31 14:45:00'), 'latitude' => $locCustomsPriok->latitude, 'longitude' => $locCustomsPriok->longitude, 'location_name' => 'KPU Bea Cukai Tipe A Tanjung Priok', 'description' => 'Customs cleared. SPPB Ekspor No. 04912/WBC.07/2026 issued.', 'is_exception' => false, 'location_id' => $locCustomsPriok->id, 'created_by' => $siti->id],
                ['event_type' => 'LOADED_ON_VESSEL', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-01 07:15:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'JICT Berth 01, Tanjung Priok', 'description' => 'Container loaded aboard vessel EVER BLINK (Voyage 0441-028N).', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-01 10:30:00'), 'latitude' => -6.0820000, 'longitude' => 106.8850000, 'location_name' => 'Tanjung Priok Outer Anchorage', 'description' => 'Vessel EVER BLINK departed pilot station cruising at 17.5 knots.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-03 14:00:00'), 'latitude' => 3.9000000, 'longitude' => 108.2000000, 'location_name' => 'Natuna Sea Maritime Waypoint', 'description' => 'Cruising across Natuna Sea passing Indonesian EEZ boundary.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
                ['event_type' => 'ARRIVAL_PORT', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-06 06:45:00'), 'latitude' => $locManila->latitude, 'longitude' => $locManila->longitude, 'location_name' => 'Manila International Container Terminal (MICT)', 'description' => 'Vessel berthed safely at MICT Berth 4.', 'is_exception' => false, 'location_id' => $locManila->id, 'created_by' => $operator->id],
                ['event_type' => 'DISCHARGED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-07 09:15:00'), 'latitude' => $locManila->latitude, 'longitude' => $locManila->longitude, 'location_name' => 'MICT Container Yard, Manila', 'description' => 'Container discharged onto terminal apron. Philippine BOC cleared.', 'is_exception' => false, 'location_id' => $locManila->id, 'created_by' => $operator->id],
                ['event_type' => 'DELIVERED', 'status' => 'DELIVERED', 'event_time' => Carbon::parse('2026-02-08 14:15:00'), 'latitude' => $locPuregoldDC->latitude, 'longitude' => $locPuregoldDC->longitude, 'location_name' => 'Puregold Central Logistics DC, Valenzuela', 'description' => 'Cargo safely delivered to Puregold Distribution Center. e-POD signed by Carlos Tan.', 'is_exception' => false, 'location_id' => $locPuregoldDC->id, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s1->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 2. SHIPMENT 2: CT-MYR-2026-002 (Danisa to Rotterdam - Europe Lane)
        // -------------------------------------------------------------------------
        $s2 = Shipment::where('shipment_number', 'CT-MYR-2026-002')->first();
        if ($s2) {
            $events = [
                ['event_type' => 'GATE_IN', 'status' => 'PICKED_UP', 'event_time' => Carbon::parse('2026-02-13 11:20:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'JICT Tanjung Priok Port, Jakarta', 'description' => 'Container MSKU4982710 entered terminal gate.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $siti->id],
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-15 15:45:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'Tanjung Priok Container Terminal', 'description' => 'Vessel MAERSK MC-KINNEY MOLLER departed Tanjung Priok bound for Singapore transshipment hub.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'TRANSSHIPMENT_ARRIVAL', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-17 08:30:00'), 'latitude' => $locSingapore->latitude, 'longitude' => $locSingapore->longitude, 'location_name' => 'Port of Singapore (PSA Pasir Panjang)', 'description' => 'Feeder arrived at PSA Singapore. Container transferred to Triple-E mother vessel.', 'is_exception' => false, 'location_id' => $locSingapore->id, 'created_by' => $operator->id],
                ['event_type' => 'TRANSSHIPMENT_DEPARTURE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-18 21:00:00'), 'latitude' => $locSingapore->latitude, 'longitude' => $locSingapore->longitude, 'location_name' => 'Port of Singapore Outer Anchorage', 'description' => 'Ocean mother vessel departed Singapore en route to Europe.', 'is_exception' => false, 'location_id' => $locSingapore->id, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-24 14:00:00'), 'latitude' => 5.5000000, 'longitude' => 80.2000000, 'location_name' => 'Indian Ocean Waypoint (South of Sri Lanka)', 'description' => 'Vessel steaming across North Indian Ocean on schedule.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s2->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 3. SHIPMENT 7: CT-MYR-2026-007 (Danisa to Shanghai, China)
        // -------------------------------------------------------------------------
        $s7 = Shipment::where('shipment_number', 'CT-MYR-2026-007')->first();
        if ($s7) {
            $events = [
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-18 13:40:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'Tanjung Priok Port', 'description' => 'Vessel COSCO SHIPPING UNIVERSE departed Tanjung Priok on Far East express service.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-21 10:00:00'), 'latitude' => 12.1000000, 'longitude' => 112.5000000, 'location_name' => 'South China Sea Waypoint (Paracel Islands Passage)', 'description' => 'Vessel navigating northbound along South China Sea deep-water lane.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-24 16:30:00'), 'latitude' => 24.2000000, 'longitude' => 119.5000000, 'location_name' => 'Taiwan Strait Passage', 'description' => 'Proceeding across Taiwan Strait toward East China Sea. ETA Shanghai Yangshan Port confirmed.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s7->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 4. SHIPMENT 8: CT-MYR-2026-008 (Torabika to Dubai / Jebel Ali)
        // -------------------------------------------------------------------------
        $s8 = Shipment::where('shipment_number', 'CT-MYR-2026-008')->first();
        if ($s8) {
            $events = [
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-12 20:15:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'Tanjung Priok JICT', 'description' => 'Vessel CMA CGM JACQUES SAADE departed for Arabian Gulf.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-16 11:00:00'), 'latitude' => 5.8000000, 'longitude' => 95.2000000, 'location_name' => 'Malacca Strait North Gateway (Aceh/Weh Island)', 'description' => 'Cleared Malacca Strait entering open waters of Bay of Bengal.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-22 08:30:00'), 'latitude' => 23.5000000, 'longitude' => 59.8000000, 'location_name' => 'Gulf of Oman Approach', 'description' => 'Entering Gulf of Oman heading to Strait of Hormuz and Jebel Ali.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s8->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 5. SHIPMENT 9: CT-MYR-2026-009 (Danisa to Jeddah, Saudi Arabia - Red Sea Delay)
        // -------------------------------------------------------------------------
        $s9 = Shipment::where('shipment_number', 'CT-MYR-2026-009')->first();
        if ($s9) {
            $events = [
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-10 11:30:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'Tanjung Priok Port', 'description' => 'Departed Tanjung Priok bound for Jeddah Islamic Port.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'EXCEPTION_TRIGGERED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-23 14:00:00'), 'latitude' => 12.5800000, 'longitude' => 43.3400000, 'location_name' => 'Bab el-Mandeb Strait Maritime Security Corridor', 'description' => 'Vessel slowed down to participate in international naval security escort convoy into Red Sea. ETA adjusted by 14 hours.', 'is_exception' => true, 'location_id' => null, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s9->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 6. SHIPMENT 11: CT-MYR-2026-011 (Danisa to Felixstowe, UK - DELIVERED)
        // -------------------------------------------------------------------------
        $s11 = Shipment::where('shipment_number', 'CT-MYR-2026-011')->first();
        if ($s11) {
            $events = [
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-01-10 16:20:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'Tanjung Priok JICT', 'description' => 'Vessel CMA CGM ANTOINE DE SAINT EXUPERY departed Jakarta.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'ARRIVAL_PORT', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-12 10:00:00'), 'latitude' => $locFelixstowe->latitude, 'longitude' => $locFelixstowe->longitude, 'location_name' => 'Port of Felixstowe (Trinity Terminal)', 'description' => 'Vessel berthed safely at Felixstowe. UK Border Force cleared.', 'is_exception' => false, 'location_id' => $locFelixstowe->id, 'created_by' => $operator->id],
                ['event_type' => 'DELIVERED', 'status' => 'DELIVERED', 'event_time' => Carbon::parse('2026-02-14 16:00:00'), 'latitude' => 52.2900000, 'longitude' => -1.1300000, 'location_name' => 'Tesco Central Distribution Centre, Daventry UK', 'description' => 'Consignment delivered to Tesco UK warehouse. Inspection cleared.', 'is_exception' => false, 'location_id' => $locFelixstowe->id, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s11->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 7. SHIPMENT 12: CT-MYR-2026-012 (Torabika to Lagos, Nigeria - HIGH DELAY)
        // -------------------------------------------------------------------------
        $s12 = Shipment::where('shipment_number', 'CT-MYR-2026-012')->first();
        if ($s12) {
            $events = [
                ['event_type' => 'DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-01-25 12:45:00'), 'latitude' => $locPriok->latitude, 'longitude' => $locPriok->longitude, 'location_name' => 'Tanjung Priok Port', 'description' => 'Vessel MSC LORETTO departed Jakarta for West Africa via Cape of Good Hope.', 'is_exception' => false, 'location_id' => $locPriok->id, 'created_by' => $operator->id],
                ['event_type' => 'IN_TRANSIT_UPDATE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-12 14:00:00'), 'latitude' => -34.4000000, 'longitude' => 18.5000000, 'location_name' => 'Cape of Good Hope Passage (South Africa)', 'description' => 'Vessel rounded Cape of Good Hope entering South Atlantic Ocean.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
                ['event_type' => 'EXCEPTION_TRIGGERED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-25 16:00:00'), 'latitude' => 6.3800000, 'longitude' => 3.3900000, 'location_name' => 'Lagos Fairway Buoy / Gulf of Guinea Anchorage', 'description' => 'Vessel arrived at Lagos anchorage. Berthing delayed at Apapa Quays due to severe port congestion. Delay impact: 42 hours.', 'is_exception' => true, 'location_id' => $locLagos->id, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $s12->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 8. AIR CARGO: CT-MYR-2026-AIR-01 (Pesawat Kargo SQ7235 CGK -> SIN)
        // -------------------------------------------------------------------------
        $sAir1 = Shipment::where('shipment_number', 'CT-MYR-2026-AIR-01')->first();
        if ($sAir1) {
            $locCGK = Location::where('unlocode', 'IDCGK')->first() ?? $locPriok;
            $locSINAir = Location::where('unlocode', 'SGSIN-AIR')->first() ?? $locSingapore;
            $events = [
                ['event_type' => 'AIR_CARGO_ACCEPTANCE', 'status' => 'PICKED_UP', 'event_time' => Carbon::parse('2026-03-01 04:30:00'), 'latitude' => $locCGK->latitude, 'longitude' => $locCGK->longitude, 'location_name' => 'Soekarno-Hatta Cargo Terminal (CGK)', 'description' => 'Palletized Kopiko candy loaded onto PMC ULD air container. Customs air-waybill export cleared.', 'is_exception' => false, 'location_id' => $locCGK->id, 'created_by' => $operator->id],
                ['event_type' => 'AIR_DEPARTURE', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-03-01 06:45:00'), 'latitude' => -6.1256000, 'longitude' => 106.6558000, 'location_name' => 'Runway 25R Soekarno-Hatta Airport', 'description' => 'Flight SQ7235 Boeing 747-400F airborne climbing to cruising altitude 34,000 ft.', 'is_exception' => false, 'location_id' => $locCGK->id, 'created_by' => $operator->id],
                ['event_type' => 'IN_FLIGHT_AIS', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-03-01 07:30:00'), 'latitude' => -0.5000000, 'longitude' => 104.5000000, 'location_name' => 'Riau Islands / South China Sea Air Corridor (Waypoint DOLTA)', 'description' => 'Cruising at Mach 0.78 (840 km/h) at FL340 heading toward Changi TMA.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $sAir1->id; TrackingEvent::create($e); }
        }

        // -------------------------------------------------------------------------
        // 9. ROAD CARGO: CT-MYR-2026-TRK-02 (Truk Tronton Jayanti -> Percut Sei Tuan)
        // -------------------------------------------------------------------------
        $sRoad2 = Shipment::where('shipment_number', 'CT-MYR-2026-TRK-02')->first();
        if ($sRoad2) {
            $locMedanDC = Location::where('unlocode', 'IDMES-DC')->first() ?? $locPriok;
            $events = [
                ['event_type' => 'TRUCK_DEPARTED', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-27 11:15:00'), 'latitude' => -6.2131000, 'longitude' => 106.3768000, 'location_name' => 'PT Mayora Indah Jayanti Plant, Tangerang', 'description' => 'Heavy Trailer B 9421 KPY departed Mayora plant with 1,200 cartons of Torabika Cappuccino.', 'is_exception' => false, 'location_id' => $locJayanti->id, 'created_by' => $operator->id],
                ['event_type' => 'FERRY_CROSSING', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-27 15:30:00'), 'latitude' => -5.9000000, 'longitude' => 105.8500000, 'location_name' => 'Selat Sunda Ferry Crossing (Merak to Bakauheni)', 'description' => 'Truck boarded Ro-Ro ferry crossing Sunda Strait connecting Java and Sumatra.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
                ['event_type' => 'HIGHWAY_CHECKPOINT', 'status' => 'IN_TRANSIT', 'event_time' => Carbon::parse('2026-02-28 09:00:00'), 'latitude' => -4.8500000, 'longitude' => 105.2000000, 'location_name' => 'Jalan Tol Trans-Sumatera Km 182 (Terbanggi Besar, Lampung)', 'description' => 'GPS telematics verified: cruising speed 68 km/h. Cargo temperature optimal.', 'is_exception' => false, 'location_id' => null, 'created_by' => $operator->id],
            ];
            foreach ($events as $e) { $e['shipment_id'] = $sRoad2->id; TrackingEvent::create($e); }
        }
    }
}
