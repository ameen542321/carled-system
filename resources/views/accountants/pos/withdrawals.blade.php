@extends('dashboard.app')
@section('title', '  أضافة سحب نقدي')
@section('content')

{{-- عنوان الصفحة --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">سحب نقدي</h1>
    <p class="text-gray-400 text-sm mt-1">قم باختيار الموظف لاضافة عملية السحب</p>
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
                <th class="py-2 font-medium text-center">سحب</th>
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

                    {{-- زر السحب --}}
                    <td class="py-3 text-center">
                        <button
                            onclick="openWithdrawalModal({{ $emp->id }}, '{{ $emp->name }}')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm shadow">
                            سحب
                        </button>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- آخر 5 عمليات --}}
    <div class="mt-10 bg-gray-900 border border-gray-700 rounded-xl p-5">

        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <span class="text-yellow-400">🕘</span>
              عمليات السحب
        </h2>

        @forelse($lastWithdrawals as $w)
    <div class="bg-gray-800 rounded-lg p-4 mb-3 shadow-md hover:bg-gray-750 transition">

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">

            {{-- الموظف --}}
            <div class="flex items-center gap-2 text-gray-300">
                <span class="text-blue-400 text-lg">👤</span>
                <span class="font-semibold">{{ $w->person->name ?? '—' }}</span>
            </div>

            {{-- المبلغ --}}
            <div class="flex items-center gap-2 text-green-400 font-bold text-lg">
                <span>💰</span>
                {{ number_format($w->amount, 2) }} ريال
            </div>

            {{-- التاريخ --}}
            <div class="flex items-center gap-2 text-gray-400 text-sm">
                <span>📅</span>
                {{ $w->date }}
            </div>

        </div>

        {{-- الوصف إن وجد --}}
        @if(!empty($w->description))
            <div class="mt-3 flex items-start gap-2 text-gray-300 text-sm leading-relaxed">
                <span class="text-yellow-400">📝</span>
                <p>{{ $w->description }}</p>
            </div>
        @endif

    </div>
@empty
    <p class="text-gray-400 text-sm text-center py-4">
        لا توجد عمليات سحب حتى الآن.
    </p>
@endforelse


    </div>

</div>

{{-- ============================= --}}
{{-- مودال السحب (منسّق بالكامل) --}}
{{-- ============================= --}}
<div id="withdrawalModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm">

    <div class="bg-gray-800 p-6 rounded-xl w-full max-w-md border border-gray-700 shadow-2xl">

        <h2 class="text-xl font-bold text-white mb-4">
            سحب : <span id="empName" class="text-green-400"></span>
        </h2>

        <form id="withdrawalForm" method="POST">
            @csrf

            {{-- مبلغ السحب --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">مبلغ السحب</label>
                <input type="number" name="amount" step="0.01"
                       class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-blue-500"
                       required>
            </div>

            {{-- التاريخ --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">التاريخ</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}"
                       class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-blue-500"
                       required>
            </div>

            {{-- الوصف --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">الوصف (اختياري)</label>
                <textarea name="description"
                          class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-blue-500"
                          rows="3"></textarea>
            </div>

            {{-- الأزرار --}}
            <div class="flex gap-3">
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg w-full shadow">
                    حفظ السحب
                </button>

                <button type="button"
                        onclick="closeWithdrawalModal()"
                        class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg w-full shadow">
                    إلغاء
                </button>
            </div>

        </form>

    </div>
</div>

{{-- سكربت --}}
<script>
function openWithdrawalModal(empId, empName) {
    document.getElementById('empName').innerText = empName;

    // Laravel سيولّد الرابط الصحيح تلقائيًا
    const routeTemplate = "{{ route('accountant.pos.withdrawal.store', ['employee' => 'ID']) }}";

    // استبدال ID بالرقم الحقيقي
    document.getElementById('withdrawalForm').action = routeTemplate.replace('ID', empId);

    document.getElementById('withdrawalModal').classList.remove('hidden');
}

function closeWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.add('hidden');
}
</script>


@endsection
