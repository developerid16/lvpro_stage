<?php

namespace App\Helpers;

use App\Models\AdminActivityLog;

class AdminLogger
{
    public static function log($action, $model = null, $modelId = null, $description = null)
    {
        AdminActivityLog::create([
            'admin_id'    => auth()->id(),
            'action'      => $action,
            'model'       => $model,
            'model_id'    => $modelId,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
