<?php

namespace blackcube\graphql\components\filters;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class PaginationType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Pagination',
            'fields' => [
                'size' => [
                    'type' => Type::int(),
                ],
                'offset' => [
                    'type' => Type::int(),
                ],
            ]
        ];
        parent::__construct($config);
    }
    public static function getDefaultValues()
    {
        return [
            'size' => 10,
            'offset' => 0
        ];
    }

}