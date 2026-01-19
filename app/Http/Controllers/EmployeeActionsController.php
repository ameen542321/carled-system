<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Store;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\Accountant;
use App\Models\CreditSale;
use App\Models\Withdrawal;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\EmployeeLogService;
use Illuminate\Support\Facades\Auth;

class EmployeeActionsController extends Controller
{
    /**
     * عرض صفحة العمليات
     */
    public function index($id)
    {
        $person = $this->findPerson($id);
        $this->authorizePerson($person);

        return view('employees.actions', ['employee' => $person]);
    }

    /**
     * حفظ عملية السحب
     */
    public function storeWithdrawal(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'date' => 'required|date',
        'description' => 'nullable|string|max:255',
    ]);

    // ================================
    // 🔥 منع التكرار خلال يوم كامل
    // ================================
    $exists = $person->withdrawals()
        ->whereDate('date', $request->date)
        ->where('amount', $request->amount)
        ->where('description', $request->description)
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'duplicate' => 'لا يمكن تكرار نفس عملية السحب بنفس الوصف والقيمة في نفس اليوم.'
        ]);
    }

    // ================================
    // إنشاء عملية السحب
    // ================================
    $person->withdrawals()->create([
        'store_id'     => $person->store_id,
        'person_id'    => $person->id,
        'person_type'  => get_class($person),
        'amount'       => $request->amount,
        'description'  => $request->description,
        'date'         => $request->date,
        'status'       => 'pending',
        'month'        => date('Y-m'),
        'added_by'     => Auth::id(),
    ]);

    EmployeeLogService::add(
        $person,
        'withdrawal',
        "سحب مبلغ {$request->amount} ريال",
        $request->amount,
        'operation'
    );

    return back()->with('success', 'تم إضافة السحب بنجاح');
}


    /**
     * حفظ عملية الغياب
     */
    public function storeAbsence(Request $request, $id)
    {
        $person = $this->findPerson($id);
        $this->authorizePerson($person);

        $request->validate([
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        if ($person->absences()->where('date', $request->date)->exists()) {
            return back()->with('error', 'تم تسجيل غياب لهذا المستخدم في هذا التاريخ مسبقًا');
        }

        $person->absences()->create([
            'store_id'     => $person->store_id,
            'person_id'    => $person->id,
            'person_type'  => get_class($person),
            'date'         => $request->date,
            'description'  => $request->description,
            'status'       => 'pending',
            'month'        => date('Y-m'),
            'added_by'     => Auth::id(),
        ]);

        EmployeeLogService::add(
            $person,
            'absence',
            "تسجيل غياب بتاريخ {$request->date}",
            null,
            'operation'
        );

        // 🔥 إشعار تلقائي لصاحب المتجر عند تسجيل غياب
        \App\Services\NotificationService::sendTemplate('absence_recorded', [
            'sender_type' => 'CARLED',
            'target_type' => 'store',
            'target_ids'  => [$person->store_id],
        ]);

        return back()->with('success', 'تم تسجيل الغياب بنجاح');
    }

    /**
     * حفظ المديونية
     */
public function storeDebt(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
    ]);

    // منع تكرار نفس المديونية في نفس اليوم
    $exists = $person->debts()
        ->whereDate('date', $request->date)
        ->where('amount', $request->amount)
        ->where('description', $request->description)
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'duplicate' => 'لا يمكن تكرار نفس المديونية بنفس الوصف والقيمة في نفس اليوم.'
        ]);
    }

    $debt = $person->debts()->create([
        'store_id'     => $person->store_id,
        'amount'       => $request->amount,
        'description'  => $request->description,
        'date'         => $request->date,
        'type'         => 'normal',
        'status'       => 'pending',
        'month'        => date('Y-m', strtotime($request->date)),
        'added_by'     => auth()->id(),
    ]);

    EmployeeLogService::add(
        $person,
        'debt_add',
        "إضافة مديونية بقيمة {$request->amount} ريال",
        $debt->id,
        'operation'
    );

    Notification::create([
        'sender_id'    => auth()->id(),
        'sender_type'  => 'user',
        'target_type'  => 'user',
        'target_ids'   => [$person->id],
        'title'        => 'إضافة مديونية',
        'message'      => "تم إضافة مديونية بقيمة {$request->amount} ريال.",
        'template_key' => 'debt_add',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم إضافة المديونية بنجاح');
}


public function collectPartial($debtId, $amount)
{
    $debt = Debt::findOrFail($debtId);
    $person = $debt->person;
    $user = Auth::user();


    if ($amount <= 0 || $amount > $debt->amount) {
        return back()->with('error', 'مبلغ التحصيل غير صالح.');
    }

    $newAmount = $debt->amount - $amount;

    $debt->update([
        'amount' => $newAmount,
        'status' => $newAmount == 0 ? 'cleared' : 'pending'
    ]);

    EmployeeLogService::add(
        $person,
        'debt_collect_partial',
        "تحصيل جزئي بقيمة {$amount} ريال",
        $debt->id,
        'operation'
    );

    Notification::create([
        'sender_id'    => $user->id,
        'sender_type'  => 'user',
        'target_type'  => 'user',
        'target_ids'   => [$person->id],
        'title'        => 'تحصيل جزئي',
        'message'      => "تم تحصيل مبلغ {$amount} ريال من مديونيتك. المتبقي الآن {$newAmount} ريال.",
        'template_key' => 'debt_collect_partial',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم التحصيل الجزئي بنجاح');
}

// دالة إنشاء بيع آجل جديد
public function storeCreditSale(Request $request, $employeeId)
{
    $person = $this->findPerson($employeeId);
    $this->authorizePerson($person);

    $validated = $request->validate([
        'amount'      => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
    ]);

    $sale = CreditSale::create([
        'person_id'        => $person->id,
        'person_type'      => get_class($person),
        'store_id'         => $person->store_id,
        'amount'           => $validated['amount'],
        'remaining_amount' => $validated['amount'],
        'description'      => $validated['description'] ?? null,
        'date'             => $validated['date'],
        'status'           => 'pending',
        'month'            => date('Y-m'),
        'added_by'         => auth()->id(),
        'partial_payments' => [],
    ]);

    EmployeeLogService::add(
        $person,
        'credit_sale_created',
        "إضافة بيع آجل بقيمة {$sale->amount} ريال",
        $sale->amount,
        'operation'
    );

    return back()->with('success', 'تم إنشاء عملية بيع آجل بنجاح');
}
// دالة التحصيل الكامل
public function collectCreditSale($employeeId, CreditSale $sale)
{
    $person = $this->findPerson($employeeId);
    $this->authorizePerson($person);

    if ($sale->person_id !== $person->id || $sale->person_type !== get_class($person)) {
        abort(403, 'غير مسموح');
    }

    $sale->remaining_amount = 0;
    $sale->status = 'deducted';
    $sale->deducted_month = date('Y-m');
    $sale->partial_payments = [];

    $sale->save();

    EmployeeLogService::add(
        $person,
        'credit_sale_deducted',
        "تحصيل بيع آجل بقيمة {$sale->amount} ريال",
        $sale->amount,
        'operation'
    );

   Notification::create([
    'sender_id'    => auth()->id(),
    'sender_type'  => 'system',
    'target_type'  => 'store',
    'target_ids'   => [$person->store_id],
    'title'        => 'تحصيل كامل',
    'message'      => "تم تحصيل مبلغ {$sale->amount} ريال بالكامل.",
    'template_key' => 'due_collected',
    'channel'      => 'CARLED',
]);


    return back()->with('success', 'تم التحصيل الكامل بنجاح');
}


public function collectFull($debtId)
{
    $debt = Debt::findOrFail($debtId);
    $person = $debt->person;
    $user = Auth::user();

    if ($debt->amount <= 0) {
        return back()->with('error', 'لا توجد مديونية لتسديدها.');
    }

    $amount = $debt->amount;

    $debt->update([
        'amount' => 0,

    ]);

    EmployeeLogService::add(
        $person,
        'debt_collect_full',
        "تحصيل كامل بقيمة {$amount} ريال",
        $debt->id,
        'operation'
    );

    Notification::create([
        'sender_id'    => $user->id,
        'sender_type'  => 'user',
        'target_type'  => 'user',
        'target_ids'   => [$person->id],
        'title'        => 'تحصيل كامل',
        'message'      => "تم تسديد كامل مديونيتك بقيمة {$amount} ريال.",
        'template_key' => 'debt_collect_full',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم التحصيل الكامل بنجاح');
}

public function collectDebt(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
        'mode'        => 'required|in:partial,full',
    ]);

    $month = date('Y-m', strtotime($request->date));
    $currentBalance = $person->debts()->sum('amount');

    if ($currentBalance <= 0) {
        return back()->withErrors(['amount' => 'لا توجد مديونية حالية على هذا الموظف.']);
    }

    // ================================
    // 🔥 منع التكرار خلال يوم كامل
    // ================================
    $exists = $person->debts()
        ->whereDate('date', $request->date)
        ->where('amount', -($request->mode === 'full' ? $currentBalance : min($request->amount, $currentBalance)))
        ->where('description', $request->description ?: 'تحصيل مديونية')
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'duplicate' => 'لا يمكن تكرار نفس عملية التحصيل بنفس الوصف والقيمة في نفس اليوم.'
        ]);
    }

    // ================================
    // حساب مبلغ التحصيل
    // ================================
    $collectAmount = $request->mode === 'full'
        ? $currentBalance
        : min($request->amount, $currentBalance);

    $person->debts()->create([
        'store_id'     => $person->store_id,
        'amount'       => -$collectAmount,
        'description'  => $request->description ?: 'تحصيل مديونية',
        'date'         => $request->date,
        'type'         => 'normal',
        'status'       => 'pending',
        'month'        => $month,
        'added_by'     => auth()->id(),
    ]);

    EmployeeLogService::add(
        $person,
        'debt_collect',
        "تحصيل مديونية بقيمة {$collectAmount} ريال"
    );

    // 🔥 إشعار داخلي
    $message = $request->mode === 'full'
        ? "قام المحاسب بتحصيل كامل مديونية الموظف {$person->name} بقيمة {$collectAmount} ريال"
        : "قام المحاسب بتحصيل مبلغ جزئي بقيمة {$collectAmount} ريال من مديونية الموظف {$person->name}";

    Notification::create([
        'sender_id'    => auth()->id(),
        'sender_type'  => 'user',
        'target_type'  => 'user',
        'target_ids'   => [$person->store->user->id],
        'title'        => 'تحصيل مديونية',
        'message'      => $message,
        'template_key' => 'debt_collect',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم تحصيل المديونية بنجاح');
}

public function collectPartialCreditSale($employeeId, CreditSale $sale, $amount)
{
    $person = $this->findPerson($employeeId);
    $this->authorizePerson($person);

    if ($sale->person_id !== $person->id || $sale->person_type !== get_class($person)) {
        abort(403, 'غير مسموح');
    }

    if ($amount <= 0 || $amount > $sale->remaining_amount) {
        return back()->with('error', 'مبلغ التحصيل غير صالح.');
    }

    // خصم من المتبقي
    $sale->remaining_amount -= $amount;

    // إضافة سجل JSON
    $payments = $sale->partial_payments ?? [];

    $payments[] = [
        'amount' => $amount,
        'date'   => now()->toDateString(),
    ];

    $sale->partial_payments = $payments;

    // إذا انتهى السداد → إغلاق العملية
    if ($sale->remaining_amount == 0) {
        $sale->status = 'deducted';
        $sale->deducted_month = date('Y-m');
    }

    $sale->save();

    // لوج
    EmployeeLogService::add(
        $person,
        'credit_sale_partial',
        "تحصيل جزئي من بيع آجل بقيمة {$amount} ريال",
        $amount,
        'operation'
    );

    // إشعار
    Notification::create([
        'sender_id'    => auth()->id(),
        'sender_type'  => 'system',
        'target_type'  => 'store',
        'target_ids'   => [$person->store_id],
        'title'        => 'تحصيل جزئي',
        'message'      => "تم تحصيل مبلغ {$amount} ريال. المتبقي الآن {$sale->remaining_amount} ريال.",
        'template_key' => 'due_collected_partial',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم التحصيل الجزئي بنجاح');
}


    /**
     * صفحة السجل
     */
    public function logs($id)
    {
        $person = $this->findPerson($id);
        $this->authorizePerson($person);

        $logs = $person->logs()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employees.logs', [
            'employee' => $person,
            'logs'     => $logs,
        ]);
    }

    /**
     * إيجاد موظف أو محاسب
     */
    private function findPerson($id)
    {
        return Employee::find($id) ?? Accountant::findOrFail($id);
    }

    /**
     * حماية المستخدم حسب المتجر
     */
   private function authorizePerson($person)
{
    $user = auth()->user();
/** @var \App\Models\User $user */
    // المالك: يجب أن يكون المتجر تابعاً له حصراً
    if (auth('web')->check() && $user->role === 'user') {
        if (!$user->stores()->where('id', $person->store_id)->exists()) {
            abort(403, 'هذا الموظف لا ينتمي لمتاجرك');
        }
        return;
    }

    // المحاسب: يجب أن يكون في نفس المتجر
    if (auth('accountant')->check()) {
        if ($person->store_id !== auth('accountant')->user()->store_id) {
            abort(403, 'لا يمكنك إدارة موظفين خارج متجرك');
        }
        return;
    }

    // الأدمن له صلاحية كاملة تلقائياً
    if ($user && $user->role === 'admin') return;

    abort(403, 'غير مسموح');
}
}
