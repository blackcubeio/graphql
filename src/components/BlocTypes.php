<?php

namespace blackcube\graphql\components;

use GraphQL\Type\Definition\Type;
use Yii;
use Exception;

class BlocTypes
{
    private static $types;

    /**
     * @param string $classname
     * @return Type
     */
    private static function get($classname)
    {
        return static::byClassName($classname);
    }

    /**
     * @param string $classname
     * @return Type
     * @throws \yii\base\InvalidConfigException|Exception
     */
    private static function byClassName($classname)
    {
        $parts = explode('\\', $classname);

        $cacheName = strtolower(preg_replace('~Type$~', '', $parts[count($parts) - 1]));

        if (isset(self::$types[$cacheName]) === false && class_exists($classname)) {
            $type = Yii::createObject($classname);
            /* @var Type $type */
            self::$types[$cacheName] = $type;
            if ($type === null) {
                throw new Exception('Unknown graphql type: ' . $classname);
            }
        }

        return self::$types[$cacheName];
    }

}