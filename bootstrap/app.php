<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.only' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An expired page/session (HTTP 419) on the public booking forms should send the customer
        // back with a clear message instead of a dead-end "Page Expired" screen.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->routeIs('booking.slip')) {
                return redirect()
                    ->route('booking.thankyou', ['booking' => $request->route('booking')])
                    ->withErrors(['slip' => __('site.thankyou.page_expired')]);
            }

            if ($request->routeIs('booking.store')) {
                return back()
                    ->withInput($request->except(['_token']))
                    ->withErrors(['check_in' => __('site.booking.page_expired')]);
            }

            return null;
        });
    })->create();
