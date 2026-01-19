<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'sale_id',
        'customer_name',
        'customer_phone',
        'vehicle_type',
        'plate_number',
        'tax_number',
        'subtotal',      // تم الإضافة
        'tax_amount',    // تم الإضافة
        'total_amount',  // تم الإضافة
        'status',
    ];

    // عملية البيع المرتبطة بالفاتورة
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }


public function getZatcaQrCodeAttribute()
{
    $store = $this->sale->store;

    // التحقق: إذا كان الرقم موجوداً نستخدمه، وإلا نضع 15 صفراً
    $taxNumber = $store->tax_number ?: '000000000000000';

    $data = [
        [1, $store->name],
        [2, $taxNumber],
        [3, $this->created_at->toIso8601String()],
        [4, $this->total_amount],
        [5, $this->tax_amount]
    ];

    $tlv = '';
    foreach ($data as $field) {
        $tag = pack("C", $field[0]);
        $length = pack("C", strlen($field[1]));
        $value = $field[1];
        $tlv .= $tag . $length . $value;
    }

    return base64_encode($tlv);
}
}
