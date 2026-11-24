<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LifecycleTransitionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_status_can_only_follow_valid_sequence(): void
    {
        $service = app(ShipmentService::class);
        $shipment = Shipment::factory()->create(['status' => 'CREATED']);
        $actor = User::factory()->create(['role' => 'operator']);

        $service->transitionStatus($shipment, 'BOOKED', $actor);
        $this->assertSame('BOOKED', $shipment->fresh()->status);

        $this->expectException(ValidationException::class);
        $service->transitionStatus($shipment, 'DELIVERED', $actor);
    }

    public function test_delivered_status_is_terminal(): void
    {
        $service = app(ShipmentService::class);
        $shipment = Shipment::factory()->create(['status' => 'DELIVERED']);
        $actor = User::factory()->create(['role' => 'operator']);

        $this->expectException(ValidationException::class);
        $service->transitionStatus($shipment, 'IN_TRANSIT', $actor);
    }
}
