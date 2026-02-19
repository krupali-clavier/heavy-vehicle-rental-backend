<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends ApiBaseController
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = $this->vehicleService->getVehicleList();

        return $this->sendResponse($vehicles, 'Vehicles retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'nullable|exists:users,id',
            'registration_number' => 'required|unique:vehicles,registration_number,'.$request->id.',id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'capacity' => 'required|string',
            'engine_power' => 'required|string',
            'fuel_type' => 'nullable|in:Diesel,Gasoline,Petrol,Electric,Hybrid,Other',
            'year' => 'nullable|integer',
            'daily_rate' => 'required|numeric|min:0',
            'weekly_rate' => 'required|numeric|min:0',
            'hourly_rate' => 'required|numeric|min:0',
            'driver_available' => 'boolean',
        ]);
        $vehicle = $this->vehicleService->createVehicle($validated);

        return $this->sendResponse($vehicle, 'Vehicle created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vehicle = $this->vehicleService->getVehicleDetails($id);

        return $this->sendResponse($vehicle, 'Vehicle details retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Change the status of the vehicle (suspend/activate).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);
        $vehicle = Vehicle::findOrFail($id);
        $this->vehicleService->updateStatus($vehicle, $request->input('status'));

        return $this->sendResponse([
            'status' => $vehicle->status,
        ], 'Vehicle status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
