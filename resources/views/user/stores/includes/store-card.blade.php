<div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-5 hover:border-[#3a3d41] transition-all duration-200">

    {{-- اسم المتجر --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-white">
            {{ $store->name }}
        </h3>

        

        {{-- شارة الحالة --}}
        @include('user.stores.includes.status-badge', ['status' => $store->status])
    </div>

    {{-- الوصف --}}
    <p class="text-sm text-gray-400 line-clamp-2 mb-4">
        {{ $store->description ?? 'لا يوجد وصف لهذا المتجر' }}
    </p>

    {{-- معلومات إضافية --}}
    <div class="text-xs text-gray-500 mb-4">
        <div>رقم الهاتف: {{ $store->phone ?? '—' }}</div>
        <div>العنوان: {{ $store->address ?? '—' }}</div>
    </div>

    {{-- الأزرار --}}
    <div class="flex items-center justify-between mt-4">
        @include('user.stores.includes.actions', ['store' => $store])
    </div>

</div>
