<?php

namespace App\Http\Controllers\Store;

use App\Models\Expense;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EmployeeLogService;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * عرض المصروفات الخاصة بالمتجر
     */
public function index(Request $request)
{
    $user = auth('accountant')->user() ?? auth()->user();
    $storeId = $user->store_id;

    // الشهر المطلوب (افتراضي: الشهر الحالي)
    $month = $request->month ?? now()->month;
    $year  = $request->year  ?? now()->year;

    $expenses = Expense::where('store_id', $storeId)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->orderBy('created_at', 'desc')
        ->get();

    $total = $expenses->sum('amount');

    return view('accountants.pos.expense', compact('expenses', 'total', 'month', 'year'));
}




    /**
     * حفظ مصروف جديد
     */
public function store(Request $request)
{
    // المحاسب أو المستخدم
    $accountant = auth('accountant')->user();
    $user       = auth()->user();

    // الشخص الذي أضاف المصروف
    $addedBy = $accountant ? $accountant : $user;
    $addedByName = $addedBy->name;

    // صاحب المتجر (الهدف من الإشعار)
    $storeOwner = $accountant
        ? $accountant->store->user
        : $user;

    $storeId = $addedBy->store_id;

    $request->validate([
        'type' => 'required|string|max:255',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:500',
    ]);

    // إنشاء المصروف
    $expense = Expense::create([
        'store_id'    => $storeId,
        'user_id'     => $addedBy->id,
        'type'        => $request->type,
        'description' => $request->description,
        'amount'      => $request->amount,
        'actor_type'  => 'expense',
    ]);

    /*
    |--------------------------------------------------------------------------
    | 1) تسجيل لوق باستخدام النظام الخاص بك
    |--------------------------------------------------------------------------
    */
    EmployeeLogService::add(
        $addedBy,
        'expense_added',
        "قام {$addedByName} بإضافة مصروف بقيمة {$request->amount} ريال",
        $request->amount,
        'operation'
    );

    /*
    |--------------------------------------------------------------------------
    | 2) إرسال إشعار لصاحب المتجر
    |--------------------------------------------------------------------------
    */
    Notification::create([
        'sender_id'    => $addedBy->id,
        'sender_type'  => $accountant ? 'accountant' : 'user',
        'target_type'  => 'user',
        'target_ids'   => [$storeOwner->id],
        'title'        => 'مصروف جديد',
        'message'      => "قام {$addedByName} بإضافة مصروف بقيمة {$request->amount} ريال",
        'template_key' => 'expense_added',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم تسجيل المصروف بنجاح');
}




    /**
     * حذف مصروف (Soft Delete)
     */
    public function destroy($id)
{
    // المحاسب أو المستخدم
    $user = auth('accountant')->user() ?? auth('user')->user();

    if (!$user) {
        abort(403, 'غير مصرح بالدخول');
    }

    $storeId = $user->store_id;

    $expense = Expense::where('store_id', $storeId)->findOrFail($id);

    $expense->delete();

    return back()->with('success', 'تم حذف المصروف بنجاح');
}

public function update(Request $request, $id)
{
    $user = auth('user')->user();
    $storeId = $user->store_id;

    $expense = Expense::where('store_id', $storeId)->findOrFail($id);

    $request->validate([
        'type' => 'required|string|max:255',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:500',
    ]);

    $expense->update([
        'type' => $request->type,
        'amount' => $request->amount,
        'description' => $request->description,
    ]);

    return redirect()->route('user.pos.expense.page')
                     ->with('success', 'تم تعديل المصروف بنجاح');
}

}
