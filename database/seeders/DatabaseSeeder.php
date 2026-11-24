<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Master orchestrator for CargoTrace - PT Mayora Indah Tbk Export Visibility Platform.
     */
    public function run(): void
    {
        $this->command?->info('Seeding CargoTrace - Real-World Export Logistics Data for PT Mayora Indah Tbk...');

        DB::transaction(function () {
            // 1. Roles & Users (Admin, Logistics Operators, Global Buyers)
            $this->call(UserSeeder::class);
            $this->command?->info('✓ Users seeded (Admin, Operators, Global Consignees).');

            // 2. Real Ocean Carriers (Evergreen, Maersk, MSC, ONE, CMA CGM)
            $this->call(CarrierSeeder::class);
            $this->command?->info('✓ Ocean Carriers seeded with official SCAC and tracking endpoints.');

            // 3. Geographic Facilities & Marine Terminals (Tanjung Priok, Manila, Rotterdam, LA, Melbourne)
            $this->call(LocationSeeder::class);
            $this->command?->info('✓ Locations seeded with real UN/LOCODE and GPS coordinates.');

            // 4. Shipments across full lifecycles & delay states
            $this->call(ShipmentSeeder::class);
            $this->command?->info('✓ Shipments seeded across all lifecycle statuses.');

            // 5. Authentic Export Commodities (Kopiko Candy, Danisa Cookies, Torabika Coffee, Beng-Beng)
            $this->call(ShipmentItemSeeder::class);
            $this->command?->info('✓ Shipment Items seeded with authentic Master Carton packaging & HS Codes.');

            // 6. Chronological Milestone Tracking Events & Marine Waypoints
            $this->call(TrackingEventSeeder::class);
            $this->command?->info('✓ Chronological Tracking Events seeded.');

            // 7. Realistic Operational & Regulatory Exceptions (Customs Hold, Weather Delay, Port Congestion)
            $this->call(ShipmentExceptionSeeder::class);
            $this->command?->info('✓ Shipment Exceptions seeded.');

            // 8. In-App Notifications for Operators & Buyers
            $this->call(NotificationSeeder::class);
            $this->command?->info('✓ In-App Notifications seeded.');

            // 9. Immutable Audit Trail
            $this->call(AuditLogSeeder::class);
            $this->command?->info('✓ Audit Logs seeded.');
        });

        $this->command?->info('========================================================================');
        $this->command?->info('CargoTrace Database Seeded Successfully with 100% Real-World Logistics Data!');
        $this->command?->info('========================================================================');
    }
}
