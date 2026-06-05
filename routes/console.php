<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ── Alertes clients automatiques ──────────────────────────────────
// Anniversaires du jour + relance inactifs (1er du mois)
// Tourne chaque jour à 09h00
Schedule::command('clients:alerts')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/clients-alerts.log'));
