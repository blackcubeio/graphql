<?php

declare(strict_types=1);

/**
 * routes.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Graphql\Handlers\Graphql;
use Blackcube\Graphql\Middlewares\PreviewMiddleware;
use Yiisoft\Router\Route;

/** @var array $params */

return [
    Route::post($params['blackcube/graphql']['routePrefix'] ?? '/api/graphql')
        ->middleware(PreviewMiddleware::class)
        ->action(Graphql::class)
        ->name('graphql'),
];
