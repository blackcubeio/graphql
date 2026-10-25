<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL input filtering tags by type, level and parent.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TagFilterInput extends InputObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'TagFilter',
            'fields' => [
                'typeId' => Type::int(),
                'level' => Type::int(),
                'parentId' => Type::int(),
            ],
        ]);
    }
}
