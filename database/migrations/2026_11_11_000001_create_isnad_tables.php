<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title');                 // مثال: بنك فلسطين، جوال باي
            $table->string('type')->default('bank'); // bank | wallet
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();   // رابط التتبع الخاص بالطالب
            $table->string('code', 16)->unique();    // كود قصير مثل ISN-48213
            $table->string('name');
            $table->string('phone', 32)->index();
            $table->string('university');
            $table->string('major');
            $table->string('track', 32)->index();
            $table->unsignedSmallInteger('hours');
            $table->string('portfolio_url')->nullable();
            $table->boolean('eligible')->default(true);
            $table->text('notes')->nullable();

            $table->string('status', 32)->default('submitted')->index();

            $table->dateTime('interview_at')->nullable();
            $table->string('interview_location')->nullable();

            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('payment_deadline')->nullable()->index();

            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->dateTime('payment_submitted_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();

            $table->dateTime('expired_at')->nullable();
            $table->dateTime('waitlisted_at')->nullable();

            $table->text('student_message')->nullable(); // رسالة من الإدارة بتظهر للطالب
            $table->text('admin_note')->nullable();      // ملاحظة داخلية
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('settings');
    }
};
