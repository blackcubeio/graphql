<?php

declare(strict_types=1);

/**
 * ContentType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Content;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class ContentType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Content',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::int()),
                'name' => Type::string(),
                'languageId' => Type::string(),
                'typeId' => Type::int(),
                'level' => Type::int(),
                'active' => Type::nonNull(Type::boolean()),
                'dateStart' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $c) => $c->getDateStart()?->format('Y-m-d H:i:s'),
                ],
                'dateEnd' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $c) => $c->getDateEnd()?->format('Y-m-d H:i:s'),
                ],
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $c) => $c->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $c) => $c->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
                'slug' => [
                    'type' => TypeFactory::slug(),
                    'resolve' => fn (Content $c) => $c->getSlugQuery()->one(),
                ],
                'language' => [
                    'type' => TypeFactory::language(),
                    'resolve' => fn (Content $c) => $c->getLanguageQuery()->one(),
                ],
                'children' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'resolve' => fn (Content $c) => Content::query()->children()->andWhere(['>', 'left', $c->left])->andWhere(['<', 'right', $c->right])->andWhere(['level' => $c->level + 1])->all(),
                ],
                'parent' => [
                    'type' => TypeFactory::content(),
                    'resolve' => fn (Content $c) => Content::query()->parent()->andWhere(['<', 'left', $c->left])->andWhere(['>', 'right', $c->right])->andWhere(['level' => $c->level - 1])->one(),
                ],
                'tags' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::tag()))),
                    'resolve' => fn (Content $c) => $c->getTagsQuery()->all(),
                ],
                'blocs' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::bloc()))),
                    'resolve' => fn (Content $c) => $c->getBlocsQuery()->all(),
                ],
                'authors' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::author()))),
                    'resolve' => fn (Content $c) => $c->getAuthorsQuery()->all(),
                ],
                'translations' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'resolve' => fn (Content $c) => $c->getTranslationsQuery()->all(),
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
                'resolve' => function (Content $c): ?array {
                    $schemaId = $c->getElasticSchemaId();
                    if ($schemaId === null || TypeFactory::elasticType((int) $schemaId) === null) {
                        return null;
                    }

                    return ['_elasticSchemaId' => (int) $schemaId] + $c->getElasticValues();
                },
            ],
        ];
    }

    /**
     * Resolve a single Content by ID.
     */
    public static function one(mixed $root, array $args): ?Content
    {
        return Content::query()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    /**
     * Resolve a list of Contents with pagination and filters.
     *
     * @return Content[]
     */
    public static function list(mixed $root, array $args): array
    {
        $query = Content::query()->orderBy(['dateCreate' => SORT_DESC]);

        $filters = $args['filters'] ?? [];
        if (!empty($filters['typeId'])) {
            $query->andWhere(['typeId' => $filters['typeId']]);
        }
        if (!empty($filters['languageId'])) {
            $query->andWhere(['languageId' => $filters['languageId']]);
        }
        if (isset($filters['level'])) {
            $query->andWhere(['level' => $filters['level']]);
        }
        if (!empty($filters['parentId'])) {
            $parent = Content::query()->andWhere(['id' => $filters['parentId']])->one();
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
