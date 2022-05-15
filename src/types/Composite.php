<?php

namespace blackcube\graphql\types;

use blackcube\core\models\Composite as Model;
use blackcube\graphql\Plugin;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as DefinitionType;
use yii\helpers\Url;

class Composite extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Composite',
            'description' => Plugin::t('types', 'Composite element. Composites are used to represent an article'),
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
                    'description' => Plugin::t('types', 'Language of the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Blackcube::type(); },
                    'description' => Plugin::t('types', 'Type of the composite'),
                    'resolve' => [Type::class, 'retrieve'],
                ],
                'slug' => [
                    'type' => function() { return Blackcube::slug(); },
                    'description' => Plugin::t('types', 'Slug of the composite'),
                    'resolve' => [Slug::class, 'retrieve'],
                ],
                'nodes' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::node()); },
                    'description' => Plugin::t('types', 'Nodes linked to the composite (rubrics)'),
                    'resolve' => function(Model $composite) {
                        return $composite->getNodes()->active()->all();
                    }
                ],
                'tags' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::tag()); },
                    'description' => Plugin::t('types', 'Tags attached to the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => function() { return DefinitionType::listOf(Blackcube::bloc()); },
                    'description' => Plugin::t('types', 'List of blocs (smallest content element) attached to the composite'),
                    'resolve' => function(Model $composite) {
                        return $composite->getBlocs()->active()->all();
                    }
                ],
                'dateStart' => [
                    'type' => DefinitionType::string(),
                    'description' => Plugin::t('types', 'Publication starting date (used if not null)')
                ],
                'dateEnd' => [
                    'type' => DefinitionType::string(),
                    'description' => Plugin::t('types', 'Publication ending date (used if not null)')
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