<?php

declare(strict_types=1);

/**
 * routes.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
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
