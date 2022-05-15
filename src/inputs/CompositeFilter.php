<?php

namespace blackcube\graphql\inputs;

use blackcube\graphql\Plugin;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

class CompositeFilter extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'CompositeFilter',
            'fields' => [
                'typeId' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Type of the composite')
                ],
                'languageId' => [
                    'type' => Type::string(),
                    'description' => Plugin::t('types', 'Language used')
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
        return ArrayHelper::merge($defaultArgs, ($args['filters']??[]));
    }
}