<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

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
        $request->validate([
            'id' => 'sometimes|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$request->id.',id,deleted_at,NULL',
            'phone' => 'required|string|unique:users,phone,'.$request->id.',id,deleted_at,NULL',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
            'status' => 'required|in:active,inactive,suspended',
            'password' => 'required_if:id,null|string|min:8',
        ]);
        $user = $this->userService->createUser($request->all());

        return $this->sendResponse($user, 'User '.($request->id ? 'updated' : 'created').' successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->sendResponse($user, 'User details fetched successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);
        $user = $this->userService->updateUserStatus($user, $request->status);

        return $this->sendResponse($user, 'User status updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return $this->sendResponse([], 'User deleted successfully');
    }
}
