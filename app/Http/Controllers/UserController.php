<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\PermissionSet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Class UserController.
 *
 * This class is the controller for the user actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class UserController extends Controller
{
    /**
     * Show the user details page.
     *
     * @return \Illuminate\View\View
     */
    public function page_index()
    {
        return view('user.index', [
            'users' => User::paginate(10),
        ]);
    }

    /**
     * Show the user add page.
     *
     * @return \Illuminate\View\View
     */
    public function page_add()
    {
        return view('user.add', [
            'roles'       => Role::all(),
            'permissions' => PermissionSet::tree(),
        ]);
    }

    /**
     * Add a new user.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_add(Request $request)
    {
        Validator::make($request->toArray(), [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles'       => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
        ])->validate();

        if (
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password ?? Str::random(16)),
            ])
        ) {
            Artisan::call('kbl:permissions:sync');

            $user->syncRoles($request->roles ?? []);
            $user->syncPermissions($request->permissions ?? []);

            return redirect()->route('user.index')->with('success', 'User created successfully.');
        }

        return redirect()->back()->with('warning', 'Ooops, something went wrong.');
    }

    /**
     * Show the user update page.
     *
     * @param string $user_id
     *
     * @return \Illuminate\View\View
     */
    public function page_update(string $user_id)
    {
        $user = User::where('id', $user_id)->first();

        return view('user.update', [
            'user'        => $user,
            'roles'       => Role::all(),
            'permissions' => PermissionSet::tree(),
            'mapped'      => $user->permissions->pluck('name'),
        ]);
    }

    /**
     * Update a user.
     *
     * @param string  $user_id
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_update(string $user_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user_id],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles'       => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
        ])->validate();

        if (
            $user = User::where('id', $user_id)->first()
        ) {
            $user->update([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            Artisan::call('kbl:permissions:sync');

            $user->syncRoles($request->roles ?? []);
            $user->syncPermissions($request->permissions ?? []);

            return redirect()->route('user.update', ['user_id' => $user_id])->with('success', 'User updated successfully.');
        }

        return redirect()->back()->with('warning', 'Ooops, something went wrong.');
    }

    /**
     * Delete a user.
     *
     * @param string $user_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_delete(string $user_id)
    {
        if (
            $user = User::where('id', $user_id)
                ->where('id', '!=', Auth::id())
                ->first()
        ) {
            $user->delete();

            return redirect()->route('user.index')->with('success', 'User deleted successfully.');
        }

        return redirect()->back()->with('warning', 'Ooops, something went wrong.');
    }
}
