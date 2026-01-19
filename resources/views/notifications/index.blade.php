@extends('dashboard.app')
@section('title', 'الإشعارات')
@section('content')

{{-- تحديد المستخدم الحالي --}}
@php
    $currentUser = auth('accountant')->check()
        ? auth('accountant')->user()
        : auth('web')->user();
@endphp

{{-- دالة الراوت الذكي --}}
@php
function notifRoute($name, $id = null) {

    switch (true) {

        case auth('accountant')->check():
            $prefix = 'accountant.notifications.';
            break;

        case auth('web')->check() && auth('web')->user()->role === 'admin':
            $prefix = 'admin.notifications.';
            break;
             case auth('web')->check() && auth('web')->user()->role === 'user':
            $prefix = 'user.notifications.';
            break;

        default:
            $prefix = 'user.notifications.';
            break;
    }

    return $id
        ? route($prefix . $name, $id)
        : route($prefix . $name);
}
@endphp

<div class="p-6">

    {{-- العنوان + تحديد الكل + زر تنفيذ --}}
    <div class="flex items-center justify-between mb-8">

        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-white">الإشعارات</h1>

            {{-- مربع تحديد الكل --}}
            <label class="flex items-center text-sm text-gray-300 cursor-pointer">
                <input type="checkbox" id="select-all"
                       class="w-4 h-4 rounded border-gray-500 bg-gray-800">
                <span class="mr-2">تحديد الكل</span>
            </label>
        </div>

        {{-- زر تنفيذ على المحدد --}}
        <form id="bulk-form" method="POST" action="{{ notifRoute('markSelected') }}">
            @csrf
            <button class="px-4 py-2 bg-blue-700 text-white rounded-md hover:bg-blue-600 transition">
                <i class="fa-solid fa-check ml-1"></i> تحديد المحدد كمقروء
            </button>
        </form>

    </div>

    {{-- قائمة الإشعارات --}}
    <div class="space-y-4">

        @forelse($notifications as $n)
            @php
                $isRead = $n->isReadBy($currentUser->id);
            @endphp

            <div class="bg-gray-900 border border-gray-800 rounded-lg p-5 shadow
                        {{ $isRead ? 'opacity-60' : '' }}">

                <div class="flex items-start justify-between gap-4">

                    {{-- مربع التحديد + المحتوى --}}
                    <div class="flex items-start gap-3">

                        {{-- مربع التحديد --}}
                        <div class="pt-1">
                            <input type="checkbox"
                                   form="bulk-form"
                                   name="selected[]"
                                   value="{{ $n->id }}"
                                   class="item-checkbox w-4 h-4 rounded border-gray-500 bg-gray-800">
                        </div>

                        {{-- العنوان + الرسالة --}}
                        <div>
                            <a href="{{ notifRoute('show', $n->id) }}"
                               class="text-lg font-semibold text-blue-400 hover:text-blue-300">
                                {{ $n->title }}
                            </a>

                            <p class="text-gray-300 mt-1">{{ $n->message }}</p>

                            <p class="text-gray-500 text-xs mt-2">
                                {{ $n->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- الأزرار الفردية --}}
                    <div class="flex flex-col items-end gap-2">

                        {{-- مقروء / غير مقروء --}}
                        <form method="POST" action="{{ notifRoute('toggle', $n->id) }}">
                            @csrf
                            <button class="px-3 py-1 bg-gray-800 text-gray-300 rounded-md hover:bg-gray-700 transition text-sm">
                                {{ $isRead ? 'غير مقروء' : 'مقروء' }}
                            </button>
                        </form>

                        {{-- حذف --}}
                        {{-- <form method="POST" action="{{ notifRoute('remov', $n->id) }}"> --}}
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 bg-red-700 text-white rounded-md hover:bg-red-600 transition text-sm">
                                حذف
                            </button>
                        </form>

                    </div>

                </div>

            </div>
        @empty

            <div class="text-center text-gray-400 py-10">
                لا توجد إشعارات
            </div>

        @endforelse

    </div>

</div>

{{-- سكربت تحديد الكل --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    });
</script>

@endsection
