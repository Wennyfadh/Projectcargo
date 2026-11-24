<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * In-app notifications alerting buyers across the globe and internal operations team.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.mayora.co.id')->first();
        $buyerPH = User::where('email', 'buyer.philippines@puregold.com.ph')->first();
        $buyerCN = User::where('email', 'buyer.china@vanguard.cn')->first();
        $buyerAE = User::where('email', 'buyer.middleeast@lulugroup.ae')->first();
        $buyerSA = User::where('email', 'buyer.saudi@bindawood.com')->first();
        $buyerEU = User::where('email', 'buyer.europe@orientalgroup.nl')->first();
        $buyerUK = User::where('email', 'buyer.uk@tesco.com')->first();
        $buyerUS = User::where('email', 'buyer.usa@asianfoodgrocer.com')->first();
        $buyerNG = User::where('email', 'buyer.africa@spar.ng')->first();

        $s1 = Shipment::where('shipment_number', 'CT-MYR-2026-001')->first();
        $s3 = Shipment::where('shipment_number', 'CT-MYR-2026-003')->first();
        $s4 = Shipment::where('shipment_number', 'CT-MYR-2026-004')->first();
        $s7 = Shipment::where('shipment_number', 'CT-MYR-2026-007')->first();
        $s9 = Shipment::where('shipment_number', 'CT-MYR-2026-009')->first();
        $s11 = Shipment::where('shipment_number', 'CT-MYR-2026-011')->first();
        $s12 = Shipment::where('shipment_number', 'CT-MYR-2026-012')->first();

        $notifications = [
            // Philippines - Delivered
            [
                'user_id' => $buyerPH->id,
                'shipment_id' => $s1?->id,
                'title' => 'Shipment Delivered: CT-MYR-2026-001',
                'message' => 'Your shipment of 1,600 cartons of Kopiko 100g has been successfully delivered to Puregold Central Logistics DC, Valenzuela.',
                'type' => 'INFO',
                'is_read' => true,
                'read_at' => Carbon::parse('2026-02-08 15:30:00'),
                'created_at' => Carbon::parse('2026-02-08 14:20:00'),
            ],
            // China - In Transit on Schedule
            [
                'user_id' => $buyerCN->id,
                'shipment_id' => $s7?->id,
                'title' => 'Voyage Update: CT-MYR-2026-007 to Shanghai',
                'message' => 'Vessel COSCO SHIPPING UNIVERSE is transiting the Taiwan Strait on schedule. Expected arrival at Shanghai Yangshan Port: Feb 27, 10:00.',
                'type' => 'INFO',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::parse('2026-02-24 17:00:00'),
            ],
            // USA - Critical Delay (Port Congestion 38h)
            [
                'user_id' => $buyerUS->id,
                'shipment_id' => $s3?->id,
                'title' => 'Critical Delay Alert: CT-MYR-2026-003 at Port of Los Angeles',
                'message' => 'Vessel MSC ISABELLA is queued at San Pedro Bay outer anchorage due to terminal congestion. Delay: 38 hours. Dynamic ETA updated to 2026-03-01 22:00.',
                'type' => 'CRITICAL',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::parse('2026-02-27 18:30:00'),
            ],
            // UK - Delivered to Tesco
            [
                'user_id' => $buyerUK->id,
                'shipment_id' => $s11?->id,
                'title' => 'Shipment Delivered: CT-MYR-2026-011',
                'message' => 'Danisa Butter Cookies consignment delivered into Tesco Distribution Centre Daventry. Consignee sign-off complete.',
                'type' => 'INFO',
                'is_read' => true,
                'read_at' => Carbon::parse('2026-02-14 17:00:00'),
                'created_at' => Carbon::parse('2026-02-14 16:15:00'),
            ],
            // Saudi Arabia - Security Delay
            [
                'user_id' => $buyerSA->id,
                'shipment_id' => $s9?->id,
                'title' => 'Transit Advisory: CT-MYR-2026-009 near Bab el-Mandeb',
                'message' => 'Vessel participating in naval security convoy into Red Sea. ETA at Jeddah revised +14 hours to March 01 02:00.',
                'type' => 'WARNING',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::parse('2026-02-23 15:00:00'),
            ],
            // Nigeria - Port Congestion
            [
                'user_id' => $buyerNG->id,
                'shipment_id' => $s12?->id,
                'title' => 'Berthing Advisory: CT-MYR-2026-012 at Port of Lagos',
                'message' => 'Vessel MSC LORETTO held at Lagos anchorage awaiting container berth. Delay impact: 42 hours.',
                'type' => 'CRITICAL',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::parse('2026-02-25 17:00:00'),
            ],
            // Operator - Global Escalation
            [
                'user_id' => $operator->id,
                'shipment_id' => $s12?->id,
                'title' => 'High Delay Threshold Crossed: CT-MYR-2026-012 (Lagos)',
                'message' => 'Shipment CT-MYR-2026-012 has reached 42 hours delay at Apapa Port. Action required: check with MSC West Africa line.',
                'type' => 'CRITICAL',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::parse('2026-02-25 16:15:00'),
            ],
        ];

        foreach ($notifications as $n) {
            Notification::create($n);
        }
    }
}
