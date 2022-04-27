<?php

namespace blackcube\graphql\types;

use blackcube\graphql\inputs\CompositeFilter;
use blackcube\graphql\inputs\NodeFilter;
use blackcube\graphql\inputs\Pagination;
use Yii;
use Exception;

class Blackcube
{
    public static function bloc()
    {
        return Yii::createObject(Bloc::class);
    }

    public static function category()
    {
        return Yii::createObject(Category::class);
    }

    public static function composite()
    {
        return Yii::createObject(Composite::class);
    }

    public static function compositeFilter()
    {
        return Yii::createObject(CompositeFilter::class);
    }

    public static function language()
    {
        return Yii::createObject(Language::class);
    }

    public static function node()
    {
        return Yii::createObject(Node::class);
    }

    public static function nodeFilter()
    {
        return Yii::createObject(NodeFilter::class);
    }

    public static function parameter()
    {
        return Yii::createObject(Parameter::class);
    }

    public static function readQuery()
    {
        return Yii::createObject(ReadQuery::class);
    }

    public static function slug()
    {
        return Yii::createObject(Slug::class);
    }

    public static function tag()
    {
        return Yii::createObject(Tag::class);
    }

    public static function technical()
    {
        return Yii::createObject(Technical::class);
    }

    public static function type()
    {
        return Yii::createObject(Type::class);
    }

    public static function pagination()
    {
        return Yii::createObject(Pagination::class);
    }

}