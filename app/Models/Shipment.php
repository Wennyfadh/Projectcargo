<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_number',
        'booking_reference',
        'bill_of_lading_number',
        'container_number',
        'seal_number',
        'customer_id',
        'carrier_id',
        'origin_location_id',
        'destination_location_id',
        'current_location_id',
        'transport_mode',
        'vehicle_identifier',
        'vessel_name',
        'voyage_number',
        'cargo_type',
        'container_type',
        'status',
        'delay_status',
        'expected_departure_at',
        'actual_departure_at',
        'expected_arrival_at',
        'estimated_arrival_at',
        'actual_arrival_at',
        'delay_duration_hours',
        'delay_reason',
        'temperature_control',
        'special_instructions',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expected_departure_at' => 'datetime',
            'actual_departure_at' => 'datetime',
            'expected_arrival_at' => 'datetime',
            'estimated_arrival_at' => 'datetime',
            'actual_arrival_at' => 'datetime',
            'delay_duration_hours' => 'integer',
        ];
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent()
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    public function exceptions()
    {
        return $this->hasMany(ShipmentException::class)->orderBy('detected_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function isTerminal(): bool
    {
        return in_array($this->status, ['DELIVERED', 'CANCELLED']);
    }

    public function totalGrossWeightKg(): float
    {
        return (float) $this->items()->sum('gross_weight_kg');
    }

    public function totalVolumeCbm(): float
    {
        return (float) $this->items()->sum('volume_cbm');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return $query;
        }

        if ($user->isCustomer()) {
            return $query->where('customer_id', $user->id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeMode(Builder $query, ?string $mode): Builder
    {
        if (empty($mode) || $mode === 'ALL') {
            return $query;
        }

        return $query->where('transport_mode', strtoupper($mode));
    }

    public function getTransportModeAttribute(?string $val): string
    {
        return $val ?: 'SEA';
    }

    public function isSea(): bool
    {
        return $this->transport_mode === 'SEA';
    }

    public function isAir(): bool
    {
        return $this->transport_mode === 'AIR';
    }

    public function isRoad(): bool
    {
        return $this->transport_mode === 'ROAD';
    }

    public function getTransportModeLabelAttribute(): string
    {
        return match ($this->transport_mode) {
            'AIR' => 'Kargo Udara (Pesawat)',
            'ROAD' => 'Kargo Darat (Mobil Truk)',
            default => 'Kargo Laut (Kapal Samudera)',
        };
    }

    public function getTransportModeIconAttribute(): string
    {
        return match ($this->transport_mode) {
            'AIR' => '✈️',
            'ROAD' => '🚛',
            default => '🚢',
        };
    }

    public function getTransportColorAttribute(): string
    {
        return match ($this->transport_mode) {
            'AIR' => '#0284c7', // Sky Blue
            'ROAD' => '#059669', // Emerald Green
            default => '#00a884', // Vibrant Teal
        };
    }

    public function getCalloutPrefixAttribute(): string
    {
        return match ($this->transport_mode) {
            'AIR' => 'Dalam penerbangan ke',
            'ROAD' => 'Dalam perjalanan darat ke',
            default => 'Dalam perjalanan ke',
        };
    }
}
