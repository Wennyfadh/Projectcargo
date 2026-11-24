<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Database\Seeder;

class ShipmentItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Authentic Mayora Export Commodities across 14 Worldwide Shipments.
     */
    public function run(): void
    {
        $items = [
            // 1. Shipment 1: Kopiko 100g to Manila (Puregold) - 1,600 Master Cartons
            [
                'shipment_number' => 'CT-MYR-2026-001',
                'sku' => 'KPK-100-POUCH',
                'product_name' => 'Kopiko Real Coffee Candy 100g Hanging Pouch',
                'hs_code' => '1704.90.10',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1600,
                'units_per_package' => 40,
                'net_weight_kg' => 6400.00,
                'gross_weight_kg' => 7280.00,
                'volume_cbm' => 32.1600,
                'packaging_description' => '1 Master Corrugated Carton = 40 pouches x 100g. Stuffed in 40ft High Cube Container.',
            ],

            // 2. Shipment 2: Danisa 454g to Rotterdam (Oriental Group) - 1,400 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-002',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1400,
                'units_per_package' => 12,
                'net_weight_kg' => 7630.00,
                'gross_weight_kg' => 10920.00,
                'volume_cbm' => 53.4800,
                'packaging_description' => '1 Master Shipper Carton = 12 Embossed Metal Tins x 454g.',
            ],

            // 3. Shipment 3: Mixed to Los Angeles (Asian Food Grocer)
            [
                'shipment_number' => 'CT-MYR-2026-003',
                'sku' => 'KPK-CAP-100',
                'product_name' => 'Kopiko Cappuccino Coffee Candy 100g Pouch',
                'hs_code' => '1704.90.10',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1000,
                'units_per_package' => 40,
                'net_weight_kg' => 4000.00,
                'gross_weight_kg' => 4550.00,
                'volume_cbm' => 20.1000,
                'packaging_description' => '1 Master Shipper Carton = 40 pouches x 100g. Palletized.',
            ],
            [
                'shipment_number' => 'CT-MYR-2026-003',
                'sku' => 'BNG-20-BOX',
                'product_name' => 'Beng-Beng 4-in-1 Chocolate Wafer Snack 20g',
                'hs_code' => '1905.32.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1200,
                'units_per_package' => 136,
                'net_weight_kg' => 3264.00,
                'gross_weight_kg' => 4080.00,
                'volume_cbm' => 22.2000,
                'packaging_description' => '1 Master Shipper Carton = 8 display boxes x 17 bars x 20g.',
            ],

            // 4. Shipment 4: Torabika to Manila - 1,500 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-004',
                'sku' => 'TRB-CAP-20S',
                'product_name' => 'Torabika Cappuccino Instant Coffee with Choco Granule',
                'hs_code' => '2101.12.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1500,
                'units_per_package' => 240,
                'net_weight_kg' => 9000.00,
                'gross_weight_kg' => 10350.00,
                'volume_cbm' => 48.0000,
                'packaging_description' => '1 Master Shipper Carton = 12 bags x (20 sachets x 25g).',
            ],

            // 5. Shipment 5: Danisa to Melbourne - 800 Cartons (20GP)
            [
                'shipment_number' => 'CT-MYR-2026-005',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 800,
                'units_per_package' => 12,
                'net_weight_kg' => 4360.00,
                'gross_weight_kg' => 6240.00,
                'volume_cbm' => 30.5600,
                'packaging_description' => '1 Master Shipper Carton = 12 Tins x 454g. Biosecurity timber pallets.',
            ],

            // 6. Shipment 6: Kopiko to Hamburg (Germany) - 1,600 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-006',
                'sku' => 'KPK-100-POUCH',
                'product_name' => 'Kopiko Real Coffee Candy 100g Hanging Pouch',
                'hs_code' => '1704.90.10',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1600,
                'units_per_package' => 40,
                'net_weight_kg' => 6400.00,
                'gross_weight_kg' => 7280.00,
                'volume_cbm' => 32.1600,
                'packaging_description' => 'Stuffed at Mayora Jayanti Factory. German EU food label compliant.',
            ],

            // 7. Shipment 7: Danisa to Shanghai (China) - 1,500 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-007',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1500,
                'units_per_package' => 12,
                'net_weight_kg' => 8175.00,
                'gross_weight_kg' => 11700.00,
                'volume_cbm' => 57.3000,
                'packaging_description' => 'Special Chinese New Year / Spring Festival commemorative master packaging.',
            ],

            // 8. Shipment 8: Torabika & Kopiko to Dubai (UAE)
            [
                'shipment_number' => 'CT-MYR-2026-008',
                'sku' => 'TRB-CAP-20S',
                'product_name' => 'Torabika Cappuccino Instant Coffee with Choco Granule',
                'hs_code' => '2101.12.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1000,
                'units_per_package' => 240,
                'net_weight_kg' => 6000.00,
                'gross_weight_kg' => 6900.00,
                'volume_cbm' => 32.0000,
                'packaging_description' => 'LuLu Hypermarket UAE import consignment. Bilingual Arabic/English.',
            ],
            [
                'shipment_number' => 'CT-MYR-2026-008',
                'sku' => 'KPK-100-POUCH',
                'product_name' => 'Kopiko Real Coffee Candy 100g Hanging Pouch',
                'hs_code' => '1704.90.10',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 600,
                'units_per_package' => 40,
                'net_weight_kg' => 2400.00,
                'gross_weight_kg' => 2730.00,
                'volume_cbm' => 12.0600,
                'packaging_description' => 'GCC Halal certified.',
            ],

            // 9. Shipment 9: Danisa & Roma to Jeddah (Saudi Arabia)
            [
                'shipment_number' => 'CT-MYR-2026-009',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 900,
                'units_per_package' => 12,
                'net_weight_kg' => 4905.00,
                'gross_weight_kg' => 7020.00,
                'volume_cbm' => 34.3800,
                'packaging_description' => 'Consignee: BinDawood Holding Jeddah.',
            ],
            [
                'shipment_number' => 'CT-MYR-2026-009',
                'sku' => 'ROM-KEL-300',
                'product_name' => 'Roma Kelapa Coconut Biscuits 300g Family Pack',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 800,
                'units_per_package' => 24,
                'net_weight_kg' => 5760.00,
                'gross_weight_kg' => 6880.00,
                'volume_cbm' => 28.1600,
                'packaging_description' => '1 Master Shipper Carton = 24 packs x 300g.',
            ],

            // 10. Shipment 10: Kopiko to Yokohama (Japan) - 1,400 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-010',
                'sku' => 'KPK-100-POUCH',
                'product_name' => 'Kopiko Real Coffee Candy 100g Hanging Pouch',
                'hs_code' => '1704.90.10',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1400,
                'units_per_package' => 40,
                'net_weight_kg' => 5600.00,
                'gross_weight_kg' => 6370.00,
                'volume_cbm' => 28.1400,
                'packaging_description' => 'Japan JAS compliant labeling on retail pouches.',
            ],

            // 11. Shipment 11: Danisa to Felixstowe (UK / Tesco) - 1,400 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-011',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1400,
                'units_per_package' => 12,
                'net_weight_kg' => 7630.00,
                'gross_weight_kg' => 10920.00,
                'volume_cbm' => 53.4800,
                'packaging_description' => 'Delivered to Tesco Distribution Centre, Daventry UK.',
            ],

            // 12. Shipment 12: Torabika & Beng-Beng to Lagos (Nigeria)
            [
                'shipment_number' => 'CT-MYR-2026-012',
                'sku' => 'TRB-CAP-20S',
                'product_name' => 'Torabika Cappuccino Instant Coffee with Choco Granule',
                'hs_code' => '2101.12.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1200,
                'units_per_package' => 240,
                'net_weight_kg' => 7200.00,
                'gross_weight_kg' => 8280.00,
                'volume_cbm' => 38.4000,
                'packaging_description' => 'NAFDAC Form M registration #NG-881920.',
            ],
            [
                'shipment_number' => 'CT-MYR-2026-012',
                'sku' => 'BNG-20-BOX',
                'product_name' => 'Beng-Beng 4-in-1 Chocolate Wafer Snack 20g',
                'hs_code' => '1905.32.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 800,
                'units_per_package' => 136,
                'net_weight_kg' => 2176.00,
                'gross_weight_kg' => 2720.00,
                'volume_cbm' => 14.8000,
                'packaging_description' => 'SPAR Nigeria Supermarkets import.',
            ],

            // 13. Shipment 13: Kopiko & Danisa to Santos (Brazil)
            [
                'shipment_number' => 'CT-MYR-2026-013',
                'sku' => 'KPK-100-POUCH',
                'product_name' => 'Kopiko Real Coffee Candy 100g Hanging Pouch',
                'hs_code' => '1704.90.10',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 800,
                'units_per_package' => 40,
                'net_weight_kg' => 3200.00,
                'gross_weight_kg' => 3640.00,
                'volume_cbm' => 16.0800,
                'packaging_description' => 'Portuguese nutritional information label.',
            ],
            [
                'shipment_number' => 'CT-MYR-2026-013',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 700,
                'units_per_package' => 12,
                'net_weight_kg' => 3815.00,
                'gross_weight_kg' => 5460.00,
                'volume_cbm' => 26.7400,
                'packaging_description' => 'Consignee: Grupo Pão de Açúcar, São Paulo.',
            ],

            // 14. Shipment 14: Danisa to New York (USA) - 1,400 Cartons
            [
                'shipment_number' => 'CT-MYR-2026-014',
                'sku' => 'DNS-454-TIN',
                'product_name' => 'Danisa Traditional Butter Cookies 454g Royal Tin',
                'hs_code' => '1905.31.00',
                'package_type' => 'MASTER_CARTON',
                'quantity' => 1400,
                'units_per_package' => 12,
                'net_weight_kg' => 7630.00,
                'gross_weight_kg' => 10920.00,
                'volume_cbm' => 53.4800,
                'packaging_description' => 'Consignee: Asian Food Grocer Inc. / Cost Plus World Market (US East Coast).',
            ],
        ];

        foreach ($items as $itemData) {
            $shipment = Shipment::where('shipment_number', $itemData['shipment_number'])->first();
            if ($shipment) {
                unset($itemData['shipment_number']);
                $itemData['shipment_id'] = $shipment->id;
                ShipmentItem::create($itemData);
            }
        }
    }
}
