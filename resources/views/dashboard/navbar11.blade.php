@php
    use Illuminate\Support\Facades\Auth;

    // جلب المستخدم من أي guard
    if (Auth::guard('accountant')->check()) {
        $auth = Auth::guard('accountant')->user();
    } else {
        $auth = Auth::guard('web')->user();
    }

    if (!$auth) {
        $auth = null;
    }

    // تحديد الخطة حسب نوع المستخدم
    if ($auth && $auth->role === 'accountant') {

        $owner  = $auth->user;
        $plan   = $owner?->plan;
        $userId = $owner?->id;

    } elseif ($auth && $auth->role === 'user') {

        $plan   = $auth->plan;
        $userId = $auth->id;

    } else {

        $plan   = null;
        $userId = null;
    }

    // حساب المتاجر والمحاسبين
    if ($plan && $userId) {

        $currentStores     = \App\Models\Store::where('user_id', $userId)->count();
        $remainingStores   = $plan->allowed_stores - $currentStores;

        $currentAccountants   = \App\Models\Accountant::where('user_id', $userId)->count();
        $allowedAccountants   = $plan->allowed_accountants;
        $remainingAccountants = $allowedAccountants - $currentAccountants;

    } else {

        $currentStores        = 0;
        $remainingStores      = 0;

        $currentAccountants   = 0;
        $allowedAccountants   = 0;
        $remainingAccountants = 0;
    }

    // عداد الإشعارات
    $unreadCount = $auth ? \App\Models\Notification::unreadCountFor($auth->id) : 0;

    // آخر الإشعارات
    $latestNotifications = $auth
        ? \App\Models\Notification::orderBy('created_at', 'desc')->take(5)->get()
            ->filter(function ($n) use ($auth) {
                if ($n->target_type === 'all') return true;
                if (in_array($auth->id, $n->target_ids ?? [])) return true;
                return false;
            })
        : collect([]);
@endphp


<nav class="bg-gray-900 border-b border-gray-800"
     x-data="{ openMenu: false, openUser: false, openNotif: false }">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- القسم الأيسر --}}
            <div class="flex items-center gap-4">

                {{-- زر الهامبرغر للجوال --}}
                <button
                    @click="openMenu = !openMenu"
                    class="lg:hidden text-gray-300 hover:text-white"
                >
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                {{-- الشعار --}}
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span class="text-white font-bold text-lg">Carled</span>
                </div>

            </div>

            {{-- القسم الأيمن --}}
            <div class="flex items-center gap-6">

                {{-- الإشعارات --}}
     <div class="relative">
    <button
        @click="openNotif = !openNotif; openUser = false"
        class="text-gray-300 hover:text-white relative"
    >
        <i class="fa-regular fa-bell text-xl"></i>

        @if($unreadCount > 0)
            <span data-notif-badge
                  class="absolute -top-1 -right-2 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full">
                {{ $unreadCount }}
            </span>
        @else
            <span data-notif-badge
                  class="hidden absolute -top-1 -right-2 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full">
                0
            </span>
        @endif
    </button>

    <div
        x-show="openNotif"
        @click.outside="openNotif = false"
        x-cloak
        class="absolute left-0 mt-3 w-72 bg-gray-800 border border-gray-700 rounded-lg shadow-lg py-3 z-50"
    >
        <h4 class="text-white font-semibold px-4 mb-2">الإشعارات</h4>

        <div data-notif-list>
            @forelse($latestNotifications as $n)
                <a href="{{ route('notifications.show', $n->id) }}"
                   class="block px-4 py-2 hover:bg-gray-700 cursor-pointer
                          {{ $n->isReadBy($auth?->id) ? 'text-gray-400' : 'text-gray-200 font-semibold' }}">
                    <div>{{ $n->title }}</div>
                    <div class="text-xs text-gray-400">{{ $n->message }}</div>
                </a>
            @empty
                <div class="px-4 py-2 text-gray-400 text-center">
                    لا توجد إشعارات
                </div>
            @endforelse
        </div>

        <div class="border-t border-gray-700 mt-2"></div>

        <a href="{{ route('notifications.index') }}"
           class="block px-4 py-2 text-blue-400 hover:text-blue-300 text-center">
            جميع الإشعارات
        </a>
    </div>
