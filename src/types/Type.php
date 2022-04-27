<?php

namespace blackcube\graphql\types;

use blackcube\core\interfaces\ElementInterface;
use blackcube\core\models\Type as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as DefinitionType;

class Type extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Type',
            'description' => Module::t('types', 'Type of elements. It describe the main type of an element and the route used (if available)'),
            'fields' => [
                'id' => [
                    'type' => DefinitionType::id(),
                    'description' => Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => DefinitionType::string(),
                    'description' => Module::t('types', 'Name')
                ],
                'route' => [
                    'type' => DefinitionType::id(),
                    'description' => Module::t('types', 'Route')
                ],
                'dateCreate' => [
                    'type' => DefinitionType::string(),
                    'description' => Module::t('types', 'Creation date')
                ],
                'dateUpdate' => [
                    'type' => DefinitionType::string(),
                    'description' => Module::t('types', 'Update date')
                ]
            ],
        ];
        parent::__construct($config);
    }
    public static function one($root, $args)
    {
        return Model::find()
            ->andWhere(['id' => $args['id']])
            ->one();
    }
    public static function list($root, $args)
    {
        $query = Model::find();
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        return $query->all();
    }

    public static function retrieve(ElementInterface $element)
    {
        return $element->getType()->one();
    }
}