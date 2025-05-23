<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserController.
 *
 * This class is the controller for the user actions.
 *
 * @OA\Tag(
 *     name="Users",
 *     description="Endpoints for user management"
 * )
 *
 * @OA\Parameter(
 *     name="user_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the user",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List the users.
     *
     * @OA\Get(
     *     path="/api/users",
     *     summary="List users",
     *     tags={"Users"},
     *
     *     @OA\Parameter(ref="#/components/parameters/cursor"),
     *
     *     @OA\Response(response=200, description="Users retrieved successfully"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        $users = User::cursorPaginate(10);

        return Response::generate(200, 'success', 'Users retrieved', [
            'users' => collect($users->items())->map(function ($item) {
                return $item->toArray();
            }),
            'links' => [
                'next' => $users->nextCursor()?->encode(),
                'prev' => $users->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get the user.
     *
     * @OA\Get(
     *     path="/api/users/{user_id}",
     *     summary="Get a user",
     *     tags={"Users"},
     *
     *     @OA\Parameter(ref="#/components/parameters/user_id"),
     *
     *     @OA\Response(response=200, description="User retrieved successfully"),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string $user_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get(string $user_id)
    {
        $validator = Validator::make([
            'user_id' => $user_id,
        ], [
            'user_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($user = User::where('id', $user_id)->first()) {
            return Response::generate(200, 'success', 'User retrieved', [
                'user' => $user->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'User not found');
    }

    /**
     * Add a new user.
     *
     * @OA\Post(
     *     path="/api/users",
     *     summary="Add a new user",
     *     tags={"Users"},
     *
     *     @OA\Response(response=200, description="User created successfully"),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'roles'       => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ])
        ) {
            $user->syncRoles($request->roles ?? []);
            $user->syncPermissions($request->permissions ?? []);

            return Response::generate(200, 'success', 'User created', [
                'user' => $user->toArray(),
            ]);
        }

        return Response::generate(500, 'error', 'User not created');
    }

    /**
     * Update the user.
     *
     * @OA\Patch(
     *     path="/api/users/{user_id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *
     *     @OA\Parameter(ref="#/components/parameters/user_id"),
     *
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string  $user_id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_update(string $user_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles'       => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($user = User::where('id', $user_id)->first()) {
            $user->update([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            $user->syncRoles($request->roles ?? []);
            $user->syncPermissions($request->permissions ?? []);

            return Response::generate(200, 'success', 'User updated', [
                'user' => $user->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'User not found');
    }

    /**
     * Delete the user.
     *
     * @OA\Delete(
     *     path="/api/users/{user_id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *
     *     @OA\Parameter(ref="#/components/parameters/user_id"),
     *
     *     @OA\Response(response=200, description="User deleted successfully"),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string $user_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_delete(string $user_id)
    {
        $validator = Validator::make([
            'user_id' => $user_id,
        ], [
            'user_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $user = User::where('id', $user_id)
                ->where('user_id', '!=', Auth::id())
                ->first()
        ) {
            $user->delete();

            return Response::generate(200, 'success', 'User deleted', [
                'user' => $user->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'User not found');
    }
}
