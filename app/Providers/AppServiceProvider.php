<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Fix pour MySQL < 5.7.7 / MariaDB < 10.2.2 (WAMP)
        Schema::defaultStringLength(191);

        // Carbon en français
    