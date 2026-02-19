<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get user list with stats and filters
     */
    public function getUserListWithStats(array $filters = [])
    {
        $query = User::query();
        // Filtering
        if (isset($filters['role'])) {
            $query->role($filters['role']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->with('roles')->orderBy('created_at', 'desc');
        // Add more filters as needed
        $users = $query->paginate($filters['per_page'] ?? 15);

        // Stats
        $stats = [
            'total_users' => User::count(),
            'clients' => User::role('client')->count(),
            'owners' => User::role('vehicle_owner')->count(),
            'drivers' => User::role('driver')->count(),
        ];

        return [
            'users' => $users,
            'stats' => $stats,
        ];
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $user = User::find($data['id']) ?? new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->status = $data['status'] ?? 'active';
        $user->password = Hash::make($data['password']);
        $user->save();
        $user->assignRole($data['role']);

        return $user;
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $data): User
    {
        $user->fill(Arr::only($data, ['name', 'email', 'phone', 'status']));
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        if (! empty($data['role']) && ! $user->hasRole($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    /**
     * Update user status (active/inactive)
     */
    public function updateUserStatus(User $user, string $status): User
    {
        $user->status = $status;
        $user->save();

        return $user;
    }
}
