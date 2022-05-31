<?php
/**
 * Pagination.php
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
 * Class Pagination
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\inputs
 * @since XXX
 */
class Pagination extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Pagination',
            'fields' => [
                'size' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Page size'),
                    'defaultValue' => 10
                ],
                'offset' => [
                    'type' => Type::int(),
                    'description' => Plugin::t('types', 'Offset'),
                    'defaultValue' => 0
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
        $pagination = ArrayHelper::merge($defaultArgs, ($args['pagination']??[]));
        return $pagination;
    }

}