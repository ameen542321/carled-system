@extends('dashboard.app')
@section('title', 'متجر  — ' . $store->name)
@section('content')

<div class="max-w-7xl mx-auto py-8">

    {{-- شريط العنوان والأزرار --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-white">
                إدارة المتجر — {{ $store->name }}
            </h1>
            
           
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
          
            
            {{-- زر التعديل --}}
            <a href="{{ route('user.stores.edit', $store->id) }}"
               class="bg-blue-900/30 hover:bg-blue-800 text-blue-400 hover:text-white 
                      border border-blue-800 px-4 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-edit mr-2"></i> تعديل المتجر
            </a>
            
            {{-- زر تعيين كمتجر حالي --}}
           
            
            {{-- زر الرجوع --}}
            <a href="{{ route('user.stores.index') }}"
               class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
                ← الرجوع للمتاجر
            </a>
        </div>
    </div>

    {{-- معلومات المتجر --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-4 mb-4">
                    @if($store->logo)
                        <img src="{{ Storage::url($store->logo) }}" 
                             alt="{{ $store->name }}"
                             class="w-16 h-16 rounded-lg object-cover border border-gray-700">
                    @else
                        <div class="w-16 h-16 bg-gray-800 rounded-lg flex items-center justify-center border border-gray-700">
                            <i class="fa-solid fa-store text-2xl text-gray-500"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $store->name }}</h1>
                        
                        <p class="text-gray-400 mt-2">{{ $store->description ?? 'لا يوجد وصف' }} {{-- حالة المتجر --}}
            <span class="px-3 py-1 rounded-full text-xs font-bold 
                {{ $store->status == 'active' ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' }}">
                {{ $store->status == 'active' ? 'نشط' : 'معطل' }}
            </span></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-800">
                    <div>
                        <p class="text-gray-500 text-sm">رقم المتجر</p>
                        <p class="text-white font-mono text-lg">#{{ str_pad($store->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">تاريخ الإنشاء</p>
                        <p class="text-white">{{ $store->created_at->translatedFormat('Y/m/d') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">آخر تحديث</p>
                        <p class="text-white">{{ $store->updated_at->translatedFormat('Y/m/d') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">الورديات</p>
                        <p class="text-white">{{ $store->number_of_shifts }} وردية</p>
                    </div>
                </div>
            </div>
            
            {{-- معلومات الاتصال --}}
            <div class="bg-gray-800/30 border border-gray-700 rounded-lg p-4 md:w-80">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-blue-400"></i>
                    معلومات الاتصال
                </h3>
                <div class="space-y-3">
                    @if($store->phone)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-gray-500 w-5"></i>
                        <span class="text-gray-300">{{ $store->phone }}</span>
                    </div>
                    @endif
                    
                    @if($store->address)
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-location-dot text-gray-500 w-5 mt-1"></i>
                        <span class="text-gray-300">{{ $store->address }}</span>
                    </div>
                    @endif
                    
                    @if($store->tax_number)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-gray-500 w-5"></i>
                        <span class="text-gray-300">الرقم الضريبي: {{ $store->tax_number }}</span>
                    </div>
                    @endif
                    
                    @if($store->commercial_registration)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-gray-500 w-5"></i>
                        <span class="text-gray-300">السجل: {{ $store->commercial_registration }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- تنبيهات النظام - يتم التحقق من البيانات الفعلية --}}
    @php
        $hasIssues = false;
        $issues = [];
        
        // التحقق من عدد الموظفين (من جدول employees)
        $employeesCount = \App\Models\Employee::where('store_id', $store->id)->count();
        if($employeesCount == 0) {
            $hasIssues = true;
            $issues[] = 'لا يوجد موظفين في المتجر';
        }
        
        // التحقق من عدد المنتجات (من جدول products)
        $productsCount = \App\Models\Product::where('store_id', $store->id)->count();
        if($productsCount == 0) {
            $hasIssues = true;
            $issues[] = 'لم يتم إضافة أي منتجات بعد';
        }
        
        // التحقق من حالة المتجر
        if($store->status == 'suspended') {
            $hasIssues = true;
            $issues[] = 'المتجر غير مفعل حالياً' . ($store->suspension_reason ? ': ' . $store->suspension_reason : '');
        }
        
        // التحقق من عدم وجود محاسبين (اختياري)
        $accountantsCount = \App\Models\Accountant::where('store_id', $store->id)->count();
        if($accountantsCount == 0) {
            $issues[] = 'لا يوجد محاسبين (اختياري)';
        }
    @endphp

    @if($hasIssues)
    <div class="mb-6">
        <div class="bg-yellow-900/20 border border-yellow-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-exclamation-triangle text-yellow-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-bold mb-2">تنبيهات النظام</h4>
                    <ul class="text-yellow-300 text-sm space-y-1">
                        @foreach($issues as $issue)
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle text-[6px]"></i>
                            {{ $issue }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

  {{-- البطاقات الرئيسية --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">

    {{-- المحاسبين --}}
    <a href="{{ route('user.stores.accountants.index', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:bg-gray-800 transition group hover:border-blue-500">
        <div class="flex items-center justify-between mb-3">
            <i class="fa-solid fa-user-tie text-blue-400 text-2xl"></i>
            <i class="fa-solid fa-arrow-left text-gray-500 group-hover:text-blue-400 transition"></i>
        </div>
        <h4 class="text-white font-bold">المحاسبين</h4>
        <p class="text-gray-400 text-sm mt-1">إدارة محاسبي المتجر</p>

        <div class="mt-3 text-blue-400 font-bold text-lg">
            {{ $accountantsCount }} محاسب
        </div>
    </a>

    {{-- الموظفين --}}
    <a href="{{ route('user.stores.employees.index', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:bg-gray-800 transition group hover:border-green-500">
        <div class="flex items-center justify-between mb-3">
            <i class="fa-solid fa-users text-green-400 text-2xl"></i>
            <i class="fa-solid fa-arrow-left text-gray-500 group-hover:text-green-400 transition"></i>
        </div>
        <h4 class="text-white font-bold">الموظفين</h4>
        <p class="text-gray-400 text-sm mt-1">إدارة موظفي المتجر</p>

        <div class="mt-3 text-green-400 font-bold text-lg">
            {{ $employeesCount }} موظف
        </div>
    </a>

    {{-- المبيعات --}}
    <a href=""
       class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:bg-gray-800 transition group hover:border-yellow-500">
        <div class="flex items-center justify-between mb-3">
            <i class="fa-solid fa-chart-line text-yellow-400 text-2xl"></i>
            <i class="fa-solid fa-arrow-left text-gray-500 group-hover:text-yellow-400 transition"></i>
        </div>
        <h4 class="text-white font-bold">المبيعات</h4>
        <p class="text-gray-400 text-sm mt-1">عرض وإدارة المبيعات</p>

        <div class="mt-3 text-yellow-400 font-bold text-lg">
            {{ $invoicesCount ?? 0 }} عملية
        </div>
    </a>

    {{-- الأقسام والمنتجات --}}
    <a href="{{ route('user.stores.catalog', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:bg-gray-800 transition group hover:border-orange-500">
        <div class="flex items-center justify-between mb-3">
            <i class="fa-solid fa-layer-group text-orange-400 text-2xl"></i>
            <i class="fa-solid fa-arrow-left text-gray-500 group-hover:text-orange-400 transition"></i>
        </div>
        <h4 class="text-white font-bold">الأقسام والمنتجات</h4>
        <p class="text-gray-400 text-sm mt-1">إدارة المنتجات والأقسام</p>

        <div class="mt-3 text-orange-400 font-bold text-sm">
            {{ $categoriesCount }} قسم — {{ $productsCount }} منتج
        </div>
    </a>

    {{-- الإعدادات --}}
    <a href=""
       class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:bg-gray-800 transition group hover:border-purple-500">
        <div class="flex items-center justify-between mb-3">
            <i class="fa-solid fa-gear text-purple-400 text-2xl"></i>
            <i class="fa-solid fa-arrow-left text-gray-500 group-hover:text-purple-400 transition"></i>
        </div>
        <h4 class="text-white font-bold">إعدادات المتجر</h4>
        <p class="text-gray-400 text-sm mt-1">تخصيص وإعدادات متقدمة</p>
        <div class="mt-3 text-purple-400 font-bold text-sm">
            {{ $store->number_of_shifts }} وردية
        </div>
    </a>

</div>

    {{-- البطاقات الإحصائية --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <div class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:border-blue-500 transition group">
            <p class="text-gray-400 text-sm">مبيعات اليوم</p>
            <h2 class="text-2xl font-bold text-blue-400 mt-2">{{ number_format($todaySales ?? 0, 2) }} ر.س</h2>
            <div class="mt-2 flex items-center text-xs text-gray-500">
                <i class="fa-solid fa-calendar-day mr-1"></i>
                {{ now()->translatedFormat('Y/m/d') }}
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:border-green-500 transition group">
            <p class="text-gray-400 text-sm">مبيعات الشهر</p>
            <h2 class="text-2xl font-bold text-green-400 mt-2">{{ number_format($monthSales ?? 0, 2) }} ر.س</h2>
            <div class="mt-2 flex items-center text-xs text-gray-500">
                <i class="fa-solid fa-calendar mr-1"></i>
                {{ now()->translatedFormat('Y/m') }}
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:border-yellow-500 transition group">
            <p class="text-gray-400 text-sm">عدد الفواتير</p>
            <h2 class="text-2xl font-bold text-yellow-400 mt-2">{{ $invoicesCount ?? 0 }}</h2>
            <div class="mt-2 flex items-center text-xs text-gray-500">
                <i class="fa-solid fa-receipt mr-1"></i>
                إجمالي الفواتير
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 p-5 rounded-xl hover:border-purple-500 transition group">
            <p class="text-gray-400 text-sm">إجمالي الأرباح</p>
            <h2 class="text-2xl font-bold text-purple-400 mt-2">{{ number_format($totalProfit ?? 0, 2) }} ر.س</h2>
            <div class="mt-2 flex items-center text-xs text-gray-500">
                <i class="fa-solid fa-chart-pie mr-1"></i>
                صافي الربح
            </div>
        </div>

    </div>

    {{-- الرسوم البيانية والإحصائيات --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        
        {{-- مبيعات آخر 7 أيام --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-white font-bold">مبيعات آخر 7 أيام</h3>
                    <p class="text-gray-400 text-xs mt-1">إجمالي المبيعات اليومية</p>
                </div>
                @if(isset($chartData) && count($chartData) > 0)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">
                        الإجمالي: {{ number_format(array_sum($chartData), 2) }} ر.س
                    </span>
                </div>
                @endif
            </div>
            
            @if(isset($chartData) && count($chartData) > 0 && array_sum($chartData) > 0)
                <div class="h-64">
                    <div id="salesChartContainer" class="h-full">
                        <div class="h-full flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                            <span class="text-gray-500 text-sm">جاري تحميل الرسم البياني...</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="h-64 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-chart-line text-2xl text-gray-500"></i>
                    </div>
                    <p class="text-gray-400 mb-2">لا توجد بيانات مبيعات للعرض</p>
                    <p class="text-gray-500 text-sm">ابدأ بإضافة مبيعات لتظهر الإحصائيات هنا</p>
                    @if(isset($employeesCount) && $employeesCount > 0 && isset($productsCount) && $productsCount > 0)
                    <a href="{{ route('user.stores.sales.create', $store) }}" 
                       class="mt-4 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        بدء عملية بيع
                    </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- أفضل المنتجات مبيعاً --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-white font-bold">أفضل المنتجات مبيعاً</h3>
                    <p class="text-gray-400 text-xs mt-1">في آخر 30 يوم</p>
                </div>
                @if(isset($topProducts) && $topProducts->count() > 0)
                <span class="text-xs text-gray-500">
                    {{ $topProducts->sum('total_sold') }} وحدة مباعة
                </span>
                @endif
            </div>
            
            @if(isset($topProducts) && $topProducts->count() > 0)
                <div class="h-64 overflow-y-auto">
                    <div class="space-y-3">
                        @foreach($topProducts as $index => $product)
                            <div class="flex items-center justify-between p-3 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 flex items-center justify-center bg-blue-500/20 text-blue-400 text-xs rounded-full">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="max-w-[70%]">
                                        <p class="text-white text-sm font-medium truncate">{{ $product->name }}</p>
                                        <p class="text-gray-400 text-xs">
                                            {{ number_format($product->price ?? 0, 2) }} ر.س
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-green-400 font-bold">{{ $product->total_sold ?? 0 }}</p>
                                    <p class="text-gray-400 text-xs">وحدة</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="h-64 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-box text-2xl text-gray-500"></i>
                    </div>
                    <p class="text-gray-400 mb-2">لا توجد بيانات مبيعات للمنتجات</p>
                    <p class="text-gray-500 text-sm">ابدأ ببيع منتجاتك لتظهر الإحصائيات</p>
                    @if(isset($productsCount) && $productsCount > 0 && isset($employeesCount) && $employeesCount > 0)
                    <a href="{{ route('user.stores.sales.create', $store) }}" 
                       class="mt-4 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        بدء عملية بيع
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- آخر العمليات --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-2xl mb-10">
        <div class="p-5 border-b border-slate-800 bg-slate-900/50 flex items-center justify-between">
            <h3 class="text-white font-bold flex items-center gap-2">
                <span class="w-1.5 h-5 bg-blue-500 rounded-full"></span>
                آخر العمليات المسجلة
            </h3>
            
            @if(isset($operations) && $operations->count() > 0)
            <a href=""
               class="text-blue-400 hover:text-blue-300 text-sm flex items-center gap-1 transition">
                <span>عرض الكل</span>
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right min-w-[700px]">
                <thead class="bg-slate-950/50 text-slate-500 text-[11px] font-black uppercase tracking-widest">
                    <tr>
                        <th class="p-4">نوع العملية</th>
                        <th class="p-4">الفاعل</th>
                        <th class="p-4">العنصر</th>
                        <th class="p-4">التفاصيل</th>
                        <th class="p-4">الوقت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($operations ?? [] as $op)
                    @php
                        // منطق حماية الاسم
                        $actorName = 'نظام آلي';
                        if(!empty($op->actor_type) && class_exists($op->actor_type)) {
                            $actor = $op->actor_type::find($op->actor_id);
                            $actorName = $actor ? $actor->name : 'غير معروف';
                        } elseif($op->user) {
                            $actorName = $op->user->name;
                        }
                        
                        $details = is_array($op->details) ? $op->details : json_decode($op->details, true);
                        
                        // تلوين العملية
                        $actionColors = [
                            'create' => 'bg-green-900/20 text-green-400 border-green-800',
                            'update' => 'bg-blue-900/20 text-blue-400 border-blue-800',
                            'delete' => 'bg-red-900/20 text-red-400 border-red-800',
                            'restore' => 'bg-yellow-900/20 text-yellow-400 border-yellow-800',
                            'login' => 'bg-purple-900/20 text-purple-400 border-purple-800',
                            'sale' => 'bg-emerald-900/20 text-emerald-400 border-emerald-800',
                            'set_current' => 'bg-indigo-900/20 text-indigo-400 border-indigo-800',
                            'status_change' => 'bg-orange-900/20 text-orange-400 border-orange-800',
                        ];
                        $actionIcons = [
                            'create' => 'fa-plus',
                            'update' => 'fa-edit',
                            'delete' => 'fa-trash',
                            'restore' => 'fa-rotate-left',
                            'login' => 'fa-sign-in',
                            'sale' => 'fa-credit-card',
                            'set_current' => 'fa-check',
                            'status_change' => 'fa-exchange-alt',
                        ];
                        $color = $actionColors[$op->action] ?? 'bg-gray-800 text-gray-300 border-gray-700';
                        $icon = $actionIcons[$op->action] ?? 'fa-circle';
                        $actionNames = [
                            'create' => 'إضافة',
                            'update' => 'تعديل',
                            'delete' => 'حذف',
                            'restore' => 'استعادة',
                            'login' => 'تسجيل دخول',
                            'sale' => 'بيع',
                            'set_current' => 'تعيين',
                            'status_change' => 'تغيير حالة',
                        ];
                        $actionText = $actionNames[$op->action] ?? $op->action;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors text-sm">
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid {{ $icon }} text-xs"></i>
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black border {{ $color }}">
                                    {{ $actionText }}
                                </span>
                            </div>
                        </td>
                        <td class="p-4 text-white font-medium">{{ $actorName }}</td>
                        <td class="p-4 text-slate-400 italic">
                            @if($op->model_type == 'App\Models\Store')
                                متجر
                            @elseif($op->model_type == 'App\Models\Product')
                                منتج
                            @elseif($op->model_type == 'App\Models\Employee')
                                موظف
                            @else
                                #{{ $op->model_id }}
                            @endif
                        </td>
                        <td class="p-4 text-[10px] text-slate-500">
                            @if(!empty($details) && is_iterable($details))
                                @foreach($details as $k => $v) 
                                    @if(!is_array($v))
                                        {{ $k }}: {{ $v }}
                                        @if(!$loop->last) • @endif
                                    @endif
                                @endforeach
                            @elseif($op->description)
                                {{ Str::limit($op->description, 50) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-4 text-slate-500 text-xs whitespace-nowrap">{{ $op->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-16 text-center text-slate-600 italic tracking-widest text-sm">
                            لا توجد سجلات حالياً
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- JavaScript لإدارة الرسوم البيانية --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // بيانات الرسم البياني
    const chartLabels = @json($chartLabels ?? []);
    const chartData = @json($chartData ?? []);
    
    // إنشاء الرسم البياني إذا كانت هناك بيانات
    if (chartData && chartData.length > 0 && chartData.some(value => value > 0)) {
        setTimeout(() => {
            const ctx = document.createElement('canvas');
            document.getElementById('salesChartContainer').innerHTML = '';
            document.getElementById('salesChartContainer').appendChild(ctx);
            
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'المبيعات (ر.س)',
                        data: chartData,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#d1d5db',
                            borderColor: 'rgb(75, 85, 99)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return `المبلغ: ${context.parsed.y.toLocaleString()} ر.س`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(75, 85, 99, 0.2)'
                            },
                            ticks: {
                                color: '#9ca3af'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(75, 85, 99, 0.2)'
                            },
                            ticks: {
                                color: '#9ca3af',
                                callback: function(value) {
                                    return value.toLocaleString() + ' ر.س';
                                }
                            }
                        }
                    }
                }
            });
        }, 500);
    }
    
    // تأثيرات تفاعلية للبطاقات
    const cards = document.querySelectorAll('.bg-gray-900.border');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.transition = 'transform 0.2s ease, border-color 0.2s ease';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endpush

@endsection