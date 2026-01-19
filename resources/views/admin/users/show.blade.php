@extends('dashboard.app')

@section('content')

<div class="p-6"
     x-data="{
        tab: 'basic',
        openEditModal: false,
        editUser: {
            id: null,
            name: '',
            email: '',
            phone: '',
            role: '',
            status: '',
            subscription_end_date: '',
            expires_at: '',
            allowed_stores: '',
            allowed_accountants: ''
        }
     }">


    {{-- العنوان --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
            تفاصيل المستخدم
        </h1>

        <a href="{{ route('admin.users.index') }}"
           class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
            رجوع
        </a>
    </div>
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'تم بنجاح',
    text: '{{ session('success') }}',
    confirmButtonColor: '#3085d6'
});
</script>
@endif

    {{-- التبويبات --}}
    <div class="flex gap-3 border-b border-gray-200 dark:border-gray-700 mb-6">

        <button @click="tab = 'basic'"
                :class="tab === 'basic' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                class="pb-2 px-2 text-sm font-medium">
            البيانات الأساسية
        </button>

        <button @click="tab = 'status'"
                :class="tab === 'status' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                class="pb-2 px-2 text-sm font-medium">
            الحالة والصلاحيات
        </button>

        <button @click="tab = 'subscription'"
                :class="tab === 'subscription' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                class="pb-2 px-2 text-sm font-medium">
            الاشتراك
        </button>

        <button @click="tab = 'stores'"
                :class="tab === 'stores' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                class="pb-2 px-2 text-sm font-medium">
            المتاجر
        </button>

        <button @click="tab = 'accountants'"
                :class="tab === 'accountants' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                class="pb-2 px-2 text-sm font-medium">
            المحاسبون
        </button>

    </div>

    {{-- المحتوى --}}
    <div class="space-y-6">
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'تم بنجاح',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6'
        });
    </script>
@endif

{{-- تبويب: البيانات الأساسية --}}
<div x-show="tab === 'basic'" x-transition>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">البيانات الأساسية</h2>

            <div class="flex gap-2">

                {{-- زر تعديل --}}
                <button
                    @click="
                        editUser = {
                            id: {{ $user->id }},
                            name: '{{ $user->name }}',
                            email: '{{ $user->email }}',
                            phone: '{{ $user->phone }}',
                            role: '{{ $user->role }}',
                            status: '{{ $user->status }}',
                            subscription_end_date: '{{ $user->subscription_end_date }}',
                            expires_at: '{{ $user->expires_at }}',
                            allowed_stores: '{{ $user->allowed_stores }}',
                            allowed_accountants: '{{ $user->allowed_accountants }}'
                        };
                        openEditModal = true;
                    "
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    تعديل
                </button>

                {{-- زر تفعيل / إيقاف مع SweetAlert --}}
                <button
                    onclick="confirmToggle({{ $user->id }}, '{{ $user->status }}')"
                    class="px-4 py-2
                        @if($user->status === 'active')
                            bg-red-600 hover:bg-red-700
                        @else
                            bg-emerald-600 hover:bg-emerald-700
                        @endif
                        text-white rounded-lg transition text-sm">
                    @if($user->status === 'active')
                        إيقاف
                    @else
                        تفعيل
                    @endif
                </button>

            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

            <x-user-info label="الاسم" :value="$user->name" />
            <x-user-info label="البريد" :value="$user->email" />
            <x-user-info label="الهاتف" :value="$user->phone ?? '—'" />

        </div>

    </div>
    <form id="toggleForm-{{ $user->id }}" method="POST" action="{{ route('admin.users.toggle', $user->id) }}" class="hidden">
    @csrf
</form>

