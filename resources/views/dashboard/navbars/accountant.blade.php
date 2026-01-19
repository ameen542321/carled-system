@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\Notification;

    $auth = Auth::guard('accountant')->user();

    /** * تحسين الاستعلام: جلب الإشعارات الموجهة لهذا المحاسب فقط من قاعدة البيانات
     * لضمان عدم ظهور القائمة فارغة إذا كانت آخر الإشعارات العامة ليست له.
     */
    $latestNotifications = Notification::where(function ($query) use ($auth) {
            $query->where('target_type', 'all')
                  ->orWhereJsonContains('target_ids', (string)$auth->id);
        })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // العداد الذكي
    $unreadCount = Notification::unreadCountFor($auth->id);
@endphp

<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50"
     x-data="{ openMenu: false, openUser: false, openNotif: false }">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <div class="flex items-center gap-6">
                {{-- زر القائمة للجوال --}}
                <button @click="openMenu = !openMenu" class="lg:hidden text-gray-300 hover:text-white transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                {{-- شعار CARLED: تم وضعه هنا ليكون أول ما تقع عليه العين في القراءة العربية --}}
                <a href="{{ route('accountant.dashboard') }}" class="flex items-center gap-2 group">
                    <div class="relative">
                        <div class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_8px_#3b82f6] group-hover:scale-125 transition-transform"></div>
                        <div class="absolute inset-0 w-3 h-3 rounded-full bg-blue-400 animate-ping opacity-20"></div>
                    </div>
                    <span class="text-white font-black text-xl tracking-wider uppercase">Car<span class="text-blue-500">led</span></span>
                </a>
            </div>

            <div class="flex items-center gap-4">


                {{-- الإشعارات --}}
                <div class="relative">
                    <button
                        @click="openNotif = !openNotif; openUser = false"
                        class="p-2 text-gray-400 hover:text-white relative transition rounded-full hover:bg-gray-800"
                    >
                        <i class="fa-regular fa-bell text-xl"></i>
                        @if($unreadCount > 0)
                            <span class="absolute top-1.5 right-1.5 bg-red-600 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center rounded-full border-2 border-gray-900 animate-bounce">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>

                    {{-- قائمة الإشعارات المنسدلة --}}
                    <div
                        x-show="openNotif"
                        @click.outside="openNotif = false"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="absolute left-0 mt-3 w-80 bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden z-50"
                    >
                        <div class="p-4 border-b border-gray-800 flex justify-between items-center">
                            <h4 class="text-white font-bold">الإشعارات</h4>
                            <span class="text-xs bg-gray-800 text-gray-400 px-2 py-1 rounded-md">{{ $unreadCount }} جديدة</span>
                        </div>

                        <div class="max-h-[400px] overflow-y-auto custom-scroll">
                            @forelse($latestNotifications as $n)
                                <a href="{{ route('accountant.notifications.show', $n->id) }}"
                                   class="flex items-start gap-3 p-4 border-b border-gray-800/50 hover:bg-gray-800/40 transition {{ $n->isReadBy($auth->id) ? 'opacity-60' : 'border-r-4 border-r-blue-500 bg-blue-500/5' }}">
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex-shrink-0 flex items-center justify-center text-blue-500">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-200 font-medium leading-tight">{{ $n->title }}</p>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $n->message }}</p>
                                        <span class="text-[10px] text-gray-600 mt-2 block">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="p-10 text-center">
                                    <i class="fa-solid fa-bell-slash text-gray-700 text-3xl mb-3 block"></i>
                                    <p class="text-gray-500 text-sm">لا توجد إشعارات حالياً</p>
                                </div>
                            @endforelse
                        </div>

                        <a href="{{ route('accountant.notifications.index') }}" class="block p-3 text-center text-xs font-bold text-blue-500 hover:bg-gray-800 transition">
                            عرض الكل
                        </a>
                    </div>
                </div>

                {{-- بروفايل المستخدم --}}
                <div class="relative">
                    <button @click="openUser = !openUser; openNotif = false" class="flex items-center gap-2 p-1 pr-3 hover:bg-gray-800 rounded-full transition border border-transparent hover:border-gray-700">
                       <div class="text-right hidden sm:block">
    <p class="text-xs font-bold text-white leading-none">{{ $auth->name }}</p>
    {{-- إظهار اسم المتجر المرتبط --}}
    <p class="text-[10px] text-blue-400 mt-1 font-medium">
        <i class="fa-solid fa-store text-[9px] mr-1"></i>
        {{ $auth->store->name ?? 'محاسب النظام' }}
    </p>
</div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($auth->name) }}&background=0D8ABC&color=fff" class="w-8 h-8 rounded-full shadow-inner">
                    </button>

                    <div x-show="openUser" @click.outside="openUser = false" x-cloak x-transition class="absolute left-0 mt-3 w-52 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-2 z-50">
                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition">
                            <i class="fa-solid fa-id-card text-gray-500"></i> الملف الشخصي
                        </a>
                        <div class="border-t border-gray-800 my-2"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition">
                                <i class="fa-solid fa-power-off"></i> تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- قائمة الجوال --}}
    <div x-show="openMenu" x-cloak class="lg:hidden border-t border-gray-800 bg-gray-900/95 p-4 space-y-2">
        <a href="#" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600 text-white font-bold"><i class="fa-solid fa-cash-register"></i> نقطة البيع</a>
        <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-300 hover:bg-gray-800"><i class="fa-solid fa-file-invoice-dollar"></i> الفواتير</a>
    </div>
</nav>
