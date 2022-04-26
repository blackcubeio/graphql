<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Composite;
use blackcube\core\models\Language;
use blackcube\core\models\Tag;
use blackcube\graphql\Module;
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
            'description' => 'Technical elements related to the CMS',
            'fields' => [
                'parameter' => [
                    'type' => Blackcube::parameter(),
                    'description' => Module::t('types', 'Return parameter by domain and name'),
                    'args' => [
                        'domain' => DefinitionType::nonNull(DefinitionType::string()),
                        'name' => DefinitionType::nonNull(DefinitionType::string()),
                    ],
                    'resolve' => [Parameter::class, 'one']
                ],
                'parameters' => [
                    'type' => DefinitionType::listOf(Blackcube::parameter()),
                    'description' => Module::t('types', 'List of available parameters'),
                    'args' => [
                        //'pagination' => Types::pagination(),
                    ],
                    'resolve' => [Parameter::class, 'list']
                ],
                'type' => [
                    'type' => Blackcube::type(),
                    'description' => Module::t('types', 'Return type by ID'),
                    'args' => [
                        'id' => DefinitionType::nonNull(DefinitionType::id()),
                    ],
                    'resolve' => [Type::class, 'one']
                ],
                'types' => [
                    'type' => DefinitionType::listOf(Blackcube::type()),
                    'description' => Module::t('types', 'List of available types'),
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