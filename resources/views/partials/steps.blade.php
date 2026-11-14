@php
    $labels = ['التسجيل والتحويل', 'مراجعة التحويل', 'تثبيت المقعد'];
    $current = $app ? $app->stepIndex() : 0;
    $failed = $app && in_array($app->status, [\App\Models\Application::REJECTED, \App\Models\Application::EXPIRED], true);
    $done = $app && $app->status === \App\Models\Application::CONFIRMED;
@endphp
<div class="steps" style="grid-template-columns:repeat({{ count($labels) }}, minmax(0, 1fr))" aria-label="مراحل الطلب">
    @foreach ($labels as $i => $label)
        @php
            $cls = '';
            if ($done || $i < $current) $cls = 'done';
            elseif ($i === $current) $cls = $failed ? 'done fail' : 'now';
        @endphp
        <div class="step {{ $cls }}" style="--i: {{ $i }}" @if ($i === $current && ! $done) aria-current="step" @endif>
            <i></i><span>{{ $label }}</span>
        </div>
    @endforeach
</div>
