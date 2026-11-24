<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiModalCargoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->carrier = Carrier::firstOrCreate(
            ['scac' => 'TEST'],
            ['name' => 'Test Carrier', 'is_active' => true]
        );
        $this->origin = Location::firstOrCreate(
            ['unlocode' => 'TEST-ORG'],
            [
                'name' => 'Origin Hub',
                'facility_type' => 'PORT',
                'address' => 'Jl. Pelabuhan Test No. 1',
                'city' => 'Jakarta',
                'country_code' => 'ID',
                'latitude' => -6.10,
                'longitude' => 106.88,
                'timezone' => 'Asia/Jakarta',
            ]
        );
        $this->destination = Location::firstOrCreate(
            ['unlocode' => 'TEST-DST'],
            [
                'name' => 'Destination Hub',
                'facility_type' => 'WAREHOUSE',
                'address' => 'Airport Blvd Test 88',
                'city' => 'Singapore',
                'country_code' => 'SG',
                'latitude' => 1.35,
                'longitude' => 103.98,
                'timezone' => 'Asia/Singapore',
            ]
        );
    }

    public function test_shipment_supports_sea_air_and_road_modes(): void
    {
        $seaShipment = Shipment::factory()->create([
            'shipment_number' => 'CT-TEST-SEA-01',
            'customer_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'transport_mode' => 'SEA',
            'vessel_name' => 'MV Oceanic Express',
            'status' => 'IN_TRANSIT',
            'created_by' => $this->user->id,
        ]);

        $airShipment = Shipment::factory()->create([
            'shipment_number' => 'CT-TEST-AIR-01',
            'customer_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'transport_mode' => 'AIR',
            'vehicle_identifier' => 'Boeing 777F Flight SQ7235',
            'status' => 'IN_TRANSIT',
            'created_by' => $this->user->id,
        ]);

        $roadShipment = Shipment::factory()->create([
            'shipment_number' => 'CT-TEST-ROAD-01',
            'customer_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'transport_mode' => 'ROAD',
            'vehicle_identifier' => 'Volvo FMX 440 (B 9821 TYN)',
            'status' => 'IN_TRANSIT',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('SEA', $seaShipment->transport_mode);
        $this->assertEquals('AIR', $airShipment->transport_mode);
        $this->assertEquals('ROAD', $roadShipment->transport_mode);

        $this->assertEquals('🚢', $seaShipment->transport_mode_icon);
        $this->assertEquals('✈️', $airShipment->transport_mode_icon);
        $this->assertEquals('🚛', $roadShipment->transport_mode_icon);

        $this->assertEquals('Dalam perjalanan ke', $seaShipment->callout_prefix);
        $this->assertEquals('Dalam penerbangan ke', $airShipment->callout_prefix);
        $this->assertEquals('Dalam perjalanan darat ke', $roadShipment->callout_prefix);
    }

    public function test_telemetry_service_includes_transport_mode_and_pulse_class(): void
    {
        $airShipment = Shipment::factory()->create([
            'shipment_number' => 'CT-TEST-AIR-02',
            'customer_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'transport_mode' => 'AIR',
            'vehicle_identifier' => 'Airbus A330-200F',
            'status' => 'IN_TRANSIT',
            'created_by' => $this->user->id,
        ]);

        $trackingService = new TrackingService();
        $telemetry = $trackingService->getRouteTelemetry($airShipment);

        $this->assertEquals('AIR', $telemetry['transport_mode']);
        $this->assertEquals('plane-pulse', $telemetry['pulse_class']);
        $this->assertEquals('#0284c7', $telemetry['accent_color']);
        $this->assertEquals('Dalam penerbangan ke', $telemetry['callout_prefix']);
    }

    public function test_tracking_index_filters_by_transport_mode(): void
    {
        Shipment::factory()->create([
            'shipment_number' => 'CT-FILTER-AIR-01',
            'customer_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'transport_mode' => 'AIR',
            'status' => 'IN_TRANSIT',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('tracking.index', ['mode' => 'AIR']));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-AIR-01');
    }

    public function test_live_tracking_view_renders_multi_modal_controls(): void
    {
        $roadShipment = Shipment::factory()->create([
            'shipment_number' => 'CT-VIEW-ROAD-01',
            'customer_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'transport_mode' => 'ROAD',
            'vehicle_identifier' => 'Hino 500 Tronton',
            'status' => 'IN_TRANSIT',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('tracking.show', $roadShipment));

        $response->assertStatus(200);
        $response->assertSee('Uji Moda');
        $response->assertSee('btnModeSEA', false);
        $response->assertSee('btnModeAIR', false);
        $response->assertSee('btnModeROAD', false);
        $response->assertSee('Hino 500 Tronton');
    }
}
