<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStore;

/**
 * Class Purchase
 *
 * يمثل عملية شراء (إضافة مخزون) داخل متجر معيّن.
 */
class Purchase extends Model
{
    use SoftDeletes, BelongsToStore;

    protected $fillable = [
        'store_id',
        'user_id',
        'product_id',
        'quantity',
        'cost',
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
