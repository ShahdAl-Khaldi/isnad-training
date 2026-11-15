<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use App\Support\FileStore;

class ClearApplications extends Command
{
    protected $signature = 'applications:clear {--force : بدون سؤال تأكيد}';

    protected $description = 'مسح كل طلبات الطلاب وصور التحويلات (بيضل حساب الأدمن والإعدادات وطرق الدفع)';

    public function handle(): int
    {
        $count = Application::count();
        if ($count === 0) {
            $this->info('ما في طلبات لتنمسح.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("رح ينمسح {$count} طلب مع صور التحويلات، وما في رجوع. متأكد؟")) {
            $this->warn('ما انمسح إشي.');

            return self::SUCCESS;
        }

        Application::query()->delete();
        FileStore::deleteDirectory('receipts');

        $this->info("انمسح {$count} طلب مع صور التحويلات. حساب الأدمن والإعدادات وطرق الدفع زي ما هم.");

        return self::SUCCESS;
    }
}
