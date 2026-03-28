<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class DepartmentSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $departmentId = $request->department_id;
        $user = Auth::user();

        if ($departmentId === 'all') {
            session()->forget(['active_department_id', 'active_club_location_id', 'active_permissions']);
            return redirect()->back();
        }

        $department = Department::find($departmentId);

        if (!$department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        if ($user->hasRole('Super Admin')) {
            $allPermissions = Role::where('department', $departmentId)
                ->with('permissions')
                ->get()
                ->flatMap(fn($role) => $role->permissions->pluck('name'))
                ->unique()
                ->values()
                ->toArray();
        } else {
            $matchedRoles = $user->roles->filter(function ($role) use ($departmentId) {
                return (string)$role->department === (string)$departmentId;
            });

            if ($matchedRoles->isEmpty()) {
                return redirect()->back()->with('error', 'Unauthorized department access.');
            }

            $allPermissions = $matchedRoles
                ->flatMap(fn($role) => $role->permissions->pluck('name'))
                ->unique()
                ->values()
                ->toArray();
        }

        session([
            'active_department_id'    => $department->id,
            'active_club_location_id' => $department->club_location_id,
            'active_permissions'      => $allPermissions,
        ]);

        return redirect()->back();
    }
}