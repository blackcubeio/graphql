<?php

namespace blackcube\graphql\models;

use yii\base\Model;

class ConfigureModel extends Model
{
    public $name;

    public function rules()
    {
        return [
            [['name'], 'string']
        ];
    }
}