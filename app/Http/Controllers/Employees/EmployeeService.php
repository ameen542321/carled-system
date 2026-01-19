<?php

namespace App\Http\Controllers\Employees;

use App\Models\Store;
use App\Models\Employee;
use App\Models\Accountant;
use Illuminate\Http\Request;
use App\Services\EmployeeLogService;

/**
 * --------------------------------------------------------------------------
 * EmployeeService
 * --------------------------------------------------------------------------
 * هذا الملف مسؤول عن CRUD الأساسي للموظفين
 * --------------------------------------------------------------------------
 */
class EmployeeService
{
    /**
     * ----------------------------------------------------------------------
     * عرض قائمة الموظفين
     * ----------------------------------------------------------------------
     */
    public static function index()
{
    $user = auth()->user();

    // تحديد المتاجر حسب نوع المستخدم
    if ($user->role === 'admin') {
        $storeIds = Store::pluck('id');
    } elseif ($user->role === 'user') {
        $storeIds = $user->stores->pluck('id');
    } elseif (auth('accountant')->check()) {
        $storeIds = [auth('accountant')->user()->store_id];
    } else {
        abort(403);
    }

    // جلب IDs الموظفين المرتبطين بمحاسبين فعالين
    $activeAccountantEmployeeIds = Accountant::whereIn('store_id', $storeIds)
        ->where('status', 'active')
        ->pluck('employee_id')
        ->filter() // إزالة null
        ->toArray();

    // جلب الموظفين مع استبعاد موظفي المحاسبين الفعالين
    $employees = Employee::whereIn('store_id', $storeIds)
        ->whereNotIn('id', $activeAccountantEmployeeIds)
        ->get();

    // جلب المحاسبين غير الفعالين فقط
    $accountants = Accountant::whereIn('store_id', $storeIds)
        ->where('status', '!=', 'active')
        ->get();

    return view('employees.index', compact('employees', 'accountants'));
}




    /**
     * ----------------------------------------------------------------------
     * صفحة إنشاء موظف
     * ----------------------------------------------------------------------
     */
    public function create(array $data)
    {
        return Employee::create($data);
         }

    /**
     * ----------------------------------------------------------------------
     * حفظ موظف جديد
     * ----------------------------------------------------------------------
     */
    public static function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'salary'   => 'required|numeric|min:0',
            'date'     => 'nullable|date',
        ]);

        $user = auth()->user();

        $employee = Employee::create([
            'user_id'  => $user->id,
            'store_id' => $request->store_id,
            'name'     => $request->name,
            'phone'    => $request->phone,
            'salary'   => $request->salary,
            'added_by' => $user->id,
            'date'     => $request->date,
        ]);

        // سجل إنشاء الموظف
        EmployeeLogService::add(
            $employee,
            'employee_created',
            "تم إنشاء الموظف {$employee->name}"
        );

        return redirect()
            ->route('user.employees.index')
            ->with('success', 'تم إضافة العامل بنجاح');
    }

    /**
     * ----------------------------------------------------------------------
     * صفحة تعديل موظف
     * ----------------------------------------------------------------------
     */
   public static function edit(Employee $employee)
{
    // منع المحاسب من الوصول
    if (auth('accountant')->check()) {
        abort(403);
    }

    // السماح للمستخدم أو الأدمن فقط
    if (!auth('web')->check() && !auth('admin')->check()) {
        abort(403);
    }

    // جلب المستخدم الصحيح حسب الـ Guard
    $user = auth('admin')->check()
        ? auth('admin')->user()
        : auth('web')->user();

    // جلب المتاجر حسب نوع المستخدم
    $stores = $user->is_admin ? Store::all() : $user->stores;

    return view('employees.edit', compact('employee', 'stores'));
}



    /**
     * ----------------------------------------------------------------------
     * تحديث بيانات موظف
     * ----------------------------------------------------------------------
     */
    public static function update(Request $request, Employee $employee)
    {
        if (auth('accountant')->check()) {
            abort(403);
        }

        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'salary'   => 'required|numeric|min:0',
        ]);

        $oldStoreId = $employee->store_id;
        $oldSalary  = $employee->salary;

        $employee->update($request->only('store_id', 'name', 'phone', 'salary'));

        // سجل تعديل البيانات
        EmployeeLogService::add(
            $employee,
            'employee_updated',
            "تم تعديل بيانات الموظف {$employee->name}"
        );

        // سجل نقل المتجر
        if ($oldStoreId != $employee->store_id) {
            $oldStore = Store::find($oldStoreId);
            $newStore = $employee->store;

            if ($oldStore && $newStore) {
                EmployeeLogService::add(
                    $employee,
                    'store_transfer',
                    "نقل الموظف من متجر {$oldStore->name} إلى متجر {$newStore->name}"
                );
            }
        }

        // سجل تعديل الراتب
        if ($oldSalary != $employee->salary) {
            EmployeeLogService::add(
                $employee,
                'salary_update',
                "تعديل الراتب من {$oldSalary} إلى {$employee->salary} ريال"
            );
        }

        return redirect()
            ->route('user.employees.index')
            ->with('success', 'تم تحديث بيانات العامل بنجاح');
    }
}
