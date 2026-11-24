<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'scac',
        'name',
        'contact_email',
        'contact_phone',
        'website_url',
        'tracking_url_template',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
