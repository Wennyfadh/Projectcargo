<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    public function record(Shipment $shipment, array $attributes, User $actor): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $attributes, $actor) {
            $event = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $attributes['location_id'] ?? $shipment->current_location_id,
                'event_type' => $attributes['event_type'] ?? 'STATUS_UPDATE',
                'status' => $attributes['status'] ?? $shipment->status,
                'event_time' => $attributes['event_time'] ?? now(),
                'latitude' => $attributes['latitude'] ?? null,
                'longitude' => $attributes['longitude'] ?? null,
                'location_name' => $attributes['location_name'] ?? null,
                'description' => $attributes['description'] ?? 'Shipment milestone recorded.',
                'is_exception' => (bool) ($attributes['is_exception'] ?? false),
                'created_by' => $actor->id,
            ]);

            if (! empty($attributes['location_id'])) {
                $shipment->current_location_id = $attributes['location_id'];
            }

            if (! empty($attributes['status']) && $shipment->status !== $attributes['status']) {
                $shipment->status = $attributes['status'];
            }

            $shipment->save();

            return $event;
        });
    }

    public function history(Shipment $shipment): Collection
    {
        return $shipment->trackingEvents()->with('location')->get();
    }

    public function currentLocation(Shipment $shipment): ?Location
    {
        $event = $shipment->trackingEvents()->with('location')->latest('event_time')->first();

        return $event?->location;
    }

    public function getRouteTelemetry(Shipment $shipment): array
    {
        $origin = $shipment->origin;
        $destination = $shipment->destination;

        $originLat = (float) ($origin?->latitude ?? -6.1018);
        $originLng = (float) ($origin?->longitude ?? 106.8837);
        $destLat = (float) ($destination?->latitude ?? $originLat);
        $destLng = (float) ($destination?->longitude ?? $originLng);

        // Find current coordinates from events or current location
        $events = $shipment->trackingEvents()->with('location')->orderBy('event_time', 'asc')->get();
        $eventWithCoords = $events->filter(fn ($e) => ! is_null($e->latitude) && ! is_null($e->longitude))->values();

        $latestCoordEvent = $eventWithCoords->last();

        if ($shipment->status === 'DELIVERED') {
            $currLat = $destLat;
            $currLng = $destLng;
            $currLocationName = $destination?->name ?? 'Delivered at Destination Port';
        } elseif ($latestCoordEvent) {
            $currLat = (float) $latestCoordEvent->latitude;
            $currLng = (float) $latestCoordEvent->longitude;
            $currLocationName = $latestCoordEvent->location_name ?? $latestCoordEvent->location?->name ?? 'En Route Maritime Waypoint';
        } elseif ($shipment->currentLocation) {
            $currLat = (float) $shipment->currentLocation->latitude;
            $currLng = (float) $shipment->currentLocation->longitude;
            $currLocationName = $shipment->currentLocation->name;
        } else {
            $currLat = $originLat;
            $currLng = $originLng;
            $currLocationName = $origin?->name ?? 'Origin Port / Facility';
        }

        // Build Traversed Route Points (Origin -> Milestone Waypoints -> Current Position)
        $traversed = [];
        $traversed[] = [$originLat, $originLng];

        foreach ($eventWithCoords as $ev) {
            $lat = (float) $ev->latitude;
            $lng = (float) $ev->longitude;
            // Prevent consecutive exact duplicates
            $last = end($traversed);
            if (! $last || (abs($last[0] - $lat) > 0.0001 || abs($last[1] - $lng) > 0.0001)) {
                $traversed[] = [$lat, $lng];
            }
        }

        $lastTraversed = end($traversed);
        if (! $lastTraversed || (abs($lastTraversed[0] - $currLat) > 0.0001 || abs($lastTraversed[1] - $currLng) > 0.0001)) {
            $traversed[] = [$currLat, $currLng];
        }

        // Build Remaining Route Points (Current Position -> Destination)
        $remaining = [];
        if ($shipment->status !== 'DELIVERED') {
            $remaining[] = [$currLat, $currLng];
            if (abs($currLat - $destLat) > 0.0001 || abs($currLng - $destLng) > 0.0001) {
                $remaining[] = [$destLat, $destLng];
            }
        }

        // Calculate distances
        $sailedKm = 0.0;
        for ($i = 0; $i < count($traversed) - 1; $i++) {
            $sailedKm += $this->calculateHaversineDistance($traversed[$i][0], $traversed[$i][1], $traversed[$i+1][0], $traversed[$i+1][1]);
        }

        $remainingKm = 0.0;
        for ($i = 0; $i < count($remaining) - 1; $i++) {
            $remainingKm += $this->calculateHaversineDistance($remaining[$i][0], $remaining[$i][1], $remaining[$i+1][0], $remaining[$i+1][1]);
        }

        $totalKm = $sailedKm + $remainingKm;
        if ($totalKm <= 0.1) {
            $totalKm = max(1.0, $this->calculateHaversineDistance($originLat, $originLng, $destLat, $destLng));
        }

        $progressPercent = $shipment->status === 'DELIVERED'
            ? 100
            : min(99, max(5, round(($sailedKm / max(1.0, $totalKm)) * 100)));

        $mode = $shipment->transport_mode ?? 'SEA';
        $modeIcon = $shipment->transport_mode_icon;
        $modeLabel = $shipment->transport_mode_label;
        $calloutPrefix = $shipment->callout_prefix;
        $accentColor = $shipment->transport_color;

        $speedFormatted = match ($mode) {
            'AIR' => $shipment->status === 'IN_TRANSIT' ? '840 km/h (Mach 0.78)' : '0 km/h',
            'ROAD' => $shipment->status === 'IN_TRANSIT' ? '68 km/h' : '0 km/h',
            default => $shipment->status === 'IN_TRANSIT' ? '18.5 Kts (34.3 km/h)' : '0.0 Kts',
        };

        return [
            'transport_mode' => $mode,
            'transport_mode_label' => $modeLabel,
            'transport_mode_icon' => $modeIcon,
            'callout_prefix' => $calloutPrefix,
            'accent_color' => $accentColor,
            'pulse_class' => match ($mode) {
                'AIR' => 'plane-pulse',
                'ROAD' => 'truck-pulse',
                default => 'vessel-pulse',
            },
            'speed_formatted' => $speedFormatted,
            'vehicle_name' => $shipment->vehicle_identifier ?? $shipment->vessel_name ?? ($mode === 'AIR' ? 'Cargo Aircraft' : ($mode === 'ROAD' ? 'Container Truck' : 'Ocean Vessel')),
            'origin' => [
                'name' => $origin?->name ?? 'Origin Port',
                'city' => $origin?->city ?? 'Jakarta',
                'country_code' => $origin?->country_code ?? 'ID',
                'unlocode' => $origin?->unlocode ?? 'IDTPP',
                'latitude' => $originLat,
                'longitude' => $originLng,
            ],
            'destination' => [
                'name' => $destination?->name ?? 'Destination Port',
                'city' => $destination?->city ?? 'International Port',
                'country_code' => $destination?->country_code ?? '',
                'unlocode' => $destination?->unlocode ?? '',
                'latitude' => $destLat,
                'longitude' => $destLng,
            ],
            'current_position' => [
                'latitude' => $currLat,
                'longitude' => $currLng,
                'name' => $currLocationName,
                'status' => $shipment->status,
                'is_at_sea' => in_array($shipment->status, ['IN_TRANSIT'], true),
            ],
            'vessel' => [
                'name' => $shipment->vessel_name ?? 'Ocean Carrier Vessel',
                'voyage_number' => $shipment->voyage_number ?? '—',
                'carrier_name' => $shipment->carrier?->name ?? 'Ocean Carrier',
                'carrier_scac' => $shipment->carrier?->scac ?? 'OCEN',
                'speed_knots' => $shipment->status === 'IN_TRANSIT' ? 18.5 : 0.0,
                'course_heading' => 45,
            ],
            'route' => [
                'traversed_points' => $traversed,
                'remaining_points' => $remaining,
                'total_distance_km' => round($totalKm, 1),
                'total_distance_nm' => round($totalKm * 0.539957, 1),
                'sailed_distance_km' => round($sailedKm, 1),
                'remaining_distance_km' => round($remainingKm, 1),
                'progress_percent' => $progressPercent,
            ],
            'waypoints' => $events->map(function ($ev) {
                return [
                    'id' => $ev->id,
                    'event_type' => $ev->event_type,
                    'status' => $ev->status,
                    'event_time' => $ev->event_time?->format('d M Y, H:i'),
                    'location_name' => $ev->location_name ?? $ev->location?->name ?? 'Milestone',
                    'latitude' => $ev->latitude ? (float) $ev->latitude : null,
                    'longitude' => $ev->longitude ? (float) $ev->longitude : null,
                    'description' => $ev->description,
                    'is_exception' => (bool) $ev->is_exception,
                ];
            })->values()->toArray(),
        ];
    }

    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 1);
    }
}
