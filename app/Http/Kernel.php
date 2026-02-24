<?php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

protected $middlewareAliases = [
    // ... otros middleware
    'role' => \App\Http\Middleware\CheckRole::class,
];