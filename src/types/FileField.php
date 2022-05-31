<?php

namespace blackcube\plugins\graphql\types;

use blackcube\core\web\helpers\Html;
use GraphQL\Type\Definition\Type;

class FileField
{
    public static function build($key, $multi = false)
    {
        if ($multi === false) {
            return [
                'type' => Type::string(),
                'args' => [

                ],
                'resolve' => static function($object, $args) use ($key) {
                    $file = $object->{$key};

                    $realFile = \Yii::$app->request->hostInfo . Html::cacheFile($file);

                    return $realFile;
                }
            ];
        } else {
            return [
                'type' => Type::listOf(Type::string()),
                'args' => [

                ],
                'resolve' => static function($object, $args) use ($key) {
                    $files = $object->{$key};
                    $files = preg_split('/\s*,\s*/', $files, -1, PREG_SPLIT_NO_EMPTY);
                    $result = [];
                    foreach($files as $file) {
                        $result[] = \Yii::$app->request->hostInfo . Html::cacheFile($file);
                    }
                    return $result;
                }
            ];

        }
    }
}