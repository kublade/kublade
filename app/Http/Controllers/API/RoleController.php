<?php

declare(strict_types=1);

namespace App\Http\API\Controllers;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

/**
 * Class RoleController.
 *
 * This class is the controller for the role actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List the roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        $roles = Role::cursorPaginate(10);

        return Response::generate(200, 'success', 'Roles retrieved', [
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
            return Response::generate(200, 'success', 'Role retrieved', $role->toArray());
        }

        return Response::generate(404, 'error', 'Role not found');
    }

    /**
     * Add a new role.
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
            $role->syncPermissions($request->permissions ?? []);

            return Response::generate(200, 'success', 'Role created', $role->toArray());
        }

        return Response::generate(400, 'error', 'Role not created');
    }

    /**
     * Update the role.
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

            $role->syncPermissions($request->permissions ?? []);

            return Response::generate(200, 'success', 'Role updated', $role->toArray());
        }

        return Response::generate(404, 'error', 'Role not found');
    }

    /**
     * Delete the role.
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

            return Response::generate(200, 'success', 'Role deleted', $role->toArray());
        }

        return Response::generate(404, 'error', 'Role not found');
    }
}
