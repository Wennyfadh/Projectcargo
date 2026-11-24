<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED,CANCELLED'],
            'vessel_name' => ['nullable', 'string', 'max:100'],
            'voyage_number' => ['nullable', 'string', 'max:50'],
            'estimated_arrival_at' => ['nullable', 'date'],
            'delay_reason' => ['nullable', 'string'],
        ];
    }
}
