<?php

declare(strict_types=1);

/**
 * AuthorType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Author;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class AuthorType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Author',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::int()),
                'firstname' => Type::nonNull(Type::string()),
                'lastname' => Type::nonNull(Type::string()),
                'email' => Type::string(),
                'jobTitle' => Type::string(),
                'worksFor' => Type::string(),
                'knowsAbout' => Type::string(),
                'sameAs' => Type::string(),
                'url' => Type::string(),
                'image' => Type::string(),
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Author $a) => $a->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Author $a) => $a->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }

    public static function one(mixed $root, array $args): ?Author
    {
        return Author::query()
            ->andWhere(['id' => $args['id']])
            ->active()
            ->one();
    }

    /**
     * @return Author[]
     */
    public static function list(mixed $root, array $args): array
    {
        $query = Author::query()
            ->active()
            ->orderBy(['lastname' => SORT_ASC, 'firstname' => SORT_ASC]);

        $pagination = PaginationInput::extract($args);
        $query->limit($pagination['size'])->offset($pagination['offset']);

        return $query->all();
    }
}
