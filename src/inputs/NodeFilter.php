<?php

namespace blackcube\graphql\inputs;

use blackcube\graphql\Module;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

class NodeFilter extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'NodeFilter',
            'fields' => [
                'level' => [
                    'type' => Type::int(),
                    'description' => Module::t('types', 'Level'),
                ],
                'typeId' => [
                    'type' => Type::int(),
                    'description' => Module::t('types','Type of the node (rubric)'),
                ],
                'languageId' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Language used')
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