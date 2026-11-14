<?php

use Illuminate\Support\Facades\Schedule;

// كل 5 دقايق: الطلبات اللي خلصت مهلتها بتتحول لـ "انتهت المهلة"
// (والنظام كمان بيفحص المهل كل ما حدا يفتح صفحة الطالب أو لوحة إسناد)
Schedule::command('applications:expire')->everyFiveMinutes();
