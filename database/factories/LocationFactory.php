<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'unlocode' => strtoupper(fake()->countryCode().fake()->unique()->lexify('???')),
            'name' => fake()->city().' Port',
            'facility_type' => 'PORT',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country_code' => strtoupper(fake()->countryCode()),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'timezone' => 'UTC',
        ];
    }
}
