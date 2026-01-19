<?php

namespace App\Http\Controllers\Employees;

use App\Models\Store;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Employees\EmployeeTrash;
use App\Http\Controllers\Employees\EmployeeActions;
use App\Http\Controllers\Employees\EmployeeReports;
use App\Http\Controllers\Employees\EmployeeService;

/**
 * --------------------------------------------------------------------------
 * EmployeeController
 * --------------------------------------------------------------------------
 * هذا الكنترولر هو الواجهة الرئيسية لطلبات الموظفين.
 * لا يحتوي أي منطق داخلي، بل يقوم فقط بتحويل الطلبات إلى الخدمات المناسبة.
 * --------------------------------------------------------------------------
 */
class EmployeeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1) عرض قائمة الموظفين
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return EmployeeService::index();
    }

    /*
    |--------------------------------------------------------------------------
    | 2) عرض صفحة الموظف (العمليات)
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        return EmployeeActions::show($id);
    }

    /*
    |--------------------------------------------------------------------------
    | 3) ترقية موظف إلى محاسب
    |--------------------------------------------------------------------------
    */
    public function promote(Request $request, Employee $employee)
    {
        return EmployeeActions::promote($request, $employee);
    }

    /*
    |--------------------------------------------------------------------------
    | 4) فحص الإيميل قبل الترقية
    |--------------------------------------------------------------------------
    */
    public function checkEmail(Request $request)
    {
        return EmployeeActions::checkEmail($request);
    }

    /*
    |--------------------------------------------------------------------------
    | 5) سحب صلاحية المحاسب
    |--------------------------------------------------------------------------
    */
    public function demote(Employee $employee)
    {
        return EmployeeActions::demote($employee);
    }

    /*
    |--------------------------------------------------------------------------
    | 6) صفحة إنشاء موظف
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
{
    $userId = auth()->id();

    $store = null;

    if ($request->filled('store')) {
        $store = Store::where('id', $request->store)
            ->where('user_id', $userId) // نتأكد أن المتجر فعلاً يخص هذا المستخدم
            ->first();
    }

    // جميع متاجر هذا المستخدم فقط
    $stores = Store::where('user_id', $userId)
        ->orderBy('id')
        ->get();




    return view('employees.create', compact('store', 'stores'));
}


    /*
    |--------------------------------------------------------------------------
    | 7) حفظ موظف جديد
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, EmployeeService $service)
{
    $employee = $service->create($request->all());

    return redirect($request->return_to ?? route('user.employees.index'))
        ->with('success', 'تم إضافة الموظف بنجاح');
}


    /*
    |--------------------------------------------------------------------------
    | 8) صفحة تعديل موظف
    |--------------------------------------------------------------------------
    */
    public function edit(Employee $employee)
    {
        return EmployeeService::edit($employee);
    }

    /*
    |--------------------------------------------------------------------------
    | 9) تحديث بيانات موظف
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Employee $employee)
    {
        return EmployeeService::update($request, $employee);
    }

    /*
    |--------------------------------------------------------------------------
    | 10) حذف موظف (Soft Delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(Employee $employee)
{
    return EmployeeTrash::delete($employee, request());
}


    /*
    |--------------------------------------------------------------------------
    | 11) عرض سلة المحذوفات
    |--------------------------------------------------------------------------
    */
    public function trash()
    {
        return EmployeeTrash::list();
    }

    /*
    |--------------------------------------------------------------------------
    | 12) استرجاع موظف
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        return EmployeeTrash::restore($id);
    }

    /*
    |--------------------------------------------------------------------------
    | 13) حذف نهائي
    |--------------------------------------------------------------------------
    */
    public function forceDelete($id)
    {
        return EmployeeTrash::forceDelete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | 14) تصدير تقرير PDF
    |--------------------------------------------------------------------------
    */
    public function exportSnappy($id)
    {
        return EmployeeReports::exportSnappy($id);
    }
}
