<?php

declare(strict_types=1);

/**
 * di.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Graphql\Handlers\Graphql;
use Blackcube\Graphql\Types\TypeFactory;

/** @var array $params */

return [
    Graphql::class => [
        'class' => Graphql::class,
        '__construct()' => [
            'debug' => $params['blackcube/graphql']['debug'],
            'fastSchema' => $params['blackcube/graphql']['fastSchema'],
        ],
    ],
];
