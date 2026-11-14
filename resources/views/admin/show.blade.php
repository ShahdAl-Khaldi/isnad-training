@extends('layouts.admin')
@section('title', $app->name)
@section('heading', $app->name)

@php
    use App\Models\Application as A;
    $wa = fn ($msg) => $app->whatsappLink($msg);
    $events = array_filter([
        ['سجّل', $app->created_at],
        $app->payment_submitted_at ? ['رفع صورة التحويل', $app->payment_submitted_at] : null,
        $app->expired_at ? ['انتهت المهلة', $app->expired_at] : null,
        $app->confirmed_at ? ['تثبّت المقعد', $app->confirmed_at] : null,
    ]);
    usort($events, fn ($a, $b) => $a[1] <=> $b[1]);
@endphp

@section('actions')
    <a class="btn" href="{{ route('admin.dashboard') }}">@include('partials.icon', ['n' => 'arrow-back', 'size' => 18]) كل الطلبات</a>
    @php $isnadWa = \App\Models\Application::normalizePhone(\App\Models\Setting::get('contact_whatsapp') ?: config('isnad.defaults.contact_whatsapp')); @endphp
    <a class="btn btn-green" href="https://wa.me/{{ $isnadWa }}" target="_blank" rel="noopener">@include('partials.icon', ['n' => 'chat', 'size' => 18]) واتساب</a>
@endsection

