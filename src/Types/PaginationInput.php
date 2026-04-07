<?php

declare(strict_types=1);

/**
 * PaginationInput.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class PaginationInput extends InputObjectType
{
    private const DEFAULT_SIZE = 10;
    private const DEFAULT_OFFSET = 0;

    public function __construct()
    {
        parent::__construct([
            'name' => 'Pagination',
            'fields' => [
                'size' => ['type' => Type::int(), 'defaultValue' => self::DEFAULT_SIZE],
                'offset' => ['type' => Type::int(), 'defaultValue' => self::DEFAULT_OFFSET],
            ],
        ]);
    }

    /**
     * Extract pagination values from GraphQL args.
     *
     * @return array{size: int, offset: int}
     */
    public static function extract(array $args): array
    {
        $pagination = $args['pagination'] ?? [];

        return [
            'size' => (int) ($pagination['size'] ?? self::DEFAULT_SIZE),
            'offset' => (int) ($pagination['offset'] ?? self::DEFAULT_OFFSET),
        ];
    }
}
