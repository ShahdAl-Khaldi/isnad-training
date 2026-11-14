@extends('layouts.admin')
@section('title', 'الإعدادات')
@section('heading', 'الإعدادات')

@section('content')
<style>
    /* ===== طرق الدفع بلوحة الأدمن: بطاقات بهوية كل جهة ===== */
    .apm { --pm: var(--brand); --pm-ink: var(--brand-ink); --pm-soft: var(--brand-soft); --pm-line: color-mix(in srgb, var(--brand) 35%, var(--line)); }
    .apm.pm-bop { --pm: #8C1159; --pm-ink: #fff; --pm-soft: rgba(140, 17, 89, .08); --pm-line: rgba(140, 17, 89, .45); }
    .apm.pm-jawwal { --pm: #7DB534; --pm-ink: #fff; --pm-soft: rgba(125, 181, 52, .1); --pm-line: rgba(125, 181, 52, .5); }
    :root[data-theme="dark"] .apm.pm-bop { --pm: #D0347F; --pm-soft: rgba(208, 52, 127, .1); --pm-line: rgba(208, 52, 127, .5); }
    :root[data-theme="dark"] .apm.pm-jawwal { --pm: #96CA3D; --pm-ink: #142A05; --pm-soft: rgba(150, 202, 61, .09); --pm-line: rgba(150, 202, 61, .45); }

    .apm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; align-items: start; }
    .apm { position: relative; overflow: hidden; border-radius: 22px; border: 1.5px solid var(--line); padding: 20px;
        background: radial-gradient(120% 90% at 100% 0%, var(--pm-soft), transparent 60%), var(--surface-2);
        transition: transform .35s cubic-bezier(.2, .8, .2, 1), border-color .3s, box-shadow .35s, filter .3s;
        animation: apmIn .6s cubic-bezier(.2, .8, .2, 1) backwards; animation-delay: calc(var(--i, 0) * 90ms + 120ms); }
    .apm::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 4px; background: linear-gradient(90deg, transparent, var(--pm), transparent);
        transform: scaleX(.35); opacity: .6; transition: transform .5s cubic-bezier(.2, .8, .2, 1), opacity .3s; }
    .apm:hover { transform: translateY(-3px); border-color: var(--pm-line); box-shadow: 0 22px 40px -26px var(--pm); }
    .apm:hover::before, .apm:has(details[open])::before { transform: scaleX(1); opacity: 1; }
    .apm:has(details[open]) { border-color: var(--pm-line); }
    .apm.is-off { filter: saturate(.55); }
    .apm.is-off:hover { filter: none; }
    @keyframes apmIn { from { opacity: 0; transform: translateY(14px) scale(.98); } }

    .apm-top { display: flex; align-items: center; gap: 14px; }
    .apm-logo { position: relative; overflow: hidden; flex: none; width: 112px; height: 60px; border-radius: 16px; background: #fff;
        display: flex; align-items: center; justify-content: center; padding: 8px 10px; box-shadow: 0 8px 20px -12px var(--pm), inset 0 0 0 1px rgba(0, 0, 0, .05);
        transition: transform .4s cubic-bezier(.3, 1.4, .5, 1); }
    .apm-logo img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
    .apm-logo::after { content: ""; position: absolute; top: -20%; bottom: -20%; left: -60%; width: 40%; transform: skewX(-20deg);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .85), transparent); transition: left .7s ease; pointer-events: none; }
    .apm:hover .apm-logo { transform: scale(1.05) rotate(-2deg); }
    .apm:hover .apm-logo::after { left: 130%; }
    .apm.pm-jawwal .apm-logo { width: 60px; padding: 0; background: transparent; box-shadow: 0 10px 22px -10px var(--pm); }
    .apm.pm-jawwal .apm-logo img { border-radius: 16px; }
    .apm-logo.is-icon { width: 60px; background: var(--pm); color: var(--pm-ink); }

    .apm-id { min-width: 0; flex: 1; }
    .apm-id h3 { font-size: 18px; margin: 0; line-height: 1.3; }
    .apm-id small { color: var(--muted); font-size: 13px; }

    .apm-status { flex: none; display: inline-flex; align-items: center; gap: 7px; padding: 6px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 600;
        background: var(--green-soft); color: var(--green); }
    .apm-status i { width: 8px; height: 8px; border-radius: 50%; background: currentColor; position: relative; }
    .apm-status.on i::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: currentColor; animation: apmPulse 1.8s ease-out infinite; }
    .apm-status.off { background: var(--surface); color: var(--muted); border: 1px solid var(--line); }
    @keyframes apmPulse { to { transform: scale(2.8); opacity: 0; } }

    .apm-info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px; }
    .apm-cell { min-width: 0; display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 14px; background: var(--surface); border: 1px solid var(--line); transition: border-color .25s; }
    .apm-cell:hover { border-color: var(--pm-line); }
    .apm-cell .ic { flex: none; width: 34px; height: 34px; border-radius: 10px; background: var(--pm-soft); color: var(--pm); display: flex; align-items: center; justify-content: center; }
    .apm-cell .tx { min-width: 0; flex: 1; }
    .apm-cell small { display: block; color: var(--muted); font-size: 11.5px; }
    .apm-cell b { display: block; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .apm-cell b.ltr { text-align: right; }
    .apm-cell b.empty { color: var(--muted); font-weight: 500; font-size: 13.5px; }
    .apm-cp { flex: none; width: 32px; height: 32px; border-radius: 10px; border: 1px solid var(--line); background: transparent; color: var(--muted); cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s, color .2s, border-color .2s, transform .2s; }
    .apm-cp:hover { background: var(--pm); color: var(--pm-ink); border-color: var(--pm); }
    .apm-cp:active { transform: scale(.9); }
    .apm-cp.done { background: var(--green); color: #fff; border-color: var(--green); }
    .apm-cp .label { display: none; }

    .apm-edit { margin-top: 14px; border-top: 1px dashed var(--line); padding-top: 12px; }
    .apm-edit summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px;
        font-weight: 600; color: var(--text); transition: background .2s, color .2s; user-select: none; }
    .apm-edit summary::-webkit-details-marker { display: none; }
    .apm-edit summary:hover { background: var(--pm-soft); color: var(--pm); }
    .apm-edit summary .chev { margin-inline-start: auto; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: var(--surface); border: 1px solid var(--line); transition: transform .35s cubic-bezier(.3, 1.4, .5, 1), background .2s, color .2s; }
    .apm-edit[open] summary .chev { transform: rotate(45deg); background: var(--pm); color: var(--pm-ink); border-color: var(--pm); }
    .apm-edit[open] summary { color: var(--pm); }
    .apm-body { padding: 14px 4px 4px; animation: apmOpen .4s cubic-bezier(.2, .8, .2, 1); }
    @keyframes apmOpen { from { opacity: 0; transform: translateY(-8px); } }
    .apm-actions { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
    .apm .btn-save { background: var(--pm); color: var(--pm-ink); border-color: var(--pm); }

    /* أضف طريقة دفع */
    .apm-add { border-radius: 22px; border: 2px dashed var(--line); background: transparent; transition: border-color .3s, background .3s;
        animation: apmIn .6s cubic-bezier(.2, .8, .2, 1) backwards; animation-delay: calc(var(--i, 0) * 90ms + 120ms); }
    .apm-add:hover, .apm-add[open] { border-color: color-mix(in srgb, var(--brand) 45%, var(--line)); background: var(--surface-2); }
    .apm-add summary { list-style: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; padding: 18px; font-weight: 700; user-select: none; }
    .apm-add summary::-webkit-details-marker { display: none; }
    .apm-add summary .plus { width: 38px; height: 38px; border-radius: 12px; background: var(--brand-soft); color: var(--brand); display: flex; align-items: center; justify-content: center;
        transition: transform .4s cubic-bezier(.3, 1.4, .5, 1), background .2s, color .2s; }
    .apm-add:hover summary .plus { transform: rotate(90deg) scale(1.08); background: var(--brand); color: var(--brand-ink); }
    .apm-add[open] summary .plus { transform: rotate(45deg); background: var(--brand); color: var(--brand-ink); }
    .apm-add .apm-body { padding: 0 20px 20px; }

    @media (max-width: 560px) {
        .apm-grid { grid-template-columns: 1fr; }
        .apm-info { grid-template-columns: 1fr; }
        .apm-top { flex-wrap: wrap; }
    }
    @media (prefers-reduced-motion: reduce) {
        .apm, .apm-add, .apm-body, .apm-status.on i::after { animation: none !important; }
        .apm, .apm-logo, .apm::before, .apm-logo::after { transition: none; }
    }
</style>

<section class="card stack fade-up">
    <div class="row between">
        <div><h2>طرق الدفع</h2><p class="muted small">بتظهر للطالب بنموذج التسجيل مع خطوات التحويل. فعّل الطريقة لما تعبّي رقمها.</p></div>
    </div>

    <div class="apm-grid">
        @foreach ($methods as $m)
            <article class="apm pm-{{ $m->brand() ?? 'other' }} {{ $m->active ? '' : 'is-off' }}" style="--i: {{ $loop->index }}">
                <header class="apm-top">
                    @if ($m->logoUrl())
                        <span class="apm-logo"><img src="{{ $m->logoUrl() }}" alt="{{ $m->title }}" loading="lazy"></span>
                    @else
                        <span class="apm-logo is-icon">@include('partials.icon', ['n' => $m->type === 'wallet' ? 'wallet' : 'bank', 'size' => 24])</span>
                    @endif
                    <div class="apm-id"><h3>{{ $m->title }}</h3><small>{{ $m->subtitle() }}</small></div>
                    <span class="apm-status {{ $m->active ? 'on' : 'off' }}"><i></i>{{ $m->active ? 'ظاهرة للطلاب' : 'مخفية' }}</span>
                </header>

                <div class="apm-info">
                    <div class="apm-cell">
                        <span class="ic">@include('partials.icon', ['n' => 'phone', 'size' => 17])</span>
                        <span class="tx"><small>{{ $m->type === 'wallet' ? 'رقم المحفظة' : 'رقم الحساب' }}</small>
                            @if ($m->account_number)<b class="ltr">{{ $m->account_number }}</b>@else<b class="empty">ما في رقم لسا</b>@endif
                        </span>
                        @if ($m->account_number)
                            <button class="apm-cp" type="button" data-copy="{{ $m->account_number }}" title="نسخ">@include('partials.icon', ['n' => 'copy', 'size' => 14])<span class="label">نسخ</span></button>
                        @endif
                    </div>
                    <div class="apm-cell">
                        <span class="ic">@include('partials.icon', ['n' => 'user', 'size' => 17])</span>
                        <span class="tx"><small>اسم صاحب الحساب</small>
                            @if ($m->account_name)<b>{{ $m->account_name }}</b>@else<b class="empty">مش معبّى</b>@endif
                        </span>
                    </div>
                </div>

                <details class="apm-edit" @if (! $m->account_number) open @endif>
                    <summary>@include('partials.icon', ['n' => 'pen', 'size' => 16]) تعديل البيانات <span class="chev">@include('partials.icon', ['n' => 'plus', 'size' => 14])</span></summary>
                    <div class="apm-body">
                        <form method="post" action="{{ route('admin.methods.update', $m) }}" class="stack">
                            @csrf @method('PUT')
                            @include('admin.partials.method-fields', ['m' => $m])
                            <div class="apm-actions">
                                <button class="btn btn-save" type="submit"><span class="label">احفظ</span></button>
                                <button class="btn btn-danger btn-sm" type="submit" form="del-method-{{ $m->id }}" data-no-loading>@include('partials.icon', ['n' => 'trash', 'size' => 16]) حذف</button>
                            </div>
                        </form>
                        <form id="del-method-{{ $m->id }}" method="post" action="{{ route('admin.methods.destroy', $m) }}" onsubmit="return confirm('حذف {{ e($m->title) }}؟')" hidden>
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </details>
            </article>
        @endforeach
    </div>

    <details class="apm-add" style="--i: {{ $methods->count() }}">
        <summary><span class="plus">@include('partials.icon', ['n' => 'plus', 'size' => 20])</span> أضف طريقة دفع</summary>
        <div class="apm-body">
            <form method="post" action="{{ route('admin.methods.store') }}" class="stack">
                @csrf
                @include('admin.partials.method-fields', ['m' => null])
                <div><button class="btn btn-brand" type="submit"><span class="label">أضف</span></button></div>
            </form>
        </div>
    </details>
</section>

<style>
    /* ===== إعدادات التدريب: خفيفة ومرتبة ===== */
    .ts-form { gap: 18px; }
    .ts-num { font-variant-numeric: tabular-nums; }
    .ts-lbl { display: block; font-size: 13px; font-weight: 600; color: var(--muted); margin-bottom: 6px; }
    .ts-form input[type=number]::-webkit-outer-spin-button, .ts-form input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .ts-form input[type=number] { -moz-appearance: textfield; }

    /* سطر التسجيل */
    .ts-reg { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 14px; border: 1px solid var(--line); background: var(--surface-2); cursor: pointer; transition: border-color .3s; }
    .ts-reg .led { flex: none; width: 9px; height: 9px; border-radius: 50%; background: var(--red); position: relative; transition: background .3s; }
    .ts-reg:has(input:checked) { border-color: color-mix(in srgb, var(--green) 40%, var(--line)); }
    .ts-reg:has(input:checked) .led { background: var(--green); }
    .ts-reg:has(input:checked) .led::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: var(--green); animation: tsPulse 1.8s ease-out infinite; }
    @keyframes tsPulse { to { transform: scale(2.6); opacity: 0; } }
    .ts-reg b { font-size: 15px; }
    .ts-reg small { color: var(--muted); font-size: 12.5px; flex: 1; }
    .ts-reg .on-t, .ts-reg:has(input:checked) .off-t { display: none; }
    .ts-reg:has(input:checked) .on-t { display: inline; }
    .ts-reg .switch { pointer-events: none; }

    /* الرسوم والواتس بسطر واحد */
    .ts-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap; }

    /* الرسوم: دوائر */
    .ts-coins { display: flex; gap: 18px; }
    .ts-coin { display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: text;
        animation: tsCoinIn .6s cubic-bezier(.3, 1.4, .5, 1) backwards, tsFloat 4s ease-in-out infinite alternate; animation-delay: calc(var(--i) * 120ms + 100ms), calc(var(--i) * -2s); }
    .ts-coin .disc { position: relative; width: 84px; height: 84px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: radial-gradient(circle at 30% 25%, color-mix(in srgb, var(--accent) 16%, var(--surface-2)), var(--surface-2) 70%);
        transition: transform .35s cubic-bezier(.3, 1.5, .5, 1), box-shadow .3s; }
    .ts-coin .ring { position: absolute; inset: 0; border-radius: 50%; padding: 2px; pointer-events: none;
        background: conic-gradient(from var(--a, 0deg), var(--accent), transparent 35%, color-mix(in srgb, var(--accent) 50%, transparent) 60%, transparent 85%, var(--accent));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude;
        animation: tsSpin 6s linear infinite; }
    .ts-coin:hover .disc { transform: scale(1.07); }
    .ts-coin:hover .ring { animation-duration: 2s; }
    .ts-coin:focus-within .disc { transform: scale(1.07); box-shadow: 0 0 0 5px color-mix(in srgb, var(--accent) 15%, transparent), 0 12px 26px -12px var(--accent); }
    .ts-coin input { width: 64px; border: 0; background: transparent; color: var(--text); text-align: center; outline: none; padding: 0;
        font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; font-size: 24px; font-weight: 800; line-height: 1.1; }
    .ts-coin .cur { font-size: 12px; font-weight: 700; color: var(--accent); margin-top: -2px; }
    .ts-coin .hrs { font-size: 12px; font-weight: 600; color: var(--muted); padding: 2px 10px; border-radius: 999px; background: var(--surface-2); border: 1px solid var(--line); transition: color .25s, border-color .25s; }
    .ts-coin:focus-within .hrs, .ts-coin:hover .hrs { color: var(--accent); border-color: color-mix(in srgb, var(--accent) 40%, var(--line)); }
    .ts-coin .ctl { display: flex; align-items: center; gap: 6px; }
    .ts-coin .ctl button { width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--line); background: var(--surface-2); color: var(--muted); font-size: 15px; line-height: 1; cursor: pointer;
        display: flex; align-items: center; justify-content: center; padding: 0; transition: background .2s, color .2s, border-color .2s, transform .15s; }
    .ts-coin .ctl button:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
    .ts-coin .ctl button:active { transform: scale(.85); }
    .ts-coin.bump .disc { animation: tsBump .4s cubic-bezier(.3, 1.6, .5, 1); }
    @property --a { syntax: '<angle>'; inherits: false; initial-value: 0deg; }
    @keyframes tsSpin { to { --a: 360deg; } }
    @keyframes tsFloat { to { transform: translateY(-4px); } }
    @keyframes tsCoinIn { from { opacity: 0; transform: scale(.6); } }
    @keyframes tsBump { 40% { transform: scale(1.14); } }
    @media (prefers-reduced-motion: reduce) { .ts-coin, .ts-coin .ring, .ts-coin.bump .disc { animation: none !important; } }
    /* واتس إسناد: مربع بنفس حركة الدوائر */
    .ts-wa-f { display: flex; flex-direction: column; align-items: center; }
    .ts-wa-f .ts-lbl { align-self: stretch; }
    .ts-wa-f > .ts-wa, .ts-wa-f > .wa-chip { animation: tsCoinIn .6s .35s cubic-bezier(.3, 1.4, .5, 1) backwards; }
    .ts-wa { --wa: #25D366; position: relative; display: flex; align-items: center; gap: 12px; height: 84px; padding: 0 20px; border-radius: 22px; cursor: text;
        background: radial-gradient(circle at 25% 20%, rgba(37, 211, 102, .14), var(--surface-2) 70%);
        animation: tsFloat 4s -1s ease-in-out infinite alternate; transition: transform .35s cubic-bezier(.3, 1.5, .5, 1), box-shadow .3s; }
    .ts-wa .ring { position: absolute; inset: 0; border-radius: inherit; padding: 2px; pointer-events: none;
        background: conic-gradient(from var(--a, 0deg), var(--wa), transparent 35%, rgba(37, 211, 102, .5) 60%, transparent 85%, var(--wa));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude;
        animation: tsSpin 6s linear infinite; }
    .ts-wa:hover { transform: scale(1.04); }
    .ts-wa:hover .ring { animation-duration: 2s; }
    .ts-wa:focus-within { transform: scale(1.04); box-shadow: 0 0 0 5px rgba(37, 211, 102, .14), 0 12px 26px -12px var(--wa); }
    .ts-wa .wa-logo { flex: none; width: 40px; height: 40px; border-radius: 12px; background: rgba(37, 211, 102, .14); color: var(--wa); display: flex; align-items: center; justify-content: center;
        transition: transform .4s cubic-bezier(.3, 1.5, .5, 1), background .25s, color .25s; }
    .ts-wa:hover .wa-logo, .ts-wa:focus-within .wa-logo { transform: rotate(-12deg); background: var(--wa); color: #fff; }
    .ts-wa input { width: 150px; border: 0; background: transparent; color: var(--text); outline: none; padding: 0;
        font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; font-size: 17px; font-weight: 800; letter-spacing: .3px; }
    .ts-wa.bump { animation: tsFloat 4s -1s ease-in-out infinite alternate, tsBump .4s cubic-bezier(.3, 1.6, .5, 1); }
    .wa-chip { margin-top: 8px; font-size: 12px; font-weight: 600; color: var(--muted); padding: 2px 10px; border-radius: 999px; background: var(--surface-2); border: 1px solid var(--line); transition: color .25s, border-color .25s; }
    .ts-wa-f:hover .wa-chip, .ts-wa-f:focus-within .wa-chip { color: #25D366; border-color: rgba(37, 211, 102, .45); }
    @media (prefers-reduced-motion: reduce) { .ts-wa, .ts-wa .ring, .ts-wa-f > * { animation: none !important; } }
    @media (max-width: 640px) { .ts-row { justify-content: center; } }

    /* المقاعد */
    .ts-seats { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
    .ts-seat { --g: var(--green); position: relative; overflow: hidden; display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 18px; border: 1px solid var(--line);
        background: radial-gradient(120% 120% at 100% 0%, color-mix(in srgb, var(--g) 9%, transparent), transparent 60%), var(--surface-2);
        transition: border-color .3s, transform .35s cubic-bezier(.2, .8, .2, 1), box-shadow .35s;
        animation: tsSeatIn .55s cubic-bezier(.3, 1.3, .5, 1) backwards; animation-delay: calc(var(--i) * 70ms + 200ms); }
    .ts-seat.warn { --g: var(--accent); }
    .ts-seat.is-full { --g: var(--red); }
    .ts-seat:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--g) 45%, var(--line)); box-shadow: 0 16px 30px -22px var(--g); }
    @keyframes tsSeatIn { from { opacity: 0; transform: translateY(12px) scale(.96); } }

    /* عدّاد دائري */
    .ts-gauge { position: relative; flex: none; width: 68px; height: 68px; }
    .ts-gauge svg { position: absolute; inset: 0; transform: rotate(-90deg); transition: transform .6s cubic-bezier(.3, 1.4, .5, 1); }
    .ts-seat:hover .ts-gauge svg { transform: rotate(-90deg) scale(1.06); }
    .ts-gauge .trk { fill: none; stroke: var(--line); stroke-width: 5; }
    .ts-gauge .arc { fill: none; stroke: var(--g); stroke-width: 5; stroke-linecap: round; stroke-dasharray: 188.5; stroke-dashoffset: 188.5;
        transition: stroke-dashoffset 1s cubic-bezier(.2, .8, .2, 1), stroke .3s; }
    .ts-gauge .mid { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .ts-gauge input { width: 44px; border: 0; background: transparent; color: var(--text); text-align: center; outline: none; padding: 0;
        font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; font-size: 20px; font-weight: 800; line-height: 1; }
    .ts-gauge small { font-size: 10px; color: var(--muted); margin-top: 1px; }
    .ts-seat:focus-within .ts-gauge { filter: drop-shadow(0 0 8px color-mix(in srgb, var(--g) 45%, transparent)); }
    .ts-seat.bump .ts-gauge .mid { animation: tsBump .4s cubic-bezier(.3, 1.6, .5, 1); }

    .ts-seat .body { flex: 1; min-width: 0; display: grid; gap: 6px; }
    .ts-seat .hd { display: flex; align-items: center; gap: 7px; }
    .ts-seat .hd .ic { flex: none; width: 24px; height: 24px; border-radius: 7px; background: color-mix(in srgb, var(--g) 14%, transparent); color: var(--g); display: flex; align-items: center; justify-content: center; transition: transform .45s cubic-bezier(.3, 1.6, .5, 1), background .3s, color .3s; }
    .ts-seat:hover .hd .ic { transform: rotate(-12deg) scale(1.12); }
    .ts-seat .hd b { flex: 1; min-width: 0; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right; }
    .ts-seat .full { flex: none; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: var(--red-soft); color: var(--red); animation: tsBlink 1.6s ease-in-out infinite; }
    @keyframes tsBlink { 50% { opacity: .55; } }
    .ts-meta { font-size: 12px; color: var(--muted); }
    .ts-meta b { color: var(--text); }
    .ts-step { display: flex; gap: 6px; }
    .ts-step button { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--line); background: var(--surface); color: var(--text); font-size: 15px; line-height: 1; padding: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s, color .2s, border-color .2s, transform .15s; }
    .ts-step button:hover { background: var(--g); color: #fff; border-color: var(--g); }
    .ts-step button:active { transform: scale(.85); }
    @media (prefers-reduced-motion: reduce) { .ts-seat, .ts-seat .full, .ts-seat.bump .ts-gauge .mid { animation: none !important; } .ts-gauge .arc { transition: none; } }

    /* الحفظ */
    .ts-save { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; padding-top: 6px; border-top: 1px dashed var(--line); }
    .ts-save .hint { color: var(--muted); font-size: 13px; display: none; align-items: center; gap: 8px; }
    .ts-save .hint i { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); }
    .ts-form.dirty .ts-save .hint { display: inline-flex; }
    .ts-form.dirty .ts-save .btn { animation: tsNudge .5s cubic-bezier(.3, 1.6, .5, 1); box-shadow: 0 10px 26px -12px var(--accent); }
    @keyframes tsNudge { 40% { transform: scale(1.06); } }

    @media (prefers-reduced-motion: reduce) { .ts-seat, .ts-reg .dot::after, .ts-form.dirty .ts-save .btn { animation: none !important; } }
