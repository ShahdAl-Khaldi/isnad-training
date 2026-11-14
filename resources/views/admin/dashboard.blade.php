@extends('layouts.admin')
@section('title', 'لوحة إسناد')
@section('heading', 'الطلبات')

@php
    use App\Models\Application as A;
    $meta = config('isnad.track_meta');
    $tabs = [
        'review' => ['بحاجة تأكيد', 'wallet'],
        'confirmed' => ['مثبّتين', 'award'],
        'pending' => ['رجعت للطالب', 'clock'],
        'rejected' => ['مرفوض', 'x'],
        'all' => ['الكل', 'users'],
        'seats' => ['المقاعد', 'grid'],
    ];
    // "رجعت للطالب" بيظهر بس إذا في طلبات فيه
    if (! $tabCounts['pending'] && $tab !== 'pending') unset($tabs['pending']);
    $link = fn ($t) => route('admin.dashboard', array_filter(['tab' => $t, 'track' => $filters['track'], 'q' => $filters['q']]));
    $tones = [A::PAYMENT_SUBMITTED => 'warn', A::CONFIRMED => 'ok', A::REJECTED => 'bad', A::EXPIRED => 'bad', A::WAITLISTED => 'bad'];
@endphp

@section('actions')
    <a class="btn" href="{{ route('admin.export') }}" data-no-loading>@include('partials.icon', ['n' => 'download', 'size' => 18]) تصدير Excel</a>
@endsection

