<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Category as Model;
use blackcube\core\models\Composite;
use blackcube\graphql\Plugin;
use blackcube\graphql\types\Blackcube;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as DefinitionType;

class Category extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Category',
            'description' => Plugin::t('types', 'Category element. Categories are used for transversal hierarchy'),
            'fields' => [
                'id' => [
                    'type' => DefinitionType::id(),
                    'description' => Plugin::t('types', 'ID')
                ],
                'name' => [
                    'type' => DefinitionType::string(),
                    'description' => Plugin::t('types', 'Name')
                ],
                'language' => [
                    'type' => function() { return Blackcube::language(); },
                    'description' => Plugin::t('types', 'Language of the category'),
                    'resolve' => function(Model $category) {
                        return $category->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Blackcube::type(); },
                    'description' => Plugin::t('types', 'Type of the category'),
                    'resolve' => [Type::class, 'retrieve'],
                ],
                'slug' => [
                    'type' => function() { return Blackcube::slug(); },
                    'description' => Plugin::t('types', 'Slug of the category'),
                    'resolve' => [Slug::class, 'retrieve'],
                ],

                'tags' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::tag()); },
                    'description' => Plugin::t('types', 'Tags attached to the category'),
                    'resolve' => function(Model $category) {
                        return $category->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => DefinitionType::listOf(Blackcube::bloc()),
                    'description' => Plugin::t('types', 'List of blocs (smallest content element) attached to the category'),
                    'resolve' => function(Model $category) {
                        return $category->getBlocs()->active()->all();
                    }
                ],
                'dateCreate' => [
                    'type' => DefinitionType::string(),
                    'description' => Plugin::t('types', 'Creation date')
                ],
                'dateUpdate' => [
                    'type' => DefinitionType::string(),
                    'description' => Plugin::t('types', 'Update date')
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