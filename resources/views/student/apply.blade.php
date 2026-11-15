@extends('layouts.app')
@section('title', 'التسجيل بالتدريب الميداني')

@php
    $meta = config('isnad.track_meta');
    $available = array_values(array_diff(array_keys($tracks), $fullTracks));
    $wanted = old('track', request('track'));
    $selTrack = in_array($wanted, $available, true) ? $wanted : ($available[0] ?? null);
    $selHours = (string) old('hours', $hoursOptions[0]);
    $amount = $fees[(int) $selHours] ?? reset($fees);
    $feesLabel = implode(' أو ', array_unique(array_values($fees)));
    $waContact = $contact ? 'https://wa.me/'.\App\Models\Application::normalizePhone($contact).'?text='.rawurlencode('مرحبا، عندي استفسار عن التدريب الميداني بإسناد') : null;
@endphp

@section('content')
<style>
    /* علامة الصح بالخانات: خضرا */
    .ok-mark { background: #1FA463 !important; color: #fff !important; }
    :root[data-theme="dark"] .ok-mark { background: #34C77B !important; color: #05251A !important; }
    .ok-host.is-ok .input { border-color: rgba(31, 164, 99, .55) !important; }
    /* زر الإرسال: أخضر */
    .btn-send { background: linear-gradient(135deg, #22B26C, #189355) !important; border-color: #189355 !important; color: #fff !important; box-shadow: 0 12px 26px -12px rgba(24, 147, 85, .8) !important; }
    .btn-send:hover { background: linear-gradient(135deg, #26C277, #1A9E5C) !important; color: #fff !important; box-shadow: 0 18px 34px -14px rgba(24, 147, 85, .9) !important; }
    .btn-send::after { content: ""; position: absolute; inset: 0; z-index: -1; background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .4) 50%, transparent 70%); transform: translateX(120%); transition: transform .8s; }
    .btn-send:hover::after { transform: translateX(-120%); }
    /* ===== بطاقات المسارات: بتنقلب 3D ===== */
    .tracks-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    @media (max-width: 900px) { .tracks-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .flip-tile { position: relative; display: block; height: 168px; perspective: 1000px; cursor: pointer; animation: ttIn .7s cubic-bezier(.2, .9, .3, 1.2) both; animation-delay: calc(.6s + var(--k) * 90ms); }
    @keyframes ttIn { from { opacity: 0; transform: translateY(26px) scale(.92); } to { opacity: 1; transform: none; } }
    .flip-tile input { position: absolute; opacity: 0; pointer-events: none; }
    .flip-in { position: relative; display: block; width: 100%; height: 100%; transform-style: preserve-3d; transition: transform .95s cubic-bezier(.4, 0, .2, 1); }
    .flip-face { position: absolute; inset: 0; border-radius: 20px; backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 18px; text-align: center; }
    .flip-front { background: linear-gradient(160deg, #142A5E, var(--surface)); border: 1.5px solid var(--line); transition: border-color .3s, box-shadow .3s; }
    :root:not([data-theme="dark"]) .flip-front { background: linear-gradient(160deg, #FFFFFF, #F3F5FA); }
    .flip-front .fi { width: 56px; height: 56px; border-radius: 17px; background: #1A2C57; color: #E9905C; display: flex; align-items: center; justify-content: center; transition: background .3s, color .3s; }
    :root:not([data-theme="dark"]) .flip-front .fi { background: #FBEADF; color: #C9693A; }
    .flip-front b { font-size: 18px; direction: ltr; }
    .flip-front small { display: none; color: var(--muted); font-size: 12.5px; line-height: 1.6; }
    /* ضهر البطاقة: برتقالي دافي وهادي */
    .flip-back { transform: rotateY(180deg); background: linear-gradient(155deg, #C9784A 0%, #A45A33 100%); color: #FFF6EF; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12); }
    .flip-back b { font-size: 18px; direction: ltr; }
    .flip-back span { font-size: 13.5px; line-height: 1.75; color: #FBE3D3; }
    .flip-back em { font-style: normal; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 999px; background: rgba(255, 255, 255, .16); }

    @media (hover: hover) and (pointer: fine) {
        .flip-tile:hover .flip-in, .flip-tile input:focus-visible + .flip-in { transform: rotateY(180deg); transition-delay: .05s; }
    }
    /* على الموبايل ما في قلب: الوصف بيطلع تحت الاسم */
    @media not ((hover: hover) and (pointer: fine)) {
        .flip-back { display: none; }
        .flip-front small { display: block; }
        .flip-tile { height: auto; }
        .flip-face.flip-front { position: relative; min-height: 150px; }
    }

    /* المختار */
    .flip-tile input:checked + .flip-in .flip-front { border-color: #D07A47; box-shadow: 0 0 0 1px #D07A47, 0 18px 36px -22px #D07A47; }
    .flip-tile input:checked + .flip-in .flip-front .fi { background: linear-gradient(155deg, #D9844F, #B5623A); color: #fff; }
    .flip-check { position: absolute; top: 10px; left: 10px; z-index: 3; width: 26px; height: 26px; border-radius: 50%; background: #2FB873; color: #fff; display: flex; align-items: center; justify-content: center; transform: scale(0); transition: transform .3s cubic-bezier(.3, 1.6, .5, 1); box-shadow: 0 4px 10px rgba(0, 0, 0, .25); }
    .flip-tile input:checked ~ .flip-check { transform: scale(1); }
    .flip-tile.full { cursor: not-allowed; opacity: .5; }
    .flip-tile.full .flip-in { transform: none !important; }
    .flip-tile .full-tag { position: absolute; top: 10px; right: 10px; z-index: 3; font-size: 11px; font-weight: 700; color: #F38585; background: #3B1A20; padding: 2px 8px; border-radius: 999px; }

    @media (prefers-reduced-motion: reduce) { .flip-tile { animation: none; } .flip-in { transition: none; } }
</style>
<div class="apply-page">
    <canvas class="hero-canvas" data-constellation aria-hidden="true"></canvas>
    <div class="container">

        <header class="apply-head">
            <span class="pill fade-up" style="--d:0">
                <span class="pulse-dot {{ $open ? '' : 'off' }}"></span>
                {{ $open ? 'التسجيل مفتوح · المقاعد محدودة' : 'التسجيل مسكّر حالياً' }}
            </span>
            <h1 class="title-words" aria-label="سجّل بالتدريب الميداني بإسناد">
                @foreach (['سجّل', 'بالتدريب', 'الميداني'] as $i => $w)<span class="w" style="--i:{{ $i }}" aria-hidden="true">{{ $w }}</span> @endforeach<em class="w" style="--i:3" aria-hidden="true">بإسناد</em>
            </h1>
            <p class="lead fade-up" style="--d:5">تدريب على مشاريع حقيقية بفريق ومشرف مختص، لطلاب الجامعات والخريجين. عبّي بياناتك، ادفع الرسوم بجوال باي وارفع صورة الإشعار، وبنثبّت مقعدك.</p>
            <div class="chips-sm fade-up" style="--d:3">
                @foreach ($fees as $h => $f)<span>@include('partials.icon', ['n' => 'clock', 'size' => 14]) {{ $h }} ساعة · {{ $f }} ₪</span>@endforeach
                <span>@include('partials.icon', ['n' => 'award', 'size' => 14]) شهادة + Portfolio</span>
            </div>
        </header>

        <div class="apply-layout">
            {{-- ===== النموذج ===== --}}
            <div class="stack-lg">
                @if (! $open)
                    <div class="card stack fade-up" style="--d:3;align-items:center;text-align:center">
                        <span class="status-ico gray">@include('partials.icon', ['n' => 'clock', 'size' => 36])</span>
                        <h2>التسجيل مسكّر حالياً</h2>
                        <p class="muted">تابعنا لتعرف موعد الدفعة الجاية.</p>
                    </div>
                @else
                @if ($errors->any())
                    <div class="alert alert-error fade-up">@include('partials.icon', ['n' => 'alert'])
                        <span>في كم حقل بحاجة تصحيح:
                            <ul style="margin:6px 0 0;padding-inline-start:18px">
                                @foreach ($errors->all() as $msg)<li>{{ $msg }}</li>@endforeach
                            </ul>
                            @unless ($errors->has('receipt'))<b style="display:block;margin-top:6px">واختار صورة التحويل مرة تانية.</b>@endunless
                        </span></div>
                @endif

                <form method="post" action="{{ route('apply.store') }}" enctype="multipart/form-data" class="card stack-lg rise" style="--d:5" novalidate data-live>
                    @csrf

                    {{-- ١ البيانات --}}
                    <div class="form-section">
                        <div class="form-section-title"><span>١</span> بياناتك</div>
                        <div class="field">
                            <label for="name">الاسم الكامل</label>
                            <div class="input-icon">@include('partials.icon', ['n' => 'user', 'size' => 18])
                                <input class="input @error('name') is-invalid @enderror" id="name" data-ok name="name" value="{{ old('name') }}" placeholder="مثلاً: سارة أحمد" required autocomplete="name"></div>
                            @error('name')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="phone">رقم الواتساب</label>
                            <div class="input-icon">@include('partials.icon', ['n' => 'chat', 'size' => 18])
                                <input class="input @error('phone') is-invalid @enderror" id="phone" data-ok name="phone" value="{{ old('phone') }}" placeholder="059xxxxxxx" inputmode="tel" dir="ltr" style="text-align:right" required autocomplete="tel"></div>
                            <span class="hint">على هالرقم رح نتواصل معك.</span>
                            @error('phone')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="grid-2">
                            <div class="field">
                                <label for="university">الجامعة</label>
                                <input class="input @error('university') is-invalid @enderror" id="university" data-ok name="university" value="{{ old('university') }}" placeholder="اسم الجامعة" required>
                                @error('university')<span class="error">{{ $message }}</span>@enderror
                            </div>
                            <div class="field">
                                <label for="major">التخصص</label>
                                <input class="input @error('major') is-invalid @enderror" id="major" data-ok name="major" value="{{ old('major') }}" placeholder="مثلاً: هندسة برمجيات" required>
                                @error('major')<span class="error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ٢ المسار والساعات --}}
                    <div class="form-section">
                        <div class="form-section-title"><span>٢</span> المسار</div>
                        <fieldset class="field" style="border:0;padding:0;margin:0">
                            <legend class="sr-only">المسار</legend>
                            <div class="tracks-grid">
                                @foreach ($tracks as $key => $label)
                                    @php $isFull = in_array($key, $fullTracks, true); @endphp
                                    <label class="flip-tile {{ $isFull ? 'full' : '' }}" style="--k: {{ $loop->index }}">
                                        <input type="radio" name="track" value="{{ $key }}" @checked($selTrack === $key) @disabled($isFull)>
                                        <span class="flip-in">
                                            <span class="flip-face flip-front">
                                                <span class="fi">@include('partials.icon', ['n' => $meta[$key]['icon'] ?? 'code', 'size' => 26])</span>
                                                <b>{{ $label }}</b>
                                                <small>{{ $meta[$key]['desc'] ?? '' }}</small>
                                            </span>
                                            <span class="flip-face flip-back" aria-hidden="true">
                                                <b>{{ $label }}</b>
                                                <span>{{ $meta[$key]['desc'] ?? '' }}</span>
                                                <em>{{ $isFull ? 'المقاعد كاملة' : 'اكبس لتختاره' }}</em>
                                            </span>
                                        </span>
                                        @if ($isFull)<span class="full-tag">المقاعد كاملة</span>@endif
                                        <span class="flip-check">@include('partials.icon', ['n' => 'check', 'size' => 15, 'stroke' => 3])</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('track')<span class="error">{{ $message }}</span>@enderror
                        </fieldset>
                        <div class="field">
                            <label for="portfolio_url">رابط أعمالك <span class="muted small">(إن وجد)</span></label>
                            <div class="input-icon">@include('partials.icon', ['n' => 'link', 'size' => 18])
                                <input class="input @error('portfolio_url') is-invalid @enderror" id="portfolio_url" data-ok name="portfolio_url" value="{{ old('portfolio_url') }}" placeholder="https://" dir="ltr" style="text-align:right" inputmode="url"></div>
                            @error('portfolio_url')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="grid-2">
                            <div class="field">
                                <span class="legend" id="hours-l">ساعات التدريب المطلوبة من جامعتك</span>
                                <div class="segment hours" role="radiogroup" aria-labelledby="hours-l">
                                    @foreach ($hoursOptions as $h)
                                        <label><input type="radio" name="hours" value="{{ $h }}" data-fee="{{ $fees[$h] ?? '' }}" @checked($selHours === (string) $h)><span>{{ $h }} ساعة<span class="hour-price">{{ $fees[$h] ?? '' }} ₪</span></span></label>
                                    @endforeach
                                </div>
                                @error('hours')<span class="error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- تواصل معنا --}}
                    <div class="form-section">
                        @if ($waContact)
                            <div class="contact-card">
                                <span class="row" style="gap:12px;flex-wrap:nowrap">
                                    <span class="ci">@include('partials.icon', ['n' => 'chat', 'size' => 22])</span>
                                    <span><b style="display:block">عندك سؤال قبل ما تحوّل؟ تواصل معنا</b><span class="muted small">واتساب: <span class="ltr">{{ $contact }}</span></span></span>
                                </span>
                                <a class="btn btn-green btn-sm" href="{{ $waContact }}" target="_blank" rel="noopener">@include('partials.icon', ['n' => 'chat', 'size' => 16]) راسلنا واتساب</a>
                            </div>
                        @endif
                    </div>

                    {{-- ٣ الدفع --}}
                    <div class="form-section">
                        <div class="form-section-title"><span>٣</span> التحويل وتثبيت المقعد</div>
                        @include('student.partials.payment', ['methods' => $methods, 'amount' => $amount])
                    </div>

                    {{-- ملاحظات: آخر إشي بعد الدفع --}}
                    <div class="form-section">
                        <div class="field">
                            <label for="notes">ملاحظات <span class="muted small">(اختياري)</span></label>
                            <textarea class="input" id="notes" name="notes" placeholder="أي إشي بتحب تحكيلنا إياه">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button class="btn btn-lg btn-block btn-send" type="submit"><span class="label">أرسل الطلب والتحويل</span> @include('partials.icon', ['n' => 'arrow'])</button>
                </form>
                @endif
            </div>

            {{-- ===== معلومات جانبية ===== --}}
            <aside class="apply-aside" aria-label="معلومات التدريب">
                <div class="card card-sm spot rise warm" style="--d:7">
                    <div class="facts">
                        <div class="fact" style="--b:0"><span class="fi">@include('partials.icon', ['n' => 'briefcase', 'size' => 20])</span><div><b>مشاريع حقيقية</b><span>مهام عملية بإشراف مختصين بمجالك.</span></div></div>
                        <div class="fact" style="--b:1"><span class="fi">@include('partials.icon', ['n' => 'clock', 'size' => 20])</span><div><b>72 أو 120 ساعة</b><span>حسب ما بتطلب جامعتك للتدريب الميداني.</span></div></div>
                        <div class="fact" style="--b:2"><span class="fi">@include('partials.icon', ['n' => 'award', 'size' => 20])</span><div><b>شهادة + Portfolio</b><span>للمشاركين الملتزمين.</span></div></div>
                        <div class="fact" style="--b:3"><span class="fi o">@include('partials.icon', ['n' => 'users', 'size' => 20])</span><div><b>المقاعد محدودة</b><span>بتتثبّت حسب ترتيب التحويل.</span></div></div>
                    </div>
                </div>

                <div class="card card-sm stack spot rise warm warm-2" style="--d:9;gap:10px">
                    <b style="font-family:var(--font-display)">كيف بتمشي الأمور</b>
                    <div class="mini-journey">
                        <div data-n="١" style="--b:0">عبّي بياناتك واختار المسار والساعات</div>
                        <div data-n="٢" style="--b:1">ادفع الرسوم عن طريق جوال باي</div>
                        <div data-n="٣" style="--b:2">ارفع صورة التحويل وأرسل الطلب</div>
                        <div data-n="٤" style="--b:3">بنتأكد من التحويل ومقعدك بيتثبّت</div>
                    </div>
                </div>

                @if ($waContact)
                    <a class="card card-sm row between spot rise" style="--d:11;text-decoration:none;color:var(--text)" href="{{ $waContact }}" target="_blank" rel="noopener">
                        <span class="row" style="gap:10px"><span class="contact-card ci" style="width:40px;height:40px;border-radius:12px;padding:0;background:var(--green);color:#fff;border:0">@include('partials.icon', ['n' => 'chat', 'size' => 18])</span><span><b style="display:block;font-size:15px">تواصل معنا</b><span class="muted tiny ltr">{{ $contact }}</span></span></span>
                        @include('partials.icon', ['n' => 'arrow', 'size' => 18])
                    </a>
                @endif

            </aside>
        </div>
    </div>
</div>
@endsection
