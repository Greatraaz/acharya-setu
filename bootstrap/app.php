<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\MentorApproved;
use App\Http\Middleware\OnboardingComplete;
use App\Http\Middleware\Api\MentorMiddleware;
use App\Http\Middleware\Api\MenteeMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            //'auth'  => AuthMiddleware::class,
            'custom.auth' => AuthMiddleware::class,
            'role'  => RoleMiddleware::class,
            'mentor.approved'     => MentorApproved::class,
            'onboarding.complete' => OnboardingComplete::class,
            'mentor' => MentorMiddleware::class,
            'mentee' => MenteeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (
        \Illuminate\Auth\AuthenticationException $e,
        $request
        ) {
            return response()->json([
                'status' => false,
                'statusCode' => 401,
                'message' => 'Unauthenticated.'
            ], 401);
        });
    })->create();
