<?php
/**
 * Rbac.php
 *
 * PHP version 7.2+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\components
 */

namespace blackcube\plugins\graphql\components;

use yii\helpers\Inflector;

/**
 * Class Rbac
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\components
 */
class Rbac
{
    public const PERMISSION_GRAPHQL_CREATE = 'GRAPHQL:CREATE';
    public const PERMISSION_GRAPHQL_DELETE = 'GRAPHQL:DELETE';
    public const PERMISSION_GRAPHQL_UPDATE = 'GRAPHQL:UPDATE';
    public const PERMISSION_GRAPHQL_VIEW = 'GRAPHQL:VIEW';
    public const ROLE_GRAPHQL_MANAGER = 'GRAPHQL:MANAGER';

    /**
     * @param string $permission
     * @return string
     */
    public static function extractPermission($permission)
    {
        if (strpos($permission, ':') !== false) {
            list($type, $name) = explode(':', $permission);
        } else {
            $name = $permission;
        }
        return $name;
    }

    public static function extractRole($role)
    {
        if (strpos($role, ':') !== false) {
            list($name, $type) = explode(':', $role);
        } else {
            $name = $role;
        }
        return $name;
    }

    public static function rbac2Id($item)
    {
        $item = strtolower(str_replace(':', '_', $item));
        $item = Inflector::camelize($item);
        return Inflector::camel2id($item);
    }

    public static function rbac2Name($item)
    {
        $item = strtolower(str_replace(':', '_', $item));
        return Inflector::camelize($item);
    }

    public static function name2Rbac($name)
    {
        $name = Inflector::titleize($name, true);
        return strtoupper(str_replace(' ', ':', $name));
    }
}
