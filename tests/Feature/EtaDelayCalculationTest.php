<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EtaDelayCalculationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_eta_delay_is_deterministic_and_threshold_aware(): void
    {
        $service = app(EtaService::class);
        $shipment = Shipment::factory()->create([
            'expected_arrival_at' => now()->addDays(3),
            'estimated_arrival_at' => now()->addDays(3)->addHours(5),
        ]);

        $result = $service->evaluate($shipment);

        $this->assertSame(5, $result['delay_hours']);
        $this->assertSame('LOW_DELAY', $result['delay_status']);

        $shipment->estimated_arrival_at = now()->addDays(3)->addHours(18);
        $shipment->save();

        $result = $service->evaluate($shipment);
        $this->assertSame('MEDIUM_DELAY', $result['delay_status']);
    }
}
