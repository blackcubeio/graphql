<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Composite;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class CompositeType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Composite',
            'description' => 'Composite element. Composites are used to represent an article',
            'fields' => [
                'id' => ['type' => Type::id(), 'description' => 'ID'],
                'name' => ['type' => Type::string(), 'description' => 'Name'],
                'language' => [
                    'type' => function() { return Types::language(); },
                    'description' => 'Language of the composite',
                    'resolve' => function(Composite $composite) {
                        return $composite->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Types::type(); },
                    'description' => 'Type of the composite',
                    'resolve' => function(Composite $composite) {
                        return $composite->getType()->one();
                    }
                ],
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Public URL to access the composite',
                    'resolve' => function(Composite $composite) {
                        if ($composite->slugId !== null) {
                            return Url::toRoute($composite->getRoute(), true);
                        }
                        return null;
                    }
                ],
                'nodes' => [
                    'type' => function() { return Type::listOf(Types::node()); },
                    'description' => 'Nodes linked to the composite (rubrics)',
                    'resolve' => function(Composite $composite) {
                        return $composite->getNodes()->active()->all();
                    }
                ],
                'tags' => [
                    'type' => function() { return Type::listOf(Types::tag()); },
                    'description' => 'Tags attached to the composite',
                    'resolve' => function(Composite $composite) {
                        return $composite->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => function() { return Type::listOf(Types::bloc()); },
                    'description' => 'List of blocs (smallest content element) attached to the composite',
                    'resolve' => function(Composite $composite) {
                        return $composite->getBlocs()->active()->all();
                    }
                ],
                'dateStart' => ['type' => Type::string(), 'description' => 'Publication starting date (used if not null)'],
                'dateEnd' => ['type' => Type::string(), 'description' => 'Publication ending date (used if not null)'],
                'dateCreate' => ['type' => Type::string(), 'description' => 'Creation date'],
                'dateUpdate' => ['type' => Type::string(), 'description' => 'Update date']
            ],
        ];
        parent::__construct($config);
    }
    public static function one($root, $args)
    {
        return Composite::find()
            ->active()
            ->andWhere(['id' => $args['id']])
            ->one();
    }
    public static function list($root, $args)
    {
        $query = Composite::find()->active();
        if (isset($args['pagination'])) {
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        }
        if (isset($args['filters'])) {
            if (isset($args['filters']['typeId'])) {
                $query->andWhere(['typeId' => $args['filters']['typeId']]);
            }
            if (isset($args['filters']['languageId'])) {
                $query->andWhere(['languageId' => $args['filters']['languageId']]);
            }
        }
        return $query->all();
    }
}