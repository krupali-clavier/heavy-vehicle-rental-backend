<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersController extends ApiBaseController
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $result = $this->userService->getUserListWithStats($request->all());

        return $this->sendResponse($result, 'User list fetched successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422);
        }
        $user = $this->userService->createUser($request->all());

        return $this->sendResponse(new UserResource($user), 'User created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->sendResponse(new UserResource($user), 'User details fetched successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'phone' => 'sometimes|required|string|unique:users,phone,'.$user->id,
            'role' => 'sometimes|required|in:client,vehicle_owner,driver,admin',
            'status' => 'sometimes|required|in:active,inactive',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422);
        }
        $user = $this->userService->updateUser($user, $request->all());

        return $this->sendResponse(new UserResource($user), 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return $this->sendResponse([], 'User deleted successfully');
    }

    /**
     * Suspend or activate a user (status update)
     */
    public function updateStatus(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422);
        }
        $user = $this->userService->updateUserStatus($user, $request->status);

        return $this->sendResponse(new UserResource($user), 'User status updated successfully');
    }
}
