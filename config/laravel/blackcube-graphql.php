<?php

declare(strict_types=1);

/**
 * Blackcube GraphQL configuration for Laravel.
 * Publish with: php artisan vendor:publish --tag=blackcube-graphql-config
 */

return [
    'routePrefix' => '/api/graphql',
    'debug' => (bool) env('APP_DEBUG', false),
    'fastSchema' => true,
];
