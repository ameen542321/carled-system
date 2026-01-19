@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">إغلاق راتب الشهر</h1>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 max-w-2xl mx-auto">

    {{-- عنوان --}}
    <h2 class="text-xl text-gray-200 font-semibold mb-6">
        إغلاق راتب شهر {{ $month }} للعامل: {{ $worker->name }}
    </h2>

    {{-- تفاصيل الراتب --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <div>
            <h3 class="text-gray-400 mb-1">الراتب الأساسي</h3>
            <p class="text-gray-200 text-lg">{{ $worker->salary }} ريال</p>
        </div>

        <div>
            <h3 class="text-gray-400 mb-1">إجمالي السحوبات</h3>
            <p class="text-red-400 text-lg">{{ $withdrawals }} ريال</p>
        </div>

        <div>
            <h3 class="text-gray-400 mb-1">صافي الراتب</h3>
            <p class="text-green-400 text-2xl font-bold">{{ $net_salary }} ريال</p>
        </div>

        <div>
            <h3 class="text-gray-400 mb-1">الحالة</h3>
            <span class="px-3 py-1 bg-yellow-600 text-white rounded text-sm">
                غير مدفوع
            </span>
        </div>

    </div>

    <hr class="border-gray-700 my-6">

    {{-- ملاحظة --}}
    <div class="mb-6">
        <label class="text-gray-300 mb-1 block">ملاحظة (اختياري)</label>
        <textarea rows="3"
                  class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                  placeholder="مثال: تم الدفع نقدًا – تحويل بنكي – دفعة مقدمة"></textarea>
    </div>

    {{-- زر تأكيد الدفع --}}
    <div class="flex justify-end">
        <button class="bg-green-600 hover:bg-green-700 px-5 py-2 rounded text-white text-lg">
            تأكيد دفع الراتب
        </button>
    </div>

</div>

@endsection
