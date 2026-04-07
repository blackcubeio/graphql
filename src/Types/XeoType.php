<?php

declare(strict_types=1);

/**
 * XeoType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Models\Xeo;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class XeoType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Xeo',
            'fields' => fn () => [
                'title' => Type::string(),
                'description' => Type::string(),
                'image' => Type::string(),
                'noindex' => Type::nonNull(Type::boolean()),
                'nofollow' => Type::nonNull(Type::boolean()),
                'og' => Type::nonNull(Type::boolean()),
                'ogType' => Type::string(),
                'twitter' => Type::nonNull(Type::boolean()),
                'twitterCard' => Type::string(),
                'jsonldType' => Type::nonNull(Type::string()),
                'keywords' => Type::string(),
                'speakable' => Type::nonNull(Type::boolean()),
                'accessibleForFree' => Type::nonNull(Type::boolean()),
                'active' => Type::nonNull(Type::boolean()),
            ],
        ]);
    }
}
