<?php

declare(strict_types=1);

/**
 * ContentType.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Content;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ContentType extends ObjectType
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
                    'resolve' => fn (Content $content) => $content->getDateStart()?->format('Y-m-d H:i:s'),
                ],
                'dateEnd' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $content) => $content->getDateEnd()?->format('Y-m-d H:i:s'),
                ],
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $content) => $content->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Content $content) => $content->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
                'slug' => [
                    'type' => TypeFactory::slug(),
                    'resolve' => fn (Content $content) => $content->getSlugQuery()->one(),
                ],
                'language' => [
                    'type' => TypeFactory::language(),
                    'resolve' => fn (Content $content) => $content->getLanguageQuery()->one(),
                ],
                'children' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'resolve' => fn (Content $content) => Content::query()->children()->andWhere(['>', 'left', $content->left])->andWhere(['<', 'right', $content->right])->andWhere(['level' => $content->level + 1])->all(),
                ],
                'parent' => [
                    'type' => TypeFactory::content(),
                    'resolve' => fn (Content $content) => Content::query()->parent()->andWhere(['<', 'left', $content->left])->andWhere(['>', 'right', $content->right])->andWhere(['level' => $content->level - 1])->one(),
                ],
                'tags' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::tag()))),
                    'resolve' => fn (Content $content) => $content->getTagsQuery()->all(),
                ],
                'blocs' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::bloc()))),
                    'resolve' => fn (Content $content) => $content->getBlocsQuery()->all(),
                ],
                'authors' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::author()))),
                    'resolve' => fn (Content $content) => $content->getAuthorsQuery()->all(),
                ],
                'translations' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'resolve' => fn (Content $content) => $content->getTranslationsQuery()->all(),
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
                    $schemaId = $content->getElasticSchemaId();
                    if ($schemaId === null || TypeFactory::elasticType((int) $schemaId) === null) {
                        return null;
                    }

                    return ['_elasticSchemaId' => (int) $schemaId] + $content->getElasticValues();
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
        if (empty($filters['typeId']) === false) {
            $query->andWhere(['typeId' => $filters['typeId']]);
        }
        if (empty($filters['languageId']) === false) {
            $query->andWhere(['languageId' => $filters['languageId']]);
        }
        if (isset($filters['level']) === true) {
            $query->andWhere(['level' => $filters['level']]);
        }
        if (empty($filters['parentId']) === false) {
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
