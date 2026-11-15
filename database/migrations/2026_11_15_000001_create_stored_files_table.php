<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // صور التحويلات والبروفايل لما تنحفظ جوّا قاعدة البيانات (على الاستضافة)
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->longText('data'); // base64
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
