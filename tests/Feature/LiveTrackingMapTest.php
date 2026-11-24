<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LiveTrackingMapTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_view_live_tracking_map_with_callout_and_telemetry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $origin = Location::factory()->create([
            'name' => 'Tanjung Priok',
            'latitude' => -6.1018,
            'longitude' => 106.8837,
            'city' => 'Jakarta',
            'country_code' => 'ID',
        ]);
        $destination = Location::factory()->create([
            'name' => 'Port of Rotterdam',
            'latitude' => 51.9547,
            'longitude' => 4.1350,
            'city' => 'Rotterdam',
            'country_code' => 'NL',
        ]);
        $carrier = Carrier::firstOrCreate(['scac' => 'MAEU'], ['name' => 'Maersk', 'contact_email' => 'test@maersk.com', 'is_active' => true]);

        $shipment = Shipment::factory()->create([
            'customer_id' => $admin->id,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $destination->id,
            'carrier_id' => $carrier->id,
            'status' => 'IN_TRANSIT',
            'delay_status' => 'ON_TIME',
            'vessel_name' => 'MAERSK MC-KINNEY MOLLER',
            'container_number' => 'MSKU4982710',
        ]);

        $response = $this->actingAs($admin)->get(route('tracking.show', $shipment));

        $response->assertStatus(200);
        $response->assertSee('liveTrackingMap');
        $response->assertSee('Dalam perjalanan ke');
        $response->assertSee('Open-Meteo');
        $response->assertSee('btnRecenter');
    }

    public function test_user_can_fetch_live_tracking_data_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $origin = Location::factory()->create([
            'name' => 'Tanjung Priok',
            'latitude' => -6.1018,
            'longitude' => 106.8837,
            'city' => 'Jakarta',
            'country_code' => 'ID',
        ]);
        $destination = Location::factory()->create([
            'name' => 'Port of Rotterdam',
            'latitude' => 51.9547,
            'longitude' => 4.1350,
            'city' => 'Rotterdam',
            'country_code' => 'NL',
        ]);
        $carrier = Carrier::firstOrCreate(['scac' => 'MAEU'], ['name' => 'Maersk', 'contact_email' => 'test@maersk.com', 'is_active' => true]);

        $shipment = Shipment::factory()->create([
            'customer_id' => $admin->id,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $destination->id,
            'carrier_id' => $carrier->id,
            'status' => 'IN_TRANSIT',
            'delay_status' => 'ON_TIME',
            'vessel_name' => 'MAERSK MC-KINNEY MOLLER',
            'container_number' => 'MSKU4982710',
        ]);

        $response = $this->actingAs($admin)->get(route('tracking.data', $shipment));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'shipment' => ['id', 'shipment_number', 'container_number', 'status', 'vessel_name'],
            'telemetry' => [
                'origin',
                'destination',
                'current_position' => ['latitude', 'longitude', 'name'],
                'route' => ['traversed_points', 'remaining_points', 'progress_percent'],
                'waypoints',
            ],
            'weather' => ['temperature', 'wind_speed_knots', 'condition_text', 'sea_state'],
            'synced_at',
        ]);
    }
}
