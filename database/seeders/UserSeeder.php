<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Realistic users representing Mayora Internal Supply Chain & Global Buyers across all continents.
     */
    public function run(): void
    {
        $users = [
            // =========================================================================
            // INTERNAL LOGISTICS & IT (ADMINISTRATOR & OPERATORS)
            // =========================================================================
            [
                'name' => 'Budi Hartono (Director of Global Logistics)',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'company_name' => 'PT Mayora Indah Tbk',
                'phone' => '+62-21-5655320',
                'avatar' => 'avatars/admin_budi.png',
                'is_active' => true,
            ],
            [
                'name' => 'Administrator CargoTrace',
                'email' => 'admin@cargotrace.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'company_name' => 'PT Mayora Indah Tbk',
                'phone' => '+62-21-5655320',
                'avatar' => 'avatars/admin_budi.png',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Hartono (Director of Global Logistics)',
                'email' => 'admin@cargotrace.mayora.co.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'company_name' => 'PT Mayora Indah Tbk',
                'phone' => '+62-21-5655320',
                'avatar' => 'avatars/admin_budi.png',
                'is_active' => true,
            ],
            [
                'name' => 'Rendra Pratama (Export Freight Specialist)',
                'email' => 'operator@cargotrace.mayora.co.id',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'company_name' => 'PT Mayora Indah Tbk - Global Supply Chain',
                'phone' => '+62-811-9821-432',
                'avatar' => 'avatars/operator_rendra.png',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Rahmawati (Port & Customs Clearance Lead)',
                'email' => 'siti.rahmawati@cargotrace.mayora.co.id',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'company_name' => 'PT Mayora Indah Tbk - Port Logistics Hub Priok',
                'phone' => '+62-812-8871-902',
                'avatar' => 'avatars/operator_siti.png',
                'is_active' => true,
            ],

            // =========================================================================
            // GLOBAL BUYERS / CUSTOMERS (WORLDWIDE REGIONS)
            // =========================================================================
            // 1. Southeast Asia - Philippines
            [
                'name' => 'Carlos Tan (VP Procurement)',
                'email' => 'buyer.philippines@puregold.com.ph',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'Puregold Price Club Inc. / JG Summit Group (Philippines)',
                'phone' => '+63-2-8526-0101',
                'avatar' => 'avatars/buyer_carlos.png',
                'is_active' => true,
            ],
            // 2. East Asia - China (Largest market for Danisa Cookies)
            [
                'name' => 'Li Wei (Senior Import Sourcing Manager)',
                'email' => 'buyer.china@vanguard.cn',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'China Resources Vanguard Supermarket Co., Ltd. (Shanghai, China)',
                'phone' => '+86-21-6120-8888',
                'avatar' => 'avatars/buyer_liwei.png',
                'is_active' => true,
            ],
            // 3. East Asia - Japan
            [
                'name' => 'Kenji Takahashi (Head of Confectionery Merchandising)',
                'email' => 'buyer.japan@aeon.info',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'AEON Retail Co., Ltd. Global SCM (Tokyo, Japan)',
                'phone' => '+81-3-6898-4111',
                'avatar' => 'avatars/buyer_kenji.png',
                'is_active' => true,
            ],
            // 4. Middle East - UAE & GCC
            [
                'name' => 'Firoz Khan (Head of FMCG Imports)',
                'email' => 'buyer.middleeast@lulugroup.ae',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'LuLu Group International LLC (Dubai, UAE)',
                'phone' => '+971-4-298-6999',
                'avatar' => 'avatars/buyer_firoz.png',
                'is_active' => true,
            ],
            // 5. Middle East - Saudi Arabia
            [
                'name' => 'Abdulrahman Al-Ghamdi (Supply Chain Director)',
                'email' => 'buyer.saudi@bindawood.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'BinDawood Holding / Danube Supermarkets (Jeddah, Saudi Arabia)',
                'phone' => '+966-12-653-3333',
                'avatar' => 'avatars/buyer_abdulrahman.png',
                'is_active' => true,
            ],
            // 6. Western Europe - Netherlands & EU
            [
                'name' => 'Johan van der Meer (Supply Chain Director)',
                'email' => 'buyer.europe@orientalgroup.nl',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'Oriental Group B.V. (Rotterdam, Netherlands)',
                'phone' => '+31-10-432-8800',
                'avatar' => 'avatars/buyer_johan.png',
                'is_active' => true,
            ],
            // 7. Central Europe - Germany
            [
                'name' => 'Klaus Müller (Category Procurement Specialist)',
                'email' => 'buyer.germany@edeka.de',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'EDEKA Zentrale Stiftung & Co. KG (Hamburg, Germany)',
                'phone' => '+49-40-6377-0',
                'avatar' => 'avatars/buyer_klaus.png',
                'is_active' => true,
            ],
            // 8. Europe - United Kingdom
            [
                'name' => 'Oliver Smith (Overseas Logistics Lead)',
                'email' => 'buyer.uk@tesco.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'Tesco Stores Ltd SCM (London / Welwyn Garden City, UK)',
                'phone' => '+44-1707-912000',
                'avatar' => 'avatars/buyer_oliver.png',
                'is_active' => true,
            ],
            // 9. North America - United States
            [
                'name' => 'Michael Chang (Head of Import Logistics)',
                'email' => 'buyer.usa@asianfoodgrocer.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'Asian Food Grocer Inc. / Cost Plus World Market (Los Angeles, USA)',
                'phone' => '+1-415-890-3200',
                'avatar' => 'avatars/buyer_michael.png',
                'is_active' => true,
            ],
            // 10. South America - Brazil
            [
                'name' => 'Rodrigo Silva (Import Division Manager)',
                'email' => 'buyer.brazil@pao-de-acucar.com.br',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'Grupo Pão de Açúcar / GPA Logistics (São Paulo, Brazil)',
                'phone' => '+55-11-3886-0533',
                'avatar' => 'avatars/buyer_rodrigo.png',
                'is_active' => true,
            ],
            // 11. West Africa - Nigeria
            [
                'name' => 'Emeka Okafor (Head of Freight Operations)',
                'email' => 'buyer.africa@spar.ng',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'SPAR Nigeria / Artee Group Distribution (Lagos, Nigeria)',
                'phone' => '+234-1-270-3071',
                'avatar' => 'avatars/buyer_emeka.png',
                'is_active' => true,
            ],
            // 12. Oceania - Australia
            [
                'name' => 'Sarah Jenkins (National Category Manager)',
                'email' => 'buyer.australia@woolworths.com.au',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'company_name' => 'Woolworths Group Logistics Distribution (Melbourne, Australia)',
                'phone' => '+61-3-8531-1000',
                'avatar' => 'avatars/buyer_sarah.png',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
