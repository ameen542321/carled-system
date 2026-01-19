<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'user_id',
        'actor_type',
        'actor_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'details',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    // علاقات اختيارية
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function actor()
    {
        return $this->morphTo(__FUNCTION__, 'actor_type', 'actor_id');
    }

    public function model()
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }
    protected static function boot()
{
    parent::boot();

    static::deleting(function ($log) {
        if ($log->action === 'balance_done') {
            // منع الحذف وإرجاع خطأ إذا حاول شخص ما مسح السجل
            throw new \Exception("لا يمكن حذف سجلات إقفال الموازنة نهائياً لأسباب أمنية.");
        }
    });
}
}
