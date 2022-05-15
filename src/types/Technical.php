<?php

namespace blackcube\graphql\types;

use blackcube\graphql\Plugin;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as DefinitionType;
use yii\helpers\Url;

class Technical extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Technical',
            'description' => Plugin::t('types', 'Technical elements related to the CMS'),
            'fields' => [
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
            ],
        ];
        parent::__construct($config);
    }
}