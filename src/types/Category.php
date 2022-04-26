<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Category as Model;
use blackcube\core\models\Composite;
use blackcube\graphql\Module;
use blackcube\graphql\types\Blackcube;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Category extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Category',
            'description' => Module::t('types', 'Category element. Categories are used for transversal hierarchy'),
            'fields' => [
                'id' => [
                    'type' => Type::id(),
                    'description' => Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Name')
                ],
                /*/
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
                /**/
                'language' => [
                    'type' => function() { return Blackcube::language(); },
                    'description' => Module::t('types', 'Language of the category'),
                    'resolve' => function(Model $category) {
                        return $category->getLanguage()->one();
                    }
                ],

                'type' => [
                    'type' => function() { return Blackcube::type(); },
                    'description' => Module::t('types', 'Type of the category'),
                    'resolve' => function(Model $category) {
                        return $category->getType()->one();
                    }
                ],
                'tags' => [
                    'type' => function() { return Type::listOf(Blackcube::tag()); },
                    'description' => Module::t('types', 'Tags attached to the category'),
                    'resolve' => function(Model $category) {
                        return $category->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => Type::listOf(Blackcube::bloc()),
                    'description' => Module::t('types', 'List of blocs (smallest content element) attached to the category'),
                    'resolve' => function(Model $category) {
                        return $category->getBlocs()->active()->all();
                    }
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
        $query->orderBy(['name' => SORT_ASC]);
        return $query->all();
    }
}