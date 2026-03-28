<?php

namespace App\Http\View\Composers;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class HeaderComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) return;

        $user         = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        if ($isSuperAdmin) {
            $allDepartments = Department::where('status', 'Active')->get();
        } else {
            $deptIds = $user->roles->pluck('department')->filter()->unique();
            $allDepartments = Department::whereIn('id', $deptIds)
                                ->where('status', 'Active')
                                ->get();
        }

        $selectedDeptId    = session('active_department_id');
        $activePermissions = session('active_permissions');
        if ($isSuperAdmin) {
            $deptPermissions = Permission::where('status', 'Active')->pluck('name');

        } elseif ($activePermissions !== null) {
            $deptPermissions = collect($activePermissions);

        } else {
            $deptPermissions = $user->roles
                ->flatMap(fn($role) => $role->permissions->pluck('name'))
                ->unique()
                ->values();
        }
        $view->with([
            'allDepartments'  => $allDepartments,
            'selectedDeptId'  => $selectedDeptId,
            'isSuperAdmin'    => $isSuperAdmin,
            'deptPermissions' => $deptPermissions,
        ]);
    }
}