@props(['title', 'backRoute', 'columns', 'items'])

<div class="max-w-7xl mx-auto py-8">

    {{-- العنوان + زر الرجوع --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>

        <a href="{{ $backRoute }}"
           class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
            ← الرجوع
        </a>
    </div>

    {{-- جدول --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <table class="w-full text-gray-300 border-collapse">
            <thead>
                <tr class="bg-[#1f2125] text-gray-400 text-sm">
                    @foreach($columns as $col)
                        <th class="p-3 text-right">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($items as $item)
                    <tr class="border-b border-[#2a2d31] hover:bg-[#232529] transition">
                        {{ $slot(['item' => $item]) }}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
