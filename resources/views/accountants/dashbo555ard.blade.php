@extends('dashboard.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-white">
        لوحة تحكم المحاسب
    </h1>
</div>

{{-- بطاقات العمليات --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    {{-- بيع نقدًا --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 hover:bg-[#232529] transition cursor-pointer">
        <h3 class="text-lg font-semibold text-white mb-2">بياع نقدًا</h3>
        <p class="text-gray-400 text-sm">إنشاء عملية بيع نقدية</p>
    </div>

    {{-- بيع شبكة --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 hover:bg-[#232529] transition cursor-pointer">
        <h3 class="text-lg font-semibold text-white mb-2">بيع شبكة</h3>
        <p class="text-gray-400 text-sm">إنشاء عملية بيع عبر الشبكة</p>
    </div>

    {{-- بيع آجل --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 hover:bg-[#232529] transition cursor-pointer">
        <h3 class="text-lg font-semibold text-white mb-2">بيع آجل</h3>
        <p class="text-gray-400 text-sm">تسجيل عملية بيع آجل</p>
    </div>

    {{-- سحب نقدي --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 hover:bg-[#232529] transition cursor-pointer">
        <h3 class="text-lg font-semibold text-white mb-2">سحب نقدي</h3>
        <p class="text-gray-400 text-sm">تسجيل سحب نقدي للموظفين</p>
    </div>

    {{-- إضافة مصروفات --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 hover:bg-[#232529] transition cursor-pointer">
        <h3 class="text-lg font-semibold text-white mb-2">إضافة مصروفات</h3>
        <p class="text-gray-400 text-sm">تسجيل مصروف جديد</p>
    </div>

    {{-- غياب الموظفين --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 hover:bg-[#232529] transition cursor-pointer">
        <h3 class="text-lg font-semibold text-white mb-2">غياب الموظفين</h3>
        <p class="text-gray-400 text-sm">تسجيل غياب موظف</p>
    </div>

</div>

@endsection
