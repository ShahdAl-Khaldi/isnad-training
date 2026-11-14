@extends('layouts.app', ['confetti' => true])
@section('title', 'تم تسجيلك')

@section('content')
<div class="done-page">
    <canvas class="hero-canvas" data-constellation aria-hidden="true"></canvas>
    <div class="card done-card spot rise">
        <span class="done-check" aria-hidden="true">
            <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg>
        </span>
        <h1>تم تسجيلك{{ $name ? ' يا '.$name : '' }} 🎉</h1>
        <p>وصلنا طلبك وتحويلك. رح نتأكد من التحويل ونتواصل معك، وبنبعتلك رسالة على الواتس.</p>
    </div>
</div>
@endsection
