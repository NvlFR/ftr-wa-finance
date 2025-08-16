<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- Import facade Schedule
use App\Console\Commands\SendDailyReport;
use App\Console\Commands\ProcessRecurringTransactions;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendDailyReport::class)->dailyAt('08:00');
Schedule::command(ProcessRecurringTransactions::class)->dailyAt('01:00');
