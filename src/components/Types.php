<?php

namespace blackcube\graphql\components;

use blackcube\graphql\components\filters\CompositeFilterType;
use blackcube\graphql\components\filters\LimitType;
use blackcube\graphql\components\filters\PaginationType;
use GraphQL\Type\Definition\Type;
use Yii;
use Exception;

class Types
{
    private static $types;

    public static function readQuery()
    {
        return static::get(ReadQueryType::class);
    }

    public static function composite()
    {
        return static::get(CompositeType::class);
    }

    public static function compositeFilter()
    {
        return static::get(CompositeFilterType::class);
    }

    public static function pagination()
    {
        return [
            'type' => static::get(PaginationType::class),
            'defaultValue' => PaginationType::getDefaultValues()
        ];
    }

    public static function node()
    {
        return static::get(NodeType::class);
    }

    public static function category()
    {
        return static::get(CategoryType::class);
    }

    public static function tag()
    {
        return static::get(TagType::class);
    }

    public static function bloc()
    {
        return static::get(BlocType::class);
    }

    public static function language()
    {
        return static::get(LanguageType::class);
    }

    public static function type()
    {
        return static::get(TypeType::class);
    }

    public static function parameter()
    {
        return static::get(ParameterType::class);
    }

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