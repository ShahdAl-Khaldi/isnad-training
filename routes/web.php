<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ===== صفحات الطالب =====
// أول ما يفوت الطالب على الموقع بيلاقي نموذج التسجيل مباشرة
Route::get('/', [StudentController::class, 'create'])->name('apply');
Route::get('/apply', fn (\Illuminate\Http\Request $r) => redirect()->route('apply', $r->query(), 301));
Route::get('/done', [StudentController::class, 'done'])->name('apply.done');
Route::post('/apply', [StudentController::class, 'store'])->middleware('throttle:apply')->name('apply.store');
// صفحة متابعة الطلب انشالت: الطالب بيسجّل، وإسناد بتأكد التحويل وبتبعتله على الواتس.
// الروابط القديمة بترجع على نموذج التسجيل
Route::redirect('/track', '/', 301);
Route::get('/status/{token}', fn () => redirect()->route('apply'));

// ===== ملفات خاصة (صور التحويلات والبروفايل) =====
// بروابط موقّعة ومؤقتة بتنعمل بس للإدارة، وبدون session عشان تحميل الصورة
// ما يتسجّل كـ "الصفحة السابقة" ويخرب رجوع الفورمات.
Route::middleware('signed')
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->group(function () {
        Route::get('/files/receipt/{application}', [AdminApplicationController::class, 'receipt'])->name('admin.applications.receipt');
        Route::get('/files/avatar/{user}', [ProfileController::class, 'showAvatar'])->name('admin.avatar');
    });

// ===== لوحة إسناد =====
Route::get('/admin/login', [AuthController::class, 'form'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export', [DashboardController::class, 'export'])->name('export');

    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/interview', [AdminApplicationController::class, 'interview'])->name('applications.interview');
    Route::post('/applications/{application}/accept', [AdminApplicationController::class, 'accept'])->name('applications.accept');
    Route::post('/applications/{application}/reject', [AdminApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('/applications/{application}/confirm', [AdminApplicationController::class, 'confirm'])->name('applications.confirm');
    Route::post('/applications/{application}/note', [AdminApplicationController::class, 'note'])->name('applications.note');
    Route::delete('/applications/{application}', [AdminApplicationController::class, 'destroy'])->name('applications.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/payment-methods', [SettingsController::class, 'storeMethod'])->name('methods.store');
    Route::put('/settings/payment-methods/{method}', [SettingsController::class, 'updateMethod'])->name('methods.update');
    Route::delete('/settings/payment-methods/{method}', [SettingsController::class, 'destroyMethod'])->name('methods.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('password');

});
