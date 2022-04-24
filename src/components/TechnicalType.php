<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Composite;
use blackcube\core\models\Language;
use blackcube\core\models\Tag;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class TechnicalType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Technical',
            'description' => 'Technical elements related to the CMS',
            'fields' => [
                'types' => [
                    'type' => function() { return Type::listOf(Types::type()); },
                    'description' => 'List of available types',
                    'resolve' => [TypeType::class, 'list']
                ],
            ],
        ];
        parent::__construct($config);
    }
}