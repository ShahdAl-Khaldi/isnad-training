<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // المبلغ المطلوب من الطالب (حسب الساعات وقت التسجيل)
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedSmallInteger('fee')->nullable()->after('hours');
        });

        // خطوات التحويل لكل طريقة دفع (كل سطر خطوة)
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->text('steps')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('applications', fn (Blueprint $table) => $table->dropColumn('fee'));
        Schema::table('payment_methods', fn (Blueprint $table) => $table->dropColumn('steps'));
    }
};
