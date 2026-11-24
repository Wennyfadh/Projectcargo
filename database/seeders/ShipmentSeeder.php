<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Truly Global Export Shipments of PT Mayora Indah Tbk spanning all continents:
     * Asia (PH, CN, JP), Europe (NL, DE, GB), Middle East (AE, SA), North America (USLAX, USNYC),
     * South America (BR), Africa (NG), and Oceania (AU).
     */
    public function run(): void
    {
        // Reference Users
        $operator = User::where('email', 'operator@cargotrace.mayora.co.id')->first();
        $buyerPH = User::where('email', 'buyer.philippines@puregold.com.ph')->first();
        $buyerCN = User::where('email', 'buyer.china@vanguard.cn')->first();
        $buyerJP = User::where('email', 'buyer.japan@aeon.info')->first();
        $buyerAE = User::where('email', 'buyer.middleeast@lulugroup.ae')->first();
        $buyerSA = User::where('email', 'buyer.saudi@bindawood.com')->first();
        $buyerNL = User::where('email', 'buyer.europe@orientalgroup.nl')->first();
        $buyerDE = User::where('email', 'buyer.germany@edeka.de')->first();
        $buyerUK = User::where('email', 'buyer.uk@tesco.com')->first();
        $buyerUS = User::where('email', 'buyer.usa@asianfoodgrocer.com')->first();
        $buyerBR = User::where('email', 'buyer.brazil@pao-de-acucar.com.br')->first();
        $buyerNG = User::where('email', 'buyer.africa@spar.ng')->first();
        $buyerAU = User::where('email', 'buyer.australia@woolworths.com.au')->first();

        // Carriers
        $carrierMSC = Carrier::where('scac', 'MSCU')->first();
        $carrierMaersk = Carrier::where('scac', 'MAEU')->first();
        $carrierCMACGM = Carrier::where('scac', 'CMDU')->first();
        $carrierCOSCO = Carrier::where('scac', 'COSCO')->first();
        $carrierHapag = Carrier::where('scac', 'HLCU')->first();
        $carrierONE = Carrier::where('scac', 'ONEY')->first();
        $carrierEvergreen = Carrier::where('scac', 'EGLV')->first();
        $carrierSQCargo = Carrier::where('scac', 'SQCARGO')->first() ?? $carrierMaersk;
        $carrierKLCargo = Carrier::where('scac', 'KLCARGO')->first() ?? $carrierMaersk;
        $carrierMayoraLog = Carrier::where('scac', 'MYR-LOG')->first() ?? $carrierMSC;
        $carrierDunex = Carrier::where('scac', 'DUNEX')->first() ?? $carrierMSC;

        // Key Locations
        $locJayanti = Location::where('unlocode', 'IDTPP-JAY')->first();
        $locJatake = Location::where('unlocode', 'IDTPP-JAT')->first();
        $locPriok = Location::where('unlocode', 'IDTPP')->first();
        $locCGK = Location::where('unlocode', 'IDCGK')->first() ?? $locJayanti;
        $locChangiAir = Location::where('unlocode', 'SGSIN-AIR')->first() ?? $locJayanti;
        $locSchipholAir = Location::where('unlocode', 'NLAMS-AIR')->first() ?? $locJayanti;
        $locSurabayaDC = Location::where('unlocode', 'IDSUB-DC')->first() ?? $locJayanti;
        $locMedanDC = Location::where('unlocode', 'IDMES-DC')->first() ?? $locJayanti;
        $locBakauheni = Location::where('unlocode', 'IDLMP-HUB')->first() ?? $locJayanti;
        $locManila = Location::where('unlocode', 'PHMNL')->first();
        $locPuregoldDC = Location::where('unlocode', 'PHMNL-DC1')->first();
        $locShanghai = Location::where('unlocode', 'CNSHA')->first();
        $locYokohama = Location::where('unlocode', 'JPYOK')->first();
        $locJebelAli = Location::where('unlocode', 'AEJEA')->first();
        $locJeddah = Location::where('unlocode', 'SAJED')->first();
        $locRotterdam = Location::where('unlocode', 'NLRTM')->first();
        $locHamburg = Location::where('unlocode', 'DEHAM')->first();
        $locFelixstowe = Location::where('unlocode', 'GBFXT')->first();
        $locLosAngeles = Location::where('unlocode', 'USLAX')->first();
        $locNewYork = Location::where('unlocode', 'USNYC')->first();
        $locSantos = Location::where('unlocode', 'BRSSZ')->first();
        $locLagos = Location::where('unlocode', 'NGLOS')->first();
        $locMelbourne = Location::where('unlocode', 'AUMEL')->first();

        $shipments = [
            // 1. PHILIPPINES (DELIVERED) - Kopiko to Manila
            [
                'shipment_number' => 'CT-MYR-2026-001',
                'booking_reference' => 'BK-EGL-098231',
                'bill_of_lading_number' => 'EGLV0419283710',
                'container_number' => 'EGLU9182341',
                'seal_number' => 'EG782011',
                'customer_id' => $buyerPH->id,
                'carrier_id' => $carrierEvergreen->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locPuregoldDC->id,
                'current_location_id' => $locPuregoldDC->id,
                'vessel_name' => 'EVER BLINK',
                'voyage_number' => '0441-028N',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'DELIVERED',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-02-01 08:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-01 10:30:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-08 16:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-08 14:00:00'),
                'actual_arrival_at' => Carbon::parse('2026-02-08 14:15:00'),
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY (18-25 C)',
                'special_instructions' => 'Food Grade Container. Consignee: Puregold Price Club Inc.',
                'created_by' => $operator->id,
            ],

            // 2. NETHERLANDS (IN_TRANSIT, ON_TIME) - Danisa to Rotterdam
            [
                'shipment_number' => 'CT-MYR-2026-002',
                'booking_reference' => 'BK-MAE-873912',
                'bill_of_lading_number' => 'MAEU2109485721',
                'container_number' => 'MSKU4982710',
                'seal_number' => 'ML-ID882194',
                'customer_id' => $buyerNL->id,
                'carrier_id' => $carrierMaersk->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locRotterdam->id,
                'current_location_id' => $locRotterdam->id,
                'vessel_name' => 'MAERSK MC-KINNEY MOLLER',
                'voyage_number' => '2412W',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-02-15 14:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-15 15:45:00'),
                'expected_arrival_at' => Carbon::parse('2026-03-15 18:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-15 18:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'DRY_VENTILATED',
                'special_instructions' => 'Handle with Care. Do not double stack. EU Food Safety Enclosed.',
                'created_by' => $operator->id,
            ],

            // 3. USA WEST COAST (IN_TRANSIT, HIGH_DELAY 38H) - Kopiko & Beng-Beng to Los Angeles
            [
                'shipment_number' => 'CT-MYR-2026-003',
                'booking_reference' => 'BK-MSC-449102',
                'bill_of_lading_number' => 'MSCU5519827364',
                'container_number' => 'MEDU8239014',
                'seal_number' => 'MSC-US091244',
                'customer_id' => $buyerUS->id,
                'carrier_id' => $carrierMSC->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locLosAngeles->id,
                'current_location_id' => $locLosAngeles->id,
                'vessel_name' => 'MSC ISABELLA',
                'voyage_number' => 'FA411A',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'HIGH_DELAY',
                'expected_departure_at' => Carbon::parse('2026-02-05 10:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-05 11:20:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-28 08:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-01 22:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 38,
                'delay_reason' => 'Port of Los Angeles terminal congestion and chassis backlog at San Pedro Bay.',
                'temperature_control' => 'AMBIENT_DRY (Max 24 C)',
                'special_instructions' => 'US FDA Prior Notice ID #9928103 filed.',
                'created_by' => $operator->id,
            ],

            // 4. PHILIPPINES (IN_TRANSIT, MEDIUM_DELAY 16H) - Torabika to Manila
            [
                'shipment_number' => 'CT-MYR-2026-004',
                'booking_reference' => 'BK-EGL-772183',
                'bill_of_lading_number' => 'EGLV8812903411',
                'container_number' => 'EGLU4421098',
                'seal_number' => 'EG991024',
                'customer_id' => $buyerPH->id,
                'carrier_id' => $carrierEvergreen->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locManila->id,
                'current_location_id' => $locManila->id,
                'vessel_name' => 'EVER BOOM',
                'voyage_number' => '0448-032N',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'MEDIUM_DELAY',
                'expected_departure_at' => Carbon::parse('2026-02-20 06:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-20 07:15:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-27 12:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-28 04:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 16,
                'delay_reason' => 'Tropical depression weather front near Luzon Strait causing nautical speed reduction.',
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Keep dry. Moisture absorbent silica bags installed.',
                'created_by' => $operator->id,
            ],

            // 5. AUSTRALIA (BOOKED, ON_TIME) - Danisa to Melbourne
            [
                'shipment_number' => 'CT-MYR-2026-005',
                'booking_reference' => 'BK-ONE-110294',
                'bill_of_lading_number' => 'ONEY9948210341',
                'container_number' => 'ONEY2048591',
                'seal_number' => 'ON827301',
                'customer_id' => $buyerAU->id,
                'carrier_id' => $carrierONE->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locMelbourne->id,
                'current_location_id' => $locPriok->id,
                'vessel_name' => 'ONE COMMITMENT',
                'voyage_number' => '031S',
                'cargo_type' => 'FCL',
                'container_type' => '20GP',
                'status' => 'BOOKED',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-03-05 10:00:00'),
                'actual_departure_at' => null,
                'expected_arrival_at' => Carbon::parse('2026-03-20 18:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-20 18:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Australia DAFF Biosecurity Compliant. Fumigation Certificate Attached.',
                'created_by' => $operator->id,
            ],

            // 6. GERMANY (PICKED_UP, ON_TIME) - Kopiko to Hamburg
            [
                'shipment_number' => 'CT-MYR-2026-006',
                'booking_reference' => 'BK-HLC-591823',
                'bill_of_lading_number' => 'HLCUBO24091823',
                'container_number' => 'HLXU6719204',
                'seal_number' => 'HL992104',
                'customer_id' => $buyerDE->id,
                'carrier_id' => $carrierHapag->id,
                'origin_location_id' => $locJayanti->id,
                'destination_location_id' => $locHamburg->id,
                'current_location_id' => $locJayanti->id,
                'vessel_name' => 'AL ZUBARA',
                'voyage_number' => '024W',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'PICKED_UP',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-03-02 20:00:00'),
                'actual_departure_at' => null,
                'expected_arrival_at' => Carbon::parse('2026-03-29 16:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-29 16:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Stuffed at Mayora Jayanti Plant. Drayage to JICT Tanjung Priok.',
                'created_by' => $operator->id,
            ],

            // 7. CHINA (IN_TRANSIT, ON_TIME) - Danisa Butter Cookies to Shanghai
            [
                'shipment_number' => 'CT-MYR-2026-007',
                'booking_reference' => 'BK-COS-901824',
                'bill_of_lading_number' => 'COSU6319204810',
                'container_number' => 'CBHU8812903',
                'seal_number' => 'CS881023',
                'customer_id' => $buyerCN->id,
                'carrier_id' => $carrierCOSCO->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locShanghai->id,
                'current_location_id' => $locShanghai->id,
                'vessel_name' => 'COSCO SHIPPING UNIVERSE',
                'voyage_number' => '028N',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-02-18 12:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-18 13:40:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-27 10:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-27 10:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY (Spring Festival Stock)',
                'special_instructions' => 'China Customs CIQ Quarantine Certificate & Chinese Labeling Compliant.',
                'created_by' => $operator->id,
            ],

            // 8. MIDDLE EAST / UAE (IN_TRANSIT, ON_TIME) - Torabika & Kopiko to Jebel Ali, Dubai
            [
                'shipment_number' => 'CT-MYR-2026-008',
                'booking_reference' => 'BK-CMA-339182',
                'bill_of_lading_number' => 'CMDU9018247102',
                'container_number' => 'CMAU7182901',
                'seal_number' => 'CM-DXB910',
                'customer_id' => $buyerAE->id,
                'carrier_id' => $carrierCMACGM->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locJebelAli->id,
                'current_location_id' => $locJebelAli->id,
                'vessel_name' => 'CMA CGM JACQUES SAADE',
                'voyage_number' => '014W',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-02-12 18:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-12 20:15:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-26 14:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-26 14:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'GCC Standardization Organization (GSO) Arabic label packaging.',
                'created_by' => $operator->id,
            ],

            // 9. MIDDLE EAST / SAUDI ARABIA (IN_TRANSIT, MEDIUM_DELAY 14H) - Danisa & Roma to Jeddah
            [
                'shipment_number' => 'CT-MYR-2026-009',
                'booking_reference' => 'BK-MAE-910243',
                'bill_of_lading_number' => 'MAEU3309182390',
                'container_number' => 'MRKU5510293',
                'seal_number' => 'ML-SA8812',
                'customer_id' => $buyerSA->id,
                'carrier_id' => $carrierMaersk->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locJeddah->id,
                'current_location_id' => $locJeddah->id,
                'vessel_name' => 'MAERSK MC-KINNEY MOLLER',
                'voyage_number' => '2414W',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'MEDIUM_DELAY',
                'expected_departure_at' => Carbon::parse('2026-02-10 10:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-10 11:30:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-28 12:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-01 02:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 14,
                'delay_reason' => 'Red Sea maritime security convoy speed restriction near Bab el-Mandeb.',
                'temperature_control' => 'DRY_VENTILATED',
                'special_instructions' => 'Saudi Food and Drug Authority (SFDA) Halal registration verified.',
                'created_by' => $operator->id,
            ],

            // 10. JAPAN (IN_TRANSIT, ON_TIME) - Kopiko Coffee Candy to Yokohama
            [
                'shipment_number' => 'CT-MYR-2026-010',
                'booking_reference' => 'BK-ONE-881203',
                'bill_of_lading_number' => 'ONEY4419208311',
                'container_number' => 'ONEY7710294',
                'seal_number' => 'ON-JP3310',
                'customer_id' => $buyerJP->id,
                'carrier_id' => $carrierONE->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locYokohama->id,
                'current_location_id' => $locYokohama->id,
                'vessel_name' => 'ONE APUS',
                'voyage_number' => '012N',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-02-17 08:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-17 09:30:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-28 16:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-28 16:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Japan Ministry of Health, Labour and Welfare (MHLW) Food Sanitation Law certificate.',
                'created_by' => $operator->id,
            ],

            // 11. UNITED KINGDOM (DELIVERED) - Danisa Butter Cookies to Felixstowe
            [
                'shipment_number' => 'CT-MYR-2026-011',
                'booking_reference' => 'BK-CMA-771024',
                'bill_of_lading_number' => 'CMDU8812049102',
                'container_number' => 'CMAU3381920',
                'seal_number' => 'CM-GB4410',
                'customer_id' => $buyerUK->id,
                'carrier_id' => $carrierCMACGM->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locFelixstowe->id,
                'current_location_id' => $locFelixstowe->id,
                'vessel_name' => 'CMA CGM ANTOINE DE SAINT EXUPERY',
                'voyage_number' => '022W',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'DELIVERED',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-01-10 14:00:00'),
                'actual_departure_at' => Carbon::parse('2026-01-10 16:20:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-14 18:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-14 15:30:00'),
                'actual_arrival_at' => Carbon::parse('2026-02-14 16:00:00'),
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'DRY_VENTILATED',
                'special_instructions' => 'Delivered to Tesco Distribution Centre, Daventry UK.',
                'created_by' => $operator->id,
            ],

            // 12. AFRICA / NIGERIA (IN_TRANSIT, HIGH_DELAY 42H) - Torabika & Beng-Beng to Lagos
            [
                'shipment_number' => 'CT-MYR-2026-012',
                'booking_reference' => 'BK-MSC-881920',
                'bill_of_lading_number' => 'MSCU7710294810',
                'container_number' => 'MEDU4491823',
                'seal_number' => 'MSC-NG9910',
                'customer_id' => $buyerNG->id,
                'carrier_id' => $carrierMSC->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locLagos->id,
                'current_location_id' => $locLagos->id,
                'vessel_name' => 'MSC LORETTO',
                'voyage_number' => 'AF2411',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'HIGH_DELAY',
                'expected_departure_at' => Carbon::parse('2026-01-25 10:00:00'),
                'actual_departure_at' => Carbon::parse('2026-01-25 12:45:00'),
                'expected_arrival_at' => Carbon::parse('2026-02-26 12:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-02-28 06:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 42,
                'delay_reason' => 'Severe berthing queue at Port of Lagos / Apapa quays.',
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'NAFDAC Nigeria regulatory certificate cleared.',
                'created_by' => $operator->id,
            ],

            // 13. SOUTH AMERICA / BRAZIL (CREATED) - Kopiko & Danisa to Santos
            [
                'shipment_number' => 'CT-MYR-2026-013',
                'booking_reference' => 'BK-MAE-718290',
                'bill_of_lading_number' => 'MAEU5519204819',
                'container_number' => 'MSKU2219401',
                'seal_number' => 'PENDING-SEAL',
                'customer_id' => $buyerBR->id,
                'carrier_id' => $carrierMaersk->id,
                'origin_location_id' => $locJayanti->id,
                'destination_location_id' => $locSantos->id,
                'current_location_id' => $locJayanti->id,
                'vessel_name' => 'MAERSK BATUR (TENTATIVE)',
                'voyage_number' => 'SA2416',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'CREATED',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-03-12 08:00:00'),
                'actual_departure_at' => null,
                'expected_arrival_at' => Carbon::parse('2026-04-18 14:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-04-18 14:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'ANVISA Brazil food import compliance declaration pending dispatch.',
                'created_by' => $operator->id,
            ],

            // 14. USA EAST COAST (BOOKED) - Danisa to New York
            [
                'shipment_number' => 'CT-MYR-2026-014',
                'booking_reference' => 'BK-MSC-110293',
                'bill_of_lading_number' => 'MSCU1192847102',
                'container_number' => 'MEDU9910243',
                'seal_number' => 'MSC-NY0019',
                'customer_id' => $buyerUS->id,
                'carrier_id' => $carrierMSC->id,
                'origin_location_id' => $locPriok->id,
                'destination_location_id' => $locNewYork->id,
                'current_location_id' => $locPriok->id,
                'vessel_name' => 'MSC TIANPING',
                'voyage_number' => 'US2418',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'BOOKED',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-03-08 12:00:00'),
                'actual_departure_at' => null,
                'expected_arrival_at' => Carbon::parse('2026-04-05 18:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-04-05 18:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Transatlantic routing via Suez/Gibraltar to Elizabeth NJ.',
                'created_by' => $operator->id,
            ],

            // =========================================================================
            // 15. AIR CARGO 1: Kargo Udara Ekspres Kopiko ke Singapura (IN_TRANSIT)
            // =========================================================================
            [
                'shipment_number' => 'CT-MYR-2026-AIR-01',
                'booking_reference' => 'AWB-SQ-910248',
                'bill_of_lading_number' => 'AWB-618-91024810',
                'container_number' => 'PMC-SQ89104',
                'seal_number' => 'AIR-SEAL-SQ01',
                'customer_id' => $buyerPH->id ?? $operator->id,
                'carrier_id' => $carrierSQCargo->id,
                'origin_location_id' => $locCGK->id,
                'destination_location_id' => $locChangiAir->id,
                'current_location_id' => $locCGK->id,
                'transport_mode' => 'AIR',
                'vehicle_identifier' => 'SQ7235 (Boeing 747-400F Freighter)',
                'vessel_name' => 'Boeing 747-400F (SQ7235)',
                'voyage_number' => 'SQ7235',
                'cargo_type' => 'FCL',
                'container_type' => '20GP',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-03-01 06:30:00'),
                'actual_departure_at' => Carbon::parse('2026-03-01 06:45:00'),
                'expected_arrival_at' => Carbon::parse('2026-03-01 09:30:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-01 09:30:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'High-Priority Air Cargo. Express retail display replenishment for Kopiko Candy.',
                'created_by' => $operator->id,
            ],

            // =========================================================================
            // 16. AIR CARGO 2: Kargo Udara Danisa ke Amsterdam Schiphol (IN_TRANSIT)
            // =========================================================================
            [
                'shipment_number' => 'CT-MYR-2026-AIR-02',
                'booking_reference' => 'AWB-KLM-401928',
                'bill_of_lading_number' => 'AWB-074-40192845',
                'container_number' => 'PAG-KL41092',
                'seal_number' => 'AIR-SEAL-KL02',
                'customer_id' => $buyerNL->id ?? $operator->id,
                'carrier_id' => $carrierKLCargo->id,
                'origin_location_id' => $locCGK->id,
                'destination_location_id' => $locSchipholAir->id,
                'current_location_id' => $locCGK->id,
                'transport_mode' => 'AIR',
                'vehicle_identifier' => 'KL809 (Boeing 777F Freighter)',
                'vessel_name' => 'Boeing 777F (KL809)',
                'voyage_number' => 'KL809',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'LOW_DELAY',
                'expected_departure_at' => Carbon::parse('2026-02-28 22:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-28 23:30:00'),
                'expected_arrival_at' => Carbon::parse('2026-03-01 14:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-01 16:30:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 2,
                'delay_reason' => 'Air Traffic Control Ground Delay at transit waypoint.',
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Perishable Confectionery Samples. Air Cargo express clearance.',
                'created_by' => $operator->id,
            ],

            // =========================================================================
            // 17. ROAD CARGO 1: Truk Wingbox Jayanti ke Surabaya (IN_TRANSIT)
            // =========================================================================
            [
                'shipment_number' => 'CT-MYR-2026-TRK-01',
                'booking_reference' => 'DO-MYR-881920',
                'bill_of_lading_number' => 'CMR-ID-8819201',
                'container_number' => 'TRK-WBX-9812',
                'seal_number' => 'MYR-TRK-9812',
                'customer_id' => $operator->id,
                'carrier_id' => $carrierMayoraLog->id,
                'origin_location_id' => $locJayanti->id,
                'destination_location_id' => $locSurabayaDC->id,
                'current_location_id' => $locJayanti->id,
                'transport_mode' => 'ROAD',
                'vehicle_identifier' => 'Volvo FMX 440 Wingbox (B 9812 UIX)',
                'vessel_name' => 'Volvo FMX 440 (B 9812 UIX)',
                'voyage_number' => 'TRANS-JAVA-04',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-03-01 08:00:00'),
                'actual_departure_at' => Carbon::parse('2026-03-01 08:30:00'),
                'expected_arrival_at' => Carbon::parse('2026-03-02 04:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-02 04:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Trans-Java Toll Road Route. GPS tracker unit online. Temperature controlled.',
                'created_by' => $operator->id,
            ],

            // =========================================================================
            // 18. ROAD CARGO 2: Truk Tronton Jayanti ke Medan (Percut Sei Tuan) (IN_TRANSIT)
            // =========================================================================
            [
                'shipment_number' => 'CT-MYR-2026-TRK-02',
                'booking_reference' => 'DO-MYR-992018',
                'bill_of_lading_number' => 'CMR-ID-9920182',
                'container_number' => 'TRK-TRT-9421',
                'seal_number' => 'MYR-TRK-9421',
                'customer_id' => $operator->id,
                'carrier_id' => $carrierDunex->id,
                'origin_location_id' => $locJayanti->id,
                'destination_location_id' => $locMedanDC->id,
                'current_location_id' => $locBakauheni->id,
                'transport_mode' => 'ROAD',
                'vehicle_identifier' => 'Hino 500 Heavy Trailer (B 9421 KPY)',
                'vessel_name' => 'Hino 500 Heavy Trailer (B 9421 KPY)',
                'voyage_number' => 'TRANS-SUMATRA-01',
                'cargo_type' => 'FCL',
                'container_type' => '40HC',
                'status' => 'IN_TRANSIT',
                'delay_status' => 'ON_TIME',
                'expected_departure_at' => Carbon::parse('2026-02-27 10:00:00'),
                'actual_departure_at' => Carbon::parse('2026-02-27 11:15:00'),
                'expected_arrival_at' => Carbon::parse('2026-03-03 16:00:00'),
                'estimated_arrival_at' => Carbon::parse('2026-03-03 16:00:00'),
                'actual_arrival_at' => null,
                'delay_duration_hours' => 0,
                'delay_reason' => null,
                'temperature_control' => 'AMBIENT_DRY',
                'special_instructions' => 'Lintas Sumatera Highway corridor. Destination: Percut Sei Tuan, Kab. Deli Serdang.',
                'created_by' => $operator->id,
            ],
        ];

        foreach ($shipments as $data) {
            Shipment::updateOrCreate(
                ['shipment_number' => $data['shipment_number']],
                $data
            );
        }
    }
}
