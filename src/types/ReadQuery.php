<?php

namespace blackcube\graphql\types;

use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as DefinitionType;

class ReadQuery extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'ReadQuery',
            'fields' => [
                'category' => [
                    'type' => Blackcube::category(),
                    'description' => Module::t('types', 'Return active category by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Category::class, 'one']
                ],
                'categories' => [
                    'type' => DefinitionType::listOf(Blackcube::category()),
                    'description' => Module::t('types', 'Return active categories'),
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Category::class, 'list']
                ],
                'composite' => [
                    'type' => Blackcube::composite(),
                    'description' => Module::t('types', 'Return active composite by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Composite::class, 'one']
                ],
                'composites' => [
                    'type' => DefinitionType::listOf(Blackcube::composite()),
                    'description' => Module::t('types', 'Return active composites'),
                    'args' => [
                        'pagination' => Blackcube::pagination(),
                        'filters' => Blackcube::compositeFilter(),
                    ],
                    'resolve' => [Composite::class, 'list']
                ],
                'language' => [
                    'type' => Blackcube::language(),
                    'description' => Module::t('types', 'Return language by ID'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id()),
                    ],
                    'resolve' => [Language::class, 'one']
                ],
                'languages' => [
                    'type' => DefinitionType::listOf(Blackcube::language()),
                    'description' => Module::t('types', 'List of available languages'),
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Language::class, 'list']
                ],
                'node' => [
                    'type' => Blackcube::node(),
                    'description' => 'Return active node by id',
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Node::class, 'one']
                ],
                'nodes' => [
                    'type' => DefinitionType::listOf(Blackcube::node()),
                    'description' => 'Return active nodes',
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Node::class, 'list']
                ],
                'tag' => [
                    'type' => Blackcube::tag(),
                    'description' => Module::t('types', 'Return active tag by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Tag::class, 'one']
                ],
                'tags' => [
                    'type' => DefinitionType::listOf(Blackcube::tag()),
                    'description' => Module::t('types', 'Return active tags'),
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Tag::class, 'list']
                ],
                'technical' => [
                    'type' => Blackcube::technical(),
                    'description' => Module::t('types', 'Return CMS technical data'),
                    'args' => [
                    ]
                ],
                /*/
                'node' => [
                    'type' => Types::node(),
                    'description' => 'Return active node by id',
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [NodeType::class, 'one']
                ],
                'nodes' => [
                    'type' => DefinitionType::listOf(Types::node()),
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
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [CategoryType::class, 'one']
                ],
                'categories' => [
                    'type' => DefinitionType::listOf(Types::category()),
                    'description' => 'Return active categories',
                    'args' => [
                        'pagination' => Types::pagination()
                    ],
                    'resolve' => [CategoryType::class, 'list']
                ],
                'bloc' => [
                    'type' => Types::bloc(),
                    'description' => 'Return active bloc by id',
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => function($root, $args) {
                        return Bloc::find()->active()->andWhere(['id' => $args['id']])->one();
                    }
                ],
                'languages' => [
                    'type' => DefinitionType::listOf(Types::language()),
                    'description' => 'List of available languages',

                    'args' => [
                        'pagination' => Types::pagination(),
                    ],
                    'resolve' => [LanguageType::class, 'list']
                ],
                'types' => [
                    'type' => DefinitionType::listOf(Types::type()),
                    'description' => 'List of available element types',

                    'args' => [
                        'pagination' => Types::pagination(),
                    ],
                    'resolve' => [TypeType::class, 'list']
                ],
                'parameters' => [
                    'type' => DefinitionType::listOf(Types::parameter()),
                    'description' => 'List of available parameters',
                    'args' => [
                        'pagination' => Types::pagination(),
                    ],
                    'resolve' => [ParameterType::class, 'list']
                ]
                /**/
            ],
        ]);
    }
}