<?php

declare(strict_types=1);

/**
 * di.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
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
