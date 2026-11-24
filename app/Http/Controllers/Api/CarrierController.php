<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarrierController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Carrier::query()->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'scac' => ['required', 'string', 'max:10', 'unique:carriers,scac'],
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url'],
            'tracking_url_template' => ['nullable', 'url'],
        ])->validate();

        $carrier = Carrier::create($data);

        return response()->json($carrier, 201);
    }

    public function show(Carrier $carrier): JsonResponse
    {
        return response()->json($carrier);
    }

    public function update(Request $request, Carrier $carrier): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'scac' => ['sometimes', 'string', 'max:10', 'unique:carriers,scac,'.$carrier->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url'],
            'tracking_url_template' => ['nullable', 'url'],
        ])->validate();

        $carrier->fill($data);
        $carrier->save();

        return response()->json($carrier);
    }

    public function destroy(Carrier $carrier): JsonResponse
    {
        $carrier->delete();

        return response()->json(['message' => 'Carrier deleted successfully.']);
    }
}