</div>



                {{-- المستخدم --}}
                <div class="relative">
                    <button
                        @click="openUser = !openUser; openNotif = false"
                        class="flex items-center gap-2"
                    >
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode($auth?->name ?? 'User') }}"
                            class="w-9 h-9 rounded-full border border-gray-700"
                        >
                        <i class="fa-solid fa-chevron-down text-gray-400 text-sm"></i>
                    </button>

                    <div
                        x-show="openUser"
                        @click.outside="openUser = false"
                        x-cloak
                        class="absolute left-0 mt-3 w-56 bg-gray-800 border border-gray-700 rounded-lg shadow-lg py-2 z-50"
                    >
                        <div class="px-4 py-2 flex items-center gap-3 text-gray-300 hover:bg-gray-700 cursor-pointer">
                            <i class="fa-solid fa-user text-gray-400"></i>
                            <span>الملف الشخصي</span>
                        </div>

                        <div class="px-4 py-2 flex items-center gap-3 text-gray-300 hover:bg-gray-700 cursor-pointer">
                            <i class="fa-solid fa-gear text-gray-400"></i>
                            <span>الإعدادات</span>
                        </div>

                        <div class="border-t border-gray-700 my-2"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-gray-700">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>تسجيل الخروج</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- قائمة الجوال --}}
    <div
        x-show="openMenu"
        x-cloak
        class="lg:hidden bg-gray-900 border-t border-gray-800 px-4 py-4 space-y-3"
    >

        {{-- المحاسب --}}
        @if($auth?->role === 'accountant')

            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition">
                <i class="fa-solid fa-cart-shopping text-blue-400 text-lg"></i>
                <span>بيع</span>
            </button>

        @endif

        {{-- المستخدم --}}
        @if($auth?->role === 'user')

            {{-- زر إضافة متجر أو ترقية الاشتراك --}}
            @if ($remainingStores > 0)
                <a href="{{ route('user.stores.create') }}"
                   class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-store text-blue-400 text-lg"></i>
                        <span>إضافة متجر</span>
                    </div>

                    <span class="text-xs text-gray-400">
                        {{ $remainingStores }} / {{ $plan->allowed_stores }}
                    </span>
                </a>
            @else
                <a href="#"
                   class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-yellow-600 hover:bg-yellow-500 text-gray-900 font-semibold transition">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-arrow-up text-lg"></i>
                        <span>ترقية الاشتراك</span>
                    </div>

                    <span class="text-xs text-gray-800 font-bold">
                        0 / {{ $plan->allowed_stores }}
                    </span>
                </a>
            @endif

            {{-- إضافة محاسب أو ترقية الاشتراك --}}
            @if ($remainingAccountants > 0)
                <a href="{{ route('user.accountants.create') }}"
                   class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user-tie text-green-400 text-lg"></i>
                        <span>إضافة محاسب</span>
                    </div>

                    <span class="text-xs text-gray-400">
                        {{ $remainingAccountants }} / {{ $allowedAccountants }}
                    </span>
                </a>
            @else
                <a href="#"
                   class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-yellow-600 hover:bg-yellow-500 text-gray-900 font-semibold transition">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-arrow-up text-lg"></i>
                        <span>ترقية الاشتراك</span>
                    </div>

                    <span class="text-xs text-gray-800 font-bold">
                        0 / {{ $allowedAccountants }}
                    </span>
                </a>
            @endif

        @endif

        {{-- الأدمن --}}
        @if($auth?->role === 'admin')

            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition">
                <i class="fa-solid fa-user-plus text-blue-400 text-lg"></i>
                <span>إضافة مستخدم</span>
            </button>

        @endif

    </div>

</nav>
<script>
    @if($auth)
    window.Echo.private('user.{{ $auth->id }}')
        .listen('.new-notification', (e) => {
            // تحديث العداد
            const badge = document.querySelector('[data-notif-badge]');
            if (badge) {
                let current = parseInt(badge.innerText || '0');
                badge.innerText = current + 1;
                badge.classList.remove('hidden');
            }

            // إضافة الإشعار لقائمة آخر الإشعارات (بشكل بسيط)
            const list = document.querySelector('[data-notif-list]');
            if (list) {
                const item = document.createElement('div');
                item.className = 'px-4 py-2 hover:bg-gray-700 cursor-pointer text-gray-200 font-semibold';
                item.innerHTML = `<div>${e.title}</div><div class="text-xs text-gray-400">${e.message}</div>`;
                list.prepend(item);
            }
        });
    @endif
</script>
