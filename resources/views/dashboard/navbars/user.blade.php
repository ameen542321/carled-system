@php
    // الاعتماد على البيانات الممررة من الميدلوير مع الحفاظ على المسميات الأصلية
    $auth = $global_auth ?? auth()->user();
    $plan = $global_plan ?? ($auth ? $auth->plan : null);

    // حساب المتاجر والمحاسبين باستخدام المسميات التي طلبتها
    $currentStores = $auth->stores()->count();
    $currentAccountants = $auth->accountants()->count();

    $remainingStores = max(0, ($plan->allowed_stores ?? 0) - $currentStores);
    $remainingAccountants = max(0, ($plan->allowed_accountants ?? 0) - $currentAccountants);

    // الإشعارات
    $latestNotifications = \App\Models\Notification::forUser($auth->id)->take(5)->get();
    $unreadCount = \App\Models\Notification::unreadCountFor($auth->id);
@endphp

<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 shadow-xl"
     x-data="{ openMenu: false, openUser: false, openNotif: false }">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- يسار: الشعار --}}
            <div class="flex items-center gap-4">
                <button @click="openMenu = !openMenu" class="lg:hidden text-gray-300 hover:text-white transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>

                <div class="flex items-center gap-2 group cursor-default">
                    <div class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_10px_#3b82f6] group-hover:scale-110 transition-transform"></div>
                    <span class="text-white font-black text-xl tracking-tighter uppercase">Car<span class="text-blue-500">led</span></span>
                </div>
            </div>

            {{-- يمين: الإشعارات والبروفايل --}}
            <div class="flex items-center gap-4 md:gap-6">

                {{-- الإشعارات --}}
                <div class="relative">
                    <button @click="openNotif = !openNotif; openUser = false" 
                            class="text-gray-400 hover:text-white relative transition p-2 hover:bg-gray-800 rounded-lg">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span data-notif-badge class="absolute top-1.5 right-1.5 bg-red-600 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center rounded-full border-2 border-gray-900 {{ $unreadCount > 0 ? '' : 'hidden' }}">
                            {{ $unreadCount }}
                        </span>
                    </button>

                    <div x-show="openNotif" @click.outside="openNotif = false" x-cloak x-transition
                         class="absolute left-0 mt-3 w-80 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-3 z-50 overflow-hidden">
                        <div class="px-4 pb-2 border-b border-gray-800 flex justify-between items-center bg-gray-800/20">
                            <h4 class="text-white font-bold text-sm">التنبيهات</h4>
                            <span class="text-[10px] bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full font-bold uppercase">جديد</span>
                        </div>

                        <div data-notif-list class="max-h-72 overflow-y-auto custom-scroll">
                            @forelse($latestNotifications as $n)
                                <a href="{{ route('user.notifications.show', $n->id) }}"
                                   class="block px-4 py-3 transition border-b border-gray-800/40 last:border-0 hover:bg-gray-800/70 {{ $n->isReadBy($auth->id) ? 'text-gray-500' : 'text-gray-200 font-semibold bg-blue-500/5' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg {{ $n->isReadBy($auth->id) ? 'bg-gray-800' : 'bg-blue-600' }} flex items-center justify-center text-white text-xs shrink-0">
                                            <i class="fa-solid fa-bell"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-xs line-clamp-1">{{ $n->title }}</div>
                                            <div class="text-[10px] text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">{{ $n->message }}</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-gray-500 text-center text-xs italic">لا توجد إشعارات حالياً</div>
                            @endforelse
                        </div>
                        <div class="mt-2 pt-2 px-3">
                            <a href="{{ route('user.notifications.index') }}" class="block w-full py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-center text-[11px] font-bold transition">عرض كل الإشعارات</a>
                        </div>
                    </div>
                </div>

                {{-- البروفايل --}}
                <div class="relative">
                    <button @click="openUser = !openUser; openNotif = false" class="flex items-center gap-3 p-1 pr-3 hover:bg-gray-800 rounded-xl transition border border-transparent hover:border-gray-800 group">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-bold text-white group-hover:text-blue-400 transition">{{ $auth->name }}</p>
                            <div class="flex items-center justify-end gap-1.5 mt-0.5">
                                <span class="text-[8px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-500 font-black uppercase tracking-tighter">{{ $plan->name ?? 'Basic' }}</span>
                                <span class="text-[9px] text-gray-500 font-medium italic">{{ $auth->subscription_end_at ? \Carbon\Carbon::parse($auth->subscription_end_at)->format('Y-m-d') : '∞' }}</span>
                            </div>
                        </div>
                        <div class="relative">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($auth->name) }}&background=1e293b&color=3b82f6" class="w-9 h-9 rounded-lg border border-gray-700 group-hover:border-blue-500/50 transition shadow-lg">
                            <div class="absolute -bottom-1 -left-1 w-2.5 h-2.5 bg-green-500 border-2 border-gray-900 rounded-full"></div>
                        </div>
                    </button>

                    <div x-show="openUser" @click.outside="openUser = false" x-cloak x-transition
                         class="absolute left-0 mt-3 w-56 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-2 z-50 overflow-hidden">
                        
                        {{-- كارت سريع داخل القائمة المنسدلة للبروفايل --}}
