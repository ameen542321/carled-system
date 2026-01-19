<?php

namespace App\Helpers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogHelper
{
public static function add($action, $description, $storeId = null)
{
    // الفاعل (User أو Accountant)
    $actor = auth()->user() ?? auth('accountant')->user();

    if (!$actor) {
        return;
    }

    // تحديد المتجر
    $storeId = $storeId ?? ($actor->current_store_id ?? null);

    if (!$storeId) {
        return;
    }

    // جلب المتجر
    $store = \App\Models\Store::find($storeId);

    if (!$store) {
        return;
    }

    // جلب صاحب المتجر الحقيقي
    $ownerId = $store->user_id;

    if (!$ownerId) {
        return;
    }

    // تسجيل اللوق باسم صاحب المتجر
    \App\Models\Log::create([
        'user_id'     => $ownerId,   // ✔ صاحب المتجر
        'store_id'    => $storeId,
        'action'      => $action,
        'description' => $description,
    ]);
}



}
