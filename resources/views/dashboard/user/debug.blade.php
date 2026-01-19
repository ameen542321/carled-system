{{-- @extends('layouts.auth')

@section('content')

<div class="max-w-4xl mx-auto bg-gray-900 border border-gray-700 rounded-xl p-8 mt-10 text-gray-200">

    <h1 class="text-3xl font-bold mb-6">🔍 Debug Panel</h1>

    {{-- ========================= --}}
    {{-- SECTION: Guards Status --}}
    {{-- ========================= --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-3">🛡️ Guards Status</h2>

        <div class="space-y-2 text-sm">
            <p><strong>web authenticated:</strong> {{ auth()->guard('web')->check() ? 'YES' : 'NO' }}</p>
            <p><strong>accountant authenticated:</strong> {{ auth()->guard('accountant')->check() ? 'YES' : 'NO' }}</p>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- SECTION: Current Users --}}
    {{-- ========================= --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-3">👤 Current User (per guard)</h2>

        <div class="grid grid-cols-2 gap-6 text-sm">

            {{-- web user --}}
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="font-semibold mb-2">Guard: web</h3>
                @if(auth()->guard('web')->check())
                    <pre class="text-xs whitespace-pre-wrap">{{ json_encode(auth()->guard('web')->user(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <p class="text-gray-400">No user authenticated</p>
                @endif
            </div>

            {{-- accountant user --}}
            <div class="bg-gray-800 p-4 rounded border border-gray-700">
                <h3 class="font-semibold mb-2">Guard: accountant</h3>
                @if(auth()->guard('accountant')->check())
                    <pre class="text-xs whitespace-pre-wrap">{{ json_encode(auth()->guard('accountant')->user(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <p class="text-gray-400">No accountant authenticated</p>
                @endif
            </div>

        </div>
    </div>

    {{-- ========================= --}}
    {{-- SECTION: Session Dump --}}
    {{-- ========================= --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-3">📦 Session Dump</h2>
        <pre class="bg-gray-800 p-4 rounded border border-gray-700 text-xs whitespace-pre-wrap">
{{ json_encode(session()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>

    {{-- ========================= --}}
    {{-- SECTION: Request Dump --}}
    {{-- ========================= --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-3">📨 Request Dump</h2>
        <pre class="bg-gray-800 p-4 rounded border border-gray-700 text-xs whitespace-pre-wrap">
{{ json_encode(request()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>

    {{-- ========================= --}}
    {{-- SECTION: Routes --}}
    {{-- ========================= --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-3">🗺️ Current Route</h2>
        <pre class="bg-gray-800 p-4 rounded border border-gray-700 text-xs whitespace-pre-wrap">
{{ json_encode([
    'name' => request()->route()?->getName(),
    'uri'  => request()->route()?->uri(),
    'action' => request()->route()?->getActionName(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>

</div>

@endsection
