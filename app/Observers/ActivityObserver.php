<?php

namespace App\Observers;

use App\Helpers\AdminLogger;

class ActivityObserver
{
    public function created($model)
    {
        AdminLogger::log('create', get_class($model), $model->id);
    }

    public function updated($model)
    {
        AdminLogger::log('update', get_class($model), $model->id);
    }

    public function deleted($model)
    {
        AdminLogger::log('delete', get_class($model), $model->id);
    }
}
