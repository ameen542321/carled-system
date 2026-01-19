<?php

namespace App\Http\Controllers\Employees;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Traits\FindPersonTrait;
use App\Services\EmployeeLogService;

/**
 * --------------------------------------------------------------------------
 * EmployeeReports
 * --------------------------------------------------------------------------
 * هذا الملف مسؤول عن:
 * - تصدير تقارير PDF الخاصة بالموظف أو المحاسب
 * - يعتمد على FindPersonTrait لتحديد نوع الشخص (موظف / محاسب)
 * --------------------------------------------------------------------------
 */
class EmployeeReports
{
    use FindPersonTrait;

    /**
     * ----------------------------------------------------------------------
     * تصدير تقرير PDF باستخدام Snappy
     * ----------------------------------------------------------------------
     * - يقوم بجلب بيانات الشخص (موظف أو محاسب)
     * - يجمع جميع العمليات المرتبطة به
     * - ينشئ ملف PDF جاهز للتحميل
     * ----------------------------------------------------------------------
     */
    public static function exportSnappy($id)
{
    // إنشاء نسخة من الكلاس لاستخدام الـ trait
    $self = new self();

    // جلب الموظف أو المحاسب
    $person = $self->findPerson($id);

    // حسابات المديونية
    $remainingDebt = $person->debts()->sum('amount');

    $collectedThisMonth = $person->debts()
        ->where('amount', '<', 0)
        ->where('month', now()->format('Y-m'))
        ->sum('amount');

    $addedThisMonth = $person->debts()
        ->where('amount', '>', 0)
        ->where('month', now()->format('Y-m'))
        ->sum('amount');

    $debtOperations = $person->debts()->orderBy('date')->get();

    // تجهيز البيانات للعرض داخل الـ PDF
    $data = [
        'person'               => $person,
        'withdrawals'          => $person->withdrawals,
        'absences'             => $person->absences()->with('addedBy')->get(),
        'absences_count'       => $person->absences()->count(),
        'debts'                => $debtOperations, // العمليات كاملة
        'remainingDebt'        => $remainingDebt,  // الرصيد النهائي
        'addedThisMonth'       => $addedThisMonth, // مجموع الإضافات
        'collectedThisMonth'   => abs($collectedThisMonth), // التحصيل الشهري (موجب)
        'creditSalesPending'   => $person->creditSales()->where('status', 'pending')->get(),
        'creditSalesCollected' => $person->creditSales()->where('status', 'deducted')->with('addedBy')->get(),
        'created_by'           => auth()->user(),
    ];

    // تسجيل عملية التصدير
    EmployeeLogService::add(
        $person,
        'report_exported',
        "تم تصدير تقرير PDF للموظف/المحاسب {$person->name}"
    );

    // إنشاء ملف PDF
    $pdf = PDF::loadView('pdf.employee-snappy', $data)
        ->setPaper('a4')
        ->setOption('encoding', 'UTF-8');

    return $pdf->download("تقرير - {$person->name}.pdf");
}

}
