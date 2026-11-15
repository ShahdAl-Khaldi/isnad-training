<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'التدريب الميداني') · إسناد</title>
<meta name="description" content="التدريب الميداني في إسناد لطلاب الجامعات والخريجين: مشاريع حقيقية، إشراف مختص، وشهادة إتمام.">
<meta name="theme-color" content="#12285A">
@if (!empty($noindex))<meta name="robots" content="noindex">@endif
<link rel="icon" href="{{ asset('images/isnad-logo.png') }}">
{{-- صورة المعاينة لما ينبعت الرابط (واتساب، فيسبوك، تيليجرام...) --}}
@php $ogTitle = trim($__env->yieldContent('title', 'التدريب الميداني')).' · إسناد'; @endphp
<meta property="og:type" content="website">
<meta property="og:site_name" content="إسناد">
<meta property="og:locale" content="ar_AR">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="التدريب الميداني في إسناد لطلاب الجامعات والخريجين: مشاريع حقيقية، إشراف مختص، وشهادة إتمام.">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('images/og-image.jpg') }}?v=1">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="طلاب بيشتغلوا على مشاريع بمختبر إسناد">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ asset('images/og-image.jpg') }}?v=1">
<script>
    // الوضع الفاتح/الغامق قبل ما تنرسم الصفحة (بدون وميض)
    // الوضع الليلي هو الأساسي، والزائر بيقدر يغيّره للفاتح ومنحفظ اختياره
    (function () { var t = 'dark'; try { t = localStorage.getItem('isnad-theme') || 'dark'; } catch (e) {} document.documentElement.setAttribute('data-theme', t); })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
{{-- احتياطي لحد ما تنضاف ملفات خط ثمانية --}}
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">
@include('partials.fonts')
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
