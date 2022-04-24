<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Category;
use blackcube\core\models\Composite;
use blackcube\core\models\Tag;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class TagType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Tag',
            'description' => 'Tag element. Tags are used to create a transversal hierarchy',
            'fields' => [
                'id' => ['type' => Type::id(), 'description' => 'ID'],
                'name' => ['type' => Type::string(), 'description' => 'Name'],
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Url used to access the tag',
                    'resolve' => function(Tag $tag) {
                        if ($tag->slugId !== null) {
                            return Url::toRoute($tag->getRoute(), true);
                        }
                        return null;
                    }
                ],
                'language' => [
                    'type' => function() { return Types::language(); },
                    'description' => 'Language of the tag',
                    'resolve' => function(Tag $tag) {
                        return $tag->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Types::type(); },
                    'description' => 'Type of the tag',
                    'resolve' => function(Tag $tag) {
                        return $tag->getType()->one();
                    }
                ],
                'category' => [
                    'type' => function() { return Types::category(); },
                    'description' => 'Category where the tag is attached',
                    'resolve' => function(Tag $tag) {
                        return $tag->getCategory()->active()->one();
                    }
                ],
                'composites' => [
                    'type' => function() { return Type::listOf(Types::composite()); },
                    'description' => 'List of composites (articles) attached to the tag',
                    'resolve' => function(Tag $tag) {
                        return $tag->getComposites()->active()->all();
                    }
                ],
                'nodes' => [
                    'type' => function() { return Type::listOf(Types::node()); },
                    'description' => 'List of nodes (rubrics) attached to the tag',
                    'resolve' => function(Tag $tag) {
                        return $tag->getNodes()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => Type::listOf(Types::bloc()),
                    'description' => 'List of blocs (smallest content element) attached to the tag',
                    'resolve' => function(Tag $tag) {
                        return $tag->getBlocs()->active()->all();
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
        return Tag::find()
            ->active()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    public static function list($root, $args)
    {
        $query = Tag::find()->active();
        if (isset($args['pagination'])) {
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        }
        /**/
        $query->innerJoinWith(['category' => function($query) {
            $query->active();
        }], true);
        /**/
        $query->orderBy([
            Category::tableName().'.[[name]]' => SORT_ASC,
            Tag::tableName().'.[[name]]' => SORT_ASC
        ]);
        return $query->all();
    }
}