<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Composite;
use blackcube\core\models\Language;
use blackcube\core\models\Parameter;
use blackcube\core\models\Tag;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class ParameterType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Parameter',
            'description' => 'Parameter CMS wide',
            'fields' => [
                'domain' => ['type' => Type::string(), 'description' => 'Domain'],
                'name' => ['type' => Type::string(), 'description' => 'Name'],
                'value' => ['type' => Type::string(), 'description' => 'Value'],
                'dateCreate' => ['type' => Type::string(), 'description' => 'Creation date'],
                'dateUpdate' => ['type' => Type::string(), 'description' => 'Update date']
            ],
        ];
        parent::__construct($config);
    }
    public static function one($root, $args)
    {
        return Parameter::find()
            ->andWhere([
                'domain' => $args['domain'],
                'name' => $args['name'],
            ])
            ->one();
    }
    public static function list($root, $args)
    {
        $query = Parameter::find();
        if (isset($args['pagination'])) {
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        }
        $query->orderBy([
            'domain' => SORT_ASC,
            'name' => SORT_ASC
        ]);
        return $query->all();
    }
}