</div>


        {{-- تبويب: الحالة والصلاحيات --}}
        <div x-show="tab === 'status'" x-transition>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">الحالة والصلاحيات</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    {{-- الدور --}}
                    <div>
                        <span class="text-gray-500">الدور:</span>
                        <div class="mt-1">
                            @if($user->role === 'admin')
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs">مدير</span>
                            @elseif($user->role === 'accountant')
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">محاسب</span>
                            @else
                                <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">تاجر</span>
                            @endif
                        </div>
                    </div>

                    {{-- الحالة --}}
                    <div>
                        <span class="text-gray-500">الحالة:</span>
                        <div class="mt-1">
                            @if($user->status === 'active')
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs">نشط</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">موقوف</span>
                            @endif
                        </div>
                    </div>

                    <x-user-info label="آخر تسجيل دخول" :value="$user->last_login_at ?? '—'" />

                </div>

            </div>
        </div>

        {{-- تبويب: الاشتراك --}}
        @php
            $daysLeft = $user->subscription_end_date
                ? \Carbon\Carbon::now()->diffInDays($user->subscription_end_date, false)
                : null;

            $subColor = 'bg-gray-100 text-gray-700';

            if ($daysLeft !== null) {
                if ($daysLeft < 0) {
                    $subColor = 'bg-red-100 text-red-700';
                } elseif ($daysLeft <= 7) {
                    $subColor = 'bg-yellow-100 text-yellow-700';
                } else {
                    $subColor = 'bg-emerald-100 text-emerald-700';
                }
            }
        @endphp

        <div x-show="tab === 'subscription'" x-transition>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">الاشتراك</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    <div>
                        <span class="text-gray-500">تاريخ انتهاء الاشتراك:</span>
                        <div class="mt-1 px-3 py-1 rounded-lg inline-block {{ $subColor }}">
                            {{ $user->subscription_end_date ?? '—' }}
                        </div>
                    </div>

                    <x-user-info label="تاريخ انتهاء النظام" :value="$user->expires_at ?? '—'" />
                    <x-user-info label="الخطة" :value="$user->plan_id ?? '—'" />
                    <x-user-info label="عدد المتاجر المسموح" :value="$user->allowed_stores ?? '—'" />
                    <x-user-info label="عدد المحاسبين المسموح" :value="$user->allowed_accountants ?? '—'" />

                </div>

            </div>
        </div>

        {{-- تبويب: المتاجر --}}
        <div x-show="tab === 'stores'" x-transition>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">المتاجر</h2>

                @if($user->stores->count() > 0)
                    @foreach($user->stores as $store)
    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 flex justify-between items-center">

        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $store->name }}</h3>
            <p class="text-sm text-gray-500">الحالة:
                @if($store->status === 'active')
                    <span class="text-emerald-600">نشط</span>
                @else
                    <span class="text-red-600">موقوف</span>
                @endif
            </p>
        </div>

        <div class="flex gap-2">

            {{-- زر تعديل --}}
            <button
                @click="openStoreEdit({{ $store->id }}, '{{ $store->name }}', '{{ $store->accountant_id }}')"
                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                تعديل
            </button>

            {{-- زر تفعيل/إيقاف --}}
            <form method="POST" action="{{ route('stores.toggle', $store->id) }}">
                @csrf
                <button type="submit"
                        class="px-3 py-2
                        @if($store->status === 'active')
                            bg-red-600 hover:bg-red-700
                        @else
                            bg-emerald-600 hover:bg-emerald-700
                        @endif
                        text-white rounded-lg text-sm">
                    @if($store->status === 'active')
                        إيقاف
                    @else
                        تفعيل
                    @endif
                </button>
            </form>

        </div>

    </div>
