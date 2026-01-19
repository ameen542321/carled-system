<?php

namespace App\Models;

use App\Traits\BelongsToStore;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes, BelongsToStore;

    protected $fillable = [
        'store_id',
        'user_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'quantity',
        'barcode',
        'status',
        'image',
        'min_stock',
    ];

    protected static function boot()
    {
        parent::boot();

        // توليد الرابط المختصر مع دعم الكلمات العربية
        static::creating(function ($product) {
            $product->slug = $product->slug ?: Str::slug($product->name, '-', null);
        });

        static::updating(function ($product) {
            $product->slug = Str::slug($product->name, '-', null);
        });

        // 🔥 إشعار انخفاض المخزون عند التحديث
        static::updated(function ($product) {
            if ($product->isDirty('quantity')) {
                // الاعتماد على min_stock المحدد للمنتج أو 5 كافتراضي
                $limit = $product->min_stock ?? 5;

                if ($product->quantity <= $limit) {
                    NotificationService::sendTemplate('low_stock', [
                        'sender_type' => 'system',
                        'target_type' => 'store',
                        'target_ids'  => [$product->store_id],
                        'product_name' => $product->name,
                        'quantity'     => $product->quantity,
                    ]);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (الاستعلامات المخصصة)
    |--------------------------------------------------------------------------
    */
 public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    /**
     * جلب المنتجات التي وصلت أو نزلت عن حد المخزون الأدنى
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_stock');
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | دوال إدارة المخزون (تستخدم في الكنترولر)
    |--------------------------------------------------------------------------
    */

    public function increaseStock(int $quantity, ?string $note = null, ?int $userId = null): void
    {
        $this->increment('quantity', $quantity);

        $this->stockMovements()->create([
            'store_id'   => $this->store_id,
            'user_id'    => $userId,
            'type'       => 'increase',
            'quantity'   => $quantity,
            'note'       => $note,
        ]);
    }

    public function decreaseStock(int $quantity, ?string $note = null, ?int $userId = null): void
    {
        $this->decrement('quantity', $quantity);

        $this->stockMovements()->create([
            'store_id'   => $this->store_id,
            'user_id'    => $userId,
            'type'       => 'decrease',
            'quantity'   => $quantity,
            'note'       => $note,
        ]);
    }
}
