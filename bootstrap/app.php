<?php

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
        // Traefik terminates TLS and forwards plain HTTP into the container, so
        // without this Laravel believes every request arrived over http. Signed
        // URLs are where that bites first: Livewire signs its upload endpoint as
        // http, the browser requests it as https, the two signatures disagree and
        // every file upload comes back 401.
        //
        // Trusting every proxy is safe here only because the container port is
        // not published to the host — Traefik on the coolify network is the sole
        // ingress, so nothing else is in a position to forge the header. If the
        // port is ever exposed directly this must become an explicit address.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
