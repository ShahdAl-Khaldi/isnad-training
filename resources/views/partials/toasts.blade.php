@php
    $flash = collect(['success' => '', 'info' => '', 'error' => 'err'])
        ->filter(fn ($cls, $key) => is_string(session($key)) && session($key) !== '');
@endphp
@if ($flash->isNotEmpty())
    <div class="toasts" role="status" aria-live="polite">
        @foreach ($flash as $key => $cls)
            <div class="toast {{ $cls }}">
                @include('partials.icon', ['n' => $key === 'error' ? 'alert' : 'check-circle'])
                <span>{{ session($key) }}</span>
                <button type="button" aria-label="إغلاق">@include('partials.icon', ['n' => 'x', 'size' => 16])</button>
            </div>
        @endforeach
    </div>
@endif
