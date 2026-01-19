@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">الفواتير</h1>

    <a href="{{ route('pos.index') }}"
       class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">
        + إنشاء فاتورة
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">رقم الفاتورة</th>
                <th class="py-3">التاريخ</th>
                <th class="py-3">العميل</th>
                <th class="py-3">المبلغ</th>
                <th class="py-3">المدفوع</th>
                <th class="py-3">الحالة</th>
                <th class="py-3">الكاشير</th>
                <th class="py-3">إجراءات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال واحد (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">

                <td class="py-3">#1023</td>

                <td class="py-3">2025-12-20</td>

                <td class="py-3">عميل نقدي</td>

                <td class="py-3">150 ريال</td>

                <td class="py-3">150 ريال</td>

                <td class="py-3">
                    <span class="px-3 py-1 rounded bg-green-700 text-white text-sm">
                        مدفوعة
                    </span>
                </td>

                <td class="py-3">سعود العتيبي</td>

                <td class="py-3 flex gap-2">

                    {{-- عرض --}}
                    <a href="#"
                       class="px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded text-white text-sm">
                        عرض
                    </a>

                    {{-- طباعة --}}
                    <a href="#"
                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm">
                        طباعة
                    </a>

                </td>

            </tr>

        </tbody>
    </table>

</div>

@endsection
