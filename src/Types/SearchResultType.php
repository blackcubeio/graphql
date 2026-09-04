<?php

declare(strict_types=1);

/**
 * SearchResultType.php
 *
 * PHP Version 8.4
 *
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
        $search = trim($args['query'] ?? '');
        if ($search === '') {
            return ['contents' => [], 'tags' => []];
        }

        $pagination = PaginationInput::extract($args);

        $contentsQuery = SearchHelper::contentQuery($search, Content::class)
            ->limit($pagination['size'])
            ->offset($pagination['offset']);

        $tagsQuery = SearchHelper::tagQuery($search, Tag::class)
            ->limit($pagination['size'])
            ->offset($pagination['offset']);

        return [
            'contents' => $contentsQuery->all(),
            'tags' => $tagsQuery->all(),
        ];
    }
}
