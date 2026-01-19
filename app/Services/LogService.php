<?php

namespace App\Services;

use App\Models\Log;

class LogService
{
    public function add($action, $description, $model = null, $details = null)
    {
        // دعم جميع الحراس 
        $actor = auth()->user() ?? auth('accountant')->user() ;

        // استخراج store_id من الموديل
        $storeId = null;

        if ($model && isset($model->store_id)) {
            $storeId = $model->store_id;
        }

        // fallback
        if (!$storeId && $actor && isset($actor->store_id)) {
            $storeId = $actor->store_id;
        }

        // منع null
        if (!$storeId) {
            $storeId = 0;
        }

        // تحويل details إلى JSON بشكل آمن
        if (is_array($details) || is_object($details)) {
            $details = json_encode($details, JSON_UNESCAPED_UNICODE);
        }

        return Log::create([
            'store_id'    => $storeId,
            'user_id'     => $actor?->id,
            'actor_type'  => get_class($actor),
            'actor_id'    => $actor?->id,
            'action'      => $action,
            'description' => $description,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'details'     => $details,
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
