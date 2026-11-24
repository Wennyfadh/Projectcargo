<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    public function calculateDelayHours(Shipment $shipment): int
    {
        if (! $shipment->expected_arrival_at || ! $shipment->estimated_arrival_at) {
            return 0;
        }

        $expected = Carbon::parse($shipment->expected_arrival_at);
        $estimated = Carbon::parse($shipment->estimated_arrival_at);

        $diffInHours = (int) max(0, $expected->diffInHours($estimated, false));

        $shipment->delay_duration_hours = $diffInHours;
        $shipment->delay_status = $this->classify($diffInHours);
        $shipment->delay_reason = $shipment->delay_reason ?: 'ETA variance';
        $shipment->save();

        return $diffInHours;
    }

    public function classify(int $delayHours): string
    {
        if ($delayHours <= 0) {
            return 'ON_TIME';
        }

        if ($delayHours <= 6) {
            return 'LOW_DELAY';
        }

        if ($delayHours <= 24) {
            return 'MEDIUM_DELAY';
        }

        return 'HIGH_DELAY';
    }

    public function evaluate(Shipment $shipment): array
    {
        $delayHours = $this->calculateDelayHours($shipment);

        return [
            'delay_hours' => $delayHours,
            'delay_status' => $this->classify($delayHours),
            'severity' => $this->severityFromStatus($this->classify($delayHours)),
        ];
    }

    public function severityFromStatus(string $delayStatus): string
    {
        return match ($delayStatus) {
            'ON_TIME' => 'INFO',
            'LOW_DELAY' => 'LOW',
            'MEDIUM_DELAY' => 'MEDIUM',
            'HIGH_DELAY' => 'CRITICAL',
            default => 'INFO',
        };
    }
}
