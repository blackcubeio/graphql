<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Composite as Model;
use blackcube\graphql\Module;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class Composite extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Composite',
            'description' => Module::t('types', 'Composite element. Composites are used to represent an article'),
            'fields' => [
                'id' => [
                    'type' => Type::id(),
                    'description' => Module::t('types', 'ID')
                ],
                'name' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Name')
                ],
                'language' => [
                    'type' => function() { return Blackcube::language(); },
                    'description' => Module::t('types', 'Language of the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Blackcube::type(); },
                    'description' => Module::t('types', 'Type of the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getType()->one();
                    }
                ],
                /*/
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Public URL to access the composite',
                    'resolve' => function(Model $composite) {
                        if ($composite->slugId !== null) {
                            return Url::toRoute($composite->getRoute(), true);
                        }
                        return null;
                    }
                ],
                /**/
                'nodes' => [
                    'type' => function() { return Type::listOf(Blackcube::node()); },
                    'description' => Module::t('types', 'Nodes linked to the composite (rubrics)'),
                    'resolve' => function(Model $composite) {
                        return $composite->getNodes()->active()->all();
                    }
                ],
                'tags' => [
                    'type' => function() { return Type::listOf(Blackcube::tag()); },
                    'description' => Module::t('types', 'Tags attached to the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => function() { return Type::listOf(Blackcube::bloc()); },
                    'description' => Module::t('types', 'List of blocs (smallest content element) attached to the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getBlocs()->active()->all();
                    }
                ],
                'dateStart' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Publication starting date (used if not null)')
                ],
                'dateEnd' => [
                    'type' => Type::string(),
                    'description' => Module::t('types', 'Publication ending date (used if not null)')
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
        $filters = Blackcube::compositeFilter()->extract($args);
        $query = Model::find()->active();
        $query->limit($pagination['size']);
        $query->offset($pagination['offset']);
        if ($filters['typeId'] !== null) {
            $query->andWhere(['typeId' => $filters['typeId']]);
        }
        if ($filters['languageId'] !== null) {
            $query->andWhere(['languageId' => $filters['languageId']]);
        }
        return $query->all();
    }
}