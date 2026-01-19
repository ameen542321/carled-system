<?php

namespace App\Services;

use App\Models\Notification;

class NotificationQueryService
{
    public static function getUnreadCountFor($userId): int
    {
        if (!$userId) return 0;
        return Notification::unreadCountFor($userId);
    }

    public static function getLatestFor($userId, int $limit = 5)
    {
        if (!$userId) return collect([]);

        // ✅ التعديل هنا: الفلترة داخل قاعدة البيانات (Database Level)
        return Notification::where(function ($query) use ($userId) {
                $query->where('target_type', 'all')
                      ->orWhereJsonContains('target_ids', (string)$userId);
            })
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}
