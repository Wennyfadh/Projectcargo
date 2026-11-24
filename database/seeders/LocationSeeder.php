<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Comprehensive global logistics locations covering all major continents:
     * Asia, Europe, North America, South America, Middle East, Africa, Oceania.
     */
    public function run(): void
    {
        $locations = [
            // =========================================================================
            // 1. ORIGIN & DOMESTIC INDONESIA (FACTORY, PORT, CUSTOMS)
            // =========================================================================
            [
                'unlocode' => 'IDTPP-JAY',
                'name' => 'PT Mayora Indah Tbk - Jayanti Manufacturing Plant',
                'facility_type' => 'FACTORY',
                'address' => 'Jl. Raya Serang Km. 32.5, Desa Sumur Bandung, Kec. Jayanti',
                'city' => 'Tangerang, Banten',
                'country_code' => 'ID',
                'latitude' => -6.2131000,
                'longitude' => 106.3768000,
                'timezone' => 'Asia/Jakarta',
            ],
            [
                'unlocode' => 'IDTPP-JAT',
                'name' => 'PT Mayora Indah Tbk - Jatake Industrial Plant',
                'facility_type' => 'FACTORY',
                'address' => 'Kawasan Industri Jatake, Jl. Telesonic No. 1, Jatiuwung',
                'city' => 'Tangerang, Banten',
                'country_code' => 'ID',
                'latitude' => -6.2085000,
                'longitude' => 106.5684000,
                'timezone' => 'Asia/Jakarta',
            ],
            [
                'unlocode' => 'IDTPP',
                'name' => 'Jakarta International Container Terminal (JICT) - Tanjung Priok',
                'facility_type' => 'PORT',
                'address' => 'Jl. Sulawesi No. 1, Ujung Pelabuhan Tanjung Priok',
                'city' => 'Jakarta Utara',
                'country_code' => 'ID',
                'latitude' => -6.1018000,
                'longitude' => 106.8837000,
                'timezone' => 'Asia/Jakarta',
            ],
            [
                'unlocode' => 'IDTPP-CUS',
                'name' => 'KPU Bea dan Cukai Tipe A Tanjung Priok (Customs Examination Depot)',
                'facility_type' => 'CUSTOMS_OFFICE',
                'address' => 'Jl. Pabean No. 1, Pelabuhan Tanjung Priok',
                'city' => 'Jakarta Utara',
                'country_code' => 'ID',
                'latitude' => -6.1042000,
                'longitude' => 106.8805000,
                'timezone' => 'Asia/Jakarta',
            ],

            // =========================================================================
            // 2. SOUTHEAST ASIA & TRANSSHIPMENT HUBS
            // =========================================================================
            [
                'unlocode' => 'SGSIN',
                'name' => 'PSA Singapore Container Terminals (Port of Singapore)',
                'facility_type' => 'PORT',
                'address' => '7B Keppel Road, Tanjong Pagar / Pasir Panjang Complex',
                'city' => 'Singapore',
                'country_code' => 'SG',
                'latitude' => 1.2644000,
                'longitude' => 103.8228000,
                'timezone' => 'Asia/Singapore',
            ],
            [
                'unlocode' => 'PHMNL',
                'name' => 'Manila International Container Terminal (MICT - Port of Manila)',
                'facility_type' => 'PORT',
                'address' => 'MICT South Access Road, Port Area, Tondo',
                'city' => 'Manila',
                'country_code' => 'PH',
                'latitude' => 14.5840000,
                'longitude' => 120.9634000,
                'timezone' => 'Asia/Manila',
            ],
            [
                'unlocode' => 'PHMNL-DC1',
                'name' => 'Puregold Central Logistics Distribution Center',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Paso de Blas Road, Valenzuela City, Metro Manila',
                'city' => 'Valenzuela',
                'country_code' => 'PH',
                'latitude' => 14.7082000,
                'longitude' => 120.9951000,
                'timezone' => 'Asia/Manila',
            ],
            [
                'unlocode' => 'THLCH',
                'name' => 'Port of Laem Chabang',
                'facility_type' => 'PORT',
                'address' => 'Sukhumvit Road, Si Racha District, Chonburi 20230',
                'city' => 'Chonburi / Bangkok',
                'country_code' => 'TH',
                'latitude' => 13.0827000,
                'longitude' => 100.8845000,
                'timezone' => 'Asia/Bangkok',
            ],
            [
                'unlocode' => 'MYPKG',
                'name' => 'Port Klang (Westports Terminal)',
                'facility_type' => 'PORT',
                'address' => 'Westports, Pulau Indah, 42920 Port Klang, Selangor',
                'city' => 'Port Klang / Kuala Lumpur',
                'country_code' => 'MY',
                'latitude' => 2.9999000,
                'longitude' => 101.3928000,
                'timezone' => 'Asia/Kuala_Lumpur',
            ],

            // =========================================================================
            // 3. EAST ASIA (CHINA, JAPAN, SOUTH KOREA)
            // =========================================================================
            [
                'unlocode' => 'CNSHA',
                'name' => 'Port of Shanghai (Yangshan Deep-Water Port Phase IV)',
                'facility_type' => 'PORT',
                'address' => 'Shengsi, Zhoushan, Zhejiang / Yangshan Island, Shanghai',
                'city' => 'Shanghai',
                'country_code' => 'CN',
                'latitude' => 30.6276000,
                'longitude' => 122.0645000,
                'timezone' => 'Asia/Shanghai',
            ],
            [
                'unlocode' => 'JPYOK',
                'name' => 'Port of Yokohama (Minami Honmoku Pier)',
                'facility_type' => 'PORT',
                'address' => 'Naka Ward, Yokohama, Kanagawa 231-0001',
                'city' => 'Yokohama / Tokyo Bay',
                'country_code' => 'JP',
                'latitude' => 35.4430000,
                'longitude' => 139.6380000,
                'timezone' => 'Asia/Tokyo',
            ],
            [
                'unlocode' => 'KRPUS',
                'name' => 'Port of Busan (Busan New Port Container Terminal)',
                'facility_type' => 'PORT',
                'address' => 'Gadeokseo-ro, Gangseo-gu, Busan',
                'city' => 'Busan',
                'country_code' => 'KR',
                'latitude' => 35.0761000,
                'longitude' => 128.8322000,
                'timezone' => 'Asia/Seoul',
            ],

            // =========================================================================
            // 4. MIDDLE EAST & GCC REGION
            // =========================================================================
            [
                'unlocode' => 'AEJEA',
                'name' => 'Port of Jebel Ali (DP World Terminal 1-3)',
                'facility_type' => 'PORT',
                'address' => 'Jebel Ali Free Zone (JAFZA), Dubai',
                'city' => 'Dubai',
                'country_code' => 'AE',
                'latitude' => 24.9857000,
                'longitude' => 55.0273000,
                'timezone' => 'Asia/Dubai',
            ],
            [
                'unlocode' => 'SAJED',
                'name' => 'Jeddah Islamic Port (Red Sea Gateway Terminal)',
                'facility_type' => 'PORT',
                'address' => 'Al Mina, Jeddah 22311',
                'city' => 'Jeddah',
                'country_code' => 'SA',
                'latitude' => 21.4632000,
                'longitude' => 39.1627000,
                'timezone' => 'Asia/Riyadh',
            ],

            // =========================================================================
            // 5. EUROPE (NETHERLANDS, GERMANY, UNITED KINGDOM)
            // =========================================================================
            [
                'unlocode' => 'NLRTM',
                'name' => 'ECT Delta Terminal - Port of Rotterdam',
                'facility_type' => 'PORT',
                'address' => 'Europaweg 875, Haven 8200, Maasvlakte',
                'city' => 'Rotterdam',
                'country_code' => 'NL',
                'latitude' => 51.9547000,
                'longitude' => 4.1350000,
                'timezone' => 'Europe/Amsterdam',
            ],
            [
                'unlocode' => 'DEHAM',
                'name' => 'Port of Hamburg (HHLA Container Terminal Altenwerder - CTA)',
                'facility_type' => 'PORT',
                'address' => 'Am Ballinkai 1, 21129 Hamburg',
                'city' => 'Hamburg',
                'country_code' => 'DE',
                'latitude' => 53.5320000,
                'longitude' => 9.9640000,
                'timezone' => 'Europe/Berlin',
            ],
            [
                'unlocode' => 'GBFXT',
                'name' => 'Port of Felixstowe (Trinity Terminal)',
                'facility_type' => 'PORT',
                'address' => 'Tomline House, The Docks, Felixstowe, Suffolk IP11 3SY',
                'city' => 'Felixstowe / London Gateway',
                'country_code' => 'GB',
                'latitude' => 51.9567000,
                'longitude' => 1.3090000,
                'timezone' => 'Europe/London',
            ],

            // =========================================================================
            // 6. NORTH AMERICA (USA & CANADA)
            // =========================================================================
            [
                'unlocode' => 'USLAX',
                'name' => 'Pier 400 APM Terminals - Port of Los Angeles',
                'facility_type' => 'PORT',
                'address' => '2500 Navy Way, Terminal Island, San Pedro Bay',
                'city' => 'Los Angeles, California',
                'country_code' => 'US',
                'latitude' => 33.7432000,
                'longitude' => -118.2673000,
                'timezone' => 'America/Los_Angeles',
            ],
            [
                'unlocode' => 'USNYC',
                'name' => 'Port of New York and New Jersey (Maher Terminals Elizabeth)',
                'facility_type' => 'PORT',
                'address' => '1210 Corbin St, Elizabeth, NJ 07201',
                'city' => 'New York / Elizabeth NJ',
                'country_code' => 'US',
                'latitude' => 40.6711000,
                'longitude' => -74.1289000,
                'timezone' => 'America/New_York',
            ],

            // =========================================================================
            // 7. SOUTH AMERICA (BRAZIL)
            // =========================================================================
            [
                'unlocode' => 'BRSSZ',
                'name' => 'Port of Santos (BTP Brasil Terminal Portuário)',
                'facility_type' => 'PORT',
                'address' => 'Avenida Engenheiro Augusto Barata s/n, Alemoa, Santos - SP',
                'city' => 'Santos / São Paulo',
                'country_code' => 'BR',
                'latitude' => -23.9618000,
                'longitude' => -46.2974000,
                'timezone' => 'America/Sao_Paulo',
            ],

            // =========================================================================
            // 8. AFRICA (NIGERIA & SOUTH AFRICA)
            // =========================================================================
            [
                'unlocode' => 'NGLOS',
                'name' => 'Port of Lagos (Apapa Container Terminal / APM Terminals)',
                'facility_type' => 'PORT',
                'address' => 'Apapa Quays, Lagos Port Complex, Apapa, Lagos',
                'city' => 'Lagos',
                'country_code' => 'NG',
                'latitude' => 6.4474000,
                'longitude' => 3.3590000,
                'timezone' => 'Africa/Lagos',
            ],
            [
                'unlocode' => 'ZADUR',
                'name' => 'Port of Durban (Durban Container Terminal Pier 2)',
                'facility_type' => 'PORT',
                'address' => 'Langeberg Road, Bayhead, Durban 4001',
                'city' => 'Durban',
                'country_code' => 'ZA',
                'latitude' => -29.8683000,
                'longitude' => 31.0218000,
                'timezone' => 'Africa/Johannesburg',
            ],

            // =========================================================================
            // 9. OCEANIA (AUSTRALIA & NEW ZEALAND)
            // =========================================================================
            [
                'unlocode' => 'AUMEL',
                'name' => 'Webb Dock International Container Terminal - Port of Melbourne',
                'facility_type' => 'PORT',
                'address' => 'Webb Dock Drive, Port Melbourne, VIC 3207',
                'city' => 'Melbourne',
                'country_code' => 'AU',
                'latitude' => -37.8440000,
                'longitude' => 144.9333000,
                'timezone' => 'Australia/Melbourne',
            ],
            [
                'unlocode' => 'NZAKL',
                'name' => 'Ports of Auckland (Fergusson Container Terminal)',
                'facility_type' => 'PORT',
                'address' => 'Sunderland Street, Parnell, Auckland 1010',
                'city' => 'Auckland',
                'country_code' => 'NZ',
                'latitude' => -36.8436000,
                'longitude' => 174.7762000,
                'timezone' => 'Pacific/Auckland',
            ],

            // =========================================================================
            // 10. AIR FREIGHT CARGO HUBS (AIRPORTS)
            // =========================================================================
            [
                'unlocode' => 'IDCGK',
                'name' => 'Soekarno-Hatta International Cargo Terminal (CGK)',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Cargo Area Bandara Internasional Soekarno-Hatta, Tangerang',
                'city' => 'Tangerang',
                'country_code' => 'ID',
                'latitude' => -6.1256000,
                'longitude' => 106.6558000,
                'timezone' => 'Asia/Jakarta',
            ],
            [
                'unlocode' => 'SGSIN-AIR',
                'name' => 'Singapore Changi Airfreight Centre (SIN)',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Airport Boulevard, Changi Cargo Complex',
                'city' => 'Singapore',
                'country_code' => 'SG',
                'latitude' => 1.3644000,
                'longitude' => 103.9915000,
                'timezone' => 'Asia/Singapore',
            ],
            [
                'unlocode' => 'NLAMS-AIR',
                'name' => 'Amsterdam Airport Schiphol Cargo Hub (AMS)',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Evert van de Beekstraat 202, Schiphol-Zuidoost',
                'city' => 'Amsterdam',
                'country_code' => 'NL',
                'latitude' => 52.3105000,
                'longitude' => 4.7683000,
                'timezone' => 'Europe/Amsterdam',
            ],

            // =========================================================================
            // 11. ROAD FREIGHT LOGISTICS HUBS & DISTRIBUTION CENTERS
            // =========================================================================
            [
                'unlocode' => 'IDSUB-DC',
                'name' => 'Pusat Distribusi Regional Mayora Jawa Timur (Rungkut DC)',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Kawasan Industri SIER, Jl. Rungkut Industri Raya No. 45',
                'city' => 'Surabaya',
                'country_code' => 'ID',
                'latitude' => -7.3255000,
                'longitude' => 112.7601000,
                'timezone' => 'Asia/Jakarta',
            ],
            [
                'unlocode' => 'IDMES-DC',
                'name' => 'Pusat Distribusi Regional Mayora Sumatera Utara (Percut Sei Tuan)',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Jl. Percut Sei Tuan Km. 14, Kab. Deli Serdang',
                'city' => 'Deli Serdang / Medan',
                'country_code' => 'ID',
                'latitude' => 3.6385000,
                'longitude' => 98.7180000,
                'timezone' => 'Asia/Jakarta',
            ],
            [
                'unlocode' => 'IDLMP-HUB',
                'name' => 'Hub Transit Logistik Lintas Sumatera (Bakauheni Lampung)',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Jl. Lintas Sumatera Km. 5, Bakauheni',
                'city' => 'Lampung Selatan',
                'country_code' => 'ID',
                'latitude' => -5.8712000,
                'longitude' => 105.7541000,
                'timezone' => 'Asia/Jakarta',
            ],
        ];

        foreach ($locations as $locData) {
            Location::updateOrCreate(
                ['unlocode' => $locData['unlocode']],
                $locData
            );
        }
    }
}
