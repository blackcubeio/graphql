<?php

namespace blackcube\graphql\models;

use blackcube\core\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "graphql_tokens".
 *
 * @property string $id
 * @property string|null $auth
 * @property string $dateCreate
 * @property string|null $dateUpdate
 */
class Token extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'graphql_tokens';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDb()
    {
        return Module::getInstance()->get('db');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() :array
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'dateCreate',
            'updatedAtAttribute' => 'dateUpdate',
            'value' => Yii::createObject(Expression::class, ['NOW()']),
        ];
        return $behaviors;
    }

    public function getDecodedAuth()
    {
        try {
            $auth = Json::decode($this->auth);
        } catch (\Exception $e) {
            $auth = [];
        }
        return $auth;
    }
    public function setDecodedAuth(string $name, $value)
    {
        $auth = $this->getDecodedAuth();
        $auth[$name] = $value;
        $this->auth = Json::encode($auth);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['auth'], 'string'],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['id'], 'string', 'max' => 128],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'auth' => 'Auth',
            'dateCreate' => 'Date Create',
            'dateUpdate' => 'Date Update',
        ];
    }

    public function beforeValidate()
    {
        $status = parent::beforeValidate();
        if(empty($this->id)) {
            $this->id = Yii::$app->security->generateRandomString();
        }
        return $status;
    }
}
