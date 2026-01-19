<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js + Collapse --}}
   {{-- Alpine.js + Collapse --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>


    {{-- AlpineJS --}}

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

<style>
    /* تطبيق الخط العربي على كامل الصفحة */
    body {
        font-family: 'Cairo', sans-serif;
    }

    /* منع ظهور العناصر قبل تحميل الجافاسكريبت (الحل لمشكلة الرمش) */
    [x-cloak] { 
        display: none !important; 
    }

    /* تحسين شكل التمرير (Scrollbar) ليتناسب مع الثيم المظلم */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #0f172a;
    }
    ::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
</style>
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer" />

   <title>@yield('title', 'CARLED Dashboard')</title>


    {{-- Tailwind + Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak]{display:none!important}</style>



{{-- <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script>
    window.OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
        OneSignal.init({
            appId: "{{ $settings->app_id }}",
        });
    });
</script> --}}

</head>

<body class="bg-gray-900 text-gray-100 min-h-screen flex">

    {{-- ========================= --}}
    {{--        SIDEBAR           --}}
    {{-- ========================= --}}
   {{-- Sidebar حسب الدور --}}
@if(auth('accountant')->check())



@elseif(auth('web')->check())

    {{-- هنا لدينا نوعان داخل نفس الجدول: admin أو user --}}
    @php
        $role = auth('web')->user()->role ?? 'user';
    @endphp

    @if($role === 'admin')
        @include('dashboard.sidebars.admin')
    @else
        {{-- @include('dashboard.sidebars.user') --}}
    @endif

@endif




    <div class="flex-1 flex flex-col">

        {{-- ========================= --}}
        {{--          NAVBAR          --}}
        {{-- ========================= --}}
        <header>
          {{-- ========================= --}}
{{--         NAVBAR            --}}
{{-- ========================= --}}

@if(auth('accountant')->check())

    {{-- المحاسب --}}
    @include('dashboard.navbars.accountant')

@elseif(auth('web')->check())

    {{-- هنا لدينا نوعان داخل نفس جدول users: admin أو user --}}
    @php
        $role = auth('web')->user()->role ?? 'user';
    @endphp

    @if($role === 'admin')
        @include('dashboard.navbars.admin')
    @else
        @include('dashboard.navbars.user')
    @endif

@endif

        </header>
{{-- في layouts/app.blade.php أو أي مكان مناسب --}}
@if(session('subscription_warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('subscription_warning.message') }}
        
        @if(session('subscription_warning.days_left') <= 3)
            <a href="{{ route('user.subscription') }}" class="alert-link">
                تجديد الاشتراك الآن
            </a>
        @endif
        
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
        {{-- ========================= --}}
        {{--         CONTENT           --}}
        {{-- ========================= --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        {{-- ========================= --}}
        {{--          FOOTER           --}}
        {{-- ========================= --}}
        <footer class="mt-6">
            @include('dashboard.footer')
        </footer>

    </div>
    <script>
    @if (session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    @if (session('error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif
</script>


{{-- <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script>
    window.OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
        OneSignal.init({
            appId: "{{ config('services.onesignal.app_id') }}",
            allowLocalhostAsSecureOrigin: true,
        });

        OneSignal.on('subscriptionChange', function (isSubscribed) {
            if (isSubscribed) {
                OneSignal.getUserId(function(playerId) {
                    if (playerId) {
                        fetch("{{ route('device.token.store') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                token: playerId
                            })
                        });
                    }
                });
            }
        });
    });
</script> --}}
@yield('scripts')

</body>
</html>