</style>

<section class="card stack fade-up" style="--d:1">
    <h2>إعدادات التدريب</h2>
    <form method="post" action="{{ route('admin.settings.update') }}" class="stack ts-form" data-ts-form>
        @csrf

        <div class="ts-row">
            <div>
                <span class="ts-lbl">الرسوم</span>
                <div class="ts-coins">
                    @foreach ($fees as $h => $f)
                        <label class="ts-coin" for="fee_{{ $h }}" style="--i: {{ $loop->index }}">
                            <span class="disc">
                                <span class="ring" aria-hidden="true"></span>
                                <input class="ts-num" id="fee_{{ $h }}" type="number" name="fees[{{ $h }}]" min="0" value="{{ old("fees.$h", $f) }}" required lang="en" aria-label="رسوم {{ $h }} ساعة">
                                <span class="cur">₪</span>
                            </span>
                            <span class="ctl">
                                <button type="button" data-fee-step="5" aria-label="زِد 5">+</button>
                                <span class="hrs">{{ $h }} ساعة</span>
                                <button type="button" data-fee-step="-5" aria-label="نقّص 5">−</button>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="ts-wa-f">
                <span class="ts-lbl">واتس إسناد</span>
                <label class="ts-wa" for="contact_whatsapp">
                    <span class="ring" aria-hidden="true"></span>
                    <span class="wa-logo"><svg viewBox="0 0 24 24" width="19" height="19" aria-hidden="true"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.4 9.4 0 0 1-1.44-5.01c0-5.2 4.23-9.43 9.43-9.43a9.37 9.37 0 0 1 9.42 9.44c0 5.2-4.23 9.42-9.43 9.42M20.07 3.92A11.27 11.27 0 0 0 12.05.6C5.8.6.71 5.68.71 11.93c0 2 .52 3.95 1.52 5.67L.62 23.4l5.94-1.56a11.3 11.3 0 0 0 5.48 1.4h.01c6.25 0 11.34-5.09 11.34-11.34 0-3.03-1.18-5.88-3.32-8.02"/></svg></span>
                    <input class="ts-num" id="contact_whatsapp" name="contact_whatsapp" value="{{ old('contact_whatsapp', $contact) }}" placeholder="+970599xxxxxx" dir="ltr" lang="en">
                </label>
                <span class="wa-chip">للاستفسارات</span>
            </div>
        </div>


        {{-- المقاعد --}}
        <div>
            <span class="ts-lbl">المقاعد لكل مسار</span>
            <div class="ts-seats">
                @foreach ($tracks as $key => $label)
                    @php $t = $taken[$key] ?? 0; @endphp
                    <div class="ts-seat" style="--i: {{ $loop->index }}" data-seat data-taken="{{ $t }}">
                        <label class="ts-gauge" for="seats_{{ $key }}">
                            <svg viewBox="0 0 68 68" aria-hidden="true"><circle class="trk" cx="34" cy="34" r="30"/><circle class="arc" cx="34" cy="34" r="30" data-arc/></svg>
                            <span class="mid">
                                <input class="ts-num" id="seats_{{ $key }}" type="number" min="0" max="1000" name="seats[{{ $key }}]" value="{{ old("seats.$key", $seats[$key]) }}" required lang="en" aria-label="مقاعد {{ $label }}">
                                <small>مقعد</small>
                            </span>
                        </label>
                        <div class="body">
                            <div class="hd">
                                <span class="ic">@include('partials.icon', ['n' => $trackMeta[$key]['icon'] ?? 'grid', 'size' => 13])</span>
                                <b dir="ltr">{{ $label }}</b>
                                <span class="full" data-full hidden>كامل</span>
                            </div>
                            <div class="ts-meta">محجوز <b class="ts-num">{{ $t }}</b> · فاضي <b class="ts-num" data-free>—</b></div>
                            <div class="ts-step">
                                <button type="button" data-step="1" aria-label="زِد">+</button>
                                <button type="button" data-step="-1" aria-label="نقّص">−</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="ts-save">
            <button class="btn btn-brand" type="submit"><span class="label">احفظ الإعدادات</span></button>
            <span class="hint"><i></i> في تعديلات ما انحفظت</span>
        </div>
    </form>
