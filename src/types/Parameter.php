<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Parameter as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Parameter extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Parameter',
            'description' => Module::t('types', 'Parameter CMS wide'),
            'fields' => [
                'domain' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Domain')
                ],
                'name' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Name')
                ],
                'value' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Value')
                ],
                'dateCreate' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Creation date')
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Update date')
                ]
            ],
        ];
        parent::__construct($config);
    }
    public static function one($root, $args)
    {
        return Model::find()
            ->andWhere([
                'domain' => $args['domain'],
                'name' => $args['name'],
            ])
            ->one();
    }
    public static function list($root, $args)
    {
        $pagination = Blackcube::pagination()->extract($args);

        $query = Model::find();
        $query->limit($pagination['size']);
        $query->offset($pagination['offset']);
        $query->orderBy([
            'domain' => SORT_ASC,
            'name' => SORT_ASC
        ]);
        return $query->all();
    }
}