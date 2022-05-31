<?php
/**
 * CompositeFilter.php
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
 * Class CompositeFilter
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\inputs
 * @since XXX
 */
class CompositeFilter extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'CompositeFilter',
            'fields' => [
                'typeId' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Type of the composite')
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