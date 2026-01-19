@extends('dashboard.app')
@section('title', 'إدارة الموظفين')
@section('content')

<div class="px-6 py-8 max-w-6xl mx-auto">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-10">

        {{-- زر الرجوع --}}
        <a href="{{ url()->previous() }}"
           class="flex items-center gap-2 bg-gray-800 text-gray-200 px-4 py-2.5 rounded-lg hover:bg-gray-700 transition">
            <i class="fa-solid fa-arrow-right"></i>
            رجوع
        </a>

        {{-- العنوان --}}
        <div class="text-center flex-1">
            <h1 class="text-3xl font-bold text-gray-100">إدارة الموظفين</h1>
            <p class="text-gray-400 mt-1 text-sm">إدارة الموظفين والمحاسبين في متجرك</p>
        </div>

        {{-- زر إضافة موظف --}}
        <a href="{{ route('user.employees.create') }}"
           class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg shadow hover:bg-blue-700 transition">
            <i class="fa-solid fa-plus"></i>
            إضافة موظف
        </a>

    </div>


    {{-- بطاقات الإحصائيات --}}
   <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-10">


        {{-- عدد الموظفين --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 flex items-center gap-4 shadow hover:shadow-lg transition">
            <div class="bg-blue-700/20 text-blue-400 p-3 rounded-lg">
                <i class="fa-solid fa-user text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-400 text-sm">عدد الموظفين</p>
                <p class="text-2xl font-bold text-gray-100">{{ $employees->count() }}</p>
            </div>
        </div>

        {{-- عدد المحاسبين غير الفعالين --}}
        {{-- <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 flex items-center gap-4 shadow hover:shadow-lg transition">
            <div class="bg-emerald-700/20 text-emerald-400 p-3 rounded-lg">
                <i class="fa-solid fa-calculator text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-400 text-sm">عدد المحاسبين غير الفعالين</p>
                <p class="text-2xl font-bold text-gray-100">{{ $accountants->count() }}</p>
            </div>
        </div> --}}

    </div>


    {{-- الجدول --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden shadow-xl">

        <table class="w-full text-right">
            <thead class="bg-gray-800 border-b border-gray-700">
                <tr class="text-gray-400 text-sm">
                    <th class="p-4">الاسم</th>
                    <th class="p-4">الجوال</th>
                    <th class="p-4">المتجر</th>
                    <th class="p-4">النوع</th>
                    <th class="p-4 text-center">إجراءات</th>
                </tr>
            </thead>

            <tbody class="text-gray-300">

                @forelse ($employees as $person)
                    <tr class="border-b border-gray-800 hover:bg-gray-800/40 transition">

                        {{-- الاسم --}}
                        <td class="p-4 font-semibold flex items-center gap-3">
                            <div class="bg-gray-700 text-gray-300 w-10 h-10 rounded-full flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span>{{ $person->name }}</span>
                        </td>

                        {{-- الجوال --}}
                        <td class="p-4">{{ $person->phone ?? '—' }}</td>

                        {{-- المتجر --}}
                        <td class="p-4">{{ $person->store->name }}</td>

                        {{-- النوع --}}
                        <td class="p-4">
                            @if($person->accountant)
                                @if($person->accountant->status === 'active')
                                    <span class="px-3 py-1 text-sm bg-emerald-600/20 text-emerald-400 rounded-full">
                                        محاسب (فعّال)
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-sm bg-yellow-600/20 text-yellow-400 rounded-full">
                                        محاسب (غير فعّال)
                                    </span>
                                @endif
                            @else
                                <span class="px-3 py-1 text-sm bg-blue-600/20 text-blue-400 rounded-full">
                                    موظف
                                </span>
                            @endif
                        </td>

                        {{-- الإجراءات --}}
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-5">

                                {{-- عرض --}}
                                <a href="{{ route('user.employees.show', $person->id) }}"
                                   class="text-blue-400 hover:text-blue-300 transition"
                                   title="عرض">
                                    <i class="fa-solid fa-eye text-lg"></i>
                                </a>

                                {{-- تعديل --}}
                                <a href="{{ route('user.employees.edit', $person->id) }}"
                                   class="text-yellow-400 hover:text-yellow-300 transition"
                                   title="تعديل">
                                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                                </a>

                                {{-- إيقاف / تفعيل --}}
                                @if($person->accountant)
                                    @if($person->accountant->status === 'active')
                                        {{-- إيقاف --}}
                                        <a href="{{ route('user.accountants.suspend', $person->accountant->id) }}"
                                           class="text-red-400 hover:text-red-300 transition"
                                           title="إيقاف">
                                            <i class="fa-solid fa-pause text-lg"></i>
                                        </a>
                                    @else
                                        {{-- تفعيل --}}
                                        <a href="{{ route('user.accountants.activate', $person->accountant->id) }}"
                                           class="text-green-400 hover:text-green-300 transition"
                                           title="تفعيل">
                                            <i class="fa-solid fa-play text-lg"></i>
                                        </a>
                                    @endif
                                @endif

                                {{-- حذف --}}
                                <form action="{{ route('user.employees.destroy', $person->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-400 transition" title="حذف">
                                        <i class="fa-solid fa-trash text-lg"></i>
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-gray-500">
                            لا يوجد أشخاص حتى الآن
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

    </div>

</div>

@endsection
