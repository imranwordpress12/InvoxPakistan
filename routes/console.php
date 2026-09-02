<?php

use App\Console\Commands\ProcessExpiredSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PRD #49: "periodically" find lapsed subscriptions and process them.
// Hourly keeps the dashboard/Pending Companies page reasonably fresh
// without being excessive — access itself never depends on this timing,
// since Phase 7's middleware already checks status+ends_at in real time
// regardless of whether this has run yet. withoutOverlapping() means a
// slow run can't start a second overlapping one (PRD #64 case 7); the
// command is also self-protecting even without it — see its docblock.
Schedule::command(ProcessExpiredSubscriptions::class)
    ->hourly()
    ->withoutOverlapping();
