<?php

namespace App\Http\Middleware;

use App\Models\Department;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActiveDepartmentPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        // Super Admin bypass
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Set default department if not exists
        if (session('active_permissions') === null) {
            $this->setDefaultDepartment($user);
        }

        // 🔥 NEW: Sync latest permissions every request
        $this->syncDepartmentPermissions($user);

        $activePermissions = session('active_permissions');
        if ($activePermissions !== null) {
            $requiredPermissions = explode('|', $permission);

            $hasPermission = collect($requiredPermissions)->some(function ($perm) use ($activePermissions) {
                return in_array(trim($perm), $activePermissions);
            });
            if (!$hasPermission) {
                abort(403, 'This permission is not allowed in the selected department.');
            }
        } else {
            if (!$user->can($permission)) {
                abort(403, 'No department has been assigned yet.');
            }
        }

        return $next($request);
    }

    private function setDefaultDepartment($user): void
    {
        $firstRole = $user->roles->whereNotNull('department')->first();

        if (!$firstRole) return;

        $department = Department::find($firstRole->department);
        if (!$department) return;

        $this->setDepartmentSession($user, $department->id);
    }

    /**
     * 🔥 NEW FUNCTION: Sync permissions with DB
     */
    private function syncDepartmentPermissions($user): void
    {
        $deptId = session('active_department_id');

        if (!$deptId) return;

        // Get roles of current department
        $roles = $user->roles->filter(function ($role) use ($deptId) {
            return (string)$role->department === (string)$deptId;
        });

        // Get latest permissions from DB
        $latestPermissions = $roles
            ->flatMap(fn($role) => $role->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();

        $sessionPermissions = session('active_permissions', []);

        // Merge new permissions
        $updatedPermissions = array_unique(array_merge($sessionPermissions, $latestPermissions));

        // Update session only if changed
        if ($updatedPermissions !== $sessionPermissions) {
            session([
                'active_permissions' => $updatedPermissions
            ]);
        }
    }

    /**
     * Common method to set department session
     */
    private function setDepartmentSession($user, $departmentId): void
    {
        $department = Department::find($departmentId);
        if (!$department) return;

        $roles = $user->roles->filter(function ($role) use ($departmentId) {
            return (string)$role->department === (string)$departmentId;
        });

        $permissions = $roles
            ->flatMap(fn($role) => $role->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();

        session([
            'active_department_id'    => $department->id,
            'active_club_location_id' => $department->club_location_id,
            'active_permissions'      => $permissions,
        ]);
    }
}
