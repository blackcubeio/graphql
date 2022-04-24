<?php

namespace blackcube\graphql\components\filters;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class CompositeFilterType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'CompositeFilter',
            'fields' => [
                'typeId' => [
                    'type' => Type::int(),
                    'description' => 'Type of the composite'
                ],
                'languageId' => [
                    'type' => Type::string(),
                    'description' => 'Language used'
                ],
                'dateStart' => [
                    'type' => Type::string(),
                    'description' => 'Date start (null if not set)'
                ],
                'dateEnd' => [
                    'type' => Type::string(),
                    'description' => 'Date end (null if not set)'
                ],
                'dateCreate' => [
                    'type' => Type::string(),
                    'description' => 'Creation date'
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'description' => 'Last update date'
                ]
            ]
        ];
        parent::__construct($config);
    }

}