<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Location::query()->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'unlocode' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'facility_type' => ['required', 'in:PORT,FACTORY,WAREHOUSE,CUSTOMS_OFFICE'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'timezone' => ['required', 'string', 'max:50'],
        ])->validate();

        $location = Location::create($data);

        return response()->json($location, 201);
    }

    public function show(Location $location): JsonResponse
    {
        return response()->json($location);
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'unlocode' => ['nullable', 'string', 'max:10'],
            'name' => ['sometimes', 'string', 'max:255'],
            'facility_type' => ['sometimes', 'in:PORT,FACTORY,WAREHOUSE,CUSTOMS_OFFICE'],
            'address' => ['sometimes', 'string'],
            'city' => ['sometimes', 'string', 'max:255'],
            'country_code' => ['sometimes', 'string', 'size:2'],
            'latitude' => ['sometimes', 'numeric'],
            'longitude' => ['sometimes', 'numeric'],
            'timezone' => ['sometimes', 'string', 'max:50'],
        ])->validate();

        $location->fill($data);
        $location->save();

        return response()->json($location);
    }

    public function destroy(Location $location): JsonResponse
    {
        $location->delete();

        return response()->json(['message' => 'Location deleted successfully.']);
    }
}
