@extends('dashboard.app')
@section('title', 'تحصيل البيع الآجل')
@section('content')

{{-- عنوان الصفحة --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">تحصيل البيع الآجل</h1>
    <p class="text-gray-400 text-sm mt-1">اختر الموظف لعرض عمليات البيع الآجل وتحصيلها</p>
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
                <th class="py-2 font-medium text-center">تحصيل</th>
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

                    {{-- زر التحصيل --}}
                    <td class="py-3 text-center">
                        <button
                            onclick="openCollectionModal({{ $emp->id }}, '{{ $emp->name }}')"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-sm shadow">
                            تحصيل
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
            آخر 5 عمليات تحصيل
        </h2>

        @foreach ($lastCollections as $log)
    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-2">

        {{-- اليسار: اسم الموظف + وصف العملية --}}
        <div class="flex flex-col">
            <span class="font-semibold text-gray-900 dark:text-gray-100">
                {{ $log->person->name ?? '—' }}
            </span>

            <span class="text-sm text-gray-600 dark:text-gray-300">
                {{ $log->description }}
            </span>

            <span class="text-xs text-gray-400 mt-1">
                {{ $log->created_at->format('Y-m-d H:i') }}
            </span>
        </div>

        {{-- اليمين: نوع العملية --}}
        <div>
            @if($log->action_name === 'credit_sale_deducted')
                <span class="px-3 py-1 text-sm rounded-full bg-green-600 text-white">
                    كامل
                </span>
            @else
                <span class="px-3 py-1 text-sm rounded-full bg-blue-600 text-white">
                    جزئي
                </span>
            @endif
        </div>

    </div>
@endforeach


    </div>

</div>

{{-- ============================= --}}
{{-- مودال التحصيل --}}
{{-- ============================= --}}
<div id="collectionModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm">

    <div class="bg-gray-800 p-6 rounded-xl w-full max-w-lg border border-gray-700 shadow-2xl">

        <h2 class="text-xl font-bold text-white mb-4">
            عمليات البيع الآجل: <span id="empName" class="text-green-400"></span>
        </h2>

        {{-- قائمة العمليات --}}
        <div id="creditSalesList" class="space-y-3 text-gray-300"></div>

        <div class="mt-4">
            <button type="button"
                    onclick="closeCollectionModal()"
                    class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg w-full shadow">
                إغلاق
            </button>
        </div>

    </div>
</div>

{{-- ============================= --}}
{{-- مودال التحصيل الجزئي --}}
{{-- ============================= --}}
<div id="partialModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm">

    <div class="bg-gray-800 p-6 rounded-xl w-full max-w-md border border-gray-700 shadow-2xl">

        <h2 class="text-xl font-bold text-white mb-4">تحصيل جزئي</h2>

        <form id="partialForm" method="GET">

            <label class="text-gray-300 text-sm">المبلغ المراد تحصيله</label>
            <input id="partialAmount" type="number" min="1"
                   class="w-full mt-2 p-2 rounded bg-gray-700 text-white border border-gray-600">

            <button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">
                تأكيد التحصيل
            </button>
        </form>

        <button onclick="closePartialModal()"
                class="mt-3 bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg w-full">
            إلغاء
        </button>

    </div>
</div>

{{-- سكربت --}}
<script>
    const allSales = @json($people->mapWithKeys(function($emp){
        return [$emp->id => $emp->pending_credit_sales];
    }));

function openCollectionModal(empId, empName) {

    // 🔥 إذا كان الموظف هو نفس المحاسب → نمنع التحصيل بالكامل
    if (empId == {{ auth('accountant')->user()->employee_id }}) {

        document.getElementById('empName').innerText = empName;

        document.getElementById('creditSalesList').innerHTML = `
            <div class="p-6 bg-gray-700 rounded-lg text-center text-white text-lg font-bold">
                عفوًا لا تملك الإذن بذلك، راجع مالك المتجر أو المدير
            </div>
        `;

        document.getElementById('collectionModal').classList.remove('hidden');
        return;
    }

    // ✔ إذا كان الموظف ليس المحاسب → نعرض العمليات بشكل طبيعي
    let sales = allSales[empId];
    let html = '';

    sales.forEach(sale => {
        const fullRoute = "{{ route('accountant.pos.collection.store', ['sale' => 'SALE']) }}"
            .replace('SALE', sale.id);

        html += `
            <div class="p-4 bg-gray-700 rounded-lg space-y-3">

                <div class="flex justify-between">
                    <div class="text-white font-semibold">المبلغ الأصلي: ${sale.amount} ريال</div>
                    <div class="text-yellow-400 font-bold">المتبقي: ${sale.remaining_amount} ريال</div>
                </div>

                <div class="text-gray-400 text-sm">التاريخ: ${sale.date}</div>

                <div class="flex gap-2">
                    <form action="${fullRoute}" method="POST" class="w-1/2">
                        @csrf
                        <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm w-full">
                            تحصيل كامل
                        </button>
                    </form>

                    <button onclick="openPartialModal(${sale.id}, ${sale.remaining_amount})"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm w-1/2">
                        تحصيل جزئي
                    </button>
                </div>

            </div>
        `;
    });

    document.getElementById('empName').innerText = empName;
    document.getElementById('creditSalesList').innerHTML = html;
    document.getElementById('collectionModal').classList.remove('hidden');
}

    function closeCollectionModal() {
        document.getElementById('collectionModal').classList.add('hidden');
    }

    // ============================
    // التحصيل الجزئي
    // ============================
    function openPartialModal(saleId, maxAmount) {
        const form = document.getElementById('partialForm');
        const amountInput = document.getElementById('partialAmount');

        amountInput.max = maxAmount;

        const route = "{{ route('accountant.pos.collection.store', ['sale' => 'SALE']) }}"
            .replace('SALE', saleId);

        form.onsubmit = function(e) {
            e.preventDefault();
            const val = amountInput.value;

            if (val < 1 || val > maxAmount) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'المبلغ غير صالح',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('amount', val);

            fetch(route, {
                method: 'POST',
                body: formData
            })
            .then(async response => {

                if (!response.ok) {
                    const data = await response.json();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: data.error ?? 'غير مصرح لك بالتحصيل',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });

                    return;
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'تم التحصيل الجزئي بنجاح',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });

                setTimeout(() => location.reload(), 1500);
            });
        };

        document.getElementById('partialModal').classList.remove('hidden');
    }

    function closePartialModal() {
        document.getElementById('partialModal').classList.add('hidden');
    }
</script>


@endsection
