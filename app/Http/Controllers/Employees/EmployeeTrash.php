<?php

namespace App\Http\Controllers\Employees;

use App\Models\Store;
use App\Models\Employee;
use App\Services\EmployeeLogService;

/**
 * --------------------------------------------------------------------------
 * EmployeeTrash
 * --------------------------------------------------------------------------
 * مسؤول عن:
 * - حذف الموظف (Soft Delete)
 * - عرض سلة المحذوفات
 * - استرجاع الموظف
 * - الحذف النهائي (Force Delete)
 * --------------------------------------------------------------------------
 */
class EmployeeTrash
{
    /**
     * ----------------------------------------------------------------------
     * حذف موظف (Soft Delete)
     * ----------------------------------------------------------------------
     */
    public static function delete(Employee $employee, $request)
{
    // منع المحاسب
    if (auth('accountant')->check()) {
        abort(403);
    }

    $user = auth()->user();

    if ($user->role === 'user') {
        $storeIds = $user->stores->pluck('id')->toArray();

        if (!in_array($employee->store_id, $storeIds)) {
            abort(403);
        }
    }

    // تنفيذ الحذف
    $employee->delete();

    // تسجيل العملية
    EmployeeLogService::add(
        $employee,
        'employee_deleted',
        "تم حذف الموظف {$employee->name}"
    );

    // 🔥 هنا return_to الحقيقي
    if ($request->query('return_to')) {
        return redirect($request->query('return_to'))
            ->with('success', 'تم حذف الموظف');
    }

    return redirect()
        ->route('user.employees.index')
        ->with('success', 'تم حذف الموظف');
}



    /**
     * ----------------------------------------------------------------------
     * عرض سلة المحذوفات
     * ----------------------------------------------------------------------
     */
    public static function list()
    {
        $user = auth()->user();

        // منع المحاسب
        if ($user->role === 'accountant') {
            abort(403);
        }

        // المدير يرى كل المتاجر – المستخدم يرى متاجره فقط
        $storeIds = $user->role === 'admin'
            ? Store::pluck('id')->toArray()
            : $user->stores->pluck('id')->toArray();

        $employees = Employee::onlyTrashed()
            ->whereIn('store_id', $storeIds)
            ->paginate(20);

        return view('employees.trash', compact('employees'));
    }

    /**
     * ----------------------------------------------------------------------
     * استرجاع موظف محذوف
     * ----------------------------------------------------------------------
     */
    public static function restore($id)
    {
        $user = auth()->user();

        $storeIds = $user->role === 'admin'
            ? Store::pluck('id')
            : $user->stores->pluck('id');

        $employee = Employee::onlyTrashed()
            ->whereIn('store_id', $storeIds)
            ->findOrFail($id);

        $employee->restore();

        // تسجيل العملية
        EmployeeLogService::add(
            $employee,
            'employee_restored',
            "تم استعادة الموظف {$employee->name}"
        );

        return back()->with('success', 'تم استرجاع الموظف');
    }

    /**
     * ----------------------------------------------------------------------
     * حذف نهائي (Force Delete)
     * ----------------------------------------------------------------------
     */
    public static function forceDelete($id)
    {
        $user = auth()->user();

        // منع المحاسب
        if ($user->role === 'accountant') {
            abort(403);
        }

        // السماح فقط للمدير والمستخدم
        if (!in_array($user->role, ['admin', 'user'])) {
            abort(403);
        }

        $storeIds = $user->role === 'admin'
            ? Store::pluck('id')->toArray()
            : $user->stores->pluck('id')->toArray();

        $employee = Employee::onlyTrashed()
            ->whereIn('store_id', $storeIds)
            ->where('id', $id)
            ->firstOrFail();

        // تسجيل العملية
        EmployeeLogService::add(
            $employee,
            'employee_force_deleted',
            "تم حذف الموظف {$employee->name} نهائيًا"
        );

        // تنفيذ الحذف النهائي
        $employee->forceDelete();

        return redirect()
            ->route('user.employees.trash')
            ->with('success', 'تم حذف الموظف نهائيًا');
    }
}
