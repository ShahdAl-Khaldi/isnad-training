<?php

return [

    // المسارات التدريبية (المفتاح => الاسم الظاهر)
    'tracks' => [
        'uxui' => 'UX/UI Design',
        'frontend' => 'Front-End',
        'backend' => 'Back-End',
        'flutter' => 'Flutter',
        'graphic' => 'Graphic Design',
        'architecture' => 'Architecture',
    ],

    // أيقونة ووصف قصير لكل مسار (بيظهروا بالصفحة الرئيسية ونموذج التقديم)
    'track_meta' => [
        'uxui' => ['icon' => 'layout', 'desc' => 'بحث المستخدم، تصميم واجهات وتجارب التطبيقات والمواقع.'],
        'frontend' => ['icon' => 'code', 'desc' => 'بناء واجهات ويب تفاعلية وسريعة.'],
        'backend' => ['icon' => 'server', 'desc' => 'السيرفرات، قواعد البيانات وبناء الـ APIs.'],
        'flutter' => ['icon' => 'phone', 'desc' => 'تطبيقات موبايل لأندرويد و iOS بكود واحد.'],
        'graphic' => ['icon' => 'pen', 'desc' => 'هوية بصرية، سوشال ميديا ومطبوعات.'],
        'architecture' => ['icon' => 'building', 'desc' => 'الهندسة المعمارية: تصميم معماري، رسومات تنفيذية وإظهار ثلاثي الأبعاد.'],
    ],

    // خيارات ساعات التدريب المطلوبة من الجامعة
    'hours_options' => [72, 120],

    // الرسوم حسب عدد الساعات (بالشيكل) — بتتعدل من لوحة إسناد ← الإعدادات
    'hours_fees' => [72 => 50, 120 => 70],

    // القيم الافتراضية للإعدادات (بتتعدل من لوحة إسناد ← الإعدادات)
    'defaults' => [
        'deadline_hours' => 48,   // مهلة إعادة رفع التحويل لما يكون مش واضح
        'default_seats' => 10,
        'registration_open' => '1',
        'contact_whatsapp' => '+970566352949',
    ],

    // حساب الإدارة الأول (بينعمل بأمر: php artisan db:seed)
    'admin' => [
        'name' => env('ADMIN_NAME', 'إدارة إسناد'),
        'email' => env('ADMIN_EMAIL', 'admin@isnad.ps'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
