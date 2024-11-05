<?php

use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

Schedule::command(\App\Console\Commands\ProcessSendSystemNotification::class)
    ->cron(config('cron_schedule.cron_process_send_notification_system'))
    ->runInBackground();
