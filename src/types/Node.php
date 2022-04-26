<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Composite;
use blackcube\core\models\Node as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class Node extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Node',
            'description' => Module::t('types', 'Node contents'),
            'fields' => [
                'id' => [
                    'type' => Type::id(),
                    'description' => Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Name')
                ],
                'level' => [
                    'type' => Type::int(),
                    'description' => Module::t('types', 'Level')
                ],
                'path' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Path')
                ],
                'language' => [
                    'type' => function() { return Blackcube::language(); },
                    'description' => Module::t('types', 'Language of the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Blackcube::type(); },
                    'description' => Module::t('types','Type of the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getType()->one();
                    }
                ],
                /*/
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Public URL to access the node (rubric)',
                    'resolve' => function(Node $node) {
                        if ($node->slugId !== null) {
                            return Url::toRoute($node->getRoute(), true);
                        }
                        return null;
                    }
                ],
                /**/
                'composites' => [
                    'type' => function() { return Type::listOf(Blackcube::composite()); },
                    'description' => Module::t('types','Composites attached to the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getComposites()->active()->all();
                    }
                ],
                'nodes' => [
                    'type' => function() { return Type::listOf(Blackcube::node()); },
                    'description' => Module::t('types','Children of the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getChildren()->active()->all();
                    }
                ],
                'tags' => [
                    'type' => function() { return Type::listOf(Blackcube::tag()); },
                    'description' => Module::t('types','Tags attached to the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => Type::listOf(Blackcube::bloc()),
                    'description' => Module::t('types', 'List of blocs (smallest content element) attached to the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getBlocs()->all();
                    }
                ],
                'dateCreate' => [
                    'type' => Type::string(),
                    'description' => Module::t('types','Creation date')
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'description' => Module::t('types','Update date')
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
        $query->orderBy(['left' => SORT_ASC]);
        return $query->all();
    }
}