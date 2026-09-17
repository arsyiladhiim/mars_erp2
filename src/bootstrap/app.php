<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The "ERP AI" full-page chat lives outside the Filament panel but shares
        // its session/guard — send unauthenticated visitors to the panel login.
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Runs on the 1st of each month, posting that same month's charge
        // (--date defaults to today, resolved to that month's end). Idempotent
        // — AssetDepreciationService skips assets already run for a given
        // period — so a missed or retried run is harmless.
        $schedule->command('assets:depreciate')->monthlyOn(1, '01:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
