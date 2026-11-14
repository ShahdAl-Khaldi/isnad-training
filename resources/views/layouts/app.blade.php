<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
</head>
<body @if (!empty($confetti)) data-confetti @endif>
{{-- تنسيق الهيدر واللوقو الـ 3D هون مباشرة (ما بيعتمد على ملف التصميم) --}}
<style>
    /* الهيدر فوق الصفحة، فالكلام بيطلع لفوق جنب اللوقو */
    .bare-head { position: relative; z-index: 20; padding: 22px 0 0; }
    .bare-head.float { position: absolute; top: 0; inset-inline: 0; pointer-events: none; }
    .bare-head.float a, .bare-head.float button { pointer-events: auto; }
    .bare-head .bare-in { display: flex !important; flex-direction: row !important; align-items: flex-start !important; justify-content: space-between !important; gap: 12px; }
    .bare-head .logo-stack { display: flex !important; flex-direction: column !important; align-items: flex-end !important; gap: 12px; margin-inline-start: auto; }
    .bare-head.float + main .apply-page { padding-top: 0; }
    .bare-head.float + main .apply-head { margin-top: 30px; padding-inline-end: 260px; }

    /* اللوقو الـ 3D */
    .live-logo { position: relative; display: inline-block; line-height: 0; animation: none !important; --tx: 0deg; --ty: 0deg; }
    .logo-3d { display: inline-block; perspective: 900px; transform: perspective(900px) rotateX(var(--tx)) rotateY(var(--ty)); transform-style: preserve-3d; transition: transform .9s cubic-bezier(.2, .8, .2, 1); }
    .logo-sway { position: relative; display: inline-block; transform-style: preserve-3d; animation: logoSway 9s ease-in-out infinite alternate; }
    .live-logo img {
        height: 96px !important; width: auto; display: block;
        filter: var(--logo-3d) !important;
        transition: transform .5s cubic-bezier(.3, 1.4, .5, 1);
    }
    :root { --logo-3d: drop-shadow(1px 1px 0 #0B2C77) drop-shadow(1px 1px 0 #0A2665) drop-shadow(1px 1px 0 #081F53) drop-shadow(1px 1px 0 #061842) drop-shadow(3px 10px 14px rgba(11, 26, 58, .35)); }
    :root[data-theme="dark"] { --logo-3d: brightness(0) invert(1) drop-shadow(1px 1px 0 #D9E0EE) drop-shadow(1px 1px 0 #B8C3DA) drop-shadow(1px 1px 0 #96A4C2) drop-shadow(1px 1px 0 #F26B21) drop-shadow(3px 12px 16px rgba(0, 0, 0, .55)); }
    .live-logo .logo-shine { position: absolute; inset: 0; pointer-events: none;
        -webkit-mask: var(--logo) center / contain no-repeat; mask: var(--logo) center / contain no-repeat;
        background: linear-gradient(110deg, transparent 0%, transparent 38%, #FFD3B6 46%, #F26B21 50%, #FFD3B6 54%, transparent 62%, transparent 100%);
        background-size: 300% 100%; background-position: 120% 0; animation: logoShine3d 7s 2s ease-in-out infinite; }
    .logo-floor { position: absolute; left: 12%; right: 12%; bottom: -16px; height: 14px; border-radius: 50%; pointer-events: none;
        background: radial-gradient(closest-side, rgba(242, 107, 33, .35), transparent); filter: blur(4px); animation: floorBreath 9s ease-in-out infinite alternate; }
    :root:not([data-theme="dark"]) .logo-floor { background: radial-gradient(closest-side, rgba(18, 40, 90, .25), transparent); }
    @keyframes logoSway {
        0%   { transform: rotateY(-18deg) rotateX(6deg) translateY(0); }
        50%  { transform: rotateY(4deg) rotateX(-2deg) translateY(-7px); }
        100% { transform: rotateY(18deg) rotateX(5deg) translateY(-2px); }
    }
    /* دخلة اللوقو (بتنكتب) بدون ما تقص ظل الـ 3D بعد ما تخلص */
    .intro .live-logo img { animation: logoWrite3d 1.2s .15s cubic-bezier(.65, 0, .35, 1) backwards !important; }
    @keyframes logoWrite3d { from { clip-path: inset(-40px -40px -40px 100%); opacity: .2; } to { clip-path: inset(-40px); opacity: 1; } }
    @keyframes floorBreath { 0% { transform: scaleX(1); opacity: .9; } 50% { transform: scaleX(.8); opacity: .55; } 100% { transform: scaleX(.95); opacity: .85; } }
    @keyframes logoShine3d { 0% { background-position: 120% 0; } 30%, 100% { background-position: -20% 0; } }

    @media (max-width: 760px) {
        .bare-head.float { position: relative; pointer-events: auto; }
        .bare-head.float + main .apply-head { padding-inline-end: 0; margin-top: 8px; }
        .live-logo img { height: 60px !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .logo-sway, .logo-floor, .live-logo .logo-shine, .intro .live-logo img { animation: none !important; }
        .logo-3d { transition: none; }
    }
</style>
<header class="bare-head {{ request()->routeIs('apply', 'apply.done') ? 'intro float' : '' }}">
    <div class="container bare-in">
        {{-- يمين: زر التسجيل بالصفحات التانية بس --}}
        <div>
            @unless (request()->routeIs('apply', 'apply.done'))
                <a href="{{ route('apply') }}" class="btn btn-primary btn-sm"><span class="label">سجّل الآن</span></a>
            @endunless
        </div>
        {{-- يسار: زر الليلي فوق، واللوقو الـ 3D تحته --}}
        <div class="logo-stack">
            <button class="theme-btn" type="button" data-theme-toggle aria-label="تبديل الوضع الفاتح والغامق">
                <span class="sun">@include('partials.icon', ['n' => 'sun', 'size' => 18])</span>
                <span class="moon">@include('partials.icon', ['n' => 'moon', 'size' => 18])</span>
            </button>
            <a class="brand-logo live-logo" href="{{ route('apply') }}" aria-label="إسناد - الرئيسية">
                <span class="logo-3d"><span class="logo-sway">
                    <img src="{{ asset('images/isnad-logo.png') }}" alt="إسناد ISNAD">
                    <span class="logo-shine" aria-hidden="true" style="--logo: url('{{ asset('images/isnad-logo.png') }}')"></span>
                </span></span>
                <span class="logo-floor" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</header>
<script>
    // اللوقو بيميل بهدوء ناحية الماوس
    (function () {
        var logo = document.querySelector('.live-logo');
        if (!logo || !window.matchMedia('(hover: hover) and (pointer: fine)').matches || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        var raf;
        window.addEventListener('pointermove', function (e) {
            cancelAnimationFrame(raf);
            raf = requestAnimationFrame(function () {
                var r = logo.getBoundingClientRect(), cx = r.left + r.width / 2, cy = r.top + r.height / 2;
                var dx = Math.max(-1, Math.min(1, (e.clientX - cx) / 600)), dy = Math.max(-1, Math.min(1, (e.clientY - cy) / 400));
                logo.style.setProperty('--ty', (dx * 14).toFixed(2) + 'deg');
                logo.style.setProperty('--tx', (-dy * 10).toFixed(2) + 'deg');
            });
        }, { passive: true });
    })();
</script>

<main>
    @yield('content')
</main>

<footer class="site-foot">
    <div class="container row between">
        <span class="muted small">© {{ date('Y') }} إسناد · التدريب الميداني
            · <a class="foot-admin" href="{{ url('/admin') }}">@include('partials.icon', ['n' => 'user', 'size' => 14]) دخول الإدارة</a></span>
        <img src="{{ asset('images/isnad-logo.png') }}" alt="إسناد">
        <style>
            .foot-admin { display: inline-flex; align-items: center; gap: 5px; color: inherit; text-decoration: none; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--line); margin-inline-start: 4px; transition: color .2s, border-color .2s, background .2s; }
            .foot-admin:hover { color: #E0874F; border-color: #E0874F; background: rgba(224, 135, 79, .08); }
        </style>
    </div>
</footer>

@include('partials.toasts')
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
