<?php

declare(strict_types=1);

/**
 * MenuType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Menu;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class MenuType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Menu',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::int()),
                'name' => Type::nonNull(Type::string()),
                'languageId' => Type::nonNull(Type::string()),
                'route' => Type::string(),
                'queryString' => Type::string(),
                'level' => Type::int(),
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Menu $m) => $m->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Menu $m) => $m->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
                'children' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::menu()))),
                    'resolve' => fn (Menu $m) => Menu::query()->children()->andWhere(['>', 'left', $m->left])->andWhere(['<', 'right', $m->right])->andWhere(['level' => $m->level + 1])->all(),
                ],
                'parent' => [
                    'type' => TypeFactory::menu(),
                    'resolve' => fn (Menu $m) => Menu::query()->parent()->andWhere(['<', 'left', $m->left])->andWhere(['>', 'right', $m->right])->andWhere(['level' => $m->level - 1])->one(),
                ],
                'language' => [
                    'type' => TypeFactory::language(),
                    'resolve' => fn (Menu $m) => $m->getLanguageQuery()->one(),
                ],
            ],
        ]);
    }

    public static function one(mixed $root, array $args): ?Menu
    {
        return Menu::query()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    /**
     * @return Menu[]
     */
    public static function list(mixed $root, array $args): array
    {
        $query = Menu::query()->orderBy(['left' => SORT_ASC]);

        $pagination = PaginationInput::extract($args);
        $query->limit($pagination['size'])->offset($pagination['offset']);

        return $query->all();
    }
}
