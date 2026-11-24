<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ShipmentService $shipmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shipment::class);

        $query = Shipment::query()->with(['customer', 'carrier', 'origin', 'destination', 'currentLocation']);

        if ($request->user()->isCustomer()) {
            $query->where('customer_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('shipment_number', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bill_of_lading_number', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('destination_location_id')) {
            $query->where('destination_location_id', $request->input('destination_location_id'));
        }

        if ($request->filled('delay_status')) {
            $query->where('delay_status', $request->input('delay_status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expected_departure_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expected_arrival_at', '<=', $request->input('date_to'));
        }

        $sortField = $request->input('sort_by', 'updated_at');
        $sortDirection = $request->input('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

        return response()->json($query->paginate(10)->appends($request->query()));
    }

    public function store(StoreShipmentRequest $request): JsonResponse
    {
        $this->authorize('create', Shipment::class);

        $shipment = $this->shipmentService->create($request->validated(), $request->user());

        return response()->json($shipment->load(['customer', 'carrier', 'origin', 'destination']), 201);
    }

    public function show(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        return response()->json($shipment->load(['customer', 'carrier', 'origin', 'destination', 'currentLocation']));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        if ($request->filled('status')) {
            $shipment = $this->shipmentService->transitionStatus($shipment, $request->status, $request->user(), [
                'description' => 'Status transitioned through API contract.',
            ]);
        }

        $shipment->fill($request->only(['vessel_name', 'voyage_number', 'estimated_arrival_at', 'delay_reason']));
        $shipment->save();

        return response()->json($shipment->fresh());
    }

    public function destroy(Shipment $shipment): JsonResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->softDelete($shipment, Auth::user());

        return response()->json(['message' => 'Shipment deleted successfully.'], 200);
    }
}
