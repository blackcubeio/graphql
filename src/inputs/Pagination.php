<?php

namespace blackcube\graphql\inputs;

use blackcube\graphql\Plugin;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

class Pagination extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Pagination',
            'fields' => [
                'size' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Page size'),
                    'defaultValue' => 10
                ],
                'offset' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Offset'),
                    'defaultValue' => 0
                ],
            ]
        ];
        parent::__construct($config);
    }

    public function extract($args)
    {
        $defaultArgs = [];
        foreach($this->getFields() as $name => $field) {
            /* @var \GraphQL\Type\Definition\InputObjectField $field */
            $defaultArgs[$name] = $field->defaultValue;
        }
        $pagination = ArrayHelper::merge($defaultArgs, ($args['pagination']??[]));
        return $pagination;
    }

}