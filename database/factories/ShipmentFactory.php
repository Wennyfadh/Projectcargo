<?php

namespace Database\Factories;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        $expectedArrival = fake()->dateTimeBetween('+5 days', '+45 days');

        return [
            'shipment_number' => 'CT-'.strtoupper(fake()->unique()->bothify('MYR-####??')),
            'booking_reference' => strtoupper(fake()->unique()->bothify('BK-########')),
            'bill_of_lading_number' => strtoupper(fake()->unique()->bothify('BL-##########')),
            'container_number' => strtoupper(fake()->unique()->bothify('MAYU#######')),
            'seal_number' => strtoupper(fake()->unique()->bothify('SEAL-########')),
            'customer_id' => User::factory(),
            'carrier_id' => Carrier::factory(),
            'origin_location_id' => Location::factory(),
            'destination_location_id' => Location::factory(),
            'current_location_id' => null,
            'vessel_name' => fake()->words(2, true),
            'voyage_number' => strtoupper(fake()->bothify('VY-####')),
            'cargo_type' => 'FCL',
            'container_type' => '40HC',
            'status' => 'CREATED',
            'delay_status' => 'ON_TIME',
            'expected_departure_at' => fake()->dateTimeBetween('-10 days', '+3 days'),
            'actual_departure_at' => null,
            'expected_arrival_at' => $expectedArrival,
            'estimated_arrival_at' => $expectedArrival,
            'actual_arrival_at' => null,
            'delay_duration_hours' => 0,
            'delay_reason' => null,
            'temperature_control' => 'AMBIENT_DRY',
            'special_instructions' => null,
            'created_by' => null,
        ];
    }
}
