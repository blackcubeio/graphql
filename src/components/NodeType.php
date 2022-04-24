<?php

namespace blackcube\graphql\components;

use blackcube\core\models\Composite;
use blackcube\core\models\Node;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class NodeType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Node',
            'description' => 'Node contents',
            'fields' => [
                'id' => ['type' => Type::id(), 'description' => 'ID'],
                'name' => ['type' => Type::string(), 'description' => 'Name'],
                'level' => [
                    'type' => Type::int()
                ],
                'path' => [
                    'type' => Type::string()
                ],
                'language' => [
                    'type' => function() { return Types::language(); },
                    'description' => 'Language of the node (rubric)',
                    'resolve' => function(Node $node) {
                        return $node->getLanguage()->one();
                    }
                ],
                'type' => [
                    'type' => function() { return Types::type(); },
                    'description' => 'Type of the node (rubric)',
                    'resolve' => function(Node $node) {
                        return $node->getType()->one();
                    }
                ],
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
                'composites' => [
                    'type' => function() { return Type::listOf(Types::composite()); },
                    'description' => 'Composites attached to the node (rubric)',
                    'resolve' => function(Node $node) {
                        return $node->getComposites()->active()->all();
                    }
                ],
                'nodes' => [
                    'type' => function() { return Type::listOf(Types::node()); },
                    'description' => 'Children of the node (rubric)',
                    'resolve' => function(Node $node) {
                        return $node->getChildren()->active()->all();
                    }
                ],
                'tags' => [
                    'type' => function() { return Type::listOf(Types::tag()); },
                    'description' => 'Tags attached to the node (rubric)',
                    'resolve' => function(Node $node) {
                        return $node->getTags()->active()->all();
                    }
                ],
                'blocs' => [
                    'type' => Type::listOf(Types::bloc()),
                    'description' => 'List of blocs (smallest content element) attached to the node (rubric)',
                    'resolve' => function(Node $node) {
                        return $node->getBlocs()->all();
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
        return Node::find()
            ->active()
            ->andWhere(['id' => $args['id']])
            ->one();
    }

    public static function list($root, $args)
    {
        $query = Node::find()->active();
        if (isset($args['pagination'])) {
            if (isset($args['pagination']['size']) && $args['pagination']['size'] > 0) {
                $query->limit($args['pagination']['size']);
            }
            if (isset($args['pagination']['offset']) && $args['pagination']['offset'] > 0) {
                $query->offset($args['pagination']['offset']);
            }
        }
        $query->orderBy(['left' => SORT_ASC]);
        return $query->all();
    }
}