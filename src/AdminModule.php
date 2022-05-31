<?php
/**
 * AdminModule.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql
 */

namespace blackcube\plugins\graphql;

use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;
use Yii;
use yii\web\GroupUrlRule;
use yii\web\UrlRule;

/**
 * Class AdminModule
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql
 * @since XXX
 */
class AdminModule extends BaseModule implements BootstrapInterface
{
    public $controllerNamespace = 'blackcube\plugins\graphql\admin\controllers';

    /**
     * @var string version number
     */
    public $version = 'v3.0-dev';
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->viewPath = '@blackcube/plugins/graphql/admin/views';
        parent::init();
        if (Yii::$app instanceof WebApplication) {
            $this->initWeb(Yii::$app);
        } elseif (Yii::$app instanceof ConsoleApplication) {
            $this->initConsole(Yii::$app);
        }

    }


    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApplication) {
            $this->bootstrapConsole($app);
        }
        if ($app instanceof WebApplication) {
            $this->bootstrapWeb($app);
        }
    }

    /**
     * Init console stuff
     *
     * @param ConsoleApplication $app
     * @since XXX
     */
    protected function initConsole(ConsoleApplication $app)
    {

    }

    /**
     * Bootstrap console stuff
     *
     * @param ConsoleApplication $app
     * @since XXX
     */
    protected function bootstrapConsole(ConsoleApplication $app)
    {

    }

    /**
     * Init web stuff
     *
     * @param WebApplication $app
     * @throws \yii\base\InvalidConfigException
     * @since XXX
     */
    protected function initWeb(WebApplication $app)
    {

    }

    /**
     * Bootstrap web stuff
     *
     * @param WebApplication $app
     * @throws \yii\base\InvalidConfigException
     * @since XXX
     */
    protected function bootstrapWeb(WebApplication $app)
    {
        $app->getUrlManager()->addRules([
            [
                'class' => GroupUrlRule::class,
                'prefix' => $this->getUniqueId(),
                'rules' => [
                    ['class' => UrlRule::class, 'pattern' => '', 'route' => 'default/index'],
                    ['class' => UrlRule::class, 'pattern' => '<controller:[\w\-]+>', 'route' => '<controller>'],
                    ['class' => UrlRule::class, 'pattern' => '<controller:[\w\-]+>/<action:[\w\-]+>', 'route' => '<controller>/<action>'],
                ],
            ]
        ], false);
    }

}
