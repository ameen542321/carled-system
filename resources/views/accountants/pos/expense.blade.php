@extends('dashboard.app')

@section('title', 'تسجيل مصروف للموظفين')

@section('content')

@php
    // تحديد الدور الحالي (مستخدم أو محاسب)
    $role = auth('accountant')->check() ? 'accountant' : 'user';
@endphp

<div class="p-6">
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white"> أظافة مصاريف عامة </h1>
   
</div>

{{-- زر الرجوع --}}
<div class="mb-4">
    <a href="{{ route('accountant.dashboard') }}"
       class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
        ← الرجوع
    </a>
</div>
    {{-- زر الرجوع --}}
    <div class="flex justify-between items-center mb-6">

    

    {{-- العنوان --}}
    <h1 class="text-2xl font-bold text-white"></h1>

    {{-- زر إضافة --}}
    <button onclick="openExpenseModal()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
        إضافة مصروف
    </button>

</div>

    {{-- الفلترة --}}
    <form method="GET" class="mb-6 bg-gray-800 p-4 rounded-lg flex gap-4 items-end">

        <div class="flex flex-col w-1/3">
            <label class="text-gray-300 mb-1">من تاريخ</label>
            <input type="date" name="from" value="{{ request('from') }}"
                class="bg-gray-700 text-white rounded-lg px-3 py-2">
        </div>

        <div class="flex flex-col w-1/3">
            <label class="text-gray-300 mb-1">إلى تاريخ</label>
            <input type="date" name="to" value="{{ request('to') }}"
                class="bg-gray-700 text-white rounded-lg px-3 py-2">
        </div>

        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            تطبيق الفلترة
        </button>

    </form>

    {{-- مجموع المصروفات --}}
    <div class="bg-gray-800 p-4 rounded-lg mb-6">
        <div class="text-gray-300 text-lg">
            مجموع المصروفات:
            <span class="text-yellow-400 font-bold">{{ number_format($total) }} ريال</span>
        </div>
    </div>

    {{-- جدول المصروفات --}}
    <div class="bg-gray-800 rounded-lg overflow-hidden">
        <table class="w-full text-right">
            <thead class="bg-gray-700 text-gray-300">
                <tr>
                    <th class="p-3">النوع</th>
                    <th class="p-3">الوصف</th>
                    <th class="p-3">المبلغ</th>
                    <th class="p-3">التاريخ</th>
                    <th class="p-3">الإجراءات</th>
                </tr>
            </thead>

            <tbody class="text-gray-300">
                @forelse ($expenses as $expense)
                    <tr class="border-b border-gray-700">
                        <td class="p-3">{{ $expense->type }}</td>
                        <td class="p-3">{{ $expense->description ?? '-' }}</td>
                        <td class="p-3 text-yellow-400 font-bold">{{ number_format($expense->amount) }} ريال</td>
                        <td class="p-3">{{ $expense->created_at->format('Y-m-d') }}</td>

                        <td class="p-3">

                            {{-- المستخدم فقط يرى التعديل والحذف --}}
                            @if(auth()->check() && !auth('accountant')->check())

                                {{-- زر التعديل --}}
                                <button onclick="openEditModal({{ $expense->id }}, '{{ $expense->type }}', '{{ $expense->amount }}', '{{ $expense->description }}')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg text-sm">
                                    تعديل
                                </button>

                                {{-- زر الحذف --}}
                                <form action="{{ route('user.pos.expense.destroy', $expense->id) }}"
                                    method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm">
                                        حذف
                                    </button>
                                </form>

                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            لا توجد مصروفات مسجلة
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>


{{-- مودال إضافة مصروف --}}
<div id="expenseModal"
     class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center">

    <div class="bg-gray-800 w-96 p-6 rounded-lg">

        <h2 class="text-xl text-white font-bold mb-4">إضافة مصروف جديد</h2>

        <form action="{{ route($role . '.pos.expense.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="text-gray-300 mb-1 block">نوع المصروف</label>
                <input type="text" name="type"
                       class="bg-gray-700 text-white w-full rounded-lg px-3 py-2"
                       placeholder="مثال: كهرباء، ماء، إيجار" required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 mb-1 block">المبلغ</label>
                <input type="number" name="amount"
                       class="bg-gray-700 text-white w-full rounded-lg px-3 py-2"
                       required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 mb-1 block">الوصف (اختياري)</label>
                <textarea name="description"
                          class="bg-gray-700 text-white w-full rounded-lg px-3 py-2"
                          rows="3"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="closeExpenseModal()"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    إلغاء
                </button>

                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    حفظ
                </button>
            </div>

        </form>

    </div>
</div>


{{-- مودال تعديل مصروف --}}
<div id="editModal"
     class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center">

    <div class="bg-gray-800 w-96 p-6 rounded-lg">

        <h2 class="text-xl text-white font-bold mb-4">تعديل المصروف</h2>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="text-gray-300 mb-1 block">نوع المصروف</label>
                <input id="edit_type" type="text" name="type"
                       class="bg-gray-700 text-white w-full rounded-lg px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 mb-1 block">المبلغ</label>
                <input id="edit_amount" type="number" name="amount"
                       class="bg-gray-700 text-white w-full rounded-lg px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 mb-1 block">الوصف</label>
                <textarea id="edit_description" name="description"
                          class="bg-gray-700 text-white w-full rounded-lg px-3 py-2"
                          rows="3"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="closeEditModal()"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    إلغاء
                </button>

                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    حفظ التعديلات
                </button>
            </div>

        </form>

    </div>
</div>


<script>
    function openExpenseModal() {
        document.getElementById('expenseModal').classList.remove('hidden');
    }

    function closeExpenseModal() {
        document.getElementById('expenseModal').classList.add('hidden');
    }

    function openEditModal(id, type, amount, description) {
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_description').value = description;

        document.getElementById('editForm').action =
            "/user/pos/expense/update/" + id;

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>

@endsection
