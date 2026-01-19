@php
    $icons = [
        'sale' => ['icon' => 'fa-cart-shopping', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10'],
        'withdrawal' => ['icon' => 'fa-hand-holding-dollar', 'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/10'],
        'expense' => ['icon' => 'fa-file-invoice-dollar', 'color' => 'text-red-400', 'bg' => 'bg-red-500/10'],
        'default' => ['icon' => 'fa-circle-dot', 'color' => 'text-gray-400', 'bg' => 'bg-gray-500/10']
    ];
    $style = $icons[$type] ?? $icons['default'];
@endphp

<div class="{{ $style['bg'] }} {{ $style['color'] }} p-2 rounded-lg w-10 h-10 flex items-center justify-center">
    <i class="fa-solid {{ $style['icon'] }}"></i>
</div>
