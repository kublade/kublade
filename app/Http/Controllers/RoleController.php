<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\PermissionSet;
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
        $this->middleware('auth');
    }

    /**
     * Show the role index page.
     *
     * @return \Illuminate\View\View
     */
    public function page_index()
    {
        return view('role.index', [
            'roles' => Role::paginate(10),
        ]);
    }

    /**
     * Show the add role page.
     *
     * @return \Illuminate\View\View
     */
    public function page_add()
    {
        return view('role.add', [
            'permissions' => PermissionSet::tree(),
        ]);
    }

    /**
     * Add a new role.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_add(Request $request)
    {
        Validator::make($request->toArray(), [
            'name'        => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ])->validate();

        if (
            $role = Role::create([
                'name' => $request->name,
            ])
        ) {
            $role->syncPermissions($request->permissions ?? []);

            return redirect()->route('role.index')->with('success', 'Role created successfully.');
        }

        return redirect()->back()->with('warning', 'Ooops, something went wrong.');
    }

    /**
     * Show the update role page.
     *
     * @param string $role_id
     *
     * @return \Illuminate\View\View
     */
    public function page_update(string $role_id)
    {
        $role = Role::where('id', $role_id)->first();

        return view('role.update', [
            'role'        => $role,
            'mapped'      => $role->permissions->pluck('name'),
            'permissions' => PermissionSet::tree(),
        ]);
    }

    /**
     * Update a role.
     *
     * @param string  $role_id
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_update(string $role_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name'        => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ])->validate();

        if ($role = Role::where('id', $role_id)->first()) {
            $role->update([
                'name' => $request->name,
            ]);

            $role->syncPermissions($request->permissions ?? []);

            return redirect()->route('role.index')->with('success', 'Role updated successfully.');
        }

        return redirect()->back()->with('warning', 'Ooops, something went wrong.');
    }

    /**
     * Delete a role.
     *
     * @param string $role_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_delete(string $role_id)
    {
        if ($role = Role::where('id', $role_id)->first()) {
            $role->delete();

            return redirect()->route('role.index')->with('success', 'Role deleted successfully.');
        }

        return redirect()->back()->with('warning', 'Ooops, something went wrong.');
    }
}
