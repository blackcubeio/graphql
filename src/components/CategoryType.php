<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Category;
use blackcube\core\models\Composite;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class CategoryType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Category',
            'description' => 'Category element. Categories are used for transversal hierarchy',
            'fields' => [
                'id' => ['type' => Type::id(), 'description' => 'ID'],
                'name' => ['type' => Type::string(), 'description' => 'Name'],
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Url used to access the tag',
                    'resolve' => function(Category $category) {
                        if ($category->slugId !== null) {
                            return Url::toRoute($category->getRoute(), true);
                        }
                        return null;
                    }
                ],
                'language' => [
                    'type' => function() { return Types::language(); },
                    'description' => 'Language of the category',
                    'resolve' => function(Category $category) {
                        return $category->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Types::type(); },
                    'description' => 'Type of the category',
                    'resolve' => function(Category $category) {
                        return $category->getType()->one();
                    }
                ],
                'tags' => [
                    'type' => function() { return Type::listOf(Types::tag()); },
                    'description' => 'Tags attached to the category',
                    'resolve' => function(Category $category) {
                        return $category->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => Type::listOf(Types::bloc()),
                    'description' => 'List of blocs (smallest content element) attached to the category',
                    'resolve' => function(Category $category) {
                        return $category->getBlocs()->active()->all();
                    }
                ],
                'dateCreate' => ['type' => Type::string(), 'description' => 'Creation date'],
                'dateUpdate' => ['type' => Type::string(), 'description' => 'Update date']
            ],
        ];
        parent::__construct($config);
    }
    public static function one($root, $args)
    {
        return Category::find()
            ->active()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    public static function list($root, $args)
    {
        $query = Category::find()->active();
        if (isset($args['pagination'])) {
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        }
        $query->orderBy(['name' => SORT_ASC]);
        return $query->all();
    }
}