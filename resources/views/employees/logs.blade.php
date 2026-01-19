@extends('dashboard.app')

@section('content')

<div class="px-6 py-8">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-100">
            سجل العمليات — {{ $employee->name }}
        </h1>

        <a href="{{ route('user.employees.show', $employee->id) }}"
           class="bg-gray-800 text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            العودة لصفحة العمليات
        </a>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-100">جميع العمليات</h2>

            <a href="{{ route('user.employees.exportLog', $employee->id) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                تصدير PDF
            </a>
        </div>

        @foreach ($logs as $log)
            <div class="border-b border-gray-800 py-4">

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 font-semibold">
                            @switch($log->action)
                                @case('withdrawal') <span class="text-blue-400">سحب</span> @break
                                @case('absence') <span class="text-yellow-400">غياب</span> @break
                                @case('debt') <span class="text-red-400">مديونية</span> @break
                                @case('credit_sale') <span class="text-purple-400">بيع آجل</span> @break
                                @case('credit_sale_deducted') <span class="text-green-400">تحصيل بيع آجل</span> @break
                                @case('store_transfer') <span class="text-indigo-400">نقل بين المتاجر</span> @break
                                @case('salary_update') <span class="text-gray-400">تعديل راتب</span> @break
                                @default <span class="text-gray-400">عملية</span>
                            @endswitch
                        </p>

                        <p class="text-gray-400 text-sm mt-1">
                            {{ $log->description }}
                        </p>
                    </div>

                    <p class="text-gray-500 text-sm">
                        {{ optional($log->created_at)->format('Y-m-d H:i') }}
                    </p>
                </div>

            </div>
        @endforeach

    </div>

</div>

@endsection
