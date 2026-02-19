<?php

namespace App\Services;

use App\Models\Vehicle;

class VehicleService
{
    /**
     * Get a list of vehicles with summary info.
     */
    /**
     * Get a list of vehicles with summary info, with optional filters.
     *
     * @param  array  $filters
     *                          'type' => string|null,
     *                          'driver_available' => bool|null, // true: With Driver, false: Self Drive
     *                          'price_min' => float|null,
     *                          'price_max' => float|null
     */
    public function getVehicleList(array $filters = [])
    {
        $filters = request()->all(); // Get filters from request query parameters
        $query = Vehicle::with(['owner', 'documents'])
            ->when(! empty($filters['type']), function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            })
            ->when(isset($filters['driver_available']), function ($q) use ($filters) {
                $q->where('driver_available', (bool) $filters['driver_available']);
            })
            ->when(isset($filters['price_min']) && isset($filters['price_max']), function ($q) use ($filters) {
                $q->whereBetween('daily_rate', [$filters['price_min'], $filters['price_max']]);
            })
            ->when(isset($filters['price_min']) && ! isset($filters['price_max']), function ($q) use ($filters) {
                $q->where('daily_rate', '>=', $filters['price_min']);
            })
            ->when(! isset($filters['price_min']) && isset($filters['price_max']), function ($q) use ($filters) {
                $q->where('daily_rate', '<=', $filters['price_max']);
            })
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%');
            });
        // ->where('status', 'active'); // Only show active vehicles by default
        if (request()->has('sort_by')) {
            $sortBy = request('sort_by');
            $sortOrder = request('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        }
        if (request('limit')) {
            $data = $query->paginate(request('limit'));
        } else {
            $data = $query->get();
        }

        return $data;
    }

    /**
     * Create a new vehicle.
     */
    public function createVehicle(array $data)
    {
        $id = $data['id'] ?? null;
        if ($id) {
            $objVehicle = Vehicle::find($id);
        } else {
            $objVehicle = Vehicle::firstOrNew(['registration_number' => $data['registration_number']]);
        }
        $objVehicle->fill($data);
        $objVehicle->owner_id = $data['owner_id'] ?? auth()->id();
        $objVehicle->save();
        $vehicle = $objVehicle->fresh();
        // Optionally, eager load owner and primaryImage for response
        $vehicle->load(['owner', 'documents']);

        return $vehicle;
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
