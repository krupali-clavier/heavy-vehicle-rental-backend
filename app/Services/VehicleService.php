<?php

namespace App\Services;

use App\Models\Vehicle;

class VehicleService
{
    /**
     * Get a list of vehicles with summary info.
     */
    public function getVehicleList()
    {
        return Vehicle::with(['owner', 'primaryImage'])
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'company' => $vehicle->owner ? $vehicle->owner->company_name ?? $vehicle->owner->name : null,
                    'daily_rate' => $vehicle->daily_rate,
                    'status' => $vehicle->status,
                    'image_url' => $vehicle->primaryImage ? $vehicle->primaryImage->url : null,
                ];
            });
    }

    /**
     * Create a new vehicle.
     */
    public function createVehicle(array $data)
    {
        $vehicle = Vehicle::create($data);
        // Optionally, eager load owner and primaryImage for response
        $vehicle->load(['owner', 'primaryImage']);

        return [
            'id' => $vehicle->id,
            'name' => $vehicle->name,
            'company' => $vehicle->owner ? $vehicle->owner->company_name ?? $vehicle->owner->name : null,
            'daily_rate' => $vehicle->daily_rate,
            'status' => $vehicle->status,
            'image_url' => $vehicle->primaryImage ? $vehicle->primaryImage->url : null,
            'description' => $vehicle->description,
            'type' => $vehicle->type,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'registration_number' => $vehicle->registration_number,
            'color' => $vehicle->color,
            'requires_driver' => $vehicle->requires_driver,
            'is_available' => $vehicle->is_available,
            'location_address' => $vehicle->location_address,
        ];
    }

    /**
     * Get detailed info for a single vehicle.
     */
    public function getVehicleDetails($id)
    {
        $vehicle = Vehicle::with(['owner', 'primaryImage'])->findOrFail($id);

        return [
            'id' => $vehicle->id,
            'name' => $vehicle->name,
            'company' => $vehicle->owner ? $vehicle->owner->company_name ?? $vehicle->owner->name : null,
            'daily_rate' => $vehicle->daily_rate,
            'status' => $vehicle->status,
            'image_url' => $vehicle->primaryImage ? $vehicle->primaryImage->url : null,
            'description' => $vehicle->description,
            'type' => $vehicle->type,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'registration_number' => $vehicle->registration_number,
            'color' => $vehicle->color,
            'requires_driver' => $vehicle->requires_driver,
            'is_available' => $vehicle->is_available,
            'location_address' => $vehicle->location_address,
        ];
    }

    /**
     * Update the status of a vehicle.
     */
    public function updateStatus(Vehicle $vehicle, string $status): Vehicle
    {
        $vehicle->status = $status;
        $vehicle->save();

        return $vehicle;
    }
}
