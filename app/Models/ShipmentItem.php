<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'sku',
        'product_name',
        'hs_code',
        'package_type',
        'quantity',
        'units_per_package',
        'net_weight_kg',
        'gross_weight_kg',
        'volume_cbm',
        'packaging_description',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'units_per_package' => 'integer',
            'net_weight_kg' => 'float',
            'gross_weight_kg' => 'float',
            'volume_cbm' => 'float',
        ];
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
