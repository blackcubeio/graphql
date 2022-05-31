<?php

namespace blackcube\plugins\graphql\types;

use blackcube\plugins\graphql\Plugin;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as DefinitionType;

class ReadQuery extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'ReadQuery',
            'fields' => [
                'node' => [
                    'type' => Blackcube::node(),
                    'description' => Plugin::t('types', 'Return active node by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Node::class, 'one']
                ],
                'nodes' => [
                    'type' => DefinitionType::listOf(Blackcube::node()),
                    'description' => Plugin::t('types', 'Return active nodes'),
                    'args' => [
                        'pagination' => Blackcube::pagination(),
                        'filters' => Blackcube::nodeFilter(),
                    ],
                    'resolve' => [Node::class, 'list']
                ],
                'composite' => [
                    'type' => Blackcube::composite(),
                    'description' => Plugin::t('types', 'Return active composite by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Composite::class, 'one']
                ],
                'composites' => [
                    'type' => DefinitionType::listOf(Blackcube::composite()),
                    'description' => Plugin::t('types', 'Return active composites'),
                    'args' => [
                        'pagination' => Blackcube::pagination(),
                        'filters' => Blackcube::compositeFilter(),
                    ],
                    'resolve' => [Composite::class, 'list']
                ],
                'category' => [
                    'type' => Blackcube::category(),
                    'description' => Plugin::t('types', 'Return active category by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Category::class, 'one']
                ],
                'categories' => [
                    'type' => DefinitionType::listOf(Blackcube::category()),
                    'description' => Plugin::t('types', 'Return active categories'),
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Category::class, 'list']
                ],
                'tag' => [
                    'type' => Blackcube::tag(),
                    'description' => Plugin::t('types', 'Return active tag by id'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id())
                    ],
                    'resolve' => [Tag::class, 'one']
                ],
                'tags' => [
                    'type' => DefinitionType::listOf(Blackcube::tag()),
                    'description' => Plugin::t('types', 'Return active tags'),
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Tag::class, 'list']
                ],


                /*/
                'technical' => [
                    'type' => Blackcube::technical(),
                    'description' => Plugin::t('types', 'Return CMS technical data'),
                    'args' => [
                    ]
                ],
                /*/
                'language' => [
                    'type' => Blackcube::language(),
                    'description' => Plugin::t('types', 'Return language by ID'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id()),
                    ],
                    'resolve' => [Language::class, 'one']
                ],
                'languages' => [
                    'type' => DefinitionType::listOf(Blackcube::language()),
                    'description' => Plugin::t('types', 'List of available languages'),
                    'args' => [
                        'pagination' => Blackcube::pagination()
                    ],
                    'resolve' => [Language::class, 'list']
                ],
                'parameter' => [
                    'type' => Blackcube::parameter(),
                    'description' => Plugin::t('types', 'Return parameter by domain and name'),
                    'args' => [
                        'domain' => DefinitionType::nonNull(DefinitionType::string()),
                        'name' => DefinitionType::nonNull(DefinitionType::string()),
                    ],
                    'resolve' => [Parameter::class, 'one']
                ],
                'parameters' => [
                    'type' => DefinitionType::listOf(Blackcube::parameter()),
                    'description' => Plugin::t('types', 'List of available parameters'),
                    'args' => [
                        //'pagination' => Types::pagination(),
                    ],
                    'resolve' => [Parameter::class, 'list']
                ],
                'type' => [
                    'type' => Blackcube::type(),
                    'description' => Plugin::t('types', 'Return type by ID'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id()),
                    ],
                    'resolve' => [Type::class, 'one']
                ],
                'types' => [
                    'type' => DefinitionType::listOf(Blackcube::type()),
                    'description' => Plugin::t('types', 'List of available types'),
                    'args' => [
                        //'pagination' => Types::pagination(),
                    ],
                    'resolve' => [Type::class, 'list']
                ],
                /**/

            ],
        ]);
    }
}