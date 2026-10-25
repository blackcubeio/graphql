<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Author;
use Blackcube\Dcore\Entities\Bloc;
use Blackcube\Dcore\Entities\Content;
use Blackcube\Dcore\Entities\Tag;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type of a published Content: fields, hierarchy, relations and elastic values.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
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
                    'resolve' => fn (Content $content) => self::collectChildren($content),
                ],
                'parent' => [
                    'type' => TypeFactory::content(),
                    'resolve' => fn (Content $content) => Content::query()->parent()->andWhere([
                        '<',
                        'left',
                        $content->left,
                    ])->andWhere([
                        '>',
                        'right',
                        $content->right,
                    ])->andWhere(['level' => $content->level - 1])->one(),
                ],
                'tags' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::tag()))),
                    'resolve' => fn (Content $content) => self::collectTags($content),
                ],
                'blocs' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::bloc()))),
                    'resolve' => fn (Content $content) => self::collectBlocs($content),
                ],
                'authors' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::author()))),
                    'resolve' => fn (Content $content) => self::collectAuthors($content),
                ],
                'translations' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'resolve' => fn (Content $content) => self::collectTranslations($content),
                ],
            ] + self::elasticField(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function elasticField(): array
    {
        $field = [];
        $type = TypeFactory::elastic();
        if ($type !== null) {
            $field = [
                'elastic' => [
                    'type' => $type,
                    'resolve' => fn (Content $content) => self::elasticValues($content),
                ],
            ];
        }
        return $field;
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
                $query->andWhere([
                    '>',
                    'left',
                    $parent->left,
                ])
                    ->andWhere([
                        '<',
                        'right',
                        $parent->right,
                    ])
                    ->andWhere(['level' => $parent->level + 1]);
            } else {
                $query->andWhere('1 = 0');
            }
        }

        $pagination = PaginationInput::extract($args);
        $query->limit($pagination['size'])->offset($pagination['offset']);

        $contents = [];
        /** @var Content $listedContent */
        foreach ($query->each() as $listedContent) {
            $contents[] = $listedContent;
        }
        return $contents;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function elasticValues(Content $content): ?array
    {
        $values = null;
        $schemaId = $content->getElasticSchemaId();
        if ($schemaId !== null && TypeFactory::elasticType((int) $schemaId) !== null) {
            $values = ['_elasticSchemaId' => (int) $schemaId] + $content->getElasticValues();
        }
        return $values;
    }

    /**
     * @return Content[]
     */
    private static function collectChildren(Content $content): array
    {
        $childrenQuery = Content::query()->children()->andWhere([
            '>',
            'left',
            $content->left,
        ])->andWhere([
            '<',
            'right',
            $content->right,
        ])->andWhere(['level' => $content->level + 1]);

        $children = [];
        /** @var Content $child */
        foreach ($childrenQuery->each() as $child) {
            $children[] = $child;
        }
        return $children;
    }

    /**
     * @return Tag[]
     */
    private static function collectTags(Content $content): array
    {
        $tags = [];
        /** @var Tag $tag */
        foreach ($content->getTagsQuery()->each() as $tag) {
            $tags[] = $tag;
        }
        return $tags;
    }

    /**
     * @return Bloc[]
     */
    private static function collectBlocs(Content $content): array
    {
        $blocs = [];
        /** @var Bloc $bloc */
        foreach ($content->getBlocsQuery()->each() as $bloc) {
            $blocs[] = $bloc;
        }
        return $blocs;
    }

    /**
     * @return Author[]
     */
    private static function collectAuthors(Content $content): array
    {
        $authors = [];
        /** @var Author $author */
        foreach ($content->getAuthorsQuery()->each() as $author) {
            $authors[] = $author;
        }
        return $authors;
    }

    /**
     * @return Content[]
     */
    private static function collectTranslations(Content $content): array
    {
        $translations = [];
        /** @var Content $translation */
        foreach ($content->getTranslationsQuery()->each() as $translation) {
            $translations[] = $translation;
        }
        return $translations;
    }
}