@section('content')
<div class="grid-detail">
    {{-- ===== بيانات الطالب ===== --}}
    <div class="stack-lg">
        <section class="card stack fade-up">
            <div class="row between">
                <div class="row" style="gap:14px">
                    <span class="initials" style="width:56px;height:56px;font-size:22px;border-radius:18px">{{ mb_substr($app->name, 0, 1) }}</span>
                    <div><b style="font-family:var(--font-display);font-size:20px">{{ $app->name }}</b><div class="muted small"><span class="ltr">{{ $app->trackName() }}</span> · قدّم {{ $app->created_at->diffForHumans() }}</div></div>
                </div>
                <span class="badge badge-{{ $app->statusTone() }}">{{ $app->statusLabel() }}</span>
            </div>
            @include('partials.steps', ['app' => $app])
            <div class="table-box">
                <table class="info">
                    <tr><th>كود الطلب</th><td><span class="ltr">{{ $app->code }}</span></td></tr>
                    <tr><th>واتساب</th><td><a class="ltr" href="{{ $wa('مرحبا '.$app->name.'، معك إسناد.') }}" target="_blank" rel="noopener">{{ $app->phone }}</a></td></tr>
                    <tr><th>الجامعة</th><td>{{ $app->university }}</td></tr>
                    <tr><th>التخصص</th><td>{{ $app->major }}</td></tr>
                    <tr><th>الساعات المطلوبة</th><td>{{ $app->hours }} ساعة</td></tr>
                    <tr><th>المبلغ</th><td>{{ $app->amountDue() }} ₪</td></tr>
                    <tr><th>رابط الأعمال</th><td>@if ($app->portfolio_url)<a href="{{ $app->portfolio_url }}" target="_blank" rel="noopener noreferrer">فتح الرابط ↗</a>@else <span class="muted">—</span>@endif</td></tr>
                    @if ($app->notes)<tr><th>ملاحظات الطالب</th><td style="white-space:pre-line;font-weight:500">{{ $app->notes }}</td></tr>@endif
                </table>
            </div>
        </section>

        <section class="card stack fade-up" style="--d:1">
            <h2>سجل الطلب</h2>
            <div class="timeline">
                @foreach ($events as $e)
                    <div class="{{ $loop->last ? 'last' : '' }}"><b>{{ $e[0] }}</b><span>{{ $e[1]->format('Y-m-d H:i') }} · {{ $e[1]->diffForHumans() }}</span></div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="stack-lg">
        {{-- ===== الإجراءات حسب الحالة ===== --}}
        @if ($app->canPay())
            <section class="card stack fade-up" style="--d:1">
                <h2>بانتظار التحويل</h2>
                <p class="muted small">لسا ما وصلنا تحويل من الطالب. المبلغ: <b>{{ $app->amountDue() }} ₪</b></p>
                <a class="btn btn-green" target="_blank" rel="noopener" href="{{ $wa('مرحبا '.$app->name.'، معك إسناد. لتثبيت مقعدك بمسار '.$app->trackName().' حوّل '.$app->amountDue().' ₪ وابعتلنا صورة التحويل هون على الواتس.') }}">@include('partials.icon', ['n' => 'chat', 'size' => 18]) تذكير واتساب</a>
                <form method="post" action="{{ route('admin.applications.confirm', $app) }}">@csrf
                    <button class="btn btn-block" type="submit"><span class="label">دفع كاش / تأكيد يدوي</span></button>
                </form>
            </section>
        @endif

        @if ($app->receipt_path)
            <section class="card stack fade-up" style="--d:1">
                <h2>إثبات الدفع</h2>
                @if ($app->receiptIsImage())
                    <a class="receipt-thumb" href="{{ $app->receiptUrl() }}" data-lightbox>
                        <img class="receipt-img" src="{{ $app->receiptUrl() }}" alt="صورة التحويل">
                        <span class="zoom">@include('partials.icon', ['n' => 'search', 'size' => 14]) تكبير</span>
                    </a>
                @else
                    <a class="btn" href="{{ $app->receiptUrl() }}" target="_blank" rel="noopener">@include('partials.icon', ['n' => 'download', 'size' => 18]) افتح ملف التحويل (PDF)</a>
                @endif
                <div class="table-box"><table class="info">
                    <tr><th>طريقة الدفع</th><td>{{ $app->paymentMethod?->title ?? '—' }}</td></tr>
                    <tr><th>رقم الحوالة</th><td><span class="ltr">{{ $app->payment_reference ?: '—' }}</span></td></tr>
                    <tr><th>وقت الرفع</th><td>{{ $app->payment_submitted_at?->format('Y-m-d H:i') }}</td></tr>
                </table></div>
                @if ($app->status === A::PAYMENT_SUBMITTED)
                    <form method="post" action="{{ route('admin.applications.confirm', $app) }}">@csrf
                        <button class="btn btn-green btn-lg btn-block" type="submit">@include('partials.icon', ['n' => 'check']) <span class="label">تأكيد الدفع وتثبيت المقعد</span></button>
                    </form>
                @endif
            </section>
        @endif

        @if ($app->status === A::EXPIRED)
            <section class="card stack fade-up" style="--d:1">
                <h2>الطلب منتهي</h2>
                <p class="muted">المقعد صار فاضي لطلاب جداد. إذا حوّل الطالب بتقدر تثبّته من هون.</p>
                <form method="post" action="{{ route('admin.applications.confirm', $app) }}">@csrf
                    <button class="btn btn-block" type="submit"><span class="label">ثبّت المقعد</span></button></form>
            </section>
        @endif

        @if ($app->status === A::CONFIRMED)
            <section class="card stack fade-up center" style="--d:1;align-items:center">
                <span class="status-ico green">@include('partials.icon', ['n' => 'award', 'size' => 38])</span>
                <h2>مقعد مثبّت</h2>
                <a class="btn btn-green" target="_blank" rel="noopener" href="{{ $wa('أهلاً '.$app->name.'، تثبّت مقعدك بمسار '.$app->trackName().' بإسناد. رح نبعتلك تفاصيل أول يوم قريباً.') }}">@include('partials.icon', ['n' => 'chat', 'size' => 18]) ابعت تأكيد واتساب</a>
            </section>
        @endif

        @if (! in_array($app->status, [A::REJECTED, A::CONFIRMED], true))
            <details class="box fade-up" style="--d:2">
                <summary>خيارات تانية</summary>
                <div class="stack" style="margin-top:12px">
                    <form method="post" action="{{ route('admin.applications.reject', $app) }}" class="stack" onsubmit="return confirm('أكيد بدك ترفض طلب {{ e($app->name) }}؟')">@csrf
                        <button class="btn btn-danger btn-sm" type="submit"><span class="label">ارفض الطلب</span></button>
                    </form>
                </div>
            </details>
        @endif

        {{-- ===== ملاحظات ===== --}}
        <section class="card stack fade-up" style="--d:2">
            <h2>ملاحظات</h2>
            <form method="post" action="{{ route('admin.applications.note', $app) }}" class="stack">@csrf
                <div class="field">
                    <label for="admin_note">ملاحظة داخلية <span class="muted small">(ما بيشوفها الطالب)</span></label>
                    <textarea class="input" id="admin_note" name="admin_note">{{ $app->admin_note }}</textarea>
                </div>
                <button class="btn" type="submit"><span class="label">احفظ</span></button>
            </form>
        </section>

        <form method="post" action="{{ route('admin.applications.destroy', $app) }}" onsubmit="return confirm('أكيد بدك تحذف طلب {{ e($app->name) }}؟ ما في رجوع.')">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm" type="submit">@include('partials.icon', ['n' => 'trash', 'size' => 16]) حذف الطلب</button>
        </form>
    </div>
</div>
@endsection
