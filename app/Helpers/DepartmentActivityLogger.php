<?php

namespace App\Helpers;

use App\Models\DepartmentActivityLog;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class DepartmentActivityLogger
{
    public static function log(
        string  $action,
        string  $module,
        ?int    $recordId  = null,
        ?string $label     = null,
        array   $oldValues = [],
        array   $newValues = [],
        ?string $message   = null  // ✅ new parameter
    ): void {
        try {
            $user         = Auth::user();
            $activeDeptId = session('active_department_id');

            $department     = $activeDeptId ? Department::find($activeDeptId) : null;
            $departmentName = $department?->name;

            $activeRole = null;
            if ($user) {
                $activeRole = $user->roles->first(function ($role) use ($activeDeptId) {
                    return (string)$role->department === (string)$activeDeptId;
                }) ?? $user->roles->first();
            }

            $changedFields = [];
            if (!empty($oldValues) && !empty($newValues)) {
                foreach ($newValues as $key => $newVal) {
                    $oldVal = $oldValues[$key] ?? null;
                    if ($oldVal != $newVal) {
                        $changedFields[$key] = [
                            'from' => $oldVal,
                            'to'   => $newVal,
                        ];
                    }
                }
            }

            $sensitiveFields = ['password', 'remember_token', 'token', 'secret'];
            $oldValues = array_diff_key($oldValues, array_flip($sensitiveFields));
            $newValues = array_diff_key($newValues, array_flip($sensitiveFields));

            // ✅ Auto message generate — custom ન હોય તો
            if (!$message) {
                $message = match($action) {
                    'create'       => "{$module} '{$label}' created.",
                    'update'       => "{$module} '{$label}' updated.",
                    'delete'       => "{$module} '{$label}' moved to trash.",
                    'restore'      => "{$module} '{$label}' restored.",
                    'force_delete' => "{$module} '{$label}' permanently deleted.",
                    default        => "{$action} performed on {$module} '{$label}'.",
                };
            }

            DepartmentActivityLog::create([
                'user_id'         => $user?->id,
                'user_name'       => $user?->name,
                'action'          => $action,
                'module'          => $module,
                'record_id'       => $recordId,
                'record_label'    => $label,
                'message'         => $message, // ✅
                'old_values'      => !empty($oldValues) ? $oldValues : null,
                'new_values'      => !empty($newValues) ? $newValues : null,
                'changed_fields'  => !empty($changedFields) ? $changedFields : null,
                'department_id'   => $activeDeptId,
                'department_name' => $departmentName,
                'role_id'         => $activeRole?->id,
                'role_name'       => $activeRole?->name,
                'ip_address'      => Request::ip(),
                'user_agent'      => Request::header('User-Agent'),
                'url'             => Request::fullUrl(),
                'method'          => Request::method(),
            ]);

        } catch (\Throwable $e) {
            \Log::error('DepartmentActivityLogger error: ' . $e->getMessage());
        }
    }
}