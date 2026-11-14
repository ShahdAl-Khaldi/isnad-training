<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;

class ExpireApplications extends Command
{
    protected $signature = 'applications:expire';

    protected $description = 'تحويل الطلبات اللي انتهت مهلة دفعها إلى "انتهت المهلة"';

    public function handle(): int
    {
        $count = Application::expireOverdue();
        $this->info("Expired: {$count}");

        return self::SUCCESS;
    }
}
