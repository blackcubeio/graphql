<?php

declare(strict_types=1);

/**
 * QueryType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class QueryType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => fn () => [
                // Content
                'content' => [
                    'type' => TypeFactory::content(),
                    'args' => ['id' => Type::nonNull(Type::int())],
                    'resolve' => [ContentType::class, 'one'],
                ],
                'contents' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::content()))),
                    'args' => [
                        'pagination' => TypeFactory::pagination(),
                        'filters' => TypeFactory::contentFilter(),
                    ],
                    'resolve' => [ContentType::class, 'list'],
                ],

                // Tag
                'tag' => [
                    'type' => TypeFactory::tag(),
                    'args' => ['id' => Type::nonNull(Type::int())],
                    'resolve' => [TagType::class, 'one'],
                ],
                'tags' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::tag()))),
                    'args' => [
                        'pagination' => TypeFactory::pagination(),
                        'filters' => TypeFactory::tagFilter(),
                    ],
                    'resolve' => [TagType::class, 'list'],
                ],

                // Menu
                'menu' => [
                    'type' => TypeFactory::menu(),
                    'args' => ['id' => Type::nonNull(Type::int())],
                    'resolve' => [MenuType::class, 'one'],
                ],
                'menus' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::menu()))),
                    'args' => ['pagination' => TypeFactory::pagination()],
                    'resolve' => [MenuType::class, 'list'],
                ],

                // Language
                'language' => [
                    'type' => TypeFactory::language(),
                    'args' => ['id' => Type::nonNull(Type::string())],
                    'resolve' => [LanguageType::class, 'one'],
                ],
                'languages' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::language()))),
                    'args' => ['pagination' => TypeFactory::pagination()],
                    'resolve' => [LanguageType::class, 'list'],
                ],

                // Parameter
                'parameter' => [
                    'type' => TypeFactory::parameter(),
                    'args' => [
                        'domain' => Type::nonNull(Type::string()),
                        'name' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => [ParameterType::class, 'one'],
                ],
                'parameters' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::parameter()))),
                    'resolve' => [ParameterType::class, 'list'],
                ],

                // Author
                'author' => [
                    'type' => TypeFactory::author(),
                    'args' => ['id' => Type::nonNull(Type::int())],
                    'resolve' => [AuthorType::class, 'one'],
                ],
                'authors' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeFactory::author()))),
                    'args' => ['pagination' => TypeFactory::pagination()],
                    'resolve' => [AuthorType::class, 'list'],
                ],
            ],
        ]);
    }
}
