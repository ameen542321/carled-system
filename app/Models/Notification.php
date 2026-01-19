<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    protected $fillable = [
        'sender_id',
        'sender_type',
        'target_type',
        'target_ids',
        'title',
        'message',
        'template_key',
        'channel',
        'read_by',
    ];

    protected $casts = [
        'target_ids' => 'array',
        'read_by'    => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | دوال الفلترة (Scopes) - لضمان دقة البيانات
    |--------------------------------------------------------------------------
    */
// داخل ملف Notification.php

// جلب الإشعارات التي تخص المستخدم أو العامة
public static function scopeForUser($query, $userId)
{
    return $query->where(function ($q) use ($userId) {
        $q->where('target_type', 'all')
          ->orWhereJsonContains('target_ids', $userId);
    })->orderBy('created_at', 'desc');
}

// حساب عدد الإشعارات غير المقروءة
public static function scopeUnreadCountFor($query, $userId)
{
    // ملاحظة: هنا نفترض وجود جدول وسيط أو حقل يحدد من قرأ ماذا
    // إذا كنت تستخدم نظام لارافل الافتراضي للإشعارات:
    return $query->forUser($userId)->whereNull('read_at')->count();
}

// دالة التحقق من القراءة (التي استخدمتها أنت في الكود)
public function isReadBy($userId)
{
    return !is_null($this->read_at); // أو حسب منطق قاعدة بياناتك
}
    /**
     * جلب الإشعارات الموجهة لمستخدم محدد (عامة أو خاصة)
     */
    // public function scopeForUser(Builder $query, $userId)
    // {
    //     return $query->where(function ($q) use ($userId) {
    //         $q->where('target_type', 'all')
    //           ->orWhereJsonContains('target_ids', (string)$userId);
    //     });
    // }

    /**
     * جلب الإشعارات غير المقروءة والموجهة للمستخدم حصراً
     */
    public function scopeUnreadFor(Builder $query, $userId)
    {
        return $query->forUser($userId)
                     ->where(function ($q) use ($userId) {
                         $q->whereNull('read_by')
                           ->orWhereJsonDoesntContain('read_by', (string)$userId);
                     });
    }

    /*
    |--------------------------------------------------------------------------
    | دوال القراءة (Read System)
    |--------------------------------------------------------------------------
    */

    /**
     * تعليم الإشعار كمقروء
     */
    public function markAsRead($userId)
    {
        $readBy = $this->read_by ?? [];

        if (in_array((string)$userId, $readBy)) {
            return $this;
        }

        $readBy[] = (string)$userId;
        $this->update(['read_by' => $readBy]);

        return $this;
    }

    /**
     * هل الإشعار مقروء من قبل مستخدم معيّن؟
     */
    // public function isReadBy($userId)
    // {
    //     return in_array((string)$userId, $this->read_by ?? []);
    // }

    /*
    |--------------------------------------------------------------------------
    | دوال الإحصاء (Statistics)
    |--------------------------------------------------------------------------
    */

    /**
     * العداد الدقيق للإشعارات غير المقروءة
     */
    public static function unreadCountFor($userId)
    {
        if (!$userId) return 0;
        return self::unreadFor($userId)->count();
    }

    /**
     * مسح كافة الإشعارات (جعل الكل مقروء) للمستخدم
     */
    public static function markAllAsReadFor($userId)
    {
        $unreadNotifications = self::unreadFor($userId)->get();

        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead($userId);
        }

        return true;
    }
}
