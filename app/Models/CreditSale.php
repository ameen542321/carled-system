<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStore;

/**
 * ClassCreditSale
 *
 * يمثل عملية بيع آجل قام بها موظف داخل متجر معيّن.
 * تحتوي على:
 * - قيمة البيع
 * - الشهر المحسوب عليه
 * - الشهر الذي سيتم الخصم فيه
 * - حالة العملية
 */
class CreditSale extends Model
{
    protected $table = 'employee_credit_sales';
    use SoftDeletes, BelongsToStore;

    /**
     * الحقول القابلة للتعبئة
     */
  protected $fillable = [
    'person_id',
    'person_type',
    'store_id',
    'amount',
    'remaining_amount',
    'description',
    'date',
    'status',
    'month',
    'deducted_month',
    'added_by',
    'partial_payments',
];

public function person() { return $this->morphTo(); }
    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */
protected $casts = [
    'partial_payments' => 'array',
];

    /**
     * علاقة العملية مع الموظف
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * علاقة العملية مع المتجر (موروثة من BelongsToStore)
     * store()
     */

    /**
     * علاقة العملية مع المستخدم الذي سجّلها
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
