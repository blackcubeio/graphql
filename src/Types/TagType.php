<?php

declare(strict_types=1);

/**
 * TagType.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Tag;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TagType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Tag',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::int()),
                'name' => Type::nonNull(Type::string()),
                'typeId' => Type::int(),
                'level' => Type::int(),
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Tag $tag) => $tag->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Tag $tag) => $tag->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
                'slug' => [
                    'type' => TypeFactory::slug(),
                    'resolve' => fn (Tag $tag) => $tag->getSlugQuery()->one(),
                ],
                'children' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::tag()))),
                    'resolve' => fn (Tag $tag) => Tag::query()->children()->andWhere(['>', 'left', $tag->left])->andWhere(['<', 'right', $tag->right])->andWhere(['level' => $tag->level + 1])->all(),
                ],
                'parent' => [
                    'type' => TypeFactory::tag(),
                    'resolve' => fn (Tag $tag) => Tag::query()->parent()->andWhere(['<', 'left', $tag->left])->andWhere(['>', 'right', $tag->right])->andWhere(['level' => $tag->level - 1])->one(),
                ],
                'contents' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'resolve' => fn (Tag $tag) => $tag->getContentsQuery()->all(),
                ],
                'blocs' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::bloc()))),
                    'resolve' => fn (Tag $tag) => $tag->getBlocsQuery()->all(),
                ],
                'authors' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::author()))),
                    'resolve' => fn (Tag $tag) => $tag->getAuthorsQuery()->all(),
                ],
            ] + self::elasticField(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function elasticField(): array
    {
        $type = TypeFactory::elastic();
        if ($type === null) {
            return [];
        }

        return [
            'elastic' => [
                'type' => $type,
                'resolve' => function (Tag $t): ?array {
                    $schemaId = $tag->getElasticSchemaId();
                    if ($schemaId === null || TypeFactory::elasticType((int) $schemaId) === null) {
                        return null;
                    }

                    return ['_elasticSchemaId' => (int) $schemaId] + $tag->getElasticValues();
                },
            ],
        ];
    }

    public static function one(mixed $root, array $args): ?Tag
    {
        return Tag::query()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    /**
     * @return Tag[]
     */
    public static function list(mixed $root, array $args): array
    {
        $query = Tag::query()->orderBy(['name' => SORT_ASC]);

        $filters = $args['filters'] ?? [];
        if (empty($filters['typeId']) === false) {
            $query->andWhere(['typeId' => $filters['typeId']]);
        }
        if (isset($filters['level']) === true) {
            $query->andWhere(['level' => $filters['level']]);
        }
        if (empty($filters['parentId']) === false) {
            $parent = Tag::query()->andWhere(['id' => $filters['parentId']])->one();
            if ($parent !== null) {
                $query->andWhere(['>', 'left', $parent->left])
                    ->andWhere(['<', 'right', $parent->right])
                    ->andWhere(['level' => $parent->level + 1]);
            } else {
                $query->andWhere('1 = 0');
            }
        }

        $pagination = PaginationInput::extract($args);
        $query->limit($pagination['size'])->offset($pagination['offset']);

        return $query->all();
    }
}
