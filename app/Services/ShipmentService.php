<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public const VALID_STATUS_FLOW = [
        'CREATED' => ['BOOKED', 'CANCELLED'],
        'BOOKED' => ['PICKED_UP', 'CANCELLED'],
        'PICKED_UP' => ['IN_TRANSIT', 'CANCELLED'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED' => [],
        'CANCELLED' => [],
    ];

    public function create(array $attributes, User $actor): Shipment
    {
        return DB::transaction(function () use ($attributes, $actor) {
            $shipment = Shipment::create([
                'shipment_number' => $attributes['shipment_number'],
                'booking_reference' => $attributes['booking_reference'] ?? null,
                'bill_of_lading_number' => $attributes['bill_of_lading_number'] ?? null,
                'container_number' => $attributes['container_number'] ?? null,
                'seal_number' => $attributes['seal_number'] ?? null,
                'customer_id' => $attributes['customer_id'],
                'carrier_id' => $attributes['carrier_id'],
                'origin_location_id' => $attributes['origin_location_id'],
                'destination_location_id' => $attributes['destination_location_id'],
                'current_location_id' => $attributes['current_location_id'] ?? null,
                'vessel_name' => $attributes['vessel_name'] ?? null,
                'voyage_number' => $attributes['voyage_number'] ?? null,
                'cargo_type' => $attributes['cargo_type'] ?? 'FCL',
                'container_type' => $attributes['container_type'] ?? '40HC',
                'status' => $attributes['status'] ?? 'CREATED',
                'delay_status' => $attributes['delay_status'] ?? 'ON_TIME',
                'expected_departure_at' => $attributes['expected_departure_at'] ?? now(),
                'actual_departure_at' => $attributes['actual_departure_at'] ?? null,
                'expected_arrival_at' => $attributes['expected_arrival_at'] ?? now()->addDays(7),
                'estimated_arrival_at' => $attributes['estimated_arrival_at'] ?? ($attributes['expected_arrival_at'] ?? now()->addDays(7)),
                'actual_arrival_at' => $attributes['actual_arrival_at'] ?? null,
                'delay_duration_hours' => $attributes['delay_duration_hours'] ?? 0,
                'delay_reason' => $attributes['delay_reason'] ?? null,
                'temperature_control' => $attributes['temperature_control'] ?? 'AMBIENT_DRY',
                'special_instructions' => $attributes['special_instructions'] ?? null,
                'created_by' => $actor->id,
            ]);

            $this->logAudit($shipment, $actor, 'CREATED', null, $shipment->toArray());
            $this->createTrackingEvent($shipment, $actor, [
                'event_type' => 'BOOKING_CREATED',
                'status' => $shipment->status,
                'event_time' => now(),
                'location_name' => $shipment->origin?->name,
                'description' => 'Shipment booking created in CargoTrace.',
                'is_exception' => false,
            ]);

            return $shipment;
        });
    }

    public function transitionStatus(Shipment $shipment, string $newStatus, User $actor, array $context = []): Shipment
    {
        if ($shipment->status === 'DELIVERED') {
            throw ValidationException::withMessages([
                'status' => 'Status DELIVERED is terminal and cannot be changed without special approval.',
            ]);
        }

        $current = strtoupper($shipment->status ?? 'CREATED');
        $new = strtoupper($newStatus);

        if (! $this->canTransition($current, $new)) {
            throw ValidationException::withMessages([
                'status' => sprintf('Invalid transition from %s to %s.', $current, $new),
            ]);
        }

        if ($new === 'CANCELLED' && ! in_array($current, ['CREATED', 'BOOKED', 'PICKED_UP'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Cancellation is only allowed before shipment is picked up.',
            ]);
        }

        $oldStatus = $shipment->status;
        $shipment->status = $new;

        if ($new === 'PICKED_UP' && empty($shipment->actual_departure_at)) {
            $shipment->actual_departure_at = $context['actual_departure_at'] ?? now();
        }

        if ($new === 'DELIVERED' && empty($shipment->actual_arrival_at)) {
            $shipment->actual_arrival_at = $context['actual_arrival_at'] ?? now();
        }

        $shipment->save();

        $this->logAudit($shipment, $actor, 'STATUS_TRANSITION', ['status' => $oldStatus], ['status' => $new]);

        $this->createTrackingEvent($shipment, $actor, [
            'event_type' => strtoupper(str_replace(' ', '_', $new)),
            'status' => $new,
            'event_time' => $context['event_time'] ?? now(),
            'location_id' => $shipment->current_location_id,
            'location_name' => $shipment->currentLocation?->name,
            'description' => $context['description'] ?? sprintf('Shipment status updated from %s to %s.', $oldStatus, $new),
            'is_exception' => false,
        ]);

        $this->notify($shipment, $actor, 'Shipment status update', sprintf('Shipment %s moved to %s.', $shipment->shipment_number, $new), 'INFO');

        return $shipment->fresh();
    }

    public function updateVoyage(Shipment $shipment, array $attributes, User $actor): Shipment
    {
        $shipment->fill($attributes);
        $shipment->save();

        $this->logAudit($shipment, $actor, 'VOYAGE_UPDATED', ['voyage' => $attributes], ['voyage' => $shipment->only(['vessel_name', 'voyage_number', 'carrier_id'])]);

        return $shipment->fresh();
    }

    public function softDelete(Shipment $shipment, User $actor): bool
    {
        if (! $shipment->trashed()) {
            $shipment->delete();
        }

        $this->logAudit($shipment, $actor, 'SOFT_DELETED', ['deleted_at' => null], ['deleted_at' => now()->toDateTimeString()]);

        return true;
    }

    public function canTransition(string $current, string $target): bool
    {
        $current = strtoupper($current);
        $target = strtoupper($target);

        return isset(self::VALID_STATUS_FLOW[$current]) && in_array($target, self::VALID_STATUS_FLOW[$current], true);
    }

    public function createTrackingEvent(Shipment $shipment, User $actor, array $payload): TrackingEvent
    {
        $event = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'location_id' => $payload['location_id'] ?? $shipment->current_location_id,
            'event_type' => $payload['event_type'] ?? 'STATUS_UPDATE',
            'status' => $payload['status'] ?? $shipment->status,
            'event_time' => $payload['event_time'] ?? now(),
            'latitude' => $payload['latitude'] ?? $shipment->origin?->latitude,
            'longitude' => $payload['longitude'] ?? $shipment->origin?->longitude,
            'location_name' => $payload['location_name'] ?? $shipment->origin?->name,
            'description' => $payload['description'] ?? 'Shipment event created.',
            'is_exception' => $payload['is_exception'] ?? false,
            'created_by' => $actor->id,
        ]);

        if (array_key_exists('location_id', $payload) && filled($payload['location_id'])) {
            $shipment->current_location_id = $payload['location_id'];
            $shipment->save();
        }

        return $event;
    }

    protected function notify(Shipment $shipment, User $actor, string $title, string $message, string $type = 'INFO'): void
    {
        $users = collect([
            $shipment->customer,
            $actor,
        ])->filter()->unique('id');

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'shipment_id' => $shipment->id,
                'title' => $title,
                'message' => $message,
                'type' => strtoupper($type),
                'is_read' => false,
            ]);
        }
    }

    protected function logAudit(Shipment $shipment, User $actor, string $action, mixed $oldValues, mixed $newValues): void
    {
        AuditLog::create([
            'user_id' => $actor->id,
            'entity_type' => Shipment::class,
            'entity_id' => $shipment->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'created_at' => now(),
        ]);
    }
}
