<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Accountant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
/**
 * Class UserController
 *
 * ✅ كنترولر إدارة المستخدمين (لوحة المدير فقط)
 * ✅ مسؤول عن CRUD للمستخدمين:
 *    - index   → عرض قائمة المستخدمين
 *    - create  → عرض فورم إضافة مستخدم
 *    - store   → حفظ مستخدم جديد
 *    - edit    → عرض فورم تعديل مستخدم
 *    - update  → تحديث بيانات مستخدم
 *    - destroy → حذف مستخدم
 */
class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index → عرض قائمة المستخدمين
    |--------------------------------------------------------------------------
    */

   public function index(Request $request)
{
    $query = User::query();

    // البحث
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%");
        });
    }

    // فلترة الدور
    if ($request->role && $request->role !== 'all') {
        $query->where('role', $request->role);
    }

    // فلترة الحالة
    if ($request->status && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    $users = $query->orderBy('id', 'desc')->paginate(15);

    return view('admin.users.index', compact('users'));
}


    /*
    |--------------------------------------------------------------------------
    | create → عرض فورم إضافة مستخدم جديد
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('admin.users.create');
    }

    /*
    |--------------------------------------------------------------------------
    | store → حفظ مستخدم جديد
    |--------------------------------------------------------------------------
    */
public function store(Request $request)
{
    $data = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
    ]);

    // تحديد القيم الافتراضية
    $data['password'] = bcrypt($data['password']);
    $data['role'] = 'user';
    $data['status'] = 'active';

    // إنشاء المستخدم
    $user = User::create($data);

    // إنشاء متجر افتراضي
    $user->stores()->create([
        'name' => 'متجري',
        'status' => 'active',
    ]);

    return back()->with('success', 'تم إضافة المستخدم وإنشاء متجره الافتراضي بنجاح');
}


    /*
    |--------------------------------------------------------------------------
    | edit → عرض فورم تعديل مستخدم
    |--------------------------------------------------------------------------
    */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /*
    |--------------------------------------------------------------------------
    | update → تحديث بيانات المستخدم
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email,' . $user->id,

        'phone' => 'nullable|string',
        'role' => 'required|string',
        'subscription_end_at' => 'nullable|date',
        'expires_at' => 'nullable|date',
        'allowed_stores' => 'nullable|integer',
        'allowed_accountants' => 'nullable|integer',
    ]);

    $user->update($data);

    return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح');
}

public function show(User $user)
{
    // جلب المحاسبين فقط
   $accountants = Accountant::where('user_id', $user->id)->get();

    // جلب المتاجر التابعة للمستخدم
    $stores = $user->stores;

    return view('admin.users.show', compact('user', 'stores', 'accountants'));
}


    /*
    |--------------------------------------------------------------------------
    | destroy → حذف مستخدم
    |--------------------------------------------------------------------------
    */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم');
    }

   public function toggleStatus(User $user)
{
    $user->status = $user->status === 'active' ? 'suspended' : 'active';
    $user->save();

    return back()->with('success', 'تم تحديث حالة المستخدم بنجاح');
}


}
