<?php

declare(strict_types=1);

/**
 * LanguageType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Language;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class LanguageType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Language',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::string()),
                'name' => Type::nonNull(Type::string()),
                'main' => Type::nonNull(Type::boolean()),
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Language $l) => $l->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Language $l) => $l->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }

    public static function one(mixed $root, array $args): ?Language
    {
        return Language::query()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    /**
     * @return Language[]
     */
    public static function list(mixed $root, array $args): array
    {
        $query = Language::query()
            ->active()
            ->orderBy(['name' => SORT_ASC]);

        $pagination = PaginationInput::extract($args);
        $query->limit($pagination['size'])->offset($pagination['offset']);

        return $query->all();
    }
}
