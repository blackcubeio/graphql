<?php

namespace blackcube\graphql\types;

use blackcube\core\models\BlocType;
use blackcube\core\models\Elastic;
use blackcube\core\validators\ElasticValidator;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Swaggest\JsonSchema\Schema;
use yii\base\NotSupportedException;
use yii\helpers\Inflector;

class Bloc extends UnionType
{
    public static $blocTypes = [];
    public static $types = [];

    public function __construct()
    {
        $config = [
            'name' => 'Bloc',
            'description' => 'Data bloc',
            'types' => function() {
                if (empty(static::$types)) {
                    $blocTypes = BlocType::find()->all();
                    foreach ($blocTypes as $blocType) {
                        static::$types[] = $this->buildBlocType($blocType->id); // 'BlocType' . $blocType->id; // 'BlocType'.$blocType->id;
                    }

                }
                return static::$types;
            },
            'resolveType' => function($value) {
                return $this->buildBlocType($value->blocTypeId);
            }
        ];
        parent::__construct($config);
    }
    public function buildBlocType($id)
    {
        if (isset(static::$blocTypes[$id])) {
            return static::$blocTypes[$id];
        }
        $blocType = BlocType::find()->andWhere(['id' => $id])->one();
        $type = null;
        if ($blocType !== null) {
            if ($blocType->template !== null) {
                if (is_string($blocType->template) === true) {
                    $schema = json_decode($blocType->template);
                }
                if ($schema instanceof \StdClass) {
                    $schema = Schema::import($schema);
                }
                if ($schema instanceof Schema) {
                    $type = $this->buildObjectType($schema, $blocType);
                }
            }

        }
        static::$blocTypes[$id] = $type;
        return static::$blocTypes[$id];
    }

    private function buildObjectType($schema, $blocType)
    {
        $schemaProperties = $schema->getProperties();
        if ($schema->required !== null && count($schema->required)>0) {
            // $this->_rules[] = [$schema->required, 'required'];
        }
        $fields = [
            'id' => Type::id(),
            'blocTypeId' => ['type' => Type::int()]
        ];
        foreach ($schemaProperties as $key => $property) {
            $fields[$key] = [];
            if ($property->description !== null) {
                $fields[$key]['description'] = $property->description;
            }
            //TODO:handle subobject
            if ($property->type === 'object') {
                throw new NotSupportedException('Subschema not yet supported');
            } else {
                $fields[$key]['name'] = $key;
                switch($property->type) {
                    case 'boolean':
                        $fields[$key]['type'] = Type::boolean();
                        break;
                    case 'number':
                        $fields[$key]['type'] = Type::float();
                        break;
                    case 'integer':
                        $fields[$key]['type'] = Type::int();
                        break;
                    case 'string':
                        /**/
                        if($property->format === 'file') {
                            $fields[$key] = FileField::build($key);
                        } elseif ($property->format === 'files') {
                            $fields[$key] = FileField::build($key, true);
                        } else {
                            $fields[$key]['type'] = Type::string();
                        }
                        /*/
                        $fields[$key]['type'] = Type::string();
                        /***/
                        break;
                    default :
                        $fields[$key]['type'] = Type::string();
                        break;
                }
            }
        }
        $transliterator = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', \Transliterator::FORWARD);
        $normalized = $transliterator->transliterate($blocType->name);
        $name = Inflector::camelize($normalized);
        return new ObjectType([
            'name' => $name, //'BlocType' . $id,
            'fields' => function () use ($fields) {
                return $fields;
            }
        ]);
    }
}