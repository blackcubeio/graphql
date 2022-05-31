<?php
/**
 * ConfigureModel.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\models
 */

namespace blackcube\plugins\graphql\models;

use yii\base\Model;

/**
 * Class ConfigureModel
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\models
 */
class ConfigureModel extends Model
{
    public $name;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['name'], 'string']
        ];
    }
}