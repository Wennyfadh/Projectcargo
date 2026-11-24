<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShipmentAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_only_access_own_shipments(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);
        $origin = Location::factory()->create();
        $destination = Location::factory()->create();

        $shipment = Shipment::factory()->create([
            'customer_id' => $customerA->id,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $destination->id,
        ]);

        $this->actingAs($customerA)
            ->getJson('/api/shipments/'.$shipment->id)
            ->assertOk();

        $this->actingAs($customerB)
            ->getJson('/api/shipments/'.$shipment->id)
            ->assertStatus(403);
    }
}
