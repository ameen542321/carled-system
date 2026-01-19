@extends('dashboard.app')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">لوحة الأدمن</h1>
    <p class="text-gray-400 text-sm mt-1">نظرة عامة على النظام</p>
</div>

{{-- البطاقات --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    {{-- عدد المستخدمين --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عدد المستخدمين</p>
                <h3 class="text-2xl font-bold text-white mt-1">120</h3>
            </div>
            <div class="bg-blue-600/20 text-blue-400 p-3 rounded-lg">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
        </div>
    </div>

    {{-- عدد المحاسبين --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عدد المحاسبين</p>
                <h3 class="text-2xl font-bold text-white mt-1">34</h3>
            </div>
            <div class="bg-green-600/20 text-green-400 p-3 rounded-lg">
                <i class="fa-solid fa-user-tie text-xl"></i>
            </div>
        </div>
    </div>

    {{-- عدد المتاجر --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عدد المتاجر</p>
                <h3 class="text-2xl font-bold text-white mt-1">58</h3>
            </div>
            <div class="bg-yellow-600/20 text-yellow-400 p-3 rounded-lg">
                <i class="fa-solid fa-store text-xl"></i>
            </div>
        </div>
    </div>

    {{-- عدد الإشعارات --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">الإشعارات الجديدة</p>
                <h3 class="text-2xl font-bold text-white mt-1">12</h3>
            </div>
            <div class="bg-purple-600/20 text-purple-400 p-3 rounded-lg">
                <i class="fa-solid fa-bell text-xl"></i>
            </div>
        </div>
    </div>

</div>

{{-- آخر الأنشطة --}}
<div class="mt-10 bg-gray-800 border border-gray-700 rounded-xl p-6">
    <h2 class="text-xl font-semibold text-white mb-4">آخر الأنشطة</h2>

    <ul class="space-y-3">
        <li class="flex items-center justify-between border-b border-gray-700 pb-3">
            <span class="text-gray-300">قام الأدمن بإضافة مستخدم جديد</span>
            <span class="text-gray-500 text-sm">قبل 5 دقائق</span>
        </li>

        <li class="flex items-center justify-between border-b border-gray-700 pb-3">
            <span class="text-gray-300">تم تحديث صلاحيات محاسب</span>
            <span class="text-gray-500 text-sm">قبل ساعة</span>
        </li>

        <li class="flex items-center justify-between">
            <span class="text-gray-300">تم حذف متجر من النظام</span>
            <span class="text-gray-500 text-sm">قبل 3 ساعات</span>
        </li>
    </ul>
</div>

@endsection
