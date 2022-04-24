<?php
/**
 * Module.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\graphql
 */

namespace blackcube\graphql;

use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\di\Instance;
use yii\i18n\GettextMessageSource;
use yii\web\Application as WebApplication;
use yii\web\ErrorHandler;
use yii\web\UrlRule;
use yii\web\GroupUrlRule;
use yii\console\Application as ConsoleApplication;
use Yii;

/**
 * Class module
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\graphql
 *
 */
class Module extends BaseModule implements BootstrapInterface
{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'blackcube\graphql\controllers';

    /**
     * @var Connection|array|string database access
     */
    public $db = 'db';

    /**
     * @var CacheInterface|array|string|null
     */
    public $cache;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->layout = 'main';
        parent::init();
        //TODO: should inherit db from Core
        $this->db = Instance::ensure($this->db, Connection::class);
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, CacheInterface::class);
        }
        $this->registerErrorHandler();
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@blackcube/graphql', __DIR__);
        $this->registerTranslations();
        if ($app instanceof ConsoleApplication) {
            $this->bootstrapConsole($app);
        } elseif ($app instanceof WebApplication) {
            $this->bootstrapWeb($app);
        }
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
     * Bootstrap web stuff
     *
     * @param WebApplication $app
     * @since XXX
     */
    protected function bootstrapWeb(WebApplication $app)
    {
        $app->getUrlManager()->addRules([
            [
                'class' => GroupUrlRule::class,
                'prefix' => $this->id,
                'rules' => [
                    ['class' => UrlRule::class, 'pattern' => '', 'route' => 'graphql/index'],
                ],
            ]
        ], false);
    }

    /**
     * Register translation stuff
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['blackcube/graphql/*'] = [
            'class' => GettextMessageSource::class,
            'sourceLanguage' => 'en',
            'useMoFile' => true,
            'basePath' => '@blackcube/graphql/i18n',
        ];
    }

    /**
     * Register errorHandler for all module URLs
     * @throws \yii\base\InvalidConfigException
     */
    public function registerErrorHandler()
    {
        if (Yii::$app instanceof WebApplication) {
            list($route,) = Yii::$app->urlManager->parseRequest(Yii::$app->request);
            if (preg_match('#'.$this->uniqueId.'/#', $route) > 0) {
                Yii::configure($this, [
                    'components' => [
                        'errorHandler' => [
                            'class' => ErrorHandler::class,
                            'errorAction' => $this->uniqueId.'/technical/error',
                        ]
                    ],
                ]);
                /** @var ErrorHandler $handler */
                $handler = $this->get('errorHandler');
                Yii::$app->set('errorHandler', $handler);
                $handler->register();
            }
        }
    }

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\yii\i18n\I18N::translate()]].
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo Module::t('app', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](https://secure.php.net/manual/en/intro.intl.php)
     * message formatter. See [[\yii\i18n\I18N::translate()]] for more details.
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('blackcube/graphql/' . $category, $message, $params, $language);
    }

}
