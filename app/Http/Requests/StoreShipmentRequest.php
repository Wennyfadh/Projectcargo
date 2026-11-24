<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipment_number' => ['required', 'string', 'max:50', 'unique:shipments,shipment_number'],
            'bill_of_lading_number' => ['nullable', 'string', 'max:50', 'unique:shipments,bill_of_lading_number'],
            'container_number' => ['required', 'string', 'max:20'],
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id'],
            'cargo_type' => ['required', 'in:FCL,LCL'],
            'container_type' => ['required', 'in:20GP,40GP,40HC,45HC'],
            'status' => ['nullable', 'in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED,CANCELLED'],
            'expected_departure_at' => ['required', 'date'],
            'expected_arrival_at' => ['required', 'date', 'after_or_equal:expected_departure_at'],
        ];
    }
}
