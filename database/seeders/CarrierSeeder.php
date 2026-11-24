<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Top Global Ocean Container Shipping Alliances & Carriers:
     * 2M, Ocean Alliance, THE Alliance / Gemini Cooperation.
     */
    public function run(): void
    {
        $carriers = [
            [
                'scac' => 'MSCU',
                'name' => 'Mediterranean Shipping Company (MSC)',
                'contact_email' => 'idn-info@msc.com',
                'contact_phone' => '+62-21-5296-2288',
                'website_url' => 'https://www.msc.com',
                'tracking_url_template' => 'https://www.msc.com/en/track-a-shipment?query={tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'MAEU',
                'name' => 'A.P. Moller - Maersk',
                'contact_email' => 'id.export@maersk.com',
                'contact_phone' => '+62-21-3006-5600',
                'website_url' => 'https://www.maersk.com',
                'tracking_url_template' => 'https://www.maersk.com/tracking/{tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'CMDU',
                'name' => 'CMA CGM Group',
                'contact_email' => 'dja.exportcs@cma-cgm.com',
                'contact_phone' => '+62-21-2994-2000',
                'website_url' => 'https://www.cma-cgm.com',
                'tracking_url_template' => 'https://www.cma-cgm.com/ebusiness/tracking/search?SearchBy=BL&Reference={tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'COSCO',
                'name' => 'COSCO Shipping Lines',
                'contact_email' => 'cs.id@coscon.com',
                'contact_phone' => '+62-21-521-1888',
                'website_url' => 'https://lines.coscoshipping.com',
                'tracking_url_template' => 'https://elines.coscoshipping.com/ebusiness/cargoTracking?trackingType=BILLOFLADING&number={tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'HLCU',
                'name' => 'Hapag-Lloyd AG',
                'contact_email' => 'indonesia@hlag.com',
                'contact_phone' => '+62-21-521-2000',
                'website_url' => 'https://www.hapag-lloyd.com',
                'tracking_url_template' => 'https://www.hapag-lloyd.com/en/online-business/track/track-by-booking-solution.html?blno={tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'ONEY',
                'name' => 'Ocean Network Express (ONE)',
                'contact_email' => 'id.csv@one-line.com',
                'contact_phone' => '+62-21-3040-5200',
                'website_url' => 'https://www.one-line.com',
                'tracking_url_template' => 'https://ecomm.one-line.com/one-ecom/manage-shipment/cargo-tracking?ctrac-field={tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'EGLV',
                'name' => 'Evergreen Marine Corp.',
                'contact_email' => 'booking.jkt@evergreen-marine.com',
                'contact_phone' => '+62-21-252-0788',
                'website_url' => 'https://www.evergreen-marine.com',
                'tracking_url_template' => 'https://www.shipmentlink.com/servlet/TTrk_TracnS?BL={tracking_number}',
                'is_active' => true,
            ],

            // AIR CARGO CARRIERS
            [
                'scac' => 'SQCARGO',
                'name' => 'Singapore Airlines Cargo (SQ Cargo)',
                'contact_email' => 'cargo_jkt@singaporeair.com.sg',
                'contact_phone' => '+62-21-550-7171',
                'website_url' => 'https://www.siacargo.com',
                'tracking_url_template' => 'https://www.siacargo.com/track-and-trace?awb={tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'KLCARGO',
                'name' => 'Air France KLM Martinair Cargo',
                'contact_email' => 'cargo.indonesia@afklmp.com',
                'contact_phone' => '+62-21-550-5151',
                'website_url' => 'https://www.afklcargo.com',
                'tracking_url_template' => 'https://www.afklcargo.com/mycargo/shipment/tracking/{tracking_number}',
                'is_active' => true,
            ],

            // OVERLAND & ROAD FREIGHT FLEET
            [
                'scac' => 'MYR-LOG',
                'name' => 'PT Mayora Indah Tbk Heavy Transport Fleet',
                'contact_email' => 'logistics.fleet@mayora.co.id',
                'contact_phone' => '+62-21-8063-7000',
                'website_url' => 'https://www.mayora.com',
                'tracking_url_template' => 'https://cargotrace.mayora.co.id/tracking/{tracking_number}',
                'is_active' => true,
            ],
            [
                'scac' => 'DUNEX',
                'name' => 'Dunex Logistik Trans Mandiri (Inter-Island Trucking)',
                'contact_email' => 'ops@dunex.co.id',
                'contact_phone' => '+62-21-4390-8888',
                'website_url' => 'https://www.dunex.co.id',
                'tracking_url_template' => 'https://www.dunex.co.id/tracking?no={tracking_number}',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $carrierData) {
            Carrier::updateOrCreate(
                ['scac' => $carrierData['scac']],
                $carrierData
            );
        }
    }
}
