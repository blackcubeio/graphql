<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Language as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Language extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Language',
            'description' => Module::t('types', 'Language details'),
            'fields' => [
                'id' => [
                    'type' => Type::id(),
                    'description' => Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Name')
                ],
                'main' => [
                    'type' => Type::boolean(),
                    'description' => Module::t('types', 'true if it\'s a main language')
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
            ->active()
            ->andWhere(['id' => $args['id']])
            ->one();
    }
    public static function list($root, $args)
    {
        $pagination = Blackcube::pagination()->extract($args);

        $query = Model::find()->active();
        $query->limit($pagination['size']);
        $query->offset($pagination['offset']);

        return $query->all();
    }
}