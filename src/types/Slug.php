<?php

namespace blackcube\plugins\graphql\types;

use blackcube\core\components\Element;
use blackcube\core\interfaces\ElementInterface;
use blackcube\core\models\Category;
use blackcube\core\models\Composite;
use blackcube\core\models\Slug as Model;
use blackcube\core\web\helpers\Html;
use blackcube\plugins\graphql\Plugin;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\helpers\Url;

class Slug extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Slug',
            'description' => Plugin::t('types', 'Slug element. Slugs are used to map elements to URLs'),
            'fields' => [
                'id' => [
                    'type' => Type::id(),
                    'description' =>  Plugin::t('types', 'ID')
                ],
                'host' => [
                    'type' => Type::string(),
                    'description' =>  Plugin::t('types', 'Host')
                ],
                'url' => [
                    'type' => Type::string(),
                    'description' =>  Plugin::t('types', 'Url'),
                    'resolve' => static function($object, $args) {
                        if($object->path !== null) {
                            $protocol = \Yii::$app->request->isSecureConnection ? 'https':'http';
                            $hostname = $object->host ?? \Yii::$app->request->hostName;
                            $url = $protocol.'://'.$hostname.'/'.$object->path;
                            return $url;
                        }
                        return $object->path;
                    }
                ],
                'dateCreate' => [
                    'type' => Type::string(),
                    'description' =>  Plugin::t('types', 'Creation date')
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'description' =>  Plugin::t('types', 'Update date')
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

    public static function retrieve(ElementInterface $element)
    {
        return $element->getSlug()->one();
    }

}