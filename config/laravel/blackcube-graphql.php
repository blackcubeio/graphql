<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

/**
 * Blackcube GraphQL configuration for Laravel.
 * Publish with: php artisan vendor:publish --tag=blackcube-graphql-config
 */

return [
    'routePrefix' => '/api/graphql',
    'debug' => (bool) env('APP_DEBUG', false),
    'fastSchema' => true,
];
