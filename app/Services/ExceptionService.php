<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExceptionService
{
    public function trigger(Shipment $shipment, array $attributes, User $actor): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $attributes, $actor) {
            $exception = ShipmentException::create([
                'shipment_id' => $shipment->id,
                'exception_type' => strtoupper($attributes['exception_type']),
                'severity' => strtoupper($attributes['severity'] ?? 'MEDIUM'),
                'status' => strtoupper($attributes['status'] ?? 'OPEN'),
                'delay_impact_hours' => (int) ($attributes['delay_impact_hours'] ?? 0),
                'description' => $attributes['description'],
                'root_cause' => $attributes['root_cause'] ?? null,
                'action_taken' => $attributes['action_taken'] ?? null,
                'detected_at' => $attributes['detected_at'] ?? now(),
                'resolved_at' => $attributes['resolved_at'] ?? null,
                'logged_by' => $actor->id,
                'resolved_by' => $attributes['resolved_by'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'entity_type' => ShipmentException::class,
                'entity_id' => $exception->id,
                'action' => 'EXCEPTION_TRIGGERED',
                'old_values' => null,
                'new_values' => $exception->toArray(),
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => 'Logistics exception flagged',
                'message' => sprintf('%s exception detected for shipment %s.', $exception->exception_type, $shipment->shipment_number),
                'type' => 'WARNING',
                'is_read' => false,
            ]);

            return $exception;
        });
    }

    public function investigate(ShipmentException $exception, array $attributes, User $actor): ShipmentException
    {
        if ($exception->status === 'RESOLVED') {
            throw ValidationException::withMessages([
                'status' => 'A resolved exception cannot be investigated again.',
            ]);
        }

        $exception->status = 'INVESTIGATING';
        $exception->root_cause = $attributes['root_cause'] ?? $exception->root_cause;
        $exception->action_taken = $attributes['action_taken'] ?? $exception->action_taken;
        $exception->save();

        return $exception;
    }

    public function resolve(ShipmentException $exception, array $attributes, User $actor): ShipmentException
    {
        $exception->status = 'RESOLVED';
        $exception->resolved_at = $attributes['resolved_at'] ?? now();
        $exception->resolved_by = $actor->id;
        $exception->root_cause = $attributes['root_cause'] ?? $exception->root_cause;
        $exception->action_taken = $attributes['action_taken'] ?? $exception->action_taken;
        $exception->save();

        AuditLog::create([
            'user_id' => $actor->id,
            'entity_type' => ShipmentException::class,
            'entity_id' => $exception->id,
            'action' => 'EXCEPTION_RESOLVED',
            'old_values' => ['status' => 'INVESTIGATING'],
            'new_values' => ['status' => 'RESOLVED'],
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'created_at' => now(),
        ]);

        return $exception;
    }
}
