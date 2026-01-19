@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">الرواتب الشهرية</h1>

{{-- اختيار الشهر --}}
<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-6 max-w-xl">

    <form class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="text-gray-300 mb-1 block">الشهر</label>
            <input type="month"
                   value="{{ date('Y-m') }}"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
        </div>

        <div class="flex items-end">
            <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white w-full">
                عرض الرواتب
            </button>
        </div>

    </form>

</div>

{{-- جدول الرواتب --}}
<div class="bg-gray-800 border border-gray-700 rounded-lg p-4">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">العامل</th>
                <th class="py-3">الراتب الأساسي</th>
                <th class="py-3">السحوبات</th>
                <th class="py-3">الصافي</th>
                <th class="py-3">الحالة</th>
                <th class="py-3">العمليات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">
                <td class="py-3">أحمد علي</td>
                <td class="py-3">2,500 ريال</td>
                <td class="py-3 text-red-400">200 ريال</td>
                <td class="py-3 text-green-400">2,300 ريال</td>
                <td class="py-3">
                    <span class="px-3 py-1 bg-yellow-600 text-white rounded text-sm">غير مدفوع</span>
                </td>
                <td class="py-3">
                    <a href="{{ route('salaries.close', 1) }}"
                       class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-white text-sm">
                        إغلاق راتب الشهر
                    </a>
                </td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">سالم محمد</td>
                <td class="py-3">2,000 ريال</td>
                <td class="py-3 text-red-400">0 ريال</td>
                <td class="py-3 text-green-400">2,000 ريال</td>
                <td class="py-3">
                    <span class="px-3 py-1 bg-green-700 text-white rounded text-sm">مدفوع</span>
                </td>
                <td class="py-3">
                    <a href="{{ route('salaries.slip', 2) }}"
                       class="text-blue-400 hover:underline text-sm">
                        عرض الكشف
                    </a>
                </td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