</section>

<script>
(function () {
    var form = document.querySelector('[data-ts-form]');
    if (!form) return;
    function paint(card) {
        var input = card.querySelector('input'), total = Math.max(0, parseInt(input.value, 10) || 0), taken = +card.dataset.taken;
        var pct = total ? Math.min(100, taken / total * 100) : (taken ? 100 : 0);
        // الدائرة بتتعبّى حسب المحجوز (وبتبين شوي حتى لو صفر)
        card.querySelector('[data-arc]').style.strokeDashoffset = 188.5 * (1 - Math.max(pct, 3) / 100);
        card.classList.toggle('is-full', total <= taken);
        card.classList.toggle('warn', total > taken && pct >= 75);
        card.querySelector('[data-free]').textContent = Math.max(0, total - taken);
        card.querySelector('[data-full]').hidden = !(total <= taken);
    }
    form.querySelectorAll('[data-seat]').forEach(function (card) {
        var input = card.querySelector('input');
        card.querySelectorAll('[data-step]').forEach(function (b) {
            b.addEventListener('click', function () {
                input.value = Math.max(0, (parseInt(input.value, 10) || 0) + (+b.dataset.step));
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
        input.addEventListener('input', function () {
            paint(card); card.classList.remove('bump'); void card.offsetWidth; card.classList.add('bump');
        });
        // الدائرة بتتعبّى بعد ما تطلع البطاقة
        setTimeout(function () { paint(card); }, 350 + (+card.style.getPropertyValue('--i') || 0) * 70);
    });
    var mark = function () { form.classList.add("dirty"); };
    form.querySelectorAll('[data-fee-step]').forEach(function (b) {
        b.addEventListener('click', function () {
            var inp = b.closest('.ts-coin').querySelector('input');
            inp.value = Math.max(0, (parseInt(inp.value, 10) || 0) + (+b.dataset.feeStep));
            inp.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
    var wa = form.querySelector('.ts-wa');
    if (wa) wa.querySelector('input').addEventListener('input', function () { wa.classList.remove('bump'); void wa.offsetWidth; wa.classList.add('bump'); });
    form.querySelectorAll('.ts-coin input').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var c = inp.closest('.ts-coin'); c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump');
        });
    });
    form.addEventListener('input', mark);
    form.addEventListener('change', mark);
})();
</script>
@endsection
