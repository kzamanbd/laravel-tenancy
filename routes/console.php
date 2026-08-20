<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Certificates renew automatically, but only while the customer's DNS still
// points at us. Checking daily is what turns a silent expiry into a warning
// before browsers start showing one.
Schedule::command('domains:check')->daily();

// Backstop for the publish pipeline: if a queued republish was ever lost, a
// page should not stay stale indefinitely.
Schedule::command('status-page:publish --stale')->hourly();
