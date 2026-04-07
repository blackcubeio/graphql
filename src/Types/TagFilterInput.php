<?php

declare(strict_types=1);

/**
 * TagFilterInput.php
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

final class TagFilterInput extends InputObjectType
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
