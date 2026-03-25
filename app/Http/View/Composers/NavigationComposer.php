<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NavigationComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with('deptPermissions', collect());
            $view->with('allDepartments', collect());
            $view->with('selectedDeptId', null);
            $view->with('isSuperAdmin', false);
            return;
        }

        $user = Auth::user();

        // 1. Get user's roles with permissions
        $userRoles = $user->roles()->with('permissions')->get();

        // 2. Check Super Admin
        $isSuperAdmin = $userRoles->contains('name', 'Super Admin');

        // 3. Selected dept from session
        $selectedDeptId = session('selected_department_id');

        if ($isSuperAdmin) {

            // Super Admin: dropdown shows ALL departments in system
            $allDepartments = Department::where('status', 'active')->get();

            if ($selectedDeptId) {
                // Super Admin selected a specific dept — show that dept ki ALL roles ki permissions
                $deptPermissions = Role::with('permissions')
                    ->where('department', $selectedDeptId)
                    ->get()
                    ->flatMap(fn($role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->values();
            } else {
                // Super Admin "All" — show every permission in system
                $deptPermissions = Permission::pluck('name');
            }

        } else {

            // Normal User: dropdown shows ONLY departments from their own roles
            $userRoles = $user->roles()->with('permissions')->get();

            $userDepartmentIds = $userRoles
                ->pluck('department')
                ->filter()
                ->unique()
                ->values();

            // Dropdown list — only their departments
            $allDepartments = Department::whereIn('id', $userDepartmentIds)->get();

            if ($selectedDeptId && $userDepartmentIds->contains($selectedDeptId)) {
                // User selected specific dept from their own departments
                $deptPermissions = $userRoles
                    ->where('department', $selectedDeptId)
                    ->flatMap(fn($role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->values();
            } else {
                // "All" — show permissions from ALL their own departments
                $deptPermissions = $userRoles
                    ->whereIn('department', $userDepartmentIds->toArray())
                    ->flatMap(fn($role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->values();
            }
        }

        $view->with('deptPermissions', $deptPermissions);
        $view->with('allDepartments', $allDepartments);
        $view->with('selectedDeptId', $selectedDeptId);
        $view->with('isSuperAdmin', $isSuperAdmin);
    }
}