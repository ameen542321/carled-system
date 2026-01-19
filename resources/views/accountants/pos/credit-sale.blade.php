@extends('dashboard.app')

@section('title', 'تسجيل بيع آجل ')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">تسجيل بيع آجل </h1>
    <p class="text-gray-400 text-sm mt-1">قم باختيار الموظف لتسجيل عملية بيع آجل</p>
</div>

{{-- زر الرجوع --}}
<div class="mb-4">
    <a href="{{ route('accountant.dashboard') }}"
       class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
        ← الرجوع
    </a>
</div>

{{-- صندوق المحتوى --}}
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-lg">

    {{-- جدول الموظفين --}}
    <table class="w-full text-gray-300">
        <thead>
            <tr class="border-b border-gray-700 text-gray-400 text-sm">
                <th class="py-2 font-medium">الاسم</th>
                <th class="py-2 font-medium">الدور</th>
                <th class="py-2 font-medium text-center">بيع آجل</th>
            </tr>
        </thead>

        <tbody>
            @foreach($people as $emp)
                <tr class="border-b border-gray-700 hover:bg-gray-750 transition">

                    {{-- الاسم --}}
                    <td class="py-3 text-white font-semibold">
                        {{ $emp->name }}
                    </td>

                    {{-- الدور --}}
                   <td class="py-3">
    @if($emp->role === 'accountant')
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white">
            محاسب
        </span>
    @else
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-700 text-gray-300">
            موظف
        </span>
    @endif
</td>


                    {{-- زر البيع الآجل --}}
                    <td class="py-3 text-center">
                        <button
                            onclick="openCreditSaleModal({{ $emp->id }}, '{{ $emp->name }}')"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-sm shadow">
                            بيع آجل
                        </button>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- آخر 5 عمليات بيع آجل --}}
    <div class="mt-10 bg-gray-900 border border-gray-700 rounded-xl p-5">

      <div class="mt-6">
    <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h18M3 7h18M3 11h18M3 15h18M3 19h18" />
        </svg>
          عمليات بيع آجل
    </h3>

    <div class="space-y-3">

        @forelse ($lastCreditSales as $sale)
            <div class="bg-gray-800 rounded-lg p-4 shadow-md hover:bg-gray-750 transition">

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v2m0 8v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <div>
                            <div class="text-white font-semibold">
                                بيع آجل بقيمة {{ number_format($sale->amount, 2) }} ريال
                            </div>

                            <div class="text-gray-400 text-sm">
                                التاريخ: {{ $sale->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

                @if ($sale->description)
                    <div class="mt-2 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-400 mt-0.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v9a2 2 0 01-2 2z" />
                        </svg>

                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $sale->description }}
                        </p>
                    </div>
                @endif

            </div>

        @empty
            <div class="text-gray-400 text-sm text-center py-4">
                لا توجد عمليات بيع آجل حتى الآن
            </div>
        @endforelse

    </div>
</div>


    </div>

</div>

{{-- ============================= --}}
{{-- مودال البيع الآجل creditSaleModal --}}
{{-- ============================= --}}
<div id="creditSaleModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm">

    <div class="bg-gray-800 p-6 rounded-xl w-full max-w-md border border-gray-700 shadow-2xl">

        <h2 class="text-xl font-bold text-white mb-4">
            تسجيل بيع آجل للموظف: <span id="empName" class="text-indigo-400"></span>
        </h2>

        <form id="creditSaleForm" method="POST">
            @csrf

            {{-- مبلغ البيع --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">مبلغ البيع</label>
                <input type="number" name="amount" step="0.01"
                       class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-indigo-500"
                       required>
            </div>

            {{-- التاريخ --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">التاريخ</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}"
                       class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-indigo-500"
                       required>
            </div>

            {{-- الوصف --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">الوصف (اختياري)</label>
                <textarea name="description"
                          class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-indigo-500"
                          rows="3"></textarea>
            </div>

            {{-- الأزرار --}}
            <div class="flex gap-3">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg w-full shadow">
                    حفظ البيع الآجل
                </button>

                <button type="button"
                        onclick="closeCreditSaleModal()"
                        class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg w-full shadow">
                    إلغاء
                </button>
            </div>

        </form>

    </div>
</div>

{{-- سكربت --}}
<script>
function openCreditSaleModal(empId, empName) {
    document.getElementById('empName').innerText = empName;

    // Laravel سيولّد الرابط الصحيح تلقائيًا
    const routeTemplate = "{{ route('accountant.pos.credit-sale.store', ['employee' => 'ID']) }}";

    // استبدال ID بالرقم الحقيقي
    document.getElementById('creditSaleForm').action = routeTemplate.replace('ID', empId);

    document.getElementById('creditSaleModal').classList.remove('hidden');
}

function closeCreditSaleModal() {
    document.getElementById('creditSaleModal').classList.add('hidden');
}
</script>


@endsection
