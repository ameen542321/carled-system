<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
 

    protected $fillable = [
        'store_id',
        'employee_id',     // الموظف المرتبط بالدين (بديل credit_user_id)
        'accountant_id',   // المحاسب الذي أجرى العملية
        'sale_type',       // نوع البيع (cash, card, credit)
        'products_total',  // مجموع قطع الغيار فقط (قبل الضريبة)
        'tax_rate',        // نسبة الضريبة المطبقة (0 أو 15)
        'labor_total',     // أجور اليد (صافي - لا تخضع للضريبة حسب طلبك)
        'final_total',     // الإجمالي النهائي (المنتجات + ضريبتها + أجور اليد)
        'paid_amount',     // المبلغ المدفوع فعلياً
        'remaining_amount',// المبلغ المتبقي (الدين)
        'profit',
        'total',
        'has_invoice',     // هل العميل طلب فاتورة ضريبية؟
        'description',     // وصف أجور اليد أو ملاحظات عامة
    ];

    /**
     * تحويل البيانات لضمان الدقة الحسابية عند التعامل مع المبالغ والضرائب
     */
    protected $casts = [
        'products_total'   => 'double',
        'labor_total'      => 'double',
        'final_total'      => 'double',
        'paid_amount'      => 'double',
        'remaining_amount' => 'double',
        'tax_rate'         => 'integer',
        'has_invoice'      => 'boolean',
        'profit'           => 'double',
    ];

    // --- العلاقات (Relationships) ---

    // المتجر الذي تمت فيه العملية
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // المحاسب (User الموظف في النظام)
    public function accountant()
    {
        return $this->belongsTo(Accountant::class);
    }

    // الموظف المرتبط بالدين (تم اعتماده كبديل لـ creditUser)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /* | تم تعطيل هذه العلاقة بناءً على طلبك لعدم استخدامها في المشروع
    | public function creditUser()
    | {
    |    return $this->belongsTo(User::class, 'credit_user_id');
    | }
    */

    // تفاصيل المنتجات في هذه البيعة
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    // الفاتورة المرتبطة بالعملية
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
