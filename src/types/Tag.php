<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Category;
use blackcube\core\models\Composite;
use blackcube\core\models\Tag as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as DefinitionType;
use yii\helpers\Url;

class Tag extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Tag',
            'description' => Module::t('types', 'Tag element. Tags are used to create a transversal hierarchy'),
            'fields' => [
                'id' => [
                    'type' => DefinitionType::id(),
                    'description' =>  Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => DefinitionType::string(),
                    'description' =>  Module::t('types', 'Name')
                ],
                'language' => [
                    'type' => function() { return Blackcube::language(); },
                    'description' =>  Module::t('types', 'Language of the tag'),
                    'resolve' => function(Model $tag) {
                        return $tag->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Blackcube::type(); },
                    'description' =>  Module::t('types', 'Type of the tag'),
                    'resolve' => [Type::class, 'retrieve'],
                ],
                'slug' => [
                    'type' => function() { return Blackcube::slug(); },
                    'description' => Module::t('types', 'Slug of the tag'),
                    'resolve' => [Slug::class, 'retrieve'],
                ],
                'category' => [
                    'type' => function() { return Blackcube::category(); },
                    'description' =>  Module::t('types', 'Category where the tag is attached'),
                    'resolve' => function(Model $tag) {
                        return $tag->getCategory()->active()->one();
                    }
                ],
                'composites' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::composite()); },
                    'description' =>  Module::t('types', 'List of composites (articles) attached to the tag'),
                    'resolve' => function(Model $tag) {
                        return $tag->getComposites()->active()->all();
                    }
                ],
                'nodes' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::node()); },
                    'description' => Module::t('types', 'List of nodes (rubrics) attached to the tag'),
                    'resolve' => function(Model $tag) {
                        return $tag->getNodes()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => DefinitionType::listOf(Blackcube::bloc()),
                    'description' => Module::t('types', 'List of blocs (smallest content element) attached to the tag'),
                    'resolve' => function(Model $tag) {
                        return $tag->getBlocs()->active()->all();
                    }
                ],
                'dateCreate' => [
                    'type' => DefinitionType::string(),
                    'description' =>  Module::t('types', 'Creation date')
                ],
                'dateUpdate' => [
                    'type' => DefinitionType::string(),
                    'description' =>  Module::t('types', 'Update date')
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
        /**/
        $query->innerJoinWith(['category' => function($query) {
            $query->active();
        }], true);
        /**/
        $query->orderBy([
            Category::tableName().'.[[name]]' => SORT_ASC,
            Model::tableName().'.[[name]]' => SORT_ASC
        ]);
        return $query->all();
    }
}