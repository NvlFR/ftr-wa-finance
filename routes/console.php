<?php

use App\Console\Commands\CleanupOldExports;
use App\Console\Commands\ProcessRecurringTransactions;
use App\Console\Commands\SendDailyReport; // <-- Import facade Schedule
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendDailyReport::class)->dailyAt('08:00');
Schedule::command(ProcessRecurringTransactions::class)->dailyAt('01:00');
Schedule::command(CleanupOldExports::class)->hourly();