@section('content')
<style>
    /* ===== التبويبات ===== */
    .dtabs { position: relative; display: flex; gap: 4px; padding: 6px; border-radius: 20px; background: var(--surface); border: 1px solid var(--line); overflow-x: auto; scrollbar-width: none; }
    .dtabs::-webkit-scrollbar { display: none; }
    .dtab { position: relative; z-index: 1; flex: none; display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; border-radius: 14px; text-decoration: none; color: var(--muted); font-weight: 700; font-size: 15px; transition: color .3s; white-space: nowrap; }
    .dtab:hover { color: var(--text); }
    .dtab svg { opacity: .8; }
    .dtab em { font-style: normal; font-size: 12px; min-width: 24px; height: 22px; padding: 0 7px; border-radius: 99px; display: inline-grid; place-items: center; background: var(--surface-2); color: var(--muted); transition: background .3s, color .3s; }
    .dtab.on { color: #fff; }
    .dtab.on em { background: rgba(255, 255, 255, .22); color: #fff; }
    .dtab.hot:not(.on) em { background: #E0874F; color: #fff; animation: dtab-pulse 1.8s ease-in-out infinite; }
    .dtab-ind { position: absolute; z-index: 0; top: 6px; bottom: 6px; border-radius: 14px; background: linear-gradient(135deg, #E8955F, #C9784A); box-shadow: 0 10px 24px -12px #E0874F; transition: left .5s cubic-bezier(.65, 0, .35, 1), width .5s cubic-bezier(.65, 0, .35, 1); }
    @keyframes dtab-pulse { 50% { box-shadow: 0 0 0 6px rgba(224, 135, 79, .18); } }

    /* ===== البحث ===== */
    .dsearch { display: flex; gap: 10px; flex-wrap: wrap; }
    .dsearch .input-icon { flex: 1 1 260px; }
    .dsearch select { flex: 0 1 210px; }

    /* ===== صفوف الطلاب ===== */
    .dlist { display: flex; flex-direction: column; gap: 10px; }
    .drow { --tone: var(--line); position: relative; display: flex; align-items: center; gap: 16px; padding: 14px 18px; border-radius: 18px; background: var(--surface); border: 1px solid var(--line); overflow: hidden;
        animation: drow-in .6s cubic-bezier(.2, .8, .2, 1) both; animation-delay: calc(var(--i) * 60ms); transition: transform .25s, box-shadow .25s, border-color .25s; flex-wrap: wrap; }
    .drow::before { content: ""; position: absolute; inset-block: 14px; inset-inline-start: 0; width: 4px; border-radius: 0 4px 4px 0; background: var(--tone); }
    .drow:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--tone) 60%, var(--line)); box-shadow: 0 16px 30px -22px rgba(0, 0, 0, .6); }
    .drow.t-warn { --tone: #E9B949; } .drow.t-ok { --tone: var(--green); } .drow.t-bad { --tone: var(--red); }
    .drow .av { width: 46px; height: 46px; border-radius: 14px; flex: none; display: grid; place-items: center; font-weight: 800; font-size: 18px; color: #FFE3CF; background: linear-gradient(140deg, #E0874F, #8E4A2A); }
    .drow.t-ok .av { background: linear-gradient(140deg, #2FC27A, #14704A); color: #E6FFF2; }
    .drow.t-bad .av { background: linear-gradient(140deg, #6B7894, #36415C); color: #EEF1F7; }
    .drow .who { flex: 1 1 240px; min-width: 0; }
    .drow .who a { color: var(--text); text-decoration: none; font-weight: 800; font-size: 16px; }
    .drow .who a:hover { color: #E0874F; }
    .drow .sub { display: flex; flex-wrap: wrap; gap: 6px 12px; margin-top: 4px; color: var(--muted); font-size: 13px; }
    .drow .tags { display: flex; gap: 6px; flex-wrap: wrap; }
    .dtag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 99px; font-size: 12.5px; font-weight: 700; background: var(--surface-2); color: var(--muted); white-space: nowrap; }
    .dtag.money { background: rgba(224, 135, 79, .14); color: #E0874F; }
    .dtag.st-warn { background: rgba(233, 185, 73, .14); color: #D9A520; } .dtag.st-ok { background: var(--green-soft); color: var(--green); } .dtag.st-bad { background: var(--red-soft); color: var(--red); }
    .dtag.st-warn::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; animation: blink 1.4s ease-in-out infinite; }
    .drow .thumb { width: 52px; height: 52px; border-radius: 12px; overflow: hidden; flex: none; border: 1px solid var(--line); background: var(--surface-2); display: grid; place-items: center; color: var(--muted); transition: transform .25s; }
    .drow .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .drow .thumb:hover { transform: scale(1.08) rotate(-2deg); }
    .drow .acts { display: flex; gap: 8px; align-items: center; margin-inline-start: auto; }
    .btn-ok { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 16px; border: 0; border-radius: 99px; background: var(--green); color: #04220F; font: inherit; font-weight: 800; font-size: 14px; cursor: pointer; transition: transform .2s, box-shadow .2s; }
    .btn-ok:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -12px var(--green); }
    /* لما يتأكد: الصف بيخضرّ وبيطلع */
    .drow.done { border-color: var(--green); background: var(--green-soft); }
    .drow.done .acts { opacity: 0; transition: opacity .2s; }
    .drow.done::after { content: "✓ تثبّت المقعد"; position: absolute; inset-inline-end: 22px; top: 50%; transform: translateY(-50%); font-weight: 800; color: var(--green); animation: drow-in .35s both; }
    .drow.leave { animation: drow-out .45s .55s cubic-bezier(.65, 0, .35, 1) forwards; }
    @keyframes drow-in { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    @keyframes drow-out { to { opacity: 0; transform: translateX(-40px); max-height: 0; padding-block: 0; margin-block: -5px; border-width: 0; } }

    /* ===== فاضي ===== */
    .dempty { text-align: center; padding: 56px 20px; color: var(--muted); display: flex; flex-direction: column; align-items: center; gap: 8px; animation: drow-in .6s both; }
    .dempty .ic { width: 84px; height: 84px; border-radius: 26px; display: grid; place-items: center; background: var(--green-soft); color: var(--green); margin-bottom: 8px; animation: dfloat 3.2s ease-in-out infinite; }
    .dempty b { color: var(--text); font-size: 18px; }
    @keyframes dfloat { 50% { transform: translateY(-8px) rotate(-3deg); } }

    /* ===== المقاعد ===== */
    .dseats { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 14px; }
    .dseat { display: flex; align-items: center; gap: 16px; padding: 18px; border-radius: 20px; background: var(--surface); border: 1px solid var(--line); text-decoration: none; color: var(--text);
        animation: drow-in .6s cubic-bezier(.2, .8, .2, 1) both; animation-delay: calc(var(--i) * 70ms); transition: transform .25s, border-color .25s; }
    .dseat:hover { transform: translateY(-3px); border-color: #E0874F; }
    .dring { position: relative; width: 78px; height: 78px; flex: none; }
    .dring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .dring circle { fill: none; stroke-width: 8; stroke-linecap: round; }
    .dring .bg { stroke: var(--surface-2); }
    .dring .c-ok { stroke: var(--green); stroke-dasharray: 0 999; transition: stroke-dasharray 1.2s cubic-bezier(.4, 0, .2, 1) .2s; }
    .dring .c-wait { stroke: #E0874F; stroke-dasharray: 0 999; transition: stroke-dasharray 1.2s cubic-bezier(.4, 0, .2, 1) .2s; }
    .dring b { position: absolute; inset: 0; display: grid; place-items: center; font-size: 18px; font-weight: 800; }
    .dseat h3 { margin: 0 0 4px; font-size: 16px; display: flex; align-items: center; gap: 8px; }
    .dseat .muted { font-size: 13px; }
    .dlegend { display: flex; gap: 16px; color: var(--muted); font-size: 13px; }
    .dlegend i { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-inline-end: 6px; }

    @media (max-width: 640px) { .drow .acts { margin-inline-start: 0; width: 100%; } .drow .acts > :not(.thumb) { flex: 1; justify-content: center; } .drow .acts .btn-ok { width: 100%; justify-content: center; } }
    .dring [data-dash="0"] { opacity: 0; }
    @media (prefers-reduced-motion: reduce) { .drow, .dseat, .dempty, .dempty .ic, .dtab.hot em { animation: none !important; } .dtab-ind { transition: none; } }
</style>

<nav class="dtabs fade-up" aria-label="التبويبات" data-dtabs>
    <span class="dtab-ind" aria-hidden="true"></span>
    @foreach ($tabs as $key => [$label, $icon])
        <a class="dtab {{ $tab === $key ? 'on' : '' }} {{ $key === 'review' && $tabCounts['review'] ? 'hot' : '' }}" href="{{ $link($key) }}" data-tab="{{ $key }}" @if ($tab === $key) aria-current="page" @endif>
            @include('partials.icon', ['n' => $icon, 'size' => 17]) {{ $label }}
            @if ($key !== 'seats')<em>{{ $tabCounts[$key] ?? 0 }}</em>@endif
        </a>
    @endforeach
</nav>

@if ($tab === 'seats')
    <section class="stack">
        <div class="row between">
            <div class="dlegend"><span><i style="background:var(--green)"></i>مثبّت</span><span><i style="background:#E0874F"></i>بحاجة تأكيد</span></div>
            <a class="btn btn-sm" href="{{ route('admin.settings') }}">عدّل عدد المقاعد</a>
        </div>
        <div class="dseats">
            @foreach ($perTrack as $key => $t)
                @php
                    $c = 2 * M_PI * 33;
                    $ok = $t['seats'] ? min(1, $t['confirmed'] / $t['seats']) * $c : 0;
                    $wait = $t['seats'] ? min(1, $t['taken'] / $t['seats']) * $c : 0;
                @endphp
                <a class="dseat" style="--i:{{ $loop->index }}" href="{{ route('admin.dashboard', ['tab' => 'all', 'track' => $key]) }}">
                    <span class="dring">
                        <svg viewBox="0 0 78 78" aria-hidden="true"><circle class="bg" cx="39" cy="39" r="33"/><circle class="c-wait" cx="39" cy="39" r="33" data-dash="{{ round($wait, 1) }}"/><circle class="c-ok" cx="39" cy="39" r="33" data-dash="{{ round($ok, 1) }}"/></svg>
                        <b>{{ $t['taken'] }}</b>
                    </span>
                    <span>
                        <h3>@include('partials.icon', ['n' => $meta[$key]['icon'] ?? 'code', 'size' => 16]) <span class="ltr">{{ $t['label'] }}</span></h3>
                        <span class="muted">{{ $t['taken'] }} من {{ $t['seats'] }} مقعد · باقي {{ max(0, $t['seats'] - $t['taken']) }}</span><br>
                        <span class="muted">{{ $t['confirmed'] }} مثبّت@if ($t['taken'] - $t['confirmed']) · {{ $t['taken'] - $t['confirmed'] }} بحاجة تأكيد@endif</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@else
    <form method="get" class="dsearch fade-up" style="--d:1" role="search">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="input-icon">@include('partials.icon', ['n' => 'search', 'size' => 18])
            <input class="input" name="q" value="{{ $filters['q'] }}" placeholder="بحث بالاسم، الرقم، الكود أو الجامعة" aria-label="بحث"></div>
        <select class="input" name="track" aria-label="المسار" onchange="this.form.submit()">
            <option value="">كل المسارات</option>
            @foreach (config('isnad.tracks') as $k => $l)
                <option value="{{ $k }}" @selected($filters['track'] === $k)>{{ $l }}</option>
            @endforeach
        </select>
    </form>

    <div class="dlist">
        @forelse ($applications as $a)
            @php $tone = $tones[$a->status] ?? 'warn'; @endphp
            <div class="drow t-{{ $tone }}" style="--i:{{ min($loop->index, 12) }}">
                <span class="av">{{ mb_substr($a->name, 0, 1) }}</span>
                <div class="who">
                    <a href="{{ route('admin.applications.show', $a) }}">{{ $a->name }}</a>
                    <div class="sub">
                        <span>{{ $a->university }}</span>
                        <span>· {{ ($a->payment_submitted_at ?? $a->created_at)->locale('ar')->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="tags">
                    <span class="dtag ltr">{{ $a->trackName() }}</span>
                    <span class="dtag">{{ $a->hours }} ساعة</span>
                    <span class="dtag money">{{ $a->amountDue() }} ₪@if ($a->paymentMethod) · {{ $a->paymentMethod->title }}@endif</span>
                    @if ($tab === 'all')<span class="dtag st-{{ $tone }}">{{ $a->statusLabel() }}</span>@endif
                </div>
                <div class="acts">
                    @if ($a->status === A::PAYMENT_SUBMITTED)
                        @if ($a->receiptUrl())
                            <a class="thumb" href="{{ $a->receiptUrl() }}" title="صورة التحويل" @if ($a->receiptIsImage()) data-lightbox @else target="_blank" rel="noopener" @endif>
                                @if ($a->receiptIsImage())<img src="{{ $a->receiptUrl() }}" alt="صورة التحويل" loading="lazy">@else @include('partials.icon', ['n' => 'image', 'size' => 20]) @endif
                            </a>
                        @endif
                        <form class="inline" method="post" action="{{ route('admin.applications.confirm', $a) }}" data-confirm-row>@csrf
                            <button class="btn-ok" type="submit">@include('partials.icon', ['n' => 'check', 'size' => 16, 'stroke' => 3]) تأكيد</button></form>
                        <a class="btn btn-sm" href="{{ route('admin.applications.show', $a) }}">عرض</a>
                    @elseif ($a->canPay())
                        <a class="btn btn-sm" target="_blank" rel="noopener" href="{{ $a->whatsappLink('مرحبا '.$a->name.'، معك إسناد. لتثبيت مقعدك حوّل '.$a->amountDue().' ₪ وابعتلنا صورة التحويل هون على الواتس.') }}">@include('partials.icon', ['n' => 'chat', 'size' => 16]) تذكير</a>
                        <a class="btn btn-sm" href="{{ route('admin.applications.show', $a) }}">عرض</a>
                    @else
                        <a class="btn btn-sm" href="{{ route('admin.applications.show', $a) }}">التفاصيل @include('partials.icon', ['n' => 'arrow', 'size' => 16])</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="dempty">
                @if ($tab === 'review' && $filters['q'] === '' && ! $filters['track'])
                    <span class="ic">@include('partials.icon', ['n' => 'check-circle', 'size' => 40])</span>
                    <b>كله تمام!</b><span>ما في تحويلات بتستنى تأكيدك هلأ.</span>
                @else
                    <span class="ic" style="background:var(--surface-2);color:var(--muted)">@include('partials.icon', ['n' => 'folder', 'size' => 36])</span>
                    <b>ما في طلبات هون</b><span>جرّبي تبويب تاني أو امسحي البحث.</span>
                @endif
            </div>
        @endforelse
    </div>

    @if ($applications->hasPages())
        <div class="pagination">
            @if (! $applications->onFirstPage())<a class="btn btn-sm" href="{{ $applications->previousPageUrl() }}">السابق</a>@endif
            <span class="muted small">صفحة {{ $applications->currentPage() }} من {{ $applications->lastPage() }}</span>
            @if ($applications->hasMorePages())<a class="btn btn-sm" href="{{ $applications->nextPageUrl() }}">التالي</a>@endif
        </div>
    @endif
@endif

<script>
(function () {
    // المؤشر البرتقالي بينزلق من التبويب القديم للجديد
    var nav = document.querySelector('[data-dtabs]'); if (!nav) return;
    var ind = nav.querySelector('.dtab-ind'), on = nav.querySelector('.dtab.on');
    function place(el, anim) {
        if (!el) { ind.style.width = 0; return; }
        if (!anim) ind.style.transition = 'none';
        ind.style.left = el.offsetLeft + 'px'; ind.style.width = el.offsetWidth + 'px';
        if (!anim) { void ind.offsetWidth; ind.style.transition = ''; }
    }
    var prev = null; try { prev = sessionStorage.getItem('isnad-tab'); } catch (e) {}
    var from = prev && nav.querySelector('.dtab[data-tab="' + prev + '"]');
    place(from && from !== on ? from : on, false);
    requestAnimationFrame(function () { requestAnimationFrame(function () { place(on, true); }); });
    if (on) { try { sessionStorage.setItem('isnad-tab', on.dataset.tab); } catch (e) {} on.scrollIntoView({ block: 'nearest', inline: 'center' }); }
    nav.querySelectorAll('.dtab').forEach(function (a) {
        a.addEventListener('click', function () { try { sessionStorage.setItem('isnad-tab', on ? on.dataset.tab : ''); } catch (e) {} place(a, true); });
    });
    window.addEventListener('resize', function () { place(on, false); });

    // دوائر المقاعد بتتعبّى
    requestAnimationFrame(function () { setTimeout(function () {
        document.querySelectorAll('.dring [data-dash]').forEach(function (c) { c.style.strokeDasharray = c.dataset.dash + ' 999'; });
    }, 60); });

    // تأكيد: الصف بيخضرّ وبيطلع، وبعدين بينبعت
    document.querySelectorAll('[data-confirm-row]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (f.dataset.sent) return; e.preventDefault(); f.dataset.sent = 1;
            var row = f.closest('.drow'); row.classList.add('done', 'leave');
            setTimeout(function () { f.submit(); }, 900);
        });
    });
})();
</script>
@endsection
