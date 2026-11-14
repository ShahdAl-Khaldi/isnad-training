<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head', ['noindex' => true])
    <style>
        /* صفحة دخول الإدارة: كارد بنص الشاشة، صورة المختبر راسه والفورم تحتها */
        .login-wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px 16px;
            background: radial-gradient(900px 600px at 50% 0%, color-mix(in srgb, var(--brand) 22%, transparent), transparent 60%), var(--bg); }
        :root[data-theme="dark"] .login-wrap { background: radial-gradient(900px 600px at 50% 0%, rgba(27, 55, 112, .55), transparent 60%), var(--bg); }
        .login-box { width: min(100%, 500px); background: var(--surface); border: 1px solid var(--line); border-radius: 30px; overflow: hidden;
            box-shadow: 0 40px 80px -40px rgba(5, 12, 31, .55); animation: login-up .8s cubic-bezier(.2, .8, .2, 1) both; }
        .login-hero { position: relative; height: 210px; overflow: hidden; }
        .login-hero .ph { position: absolute; inset: 0; background: url('{{ asset('images/isnad-lab.jpg') }}') center 40% / cover; animation: login-kb 16s ease-in-out infinite alternate; }
        .login-hero::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(5, 12, 31, .2), var(--surface)); }
        .login-hero .cap { position: absolute; inset: auto 26px 16px 26px; z-index: 1; display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; }
        .login-hero h2 { margin: 0; font-size: 23px; font-weight: 800; color: var(--text); }
        .login-hero .cap img { height: 44px; filter: var(--logo-filter); }
        .login-hero .theme-btn { position: absolute; top: 14px; left: 14px; z-index: 2; background: rgba(11, 23, 51, .55); color: #fff; border-color: rgba(255, 255, 255, .25); backdrop-filter: blur(8px); }
        .login-body { padding: 18px 30px 26px; display: flex; flex-direction: column; gap: 18px; }
        .login-body > * { animation: login-up .8s cubic-bezier(.2, .8, .2, 1) both; }
        .login-body > *:nth-child(2) { animation-delay: .12s; } .login-body > *:nth-child(3) { animation-delay: .24s; }
        .login-body h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .btn-login { height: 56px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #E8955F, #C9784A); color: #fff; font: inherit; font-weight: 800; font-size: 17px;
            cursor: pointer; box-shadow: 0 14px 30px -14px #E0874F; transition: transform .2s, box-shadow .2s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 18px 34px -14px #E0874F; }
        .login-body .input:focus { border-color: #E0874F; box-shadow: 0 0 0 4px rgba(224, 135, 79, .15); }
        .login-body .switch input:checked + .tr { background: #E0874F; }
        @keyframes login-kb { from { transform: scale(1.02); } to { transform: scale(1.12) translateX(-1.5%); } }
        @keyframes login-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @media (max-width: 560px) { .login-hero { height: 170px; } .login-body { padding: 14px 20px 22px; } .login-hero h2 { font-size: 19px; } }
        @media (prefers-reduced-motion: reduce) { .login-hero .ph, .login-box, .login-body > * { animation: none; } }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <div class="login-hero">
            <span class="ph" aria-hidden="true"></span>
            <button class="theme-btn" type="button" data-theme-toggle aria-label="تبديل الوضع الفاتح والغامق">
                <span class="sun">@include('partials.icon', ['n' => 'sun', 'size' => 18])</span><span class="moon">@include('partials.icon', ['n' => 'moon', 'size' => 18])</span>
            </button>
            <div class="cap">
                <h2>لوحة التدريب الميداني</h2>
                <img src="{{ asset('images/isnad-logo.png') }}" alt="إسناد">
            </div>
        </div>

        <div class="login-body">
            <div><h1>أهلاً من جديد 👋</h1><p class="muted" style="margin:4px 0 0">ادخل على لوحة إسناد.</p></div>
            <form method="post" action="{{ route('login.attempt') }}" class="stack">
                @csrf
                <div class="field">
                    <label for="email">الإيميل</label>
                    <div class="input-icon">@include('partials.icon', ['n' => 'user', 'size' => 18])
                        <input class="input @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" dir="ltr" style="text-align:right" required autofocus autocomplete="username"></div>
                    @error('email')<span class="error">@include('partials.icon', ['n' => 'alert', 'size' => 16]) {{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">كلمة السر</label>
                    <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <label class="switch small"><input type="checkbox" name="remember" value="1"><span class="tr"></span> خلّيني داخل</label>
                <button class="btn-login" type="submit">دخول</button>
            </form>
            <a class="small muted center" href="{{ route('apply') }}" style="text-decoration:none">→ رجوع للموقع</a>
        </div>
    </div>
</div>
@include('partials.toasts')
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
</body>
</html>
