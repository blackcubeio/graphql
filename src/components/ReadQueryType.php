<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Bloc;
use blackcube\core\models\Category;
use blackcube\core\models\Node;
use blackcube\core\models\Tag;
use blackcube\graphql\components\filters\PaginationType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class ReadQueryType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'ReadQuery',
            'fields' => [
                'composite' => [
                    'type' => Types::composite(),
                    'description' => 'Return active composite by id',
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => [CompositeType::class, 'one']
                ],
                'composites' => [
                    'type' => Type::listOf(Types::composite()),
                    'description' => 'Return active composites',
                    'args' => [
                        'pagination' => Types::pagination(),
                        'filters' => Types::compositeFilter(),
                    ],
                    'resolve' => [CompositeType::class, 'list']
                ],
                'node' => [
                    'type' => Types::node(),
                    'description' => 'Return active node by id',
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => [NodeType::class, 'one']
                ],
                'nodes' => [
                    'type' => Type::listOf(Types::node()),
                    'description' => 'Return active nodes',
                    'args' => [
                        'pagination' => Types::pagination()
                    ],
                    'resolve' => [NodeType::class, 'list']
                ],
                'category' => [
                    'type' => Types::category(),
                    'description' => 'Return active category by id',
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => [CategoryType::class, 'one']
                ],
                'categories' => [
                    'type' => Type::listOf(Types::category()),
                    'description' => 'Return active categories',
                    'args' => [
                        'pagination' => Types::pagination()
                    ],
                    'resolve' => [CategoryType::class, 'list']
                ],
                'tag' => [
                    'type' => Types::tag(),
                    'description' => 'Return active tag by id',
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => [TagType::class, 'one']
                ],
                'tags' => [
                    'type' => Type::listOf(Types::tag()),
                    'description' => 'Return active tags',
                    'args' => [
                        'pagination' => Types::pagination()
                    ],
                    'resolve' => [TagType::class, 'list']
                ],
                'bloc' => [
                    'type' => Types::bloc(),
                    'description' => 'Return active bloc by id',
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function($root, $args) {
                        return Bloc::find()->active()->andWhere(['id' => $args['id']])->one();
                    }
                ],
                'languages' => [
                    'type' => Type::listOf(Types::language()),
                    'description' => 'List of available languages',

                    'args' => [
                        'pagination' => Types::pagination(),
                    ],
                    'resolve' => [LanguageType::class, 'list']
                ],
                'types' => [
                    'type' => Type::listOf(Types::type()),
                    'description' => 'List of available element types',

                    'args' => [
                        'pagination' => Types::pagination(),
                    ],
                    'resolve' => [TypeType::class, 'list']
                ],
                'parameters' => [
                    'type' => Type::listOf(Types::parameter()),
                    'description' => 'List of available parameters',
                    'args' => [
                        'pagination' => Types::pagination(),
                    ],
                    'resolve' => [ParameterType::class, 'list']
                ]
            ],
        ]);
    }
}