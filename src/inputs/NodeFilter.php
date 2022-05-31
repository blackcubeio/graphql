<?php
/**
 * NodeFilter.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\inputs
 */

namespace blackcube\plugins\graphql\inputs;

use blackcube\plugins\graphql\Plugin;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class NodeFilter
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\inputs
 * @since XXX
 */
class NodeFilter extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'NodeFilter',
            'fields' => [
                'level' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Level'),
                ],
                'typeId' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types','Type of the node (rubric)'),
                ],
                'languageId' => [
                    'type' => Type::string(),
                    'description' => Plugin::t('types', 'Language used')
                ],

            ]
        ];
        parent::__construct($config);
    }

    public function extract($args)
    {
        $defaultArgs = [];
        foreach($this->getFields() as $name => $field) {
            /* @var \GraphQL\Type\Definition\InputObjectField $field */
            $defaultArgs[$name] = $field->defaultValue;
        }
        return ArrayHelper::merge($defaultArgs, ($args['filters']??[]));
    }
}