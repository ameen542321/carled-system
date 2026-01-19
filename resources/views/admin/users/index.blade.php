
@extends('dashboard.app')
@section('content')

<div class="p-6"
     x-data="{
        openAddModal: false,
        openEditModal: false,
        openRow: null,
        editUser: {}
     }">

    {{-- تنبيه نجاح --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-100 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
            المستخدمون
        </h1>

        <button @click="openAddModal = true"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition">
            إضافة مستخدم
        </button>
    </div>

    {{-- البحث + الفلترة --}}
    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">

        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="ابحث عن مستخدم..."
               class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">

        <select name="role"
                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="all">كل الأدوار</option>
            <option value="admin" {{ request('role')=='admin' ? 'selected' : '' }}>مدير</option>
            <option value="accountant" {{ request('role')=='accountant' ? 'selected' : '' }}>محاسب</option>
            <option value="user" {{ request('role')=='user' ? 'selected' : '' }}>تاجر</option>
        </select>

        <select name="status"
                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="all">كل الحالات</option>
            <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>نشط</option>
            <option value="suspended" {{ request('status')=='suspended' ? 'selected' : '' }}>موقوف</option>
        </select>

        <button class="px-4 py-2 bg-gray-800 text-white rounded-lg">
            تطبيق الفلترة
        </button>

    </form>
 @guest
        <!-- المستخدم غير مسجّل دخول -->
        <a href="/">العودة للصفحة الرئيسية</a>
    @endguest

    @auth
        <!-- المستخدم مسجّل دخول -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">تسجيل خروج والعودة للصفحة الرئيسية</button>
        </form>
    @endauth
    {{-- الجدول --}}
    <div x-data="{ openRow: null }"
         class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">

        <table class="w-full text-right text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                <tr>
                    <th class="p-3 w-16">#</th>
                    <th class="p-3">الاسم</th>
                    <th class="p-3 hidden md:table-cell">البريد</th>
                    <th class="p-3 hidden md:table-cell">الدور</th>
                    <th class="p-3 hidden md:table-cell">الحالة</th>
                    <th class="p-3 hidden md:table-cell">انتهاء الاشتراك</th>
                    <th class="p-3 text-center md:hidden">+</th>
                </tr>
            </thead>

            <tbody class="text-gray-800 dark:text-gray-100">

                @foreach($users as $user)

                    @php
                        $daysLeft = $user->subscription_ends_at
                            ? \Carbon\Carbon::now()->diffInDays($user->subscription_ends_at, false)
                            : null;

                        $subColor = 'bg-gray-100 text-gray-700';

                        if ($daysLeft !== null) {
                            if ($daysLeft < 0) {
                                $subColor = 'bg-red-100 text-red-700'; // منتهي
                            } elseif ($daysLeft <= 7) {
                                $subColor = 'bg-yellow-100 text-yellow-700'; // أقل من 7 أيام
                            } else {
                                $subColor = 'bg-emerald-100 text-emerald-700'; // نشط
                            }
                        }
                    @endphp

                    {{-- الصف الرئيسي --}}
                    <tr :class="openRow === {{ $user->id }} ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
                        class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">

                        <td class="p-3">{{ $user->id }}</td>

                        <td class="p-3 font-medium">
                            {{ $user->name }}

                            <div class="md:hidden mt-1 text-xs text-gray-500">
                                {{ $user->email }}
                            </div>

                            <div class="md:hidden text-[11px] text-gray-400">
                                @if($user->role === 'admin')
                                    مدير
                                @elseif($user->role === 'accountant')
                                    محاسب
                                @else
                                    تاجر
                                @endif
                                •
                                @if($user->status === 'active')
                                    نشط
                                @else
                                    موقوف
                                @endif
                            </div>
                        </td>

                        <td class="p-3 hidden md:table-cell">{{ $user->email }}</td>

                        {{-- الدور --}}
                        <td class="p-3 hidden md:table-cell">
                            @if($user->role === 'admin')
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs">مدير</span>
                            @elseif($user->role === 'accountant')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">محاسب</span>
                            @else
                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">تاجر</span>
                            @endif
                        </td>

                        {{-- الحالة --}}
                        <td class="p-3 hidden md:table-cell">
                            @if($user->status === 'active')
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs">نشط</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">موقوف</span>
                            @endif
                        </td>

                        {{-- تاريخ انتهاء الاشتراك --}}
                        <td class="p-3 hidden md:table-cell">
                            @if($user->subscription_ends_at)
                                <span class="px-2 py-1 rounded-lg text-xs {{ $subColor }}">
                                    {{ \Carbon\Carbon::parse($user->subscription_ends_at)->format('Y-m-d') }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- زر + --}}
                        <td class="p-3 text-center md:hidden">
                            <button type="button"
                                @click="openRow = openRow === {{ $user->id }} ? null : {{ $user->id }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                <span x-show="openRow !== {{ $user->id }}" x-cloak>+</span>
                                <span x-show="openRow === {{ $user->id }}" x-cloak>−</span>
                            </button>
                        </td>

                    </tr>

                    {{-- التفاصيل للجوال --}}
                    <tr x-show="openRow === {{ $user->id }}" x-cloak x-transition.duration.200ms class="md:hidden bg-gray-50 dark:bg-gray-900/40">
                        <td colspan="7" class="p-4 space-y-3 text-xs">

                            <div class="flex justify-between">
                                <span class="text-gray-500">البريد:</span>
                                <span class="font-medium">{{ $user->email }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500">الدور:</span>
                                <span class="font-medium">
                                    @if($user->role === 'admin')
                                        مدير
                                    @elseif($user->role === 'accountant')
                                        محاسب
                                    @else
                                        تاجر
                                    @endif
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500">الحالة:</span>
                                <span class="font-medium">
                                    @if($user->status === 'active')
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full">نشط</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full">موقوف</span>
                                    @endif
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500">انتهاء الاشتراك:</span>
                                <span class="font-medium {{ $subColor }} px-2 py-1 rounded">
                                    @if($user->subscription_ends_at)
                                        {{ \Carbon\Carbon::parse($user->subscription_ends_at)->format('Y-m-d') }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>

                            <div class="pt-3 flex flex-wrap gap-2">

                                <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] hover:bg-blue-700 transition">
                                    عرض
                                </a>

                                <button type="button"
                                    @click="
                                        editUser = {
                                            id: {{ $user->id }},
                                            name: '{{ $user->name }}',
                                            email: '{{ $user->email }}',
                                            role: '{{ $user->role }}',
                                            status: '{{ $user->status }}',
                                            subscription_ends_at: '{{ $user->subscription_ends_at }}'
                                        };
                                        openEditModal = true;
                                    "
                                    class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-[11px] hover:bg-yellow-600 transition">
                                    تعديل
                                </button>

                            </div>

                        </td>
                    </tr>

                @endforeach

            </tbody>
        </table>

    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->onEachSide(1)->links('pagination::tailwind') }}
    </div>

    {{-- مودال إضافة مستخدم --}}
    <div x-show="openAddModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div @click.away="openAddModal = false"
             class="bg-white dark:bg-gray-800 w-full max-w-lg p-6 rounded-xl shadow-xl">

            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">
                إضافة مستخدم جديد
            </h3>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">الاسم</label>
                    <input type="text" name="name"
                           class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">البريد</label>
                    <input type="email" name="email"
                           class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">كلمة المرور</label>
                    <input type="password" name="password"
                           class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="openAddModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        إلغاء
                    </button>

                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        إضافة
                    </button>
                </div>

            </form>

        </div>
    </div>

    {{-- مودال تعديل مستخدم --}}
    <div x-show="openEditModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div @click.away="openEditModal = false"
             class="bg-white dark:bg-gray-800 w-full max-w-lg p-6 rounded-xl shadow-xl">

            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">
                تعديل المستخدم
            </h3>

             <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf


                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">الاسم</label>
                    <input type="text" name="name" x-model="editUser.name"
                           class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">البريد</label>
                    <input type="email" name="email" x-model="editUser.email"
                           class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">الدور</label>
                    <select name="role" x-model="editUser.role"
                            class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                        <option value="accountant">محاسب</option>
                        <option value="user">تاجر</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">الحالة</label>
                    <select name="status" x-model="editUser.status"
                            class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="active">نشط</option>
                        <option value="suspended">موقوف</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 dark:text-gray-400">انتهاء الاشتراك</label>
                    <input type="date" name="subscription_ends_at" x-model="editUser.subscription_ends_at"
                           class="w-full mt-1 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="openEditModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        إلغاء
                    </button>

                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        حفظ التعديلات
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
