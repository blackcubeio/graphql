<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Composite;
use blackcube\core\models\Language;
use blackcube\core\models\Tag;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class LanguageType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Language',
            'description' => 'Language contents',
            'fields' => [
                'id' => ['type' => Type::id(), 'description' => 'ID'],
                'name' => ['type' => Type::string(), 'description' => 'Name'],
                'main' => ['type' => Type::boolean(), 'description' => 'true if it\'s a main language'],
                'dateCreate' => ['type' => Type::string(), 'description' => 'Creation date'],
                'dateUpdate' => ['type' => Type::string(), 'description' => 'Update date']
            ],
        ];
        parent::__construct($config);
    }
    public static function one($root, $args)
    {
        return Language::find()
            ->active()
            ->andWhere(['id' => $args['id']])
            ->one();
    }
    public static function list($root, $args)
    {
        $query = Language::find()->active();
        if (isset($args['pagination'])) {
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        }
        return $query->all();
    }
}