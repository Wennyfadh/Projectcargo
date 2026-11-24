<?php

namespace Database\Factories;

use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Carrier>
 */
class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    public function definition(): array
    {
        return [
            'scac' => strtoupper(fake()->unique()->lexify('????')),
            'name' => fake()->company().' Shipping',
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'website_url' => fake()->url(),
            'tracking_url_template' => 'https://carrier.example/track/{tracking_number}',
            'is_active' => true,
        ];
    }
}
