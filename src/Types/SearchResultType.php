<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Content;
use Blackcube\Dcore\Entities\Tag;
use Blackcube\Dcore\Helpers\SearchHelper;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL result of a full-text search: matching contents and tags, ranked by relevance.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class SearchResultType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'SearchResult',
            'fields' => fn () => [
                'contents' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                'tags' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::tag()))),
            ],
        ]);
    }

    /**
     * Full-text search across Contents and Tags, ranked by relevance (DESC).
     *
     * Queries the Entity layer (published content only) and paginates each list.
     *
     * @return array{contents: Content[], tags: Tag[]}
     */
    public static function results(mixed $root, array $args): array
    {
        $contents = [];
        $tags = [];
        $search = trim($args['query'] ?? '');
        if ($search !== '') {
            $pagination = PaginationInput::extract($args);

            $contentsQuery = SearchHelper::contentQuery($search, Content::class)
                ->limit($pagination['size'])
                ->offset($pagination['offset']);

            $tagsQuery = SearchHelper::tagQuery($search, Tag::class)
                ->limit($pagination['size'])
                ->offset($pagination['offset']);

            /** @var Content $content */
            foreach ($contentsQuery->each() as $content) {
                $contents[] = $content;
            }

            /** @var Tag $tag */
            foreach ($tagsQuery->each() as $tag) {
                $tags[] = $tag;
            }
        }

        return [
            'contents' => $contents,
            'tags' => $tags,
        ];
    }
}
