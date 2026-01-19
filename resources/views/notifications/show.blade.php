@extends('dashboard.app')
@section('title', 'معاينة الاشعار')

@section('content')

{{-- تحديد المستخدم الحالي بشكل صحيح --}}
@php
    $currentUser = auth('accountant')->check()
        ? auth('accountant')->user()
        : auth('web')->user();
@endphp

{{-- دالة الراوت الذكي --}}
@php
    function notifRoute($name, $id = null) {
        if (auth('accountant')->check()) {
            $prefix = 'accountant.notifications.';
        } elseif (auth('web')->check() && auth('web')->user()->role === 'admin') {
            $prefix = 'admin.notifications.';
        } else {
            $prefix = 'user.notifications.';
        }

        return $id
            ? route($prefix . $name, $id)
            : route($prefix . $name);
    }
@endphp

<div class="p-6">

    {{-- العنوان + أزرار --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">تفاصيل الإشعار</h1>
            <p class="text-gray-400 text-sm mt-1">عرض كامل لمحتوى الإشعار</p>
        </div>

        <div class="flex items-center gap-3">

            {{-- زر حذف --}}
            <form method="POST" action="{{ notifRoute('delete', $notification->id) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="redirect_to" value="{{ url()->previous() }}">

                <button class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 transition">
                    <i class="fa-solid fa-trash ml-1"></i> حذف
                </button>
            </form>

            {{-- زر مقروء / غير مقروء --}}
            <form method="POST" action="{{ notifRoute('toggle', $notification->id) }}">
                @csrf

                <button class="px-4 py-2 bg-gray-800 text-gray-300 rounded-md hover:bg-gray-700 transition">
                    <i class="fa-solid fa-check ml-1"></i>
                    {{ in_array($currentUser->id, $notification->read_by ?? []) ? 'وضع كغير مقروء' : 'وضع كمقروء' }}
                </button>
            </form>

            {{-- زر رجوع --}}
            <a href="{{ notifRoute('index') }}"
               class="px-4 py-2 bg-gray-800 text-gray-300 rounded-md hover:bg-gray-700 transition">
                <i class="fa-solid fa-arrow-right ml-1"></i> رجوع
            </a>
        </div>
    </div>

    {{-- البطاقة --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-xl p-6">

        {{-- العنوان --}}
        <h2 class="text-xl font-semibold text-blue-400 mb-2">
            {{ $notification->title }}
        </h2>

        {{-- المرسل --}}
        <div class="flex items-center gap-2 text-gray-300 text-sm mb-4">
            <span class="text-gray-400">المرسل:</span>

            @switch($notification->sender_type)

                @case('admin')
                    <i class="fa-solid fa-user-shield text-blue-400"></i>
                    <span>المدير العام</span>
                    @break

                @case('user')
                    <i class="fa-solid fa-user text-gray-400"></i>
                    <span>مستخدم (ID: {{ $notification->sender_id }})</span>
                    @break

                @case('accountant')
                    <i class="fa-solid fa-user-tie text-purple-400"></i>
                    <span>محاسب (ID: {{ $notification->sender_id }})</span>
                    @break

                @case('CARLED')
                    <i class="fa-solid fa-microchip text-carled"></i>
                    <span class="px-8 py-1 rounded-full text-white text-xs bg-carled">
                        CARLED
                    </span>
                    @break

                @default
                    <i class="fa-solid fa-circle-question text-gray-400"></i>
                    <span>غير معروف</span>

            @endswitch
        </div>

        {{-- الرسالة --}}
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 mb-6">
            <p class="text-gray-200 leading-relaxed text-lg">
                {{ $notification->message }}
            </p>
        </div>

        {{-- الحالة --}}
        <div class="flex items-center gap-3">
            <span class="text-gray-400 text-sm">الحالة:</span>

            @php
                $isRead = in_array($currentUser->id, $notification->read_by ?? []);
            @endphp

            <span class="px-3 py-1 text-sm rounded-full
                {{ $isRead ? 'bg-green-700 text-green-200' : 'bg-yellow-700 text-yellow-200' }}">
                {{ $isRead ? 'مقروء' : 'غير مقروء' }}
            </span>
        </div>

    </div>

</div>

@endsection
