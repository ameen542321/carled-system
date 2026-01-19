<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'cost_price',
        'total_price',
        'total_cost',
        'price', // السعر وقت البيع (الذي أضفناه حديثاً)
        'total', // إجمالي السطر (الذي أضفناه حديثاً)
    ];

    // عملية البيع
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // المنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
