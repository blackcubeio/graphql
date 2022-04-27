<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Composite;
use blackcube\core\models\Node as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as DefinitionType;
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
                    'type' => DefinitionType::id(),
                    'description' => Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => DefinitionType::string(),
                    'description' => Module::t('types', 'Name')
                ],
                'level' => [
                    'type' => DefinitionType::int(),
                    'description' => Module::t('types', 'Level')
                ],
                'path' => [
                    'type' => DefinitionType::string(),
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
                    'resolve' => [Type::class, 'retrieve'],
                ],
                'slug' => [
                    'type' => function() { return Blackcube::slug(); },
                    'description' => Module::t('types', 'Slug of the node (rubric)'),
                    'resolve' => [Slug::class, 'retrieve'],
                ],
                'composites' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::composite()); },
                    'description' => Module::t('types','Composites attached to the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getComposites()->active()->all();
                    }
                ],
                'nodes' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::node()); },
                    'description' => Module::t('types','Children of the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getChildren()->active()->all();
                    }
                ],
                'tags' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::tag()); },
                    'description' => Module::t('types','Tags attached to the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => DefinitionType::listOf(Blackcube::bloc()),
                    'description' => Module::t('types', 'List of blocs (smallest content element) attached to the node (rubric)'),
                    'resolve' => function(Model $node) {
                        return $node->getBlocs()->all();
                    }
                ],
                'dateCreate' => [
                    'type' => DefinitionType::string(),
                    'description' => Module::t('types','Creation date')
                ],
                'dateUpdate' => [
                    'type' => DefinitionType::string(),
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
        $filters = Blackcube::nodeFilter()->extract($args);
        $query = Model::find()->active();
        if ($filters['typeId'] !== null) {
            $query->andWhere(['typeId' => $filters['typeId']]);
        }
        if ($filters['languageId'] !== null) {
            $query->andWhere(['languageId' => $filters['languageId']]);
        }
        if ($filters['level'] !== null) {
            $query->andWhere(['level' => $filters['level']]);
        }
        $query->limit($pagination['size']);
        $query->offset($pagination['offset']);
        $query->orderBy(['left' => SORT_ASC]);
        return $query->all();
    }
}