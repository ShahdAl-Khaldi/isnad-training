@extends('layouts.admin')
@section('title', 'ملفي الشخصي')
@section('heading', 'ملفي الشخصي')

@section('content')
<style>
    /* ===== الملف الشخصي: كرت جانبي + أقسام ===== */
    .pf-layout { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 24px; align-items: stretch; }
    .pf-main { display: grid; gap: 24px; min-width: 0; align-content: start; }

    /* الكرت الجانبي: بطول الأقسام كلها */
    .pf-side { padding: 0 !important; overflow: hidden; text-align: center; display: flex; flex-direction: column; }
    .pf-info { margin: 18px 16px 20px; display: grid; gap: 8px; text-align: right; }
    .pf-info div { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 14px; background: var(--surface-2); border: 1px solid var(--line); transition: border-color .25s, transform .25s; }
    .pf-info div:hover { border-color: color-mix(in srgb, var(--accent) 40%, var(--line)); transform: translateX(-3px); }
    .pf-info .ic { flex: none; width: 32px; height: 32px; border-radius: 10px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; }
    .pf-info small { display: block; color: var(--muted); font-size: 11.5px; }
    .pf-info b { display: block; font-size: 13.5px; }
    .pf-side .pf-hint { margin-top: auto; padding-top: 16px; }
    .pf-cover { height: 110px; position: relative; overflow: hidden;
        background: linear-gradient(120deg, #0B2C77, #13235A 45%, #F26B21); background-size: 220% 220%; animation: pfCover 14s ease-in-out infinite alternate; }
    .pf-cover::after { content: ""; position: absolute; inset: 0; background: radial-gradient(circle at 20% 120%, rgba(255, 255, 255, .18), transparent 45%), radial-gradient(circle at 85% -20%, rgba(255, 255, 255, .12), transparent 40%); }
    @keyframes pfCover { to { background-position: 100% 50%; } }

    .pf-ava { position: relative; width: 116px; height: 116px; margin: -58px auto 0; animation: pfPop .7s .15s cubic-bezier(.3, 1.5, .5, 1) backwards; }
    @keyframes pfPop { from { transform: scale(.4); opacity: 0; } }
    .pf-ava .ring { position: absolute; inset: -5px; border-radius: 50%; padding: 3px; pointer-events: none;
        background: conic-gradient(from var(--pa, 0deg), var(--accent), transparent 35%, color-mix(in srgb, var(--accent) 55%, transparent) 60%, transparent 85%, var(--accent));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; animation: pfSpin 7s linear infinite; }
    @property --pa { syntax: '<angle>'; inherits: false; initial-value: 0deg; }
    @keyframes pfSpin { to { --pa: 360deg; } }
    .pf-ava .avatar { width: 116px !important; height: 116px !important; font-size: 40px !important; box-shadow: 0 0 0 4px var(--surface), 0 16px 34px -14px var(--accent) !important; transition: transform .4s cubic-bezier(.3, 1.5, .5, 1); }
    .pf-ava:hover .avatar { transform: scale(1.04); }
    .pf-ava:hover .ring { animation-duration: 2.5s; }
    .pf-cam { position: absolute; bottom: 2px; inset-inline-end: 2px; width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer;
        box-shadow: 0 0 0 3px var(--surface), 0 6px 14px -4px var(--accent); transition: transform .3s cubic-bezier(.3, 1.6, .5, 1); }
    .pf-cam:hover { transform: scale(1.12) rotate(-10deg); }
    .pf-file { position: absolute; opacity: 0; width: 1px; height: 1px; pointer-events: none; }

    .pf-who { padding: 14px 20px 0; }
    .pf-who b { display: block; font-family: var(--font-display); font-size: 21px; line-height: 1.3; }
    .pf-who small { color: var(--muted); font-size: 13px; }
    .pf-tags { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; margin: 12px 16px 0; }
    .pf-tags span { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 999px; background: var(--surface-2); border: 1px solid var(--line); color: var(--muted); }
    .pf-tags .on { color: var(--green); background: var(--green-soft); border-color: transparent; }
    .pf-tags .on i { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .pf-acts { display: flex; justify-content: center; gap: 8px; margin: 16px 16px 0; flex-wrap: wrap; }
    .pf-acts .btn-cam { background: var(--accent); color: #fff; border-color: var(--accent); box-shadow: 0 10px 24px -12px var(--accent); }
    .pf-acts .btn-cam:hover { filter: brightness(1.06); }
    .pf-hint { margin: 12px 18px 20px; color: var(--muted); font-size: 12px; line-height: 1.7; }

    /* الأقسام */
    .pf-card { animation: pfIn .55s cubic-bezier(.2, .8, .2, 1) backwards; animation-delay: calc(var(--i, 0) * 110ms + 150ms); }
    @keyframes pfIn { from { opacity: 0; transform: translateY(14px); } }
    .pf-head { display: flex; align-items: center; gap: 12px; }
    .pf-head .ic { flex: none; width: 40px; height: 40px; border-radius: 12px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; transition: transform .4s cubic-bezier(.3, 1.6, .5, 1); }
    .pf-card:hover .pf-head .ic { transform: rotate(-10deg) scale(1.06); }
    .pf-head h2 { margin: 0; font-size: 19px; }
    .pf-head p { margin: 2px 0 0; color: var(--muted); font-size: 13px; }
    .pf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .pf-grid .wide { grid-column: 1 / -1; }
    .pf-foot { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .pf-foot .btn-save { background: var(--accent); color: #fff; border-color: var(--accent); box-shadow: 0 10px 24px -12px var(--accent); }
    .pf-foot .note { color: var(--muted); font-size: 12.5px; display: none; align-items: center; gap: 7px; }
    .pf-foot .note i { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); animation: pfBlink 1.4s ease-in-out infinite; }
    form.dirty .pf-foot .note { display: inline-flex; }
    @keyframes pfBlink { 50% { opacity: .35; } }

    /* كلمة السر: إظهار + قوة */
    .pf-pw { position: relative; }
    .pf-pw .input { padding-inline-end: 44px; }
    .pf-eye { position: absolute; inset-inline-end: 8px; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; border-radius: 9px; border: 0; background: transparent; color: var(--muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .2s, color .2s; }
    .pf-eye:hover { background: var(--surface-2); color: var(--text); }
    .pf-eye.on { color: var(--accent); }
    .pf-meter { display: flex; gap: 4px; margin-top: 8px; }
    .pf-meter i { flex: 1; height: 4px; border-radius: 4px; background: var(--line); transition: background .3s; }
    .pf-meter[data-s="1"] i:nth-child(-n+1) { background: var(--red); }
    .pf-meter[data-s="2"] i:nth-child(-n+2) { background: var(--accent); }
    .pf-meter[data-s="3"] i:nth-child(-n+3) { background: #E2B714; }
    .pf-meter[data-s="4"] i { background: var(--green); }
    .pf-strength { font-size: 12px; color: var(--muted); margin-top: 4px; min-height: 16px; }
    .pf-match { font-size: 12px; margin-top: 6px; min-height: 16px; }
    .pf-match.ok { color: var(--green); }
    .pf-match.no { color: var(--red); }

    @media (max-width: 1100px) { .pf-layout { grid-template-columns: 1fr; } .pf-side { position: static; } }
    @media (max-width: 640px) { .pf-grid { grid-template-columns: 1fr; } }
    @media (prefers-reduced-motion: reduce) { .pf-cover, .pf-ava, .pf-ava .ring, .pf-card, .pf-foot .note i { animation: none !important; } }
</style>

<div class="pf-layout">
    {{-- ===== الكرت الجانبي ===== --}}
    <aside class="card pf-side fade-up">
        <div class="pf-cover" aria-hidden="true"></div>
        <form method="post" action="{{ route('admin.profile.avatar') }}" enctype="multipart/form-data" id="avatar-form" data-no-loading>
            @csrf
            <div class="pf-ava">
                <span class="ring" aria-hidden="true"></span>
                @if ($user->avatarUrl())
                    <img id="avatar-preview" class="avatar avatar-xl" src="{{ $user->avatarUrl() }}" alt="صورتك">
                @else
                    <span id="avatar-preview" class="avatar avatar-xl">{{ $user->initial() }}</span>
                @endif
                <label class="pf-cam" for="avatar" title="غيّر الصورة">@include('partials.icon', ['n' => 'camera', 'size' => 17])<span class="sr-only">غيّر الصورة</span></label>
            </div>
            <input class="pf-file" id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" data-avatar-input="#avatar-preview" data-autosubmit>
        </form>
        @error('avatar')<span class="error" style="justify-content:center;margin-top:8px">{{ $message }}</span>@enderror

        <div class="pf-who">
            <b data-live-name>{{ $user->name }}</b>
            <small class="ltr">{{ $user->email }}</small>
        </div>
        <div class="pf-tags">
            <span class="on"><i></i> متّصل</span>
            <span data-live-job>{{ $user->job_title ?: 'مدير اللوحة' }}</span>
            <span>منذ {{ $user->created_at?->format('Y') ?? now()->format('Y') }}</span>
        </div>
        <div class="pf-acts">
            <label class="btn btn-sm btn-cam" for="avatar">@include('partials.icon', ['n' => 'camera', 'size' => 16]) غيّر الصورة</label>
            @if ($user->avatar_path)
                <form method="post" action="{{ route('admin.profile.avatar.remove') }}" class="inline" onsubmit="return confirm('أكيد بدك تحذف الصورة؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm" type="submit">احذفها</button>
                </form>
            @endif
        </div>
        <div class="pf-info">
            <div><span class="ic">@include('partials.icon', ['n' => 'award', 'size' => 16])</span><span><small>الصلاحية</small><b>مدير لوحة إسناد</b></span></div>
            <div><span class="ic">@include('partials.icon', ['n' => 'calendar', 'size' => 16])</span><span><small>تاريخ الانضمام</small><b>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</b></span></div>
            <div><span class="ic">@include('partials.icon', ['n' => 'clock', 'size' => 16])</span><span><small>آخر تعديل على الحساب</small><b>{{ $user->updated_at?->diffForHumans() ?? '—' }}</b></span></div>
        </div>
    </aside>

    {{-- ===== الأقسام ===== --}}
    <div class="pf-main">
        <section class="card stack pf-card" style="--i:0">
            <div class="pf-head">
                <span class="ic">@include('partials.icon', ['n' => 'user', 'size' => 20])</span>
                <div><h2>بياناتي</h2><p>هيك بيظهر اسمك باللوحة</p></div>
            </div>
            <form method="post" action="{{ route('admin.profile.update') }}" class="stack" data-pf-dirty>
                @csrf
                <div class="pf-grid">
                    <div class="field">
                        <label for="name">الاسم</label>
                        <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required data-live="name">
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="job_title">المسمى</label>
                        <input class="input" id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="مثلاً: منسّقة التدريب" data-live="job">
                        @error('job_title')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field wide">
                        <label for="email">الإيميل</label>
                        <input class="input" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" dir="ltr" style="text-align:right" required>
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-foot">
                    <button class="btn btn-save" type="submit"><span class="label">احفظ</span></button>
                    <span class="note"><i></i> في تعديلات ما انحفظت</span>
                </div>
            </form>
        </section>

        <section class="card stack pf-card" style="--i:1">
            <div class="pf-head">
                <span class="ic">@include('partials.icon', ['n' => 'settings', 'size' => 20])</span>
                <div><h2>كلمة السر</h2><p>8 أحرف على الأقل</p></div>
            </div>
            <form method="post" action="{{ route('admin.password') }}" class="stack" data-pf-dirty>
                @csrf
                <div class="pf-grid">
                    <div class="field">
                        <label for="current_password">الحالية</label>
                        <div class="pf-pw">
                            <input class="input" id="current_password" type="password" name="current_password" required autocomplete="current-password">
                            <button class="pf-eye" type="button" data-eye aria-label="أظهر كلمة السر">@include('partials.icon', ['n' => 'eye', 'size' => 17])</button>
                        </div>
                        @error('current_password')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="password">الجديدة</label>
                        <div class="pf-pw">
                            <input class="input" id="password" type="password" name="password" required minlength="8" autocomplete="new-password" data-pw-new>
                            <button class="pf-eye" type="button" data-eye aria-label="أظهر كلمة السر">@include('partials.icon', ['n' => 'eye', 'size' => 17])</button>
                        </div>
                        <div class="pf-meter" data-meter aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                        <div class="pf-strength" data-strength></div>
                        @error('password')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-foot">
                    <button class="btn" type="submit"><span class="label">غيّر كلمة السر</span></button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
(function () {
    // الاسم والمسمى بيتحدّثوا بالكرت الجانبي وإنت بتكتب
    var liveName = document.querySelector('[data-live-name]'), liveJob = document.querySelector('[data-live-job]');
    document.querySelectorAll('[data-live]').forEach(function (inp) {
        inp.addEventListener('input', function () {
            if (inp.dataset.live === 'name' && liveName) liveName.textContent = inp.value.trim() || '—';
            if (inp.dataset.live === 'job' && liveJob) liveJob.textContent = inp.value.trim() || 'مدير اللوحة';
        });
    });

    // تنبيه التعديلات اللي ما انحفظت
    document.querySelectorAll('[data-pf-dirty]').forEach(function (f) {
        f.addEventListener('input', function () { f.classList.add('dirty'); });
    });

    // إظهار/إخفاء كلمة السر
    document.querySelectorAll('[data-eye]').forEach(function (b) {
        b.addEventListener('click', function () {
            var inp = b.parentNode.querySelector('input'), show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            b.classList.toggle('on', show);
            b.setAttribute('aria-label', show ? 'خبّي كلمة السر' : 'أظهر كلمة السر');
        });
    });

    // قوة كلمة السر
    var pw = document.querySelector('[data-pw-new]');
    var meter = document.querySelector('[data-meter]'), label = document.querySelector('[data-strength]');
    var names = ['', 'ضعيفة', 'مقبولة', 'منيحة', 'قوية 💪'];
    function check() {
        var v = pw.value, s = 0;
        if (v.length >= 8) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/\d/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
        if (v && s === 0) s = 1;
        meter.dataset.s = v ? s : 0;
        label.textContent = v ? names[s] : '';
    }
    if (pw) pw.addEventListener('input', check);
})();
</script>
@endsection
