{{--
    خطوات التحويل + رفع الصورة
    بيستخدموه: نموذج التسجيل (المبلغ بيتغيّر حسب الساعات) وصفحة الطالب (لإعادة الرفع)
    المتغيرات: $methods، $amount (المبلغ الحالي)
    كل طريقة دفع بتاخد لون وشعار جهتها (بنك فلسطين / جوال باي)
--}}
@php $selected = (string) old('payment_method_id', optional($methods->first())->id); @endphp

@once
<style>
    /* ألوان كل جهة دفع */
    .pm { --pm: var(--brand); --pm-ink: var(--brand-ink); --pm-soft: var(--brand-soft); --pm-line: var(--line); }
    .pm-bop { --pm: #8C1159; --pm-ink: #fff; --pm-soft: rgba(140, 17, 89, .09); --pm-line: rgba(140, 17, 89, .45); }
    .pm-jawwal { --pm: #7DB534; --pm-ink: #fff; --pm-soft: rgba(150, 202, 61, .14); --pm-line: rgba(125, 181, 52, .55); }
    :root[data-theme="dark"] .pm-bop { --pm: #B8236F; --pm-soft: rgba(184, 35, 111, .14); --pm-line: rgba(184, 35, 111, .55); }
    :root[data-theme="dark"] .pm-jawwal { --pm: #96CA3D; --pm-ink: #142A05; --pm-soft: rgba(150, 202, 61, .12); --pm-line: rgba(150, 202, 61, .5); }

    /* بطاقات الاختيار */
    .pm-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; }
    .pm-tile { position: relative; cursor: pointer; display: block; }
    .pm-tile input { position: absolute; opacity: 0; pointer-events: none; }
    .pm-tile .pm-in { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 18px; border: 2px solid var(--line); background: var(--surface-2); transition: border-color .25s, background .25s, box-shadow .3s, transform .25s; height: 100%; }
    .pm-tile:hover .pm-in { transform: translateY(-2px); border-color: var(--pm-line); }
    .pm-tile input:checked + .pm-in { border-color: var(--pm); background: var(--pm-soft); box-shadow: 0 0 0 4px var(--pm-soft), 0 14px 30px -16px var(--pm); }
    .pm-tile input:focus-visible + .pm-in { outline: 3px solid var(--pm); outline-offset: 2px; }
    .pm-logo { flex: none; width: 104px; height: 50px; display: flex; align-items: center; justify-content: center; }
    .pm-logo img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
    .pm-jawwal .pm-logo { width: 50px; }
    .pm-jawwal .pm-head .pm-logo { width: 56px; }
    .pm-jawwal .pm-logo img { border-radius: 12px; box-shadow: 0 6px 14px -6px rgba(125, 181, 52, .7); }
    .pm-tile b { display: block; font-size: 16px; line-height: 1.3; }
    .pm-tile small { color: var(--muted); font-size: 12px; }
    .pm-tick { position: absolute; top: 10px; left: 10px; width: 24px; height: 24px; border-radius: 50%; background: var(--pm); color: var(--pm-ink); display: flex; align-items: center; justify-content: center; transform: scale(0); transition: transform .3s cubic-bezier(.3, 1.6, .5, 1); }
    .pm-tile input:checked ~ .pm-tick { transform: scale(1); }

    /* لوحة الخطوات بلون الجهة */
    .pm-panel { border: 1.5px solid var(--pm-line); border-radius: 20px; background: linear-gradient(180deg, var(--pm-soft), transparent 60%), var(--surface-2); padding: 18px; position: relative; overflow: hidden; }
    .pm-panel::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 4px; background: var(--pm); }
    .pm-head { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
    .pm-head h3 { font-size: 17px; margin: 0; }
    .pm-head .pm-logo { width: 120px; height: 56px; }
    .pm-panel .pay-steps li::before { background: var(--pm); color: var(--pm-ink); }
    .pm-panel .pay-steps li b { color: var(--text); }
    .pm-panel .kv { border-color: color-mix(in srgb, var(--pm) 18%, var(--line)); }
    .pm-panel .copy { border-color: var(--pm-line); }
    .pm-panel .copy:hover { background: var(--pm); color: var(--pm-ink); border-color: var(--pm); }
    .pm-amount { color: var(--pm) !important; font-size: 20px; }
    .pm-note { display: flex; gap: 8px; align-items: flex-start; margin-top: 10px; padding: 10px 12px; border-radius: 12px; background: var(--pm-soft); font-size: 14px; color: var(--text); }
    .pm-note .ico { color: var(--pm); margin-top: 3px; }
    /* اسم صاحب الحساب ورقمه: مربعين جنب بعض */
    .payee-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 6px 0 10px; }
    .payee-it { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 14px; background: var(--surface); border: 1px solid var(--line); transition: border-color .2s; min-width: 0; }
    .payee-it:hover { border-color: var(--pm-line); }
    .payee-ic { width: 42px; height: 42px; border-radius: 12px; background: var(--pm-soft); color: var(--pm); display: flex; align-items: center; justify-content: center; flex: none; }
    .payee-tx { min-width: 0; }
    .payee-tx small { display: block; color: var(--muted); font-size: 12px; }
    .payee-tx b { display: block; font-family: var(--font-display); font-size: 19px; line-height: 1.35; }
    .payee-copy-sm { margin-inline-start: auto; flex: none; }
    @media (max-width: 640px) { .payee-grid { grid-template-columns: 1fr; } }
    .pm-panel[hidden] { display: none !important; }

    /* باركود الدفع لتاجر */
    .pm-qr { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 18px 14px; margin: 4px 0 12px; border-radius: 18px; background: var(--surface); border: 1px solid var(--line); text-align: center; }
    .pm-qr-lbl { color: var(--muted); font-size: 14px; }
    .pm-qr-frame { display: block; padding: 12px; background: #fff; border-radius: 18px; border: 4px solid var(--pm); box-shadow: 0 16px 34px -18px var(--pm); transition: transform .35s cubic-bezier(.3, 1.5, .5, 1); }
    .pm-qr-frame:hover { transform: scale(1.03); }
    .pm-qr-frame img { display: block; width: 200px; height: 200px; image-rendering: pixelated; }
    .pm-qr-dl { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 0 24px; border-radius: 14px; background: var(--pm); color: var(--pm-ink); font-weight: 700; text-decoration: none; transition: filter .2s, transform .15s; }
    .pm-qr-dl:hover { filter: brightness(1.08); }
    .pm-qr-dl:active { transform: scale(.97); }
    .pm-qr-tip { color: var(--muted); font-size: 12.5px; }
    @media (max-width: 560px) { .pm-qr-dl { width: 100%; } .pm-qr-frame img { width: 180px; height: 180px; } }

    /* السعر: رقم كبير بألوان قوس قزح + خط ملوّن تحته */
    .price-rb { --rb: linear-gradient(90deg, #E8862F, #E2B714, #3FB86A, #2E9BE0, #8E62E8, #E8862F);
        display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 18px 10px 8px; text-align: center; }
    :root[data-theme="dark"] .price-rb { --rb: linear-gradient(90deg, #FF9A3D, #FFE14D, #4DDB7A, #3DB8FF, #B57CFF, #FF9A3D); }
    .price-rb-lbl { color: var(--muted); font-size: 15px; }
    .price-rb-row { display: flex; align-items: baseline; justify-content: center; gap: 12px; direction: ltr; }
    .price-rb-num { display: inline-block; font-family: var(--font-display); font-size: 96px; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums;
        background: var(--rb); background-size: 300% 100%; -webkit-background-clip: text; background-clip: text; color: transparent; animation: rb-flow 6s linear infinite; }
    .price-rb-num.bump { animation: rb-flow 6s linear infinite, rb-bump .5s cubic-bezier(.3, 1.6, .5, 1); }
    .price-rb-cur { font-size: 40px; font-weight: 800; color: var(--text); }
    .price-rb-bar { width: 220px; max-width: 70%; height: 6px; border-radius: 6px; background: var(--rb); background-size: 300% 100%; animation: rb-flow 6s linear infinite; }
    @keyframes rb-flow { to { background-position: 300% 0; } }
    @keyframes rb-bump { 40% { transform: scale(1.15); } }
    @media (max-width: 560px) { .price-rb-num { font-size: 76px; } .price-rb-cur { font-size: 32px; } }
    @media (prefers-reduced-motion: reduce) { .price-rb-num, .price-rb-bar { animation: none; } }
    @media (max-width: 560px) { .pm-tiles { grid-template-columns: 1fr; } .pm-logo { width: 92px; height: 44px; } }
</style>
@endonce

@if ($methods->isEmpty())
    <div class="alert alert-info">@include('partials.icon', ['n' => 'alert']) <span>بيانات التحويل رح تنضاف قريباً. تواصل معنا على الواتس لتعرفها.</span></div>
@else
    <div class="price-rb">
        <span class="price-rb-lbl">المبلغ المطلوب</span>
        <div class="price-rb-row"><span class="price-rb-num" data-amount>{{ $amount }}</span><span class="price-rb-cur">₪</span></div>
        <span class="price-rb-bar"></span>
        <span class="price-rb-lbl">رسوم تشغيلية مرة وحدة لكامل الفترة</span>
    </div>

    @if ($methods->count() === 1)
        {{-- طريقة دفع وحدة: ما في داعي يختار --}}
        <input type="hidden" name="payment_method_id" value="{{ $methods->first()->id }}">
        @error('payment_method_id')<span class="error">{{ $message }}</span>@enderror
    @else
    <fieldset class="field" style="border:0;padding:0;margin:0">
        <legend class="legend" style="margin-bottom:8px">اختار طريقة التحويل</legend>
        <div class="pm-tiles">
            @foreach ($methods as $m)
                <label class="pm-tile pm pm-{{ $m->brand() ?? 'other' }}" data-tilt>
                    <input type="radio" name="payment_method_id" value="{{ $m->id }}" data-method @checked($selected === (string) $m->id)>
                    <span class="pm-in">
                        @if ($m->logoUrl())
                            <span class="pm-logo"><img src="{{ $m->logoUrl() }}" alt="{{ $m->title }}" loading="lazy"></span>
                        @else
                            <span class="ti" style="width:52px;height:52px;border-radius:14px;background:var(--pm);color:var(--pm-ink);display:flex;align-items:center;justify-content:center">@include('partials.icon', ['n' => $m->type === 'wallet' ? 'wallet' : 'bank', 'size' => 22])</span>
                        @endif
                        <span><b>{{ $m->title }}</b><small>{{ $m->subtitle() }}</small></span>
                    </span>
                    <span class="pm-tick">@include('partials.icon', ['n' => 'check', 'size' => 14, 'stroke' => 3])</span>
                </label>
            @endforeach
        </div>
        @error('payment_method_id')<span class="error">{{ $message }}</span>@enderror
    </fieldset>
    @endif

    @foreach ($methods as $m)
        @php $isWallet = $m->type === 'wallet'; $qr = $m->qrUrl(); @endphp
        <div class="pm-panel pm pm-{{ $m->brand() ?? 'other' }} pay-panel" data-method-panel="{{ $m->id }}">
            <div class="pm-head">
                @if ($m->logoUrl())
                    <span class="pm-logo"><img src="{{ $m->logoUrl() }}" alt="" loading="lazy"></span>
                @endif
                <div><h3>{{ $qr ? 'الدفع' : 'التحويل' }} عبر {{ $m->title }}</h3><span class="tiny muted">{{ $m->subtitle() }}</span></div>
            </div>
            @if ($qr)
                {{-- باركود الدفع لتاجر + تحميله (للي فاتح من الموبايل) --}}
                <div class="pm-qr">
                    <span class="pm-qr-lbl">امسح الرمز من تطبيق {{ $m->title }}</span>
                    <span class="pm-qr-frame"><img src="{{ $qr }}" alt="باركود الدفع لـ {{ $m->account_name }}" width="200" height="200"></span>
                    <a class="pm-qr-dl" href="{{ $qr }}" download="isnad-jawwal-pay-qr.png">@include('partials.icon', ['n' => 'download', 'size' => 18]) حمّل الباركود</a>
                    <span class="pm-qr-tip">فاتح من الموبايل؟ حمّله وافتحه من التطبيق ← مسح من الصور</span>
                </div>
            @endif
            {{-- اسم صاحب الحساب ورقمه: مربعين جنب بعض --}}
            <div class="payee-grid">
                @if ($m->account_name)
                    <div class="payee-it">
                        <span class="payee-ic">@include('partials.icon', ['n' => 'user', 'size' => 20])</span>
                        <span class="payee-tx"><small>{{ $qr ? 'اسم التاجر' : 'اسم صاحب الحساب' }}</small><b>{{ $m->account_name }}</b></span>
                    </div>
                @endif
                @if ($m->account_number)
                    <div class="payee-it">
                        <span class="payee-ic">@include('partials.icon', ['n' => 'phone', 'size' => 20])</span>
                        <span class="payee-tx"><small>{{ $qr ? 'رقم التاجر' : ($isWallet ? 'رقم المحفظة' : 'رقم الجوال') }}</small><b class="ltr">{{ $m->account_number }}</b></span>
                        <button class="copy payee-copy-sm" type="button" data-copy="{{ $m->account_number }}">@include('partials.icon', ['n' => 'copy', 'size' => 14])<span class="label">نسخ</span></button>
                    </div>
                @endif
            </div>
            @if ($m->iban)
                <div class="kv"><span class="muted">IBAN</span>
                    <span class="row" style="gap:8px"><span class="v">{{ $m->iban }}</span><button class="copy" type="button" data-copy="{{ $m->iban }}">@include('partials.icon', ['n' => 'copy', 'size' => 14])<span class="label">نسخ</span></button></span></div>
            @endif
            <div class="kv"><span class="muted">المبلغ</span><span class="v pm-amount"><span data-amount>{{ $amount }}</span> ₪</span></div>
            <div class="pm-note">@include('partials.icon', ['n' => 'alert', 'size' => 16])<span>اكتب اسمك بملاحظة {{ $qr ? 'الدفع' : 'التحويل' }}.</span></div>
            @if ($qr)
                <div class="pm-note">@include('partials.icon', ['n' => 'phone', 'size' => 16])<span>ما عندك إنترنت؟ اطلب <b class="ltr">*110#</b> وادفع من غير تطبيق.</span></div>
            @endif
        </div>
    @endforeach

    <div class="field">
        <span class="legend">صورة التحويل</span>
        <label class="dropzone" for="receipt">
            <input id="receipt" name="receipt" type="file" accept="image/*,application/pdf" required>
            <span class="dz-ico">@include('partials.icon', ['n' => 'upload', 'size' => 26])</span>
            <b data-dz-title>اسحب الصورة لهون أو اكبس لتختارها</b>
            <span class="hint">صورة، سكرين شوت أو PDF · أقل من 5 ميجا</span>
            <img class="preview" alt="" hidden>
        </label>
        @error('receipt')<span class="error">@include('partials.icon', ['n' => 'alert', 'size' => 16]) {{ $message }}</span>@enderror
    </div>
@endif
