<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRoleController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'team' ? 'team' : 'roles';

        return view('admin.roles.index', [
            'tab' => $tab,
            'roles' => AdminRole::query()->withCount('users')->orderByDesc('is_super')->orderBy('name')->get(),
            'staff' => User::query()->where('role', 'admin')->with('adminRole')->orderBy('name')->get(),
            'groups' => AdminRole::groupedCatalog(),
            'moduleKeys' => array_keys(AdminRole::catalog()),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $request->session()->flash('tab', 'roles');
        $role = new AdminRole;
        $this->saveRole($request, $role);

        (new ActivityLogger())->event('admin_role_created')->module('roles')->success()
            ->description('Created admin role "'.$role->name.'".')->log();

        return back()->with('success', 'Role created.')->with('tab', 'roles');
    }

    public function updateRole(Request $request, AdminRole $role): RedirectResponse
    {
        $request->session()->flash('tab', 'roles');
        $this->saveRole($request, $role);

        (new ActivityLogger())->event('admin_role_updated')->module('roles')->success()
            ->description('Updated admin role "'.$role->name.'".')->log();

        return back()->with('success', 'Role updated.')->with('tab', 'roles');
    }

    public function destroyRole(Request $request, AdminRole $role): RedirectResponse
    {
        $request->session()->flash('tab', 'roles');
        if ($role->is_super && ! $request->user()->isSuperAdmin()) {
            abort(403);
        }
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Reassign team members before deleting this role.'])->with('tab', 'roles');
        }

        $name = $role->name;
        $role->delete();

        (new ActivityLogger())->event('admin_role_deleted')->module('roles')->warning()
            ->description('Deleted admin role "'.$name.'".')->log();

        return back()->with('success', 'Role deleted.')->with('tab', 'roles');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $request->session()->flash('tab', 'team');
        $data = $this->staffData($request);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'admin';
        $data['onboarding_completed'] = true;
        $data['mentor_status'] = User::MENTOR_STATUS_APPROVED;

        $user = User::create($data);

        (new ActivityLogger())->event('admin_staff_created')->module('roles')->success()
            ->description('Created admin user '.$user->name.'.')->log();

        return back()->with('success', 'Team member created. They can log in at the admin login.')->with('tab', 'team');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $request->session()->flash('tab', 'team');
        abort_unless($user->isAdmin(), 404);

        $data = $this->staffData($request, $user);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        (new ActivityLogger())->event('admin_staff_updated')->module('roles')->success()
            ->description('Updated admin user '.$user->name.'.')->log();

        return back()->with('success', 'Team member updated.')->with('tab', 'team');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        $request->session()->flash('tab', 'team');
        abort_unless($user->isAdmin(), 404);

        if ((int) $user->id === (int) $request->user()->id) {
            return back()->withErrors(['staff' => 'You cannot remove your own account.'])->with('tab', 'team');
        }

        $name = $user->name;
        $user->delete();

        (new ActivityLogger())->event('admin_staff_deleted')->module('roles')->warning()
            ->description('Removed admin user '.$name.'.')->log();

        return back()->with('success', 'Team member removed.')->with('tab', 'team');
    }

    private function saveRole(Request $request, AdminRole $role): void
    {
        $keys = array_keys(AdminRole::catalog());
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('admin_roles', 'name')->ignore($role->id)],
            'is_super' => ['sometimes', 'boolean'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['in:'.implode(',', $keys)],
        ]);

        if ($role->exists && $role->is_super && ! $request->user()->isSuperAdmin()) {
            abort(403);
        }

        $isSuper = $role->is_super;
        if ($request->user()->isSuperAdmin()) {
            $isSuper = $request->boolean('is_super');
        }

        $role->fill([
            'name' => $data['name'],
            'is_super' => $isSuper,
            'modules' => $isSuper ? [] : array_values(array_unique($data['modules'] ?? [])),
        ])->save();
    }

    private function staffData(Request $request, ?User $user = null): array
    {
        $isCreate = $user === null;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$isCreate ? 'required' : 'nullable', 'string', 'min:8', 'max:80'],
            'admin_role_id' => ['required', 'exists:admin_roles,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $role = AdminRole::findOrFail($data['admin_role_id']);
        if ($role->is_super && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Only a super admin can assign the Super Admin role.');
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
