<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head', ['noindex' => true])
</head>
<body>
@php
    $me = auth()->user();
    $hour = (int) now()->format('G');
    $greet = $hour < 12 ? 'صباح الخير' : ($hour < 18 ? 'مساء الخير' : 'مساء النور');
    $links = [
        ['admin.dashboard', 'admin.dashboard,admin.applications.*', 'grid', 'الطلبات', $navCounts['payments'] ?? 0],
        ['admin.settings', 'admin.settings', 'settings', 'الإعدادات', 0],
        ['admin.profile', 'admin.profile', 'user', 'ملفي الشخصي', 0],
    ];
@endphp
<div class="admin-shell">
    <aside class="sidebar" aria-label="لوحة إسناد">
        <a class="brand-logo" href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/isnad-logo.png') }}" alt="إسناد ISNAD"></a>
        <span class="side-label">لوحة التدريب الميداني</span>
        <nav class="side-nav">
            @foreach ($links as [$route, $match, $icon, $label, $count])
                <a href="{{ route($route) }}" class="{{ request()->routeIs(...explode(',', $match)) ? 'on' : '' }}">
                    @include('partials.icon', ['n' => $icon]) {{ $label }}
                    @if ($count)<span class="count" title="دفعات بحاجة تأكيد">{{ $count }}</span>@endif
                </a>
            @endforeach
            <a href="{{ route('apply') }}" target="_blank" rel="noopener">@include('partials.icon', ['n' => 'eye']) شوف الموقع</a>
        </nav>

        <div class="stack" style="margin-top:auto;gap:10px">
            <div class="row between" style="padding:0 6px">
                <span class="tiny muted">الوضع</span>
                <button class="theme-btn" type="button" data-theme-toggle aria-label="تبديل الوضع الفاتح والغامق" style="width:38px;height:38px">
                    <span class="sun">@include('partials.icon', ['n' => 'sun', 'size' => 16])</span><span class="moon">@include('partials.icon', ['n' => 'moon', 'size' => 16])</span>
                </button>
            </div>
            <a class="me-card" href="{{ route('admin.profile') }}">
                @if ($me->avatarUrl())<img class="avatar" src="{{ $me->avatarUrl() }}" alt="">@else<span class="avatar">{{ $me->initial() }}</span>@endif
                <span class="who"><b>{{ $me->name }}</b><span>{{ $me->job_title ?: 'إدارة إسناد' }}</span></span>
            </a>
            <form method="post" action="{{ route('logout') }}">@csrf
                <button class="btn btn-ghost btn-sm btn-block" type="submit" data-no-loading>@include('partials.icon', ['n' => 'logout', 'size' => 16]) خروج</button>
            </form>
        </div>
    </aside>

    <div style="flex:1;min-width:0">
        <div class="mobile-bar">
            <img class="logo" src="{{ asset('images/isnad-logo.png') }}" alt="إسناد">
            <div class="row" style="gap:8px">
                <button class="theme-btn" type="button" data-theme-toggle aria-label="تبديل الوضع" style="width:38px;height:38px">
                    <span class="sun">@include('partials.icon', ['n' => 'sun', 'size' => 16])</span><span class="moon">@include('partials.icon', ['n' => 'moon', 'size' => 16])</span>
                </button>
                <a href="{{ route('admin.profile') }}" aria-label="ملفي الشخصي">
                    @if ($me->avatarUrl())<img class="avatar avatar-sm" src="{{ $me->avatarUrl() }}" alt="">@else<span class="avatar avatar-sm">{{ $me->initial() }}</span>@endif
                </a>
            </div>
        </div>
        <nav class="mobile-nav" aria-label="القائمة">
            @foreach ($links as [$route, $match, $icon, $label, $count])
                <a href="{{ route($route) }}" class="{{ request()->routeIs(...explode(',', $match)) ? 'on' : '' }}">{{ $label }}@if ($count) ({{ $count }})@endif</a>
            @endforeach
            <form method="post" action="{{ route('logout') }}" class="inline">@csrf<button class="btn btn-sm" type="submit" data-no-loading>خروج</button></form>
        </nav>

        <main class="admin-main">
            <div class="admin-top fade-up">
                <div>
                    <span class="greet">{{ $greet }}، {{ $me->firstName() }} 👋</span>
                    <h1>@yield('heading')</h1>
                </div>
                <div class="row">@yield('actions')</div>
            </div>
            <div class="stack-lg">
                @if ($errors->any())
                    <div class="alert alert-error">@include('partials.icon', ['n' => 'alert']) <span>{{ $errors->first() }}</span></div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</div>

<div class="lightbox" id="lightbox" role="dialog" aria-label="صورة التحويل">
    <button class="icon-btn" type="button" aria-label="إغلاق">@include('partials.icon', ['n' => 'x'])</button>
    <img alt="صورة التحويل">
</div>

@include('partials.toasts')
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
