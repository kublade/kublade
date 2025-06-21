<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

/**
 * Class RoleController.
 *
 * This class is the controller for the role actions.
 *
 * @OA\Tag(
 *     name="Roles",
 *     description="Endpoints for role management"
 * )
 *
 * @OA\Parameter(
 *     name="role_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the role",
 *
 *     @OA\Schema(type="integer")
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RoleController extends Controller
{
    /**
     * List the roles.
     *
     * @OA\Get(
     *     path="/api/roles",
     *     summary="List roles",
     *     tags={"Roles"},
     *
     *     @OA\Parameter(ref="#/components/parameters/cursor"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="roles", type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/Role")
     *                 ),
     *
     *                 @OA\Property(property="links", type="object",
     *                     @OA\Property(property="next", type="string"),
     *                     @OA\Property(property="prev", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        $roles = Role::cursorPaginate(10);

        return Response::generate(200, 'success', 'Roles retrieved successfully', [
            'roles' => collect($roles->items())->map(function ($item) {
                return $item->toArray();
            }),
            'links' => [
                'next' => $roles->nextCursor()?->encode(),
                'prev' => $roles->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get the role.
     *
     * @OA\Get(
     *     path="/api/roles/{role_id}",
     *     summary="Get a role",
     *     tags={"Roles"},
     *
     *     @OA\Parameter(ref="#/components/parameters/role_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="role", ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string $role_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get(string $role_id)
    {
        $validator = Validator::make([
            'role_id' => $role_id,
        ], [
            'role_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($role = Role::where('id', $role_id)->first()) {
            return Response::generate(200, 'success', 'Role retrieved successfully', [
                'role' => $role->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Role not found');
    }

    /**
     * Add a new role.
     *
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Add a new role",
     *     tags={"Roles"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"), nullable=true),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="role", ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *
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
            'permissions' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $role = Role::create([
                'name' => $request->name,
            ])
        ) {
            Artisan::call('kbl:permissions:sync');

            $role->syncPermissions($request->permissions ?? []);

            return Response::generate(200, 'success', 'Role created successfully', [
                'role' => $role->toArray(),
            ]);
        }

        return Response::generate(500, 'error', 'Role not created');
    }

    /**
     * Update the role.
     *
     * @OA\Patch(
     *     path="/api/roles/{role_id}",
     *     summary="Update a role",
     *     tags={"Roles"},
     *
     *     @OA\Parameter(ref="#/components/parameters/role_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"), nullable=true),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="role", ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string  $role_id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_update(string $role_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($role = Role::where('id', $role_id)->first()) {
            $role->update([
                'name' => $request->name,
            ]);

            Artisan::call('kbl:permissions:sync');

            $role->syncPermissions($request->permissions ?? []);

            return Response::generate(200, 'success', 'Role updated successfully', [
                'role' => $role->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Role not found');
    }

    /**
     * Delete the role.
     *
     * @OA\Delete(
     *     path="/api/roles/{role_id}",
     *     summary="Delete a role",
     *     tags={"Roles"},
     *
     *     @OA\Parameter(ref="#/components/parameters/role_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="role", ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string $role_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_delete(string $role_id)
    {
        $validator = Validator::make([
            'role_id' => $role_id,
        ], [
            'role_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($role = Role::where('id', $role_id)->first()) {
            $role->delete();

            return Response::generate(200, 'success', 'Role deleted successfully', [
                'role' => $role->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Role not found');
    }
}