<div class="px-4 py-3 bg-gray-800/30 mb-2 border-b border-gray-800">
    {{-- شريط استهلاك المتاجر --}}
    <p class="text-[10px] text-gray-500 uppercase font-bold mb-1 text-right">استهلاك المتاجر</p>
    <div class="w-full bg-gray-800 h-1.5 rounded-full overflow-hidden">
        <div class="bg-blue-500 h-full transition-all duration-700" 
             style="width: {{ ($currentStores / max(1, $plan->allowed_stores ?? 1)) * 100 }}%"></div>
    </div>
    <p class="text-[9px] text-gray-400 mt-1.5 text-right">{{ $currentStores }} من أصل {{ $plan->allowed_stores ?? 0 }}</p>

    {{-- إضافة تاريخ انتهاء الاشتراك هنا --}}
    <div class="mt-3 pt-3 border-t border-gray-800/50">
        <p class="text-[10px] text-gray-500 uppercase font-bold mb-1 text-right">صلاحية الاشتراك</p>
        <div class="flex items-center justify-end gap-2 text-gray-300">
            @if($auth->subscription_end_at)
                <span class="text-[11px] font-bold {{ \Carbon\Carbon::parse($auth->subscription_end_at)->isFuture() ? 'text-blue-400' : 'text-red-500' }}">
                    {{ \Carbon\Carbon::parse($auth->subscription_end_at)->translatedFormat('d M Y') }}
                </span>
            @else
                <span class="text-[11px] font-bold text-green-500">مفتوح (دائم)</span>
            @endif
            <i class="fa-solid fa-calendar-day text-[10px] text-gray-600"></i>
        </div>
    </div>
</div>

                        <a href="#" class="px-4 py-2 flex items-center gap-3 text-gray-300 hover:bg-gray-800 transition text-sm">
                            <i class="fa-solid fa-circle-user text-gray-500 w-4 text-center"></i><span>الملف الشخصي</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-2 text-red-500 hover:bg-red-500/10 transition text-sm font-bold">
                                <i class="fa-solid fa-power-off w-4 text-center"></i><span>تسجيل الخروج</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- قائمة الجوال --}}
    <div x-show="openMenu" x-cloak x-transition class="lg:hidden bg-gray-900 border-t border-gray-800 px-4 py-6 space-y-4 shadow-inner">
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="p-3 rounded-xl bg-gray-800/40 border border-gray-800">
                <p class="text-[10px] text-gray-500 uppercase font-bold">المتاجر</p>
                <p class="text-sm font-bold text-white mt-1">{{ $remainingStores }} متاحة</p>
            </div>
            <div class="p-3 rounded-xl bg-gray-800/40 border border-gray-800">
                <p class="text-[10px] text-gray-500 uppercase font-bold">المحاسبين</p>
                <p class="text-sm font-bold text-white mt-1">{{ $remainingAccountants }} متاح</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-2">
            @if ($remainingStores > 0)
                <a href="{{ route('user.stores.create') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white transition font-bold shadow-lg">
                    <div class="flex items-center gap-3"><i class="fa-solid fa-plus-circle"></i><span>إضافة متجر جديد</span></div>
                    <i class="fa-solid fa-chevron-left text-xs opacity-50"></i>
                </a>
            @endif

            
            <div class="grid grid-cols-2 gap-2">
    {{-- إدارة المتاجر --}}
    <a href="{{ route('user.stores.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-800/60 border border-gray-800 text-gray-300 transition-all hover:bg-gray-800 active:scale-95">
        <i class="fa-solid fa-store text-blue-400"></i>
        <span class="text-[11px] font-bold uppercase">ادارة المتاجر</span>
    </a>

    {{-- إدارة المحاسبين --}}
    <a href="{{ route('user.accountants.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-800/60 border border-gray-800 text-gray-300 transition-all hover:bg-gray-800 active:scale-95">
        <i class="fa-solid fa-user-tie text-blue-400"></i>
        <span class="text-[11px] font-bold uppercase">ادارة المحاسبين</span>
    </a>

    {{-- إدارة الموظفين --}}
    <a href="{{ route('user.employees.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-800/60 border border-gray-800 text-gray-300 transition-all hover:bg-gray-800 active:scale-95">
        <i class="fa-solid fa-users text-blue-400"></i>
        <span class="text-[11px] font-bold uppercase">ادارة الموظفين</span>
    </a>

    {{-- التقارير --}}
    <a href="" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-800/60 border border-gray-800 text-gray-300 transition-all hover:bg-gray-800 active:scale-95">
        <i class="fa-solid fa-chart-pie text-purple-400"></i>
        <span class="text-[11px] font-bold uppercase">التقارير</span>
    </a>
</div>
        </div>
    </div>
</nav>

<script>
    window.Echo.private('user.{{ $auth->id }}')
        .listen('.new-notification', (e) => {
            const badge = document.querySelector('[data-notif-badge]');
            if (badge) {
                let current = parseInt(badge.innerText || '0');
                badge.innerText = current + 1;
                badge.classList.remove('hidden');
            }
            const list = document.querySelector('[data-notif-list]');
            if (list) {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 hover:bg-gray-800/70 border-b border-gray-800/40 cursor-pointer text-gray-200 animate-pulse';
                item.innerHTML = `<div class="text-xs font-bold">${e.title}</div><div class="text-[10px] text-gray-500 mt-1">${e.message}</div>`;
                list.prepend(item);
            }
        });
</script>