@endforeach

                @else
                    <div class="text-gray-500 text-sm">لا يوجد متجر مرتبط بهذا المستخدم.</div>
                @endif

            </div>
        </div>

        {{-- تبويب: المحاسبون --}}
        <div x-show="tab === 'accountants'" x-transition>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">المحاسبون</h2>

                @if($user->accountants->count() > 0)
                    @foreach($user->accountants as $acc)
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg mb-3 bg-gray-50 dark:bg-gray-900/40">

                            <x-user-info label="اسم المحاسب" :value="$acc->name" />
                            <x-user-info label="البريد" :value="$acc->email" />

                            <div class="mt-2">
                                <span class="text-gray-500">الحالة:</span>
                                @if($acc->status === 'active')
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs">نشط</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">موقوف</span>
                                @endif
                            </div>

                        </div>
                    @endforeach
                @else
                    <div class="text-gray-500 text-sm">لا يوجد محاسبون مرتبطون بهذا المستخدم.</div>
                @endif

            </div>
        </div>

    </div>
 <div
    x-show="openStoreModal"
    class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
    x-transition.opacity>

    <div class="bg-white dark:bg-gray-900 w-full max-w-md rounded-xl shadow-lg p-6"
         @click.away="openStoreModal = false">

        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
            تعديل المتجر
        </h2>

        <form method="POST" :action="`/admin/stores/${storeEdit.id}/update`">
            @csrf

            {{-- اسم المتجر --}}
            <div class="mb-4">
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                    اسم المتجر
                </label>
                <input type="text" name="name" x-model="storeEdit.name"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700
                              bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200
                              px-3 py-2 focus:ring-0 focus:border-gray-500">
            </div>

            {{-- المحاسب --}}
            <div class="mb-6">
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                    المحاسب
                </label>
                <select name="accountant_id" x-model="storeEdit.accountant_id"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700
                               bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200
                               px-3 py-2 focus:ring-0 focus:border-gray-500">
                    <option value="">بدون محاسب</option>
                    @foreach($accountants as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- الأزرار --}}
            <div class="flex justify-end gap-3">
                <button type="button"
                        @click="openStoreModal = false"
                        class="px-4 py-2 rounded-lg border border-gray-300
                               text-gray-700 dark:text-gray-300 dark:border-gray-600
                               hover:bg-gray-100 dark:hover:bg-gray-800">
                    إلغاء
                </button>

                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-gray-800 text-white
                               dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">
                    حفظ
                </button>
            </div>

        </form>

    </div>
</div>


{{-- مودال تعديل المستخدم --}}
<div
    x-show="openEditModal"
    x-transition.opacity
    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white dark:bg-gray-800 w-full max-w-2xl rounded-2xl shadow-xl p-6 relative"
         @click.away="openEditModal = false">

        {{-- زر الإغلاق --}}
        <button @click="openEditModal = false"
                class="absolute top-3 left-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            ✕
        </button>

        {{-- العنوان --}}
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">
            تعديل بيانات المستخدم
        </h2>

        {{-- الفورم --}}
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- الاسم --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">الاسم</label>
                    <input type="text" name="name" x-model="editUser.name"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

                {{-- البريد --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">البريد الإلكتروني</label>
                    <input type="email" name="email" x-model="editUser.email"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

                {{-- الهاتف --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">الهاتف</label>
                    <input type="text" name="phone" x-model="editUser.phone"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

                {{-- الدور --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">الدور</label>
                    <select name="role" x-model="editUser.role"
                            class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">

                        <option value="accountant">محاسب</option>
                        <option value="merchant">تاجر</option>
                    </select>
                </div>

                {{-- تاريخ انتهاء الاشتراك --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">تاريخ انتهاء الاشتراك</label>
                    <input type="date" name="subscription_end_date" x-model="editUser.subscription_end_date"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

                {{-- تاريخ انتهاء النظام --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">تاريخ انتهاء النظام</label>
                    <input type="date" name="expires_at" x-model="editUser.expires_at"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

                {{-- عدد المتاجر --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">عدد المتاجر المسموح</label>
                    <input type="number" name="allowed_stores" x-model="editUser.allowed_stores"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

                {{-- عدد المحاسبين --}}
                <div>
                    <label class="text-gray-500 text-sm font-medium">عدد المحاسبين المسموح</label>
                    <input type="number" name="allowed_accountants" x-model="editUser.allowed_accountants"
                           class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:ring-blue-500">
                </div>

            </div>

            {{-- الأزرار --}}
            <div class="flex justify-end gap-3 mt-8">
                <button type="button"
                        @click="openEditModal = false"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    إلغاء
                </button>

                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    حفظ التعديلات
                </button>
            </div>

        </form>

    </div>
</div>

</div>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
        تسجيل الخروج
    </button>
</form>

@endsection
<script>
function confirmToggle(userId, status) {

    let actionText = status === 'active' ? 'إيقاف' : 'تفعيل';
    let actionColor = status === 'active' ? '#d33' : '#0f9d58';

    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: "سيتم " + actionText + " هذا المستخدم",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: actionColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم، ' + actionText,
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('toggleForm-' + userId).submit();
        }
    });
}
</script>
<div x-data="{
    openStoreModal: false,
    storeEdit: {
        id: null,
        name: '',
        accountant_id: ''
    },
    openStoreEdit(id, name, accountant_id) {
        this.storeEdit.id = id;
        this.storeEdit.name = name;
        this.storeEdit.accountant_id = accountant_id;
        this.openStoreModal = true;
    }
}">

