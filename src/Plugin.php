<?php
/**
 * Plugin.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql
 */

namespace blackcube\plugins\graphql;

use blackcube\admin\interfaces\PluginManagerBootstrapInterface as PluginAdminBootstrapInterface;
use blackcube\admin\interfaces\RbacableInterface;
use blackcube\core\interfaces\ElementInterface;
use blackcube\core\interfaces\PluginManagerBootstrapInterface as PluginCoreBootstrapInterface;
use blackcube\admin\Module as AdminModule;
use blackcube\core\interfaces\PluginManagerConfigurableInterface;
use blackcube\core\interfaces\PluginManagerInterface;
use blackcube\core\Module as CoreModule;
use blackcube\core\traits\PluginManagerMigrableTrait;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Module;
use blackcube\plugins\graphql\components\Rbac;
use blackcube\plugins\graphql\admin\controllers\ConfigureController;
use blackcube\plugins\graphql\core\controllers\GraphqlController;
use blackcube\plugins\graphql\inputs\CompositeFilter;
use blackcube\plugins\graphql\inputs\NodeFilter;
use blackcube\plugins\graphql\inputs\Pagination;
use blackcube\plugins\graphql\types\Bloc;
use blackcube\plugins\graphql\types\Category;
use blackcube\plugins\graphql\types\Composite;
use blackcube\plugins\graphql\types\Language;
use blackcube\plugins\graphql\types\Node;
use blackcube\plugins\graphql\types\Parameter;
use blackcube\plugins\graphql\types\ReadQuery;
use blackcube\plugins\graphql\types\Slug;
use blackcube\plugins\graphql\types\Tag;
use blackcube\plugins\graphql\types\Technical;
use blackcube\plugins\graphql\types\Type;
use yii\helpers\Json;
use yii\i18n\GettextMessageSource;
use yii\web\Application as WebApplication;
use yii\web\Controller;
use yii\web\ErrorHandler;
use yii\web\UrlRule;
use yii\web\GroupUrlRule;
use yii\console\Application as ConsoleApplication;
use blackcube\plugins\graphql\AdminModule as PluginAdminModule;
use blackcube\plugins\graphql\CoreModule as PluginCoreModule;
use Yii;
use yii\web\View;

/**
 * Class Plugin
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql
 *
 */
class Plugin implements PluginManagerInterface,
    PluginCoreBootstrapInterface,
    PluginAdminBootstrapInterface,
    PluginManagerConfigurableInterface,
    RbacableInterface
{
    use PluginManagerMigrableTrait {
        // PluginManagerMigrableTrait::registerDbPlugin as originalRegisterDbPlugin;
    }
    /**
     * @var string version number
     */
    private $_version = 'v3.0-dev';
    private $_pluginId;
    private static $_instance;
    private $configureRoute;
    private $pluginCoreModule;
    private $pluginAdminModule;
    /**
     * @var string[]
     */
    public $coreSingletons = [
        Bloc::class => Bloc::class,
        Category::class => Category::class,
        Composite::class => Composite::class,
        CompositeFilter::class => CompositeFilter::class,
        Language::class => Language::class,
        Node::class => Node::class,
        NodeFilter::class => NodeFilter::class,
        Pagination::class => Pagination::class,
        Parameter::class => Parameter::class,
        ReadQuery::class => ReadQuery::class,
        Slug::class => Slug::class,
        Tag::class => Tag::class,
        Technical::class => Technical::class,
        Type::class => Type::class,
    ];

    /**
     * @var string[]
     */
    public $coreElements = [
    ];

    public static function getInstance()
    {
        return self::$_instance;
    }

    public function __construct(string $id)
    {
        $this->_pluginId = $id;
        self::$_instance = $this;
        $this->init();
    }

    public function setAlias() :void
    {
        Yii::setAlias('@blackcube/plugins/graphql', __DIR__);
    }

    public function getId() :string
    {
        return $this->_pluginId;
    }

    public function getName() :string
    {
        return 'Plugin Graphql';
    }

    public function getVersion() :string
    {
        return $this->_version;
    }

    public function getIsCompatible() :bool
    {
        return true;
    }

    public static function getRbacClass()
    {
        return Rbac::class;
    }

    public function getPluginCoreModule()
    {
        return $this->pluginCoreModule;
    }

    public function registerPluginCoreModule(Module $coreModule)
    {
        if($this->pluginCoreModule === null) {
            $this->pluginCoreModule = Yii::createObject([
                'class' => PluginCoreModule::class,
            ], [$this->getId(), Yii::$app]);
            // $coreModule->setModule($this->getId(), $this->pluginCoreModule);
            Yii::$app->setModule($this->getId(), $this->pluginCoreModule);
        }
    }

    public function getPluginAdminModule()
    {
        return $this->pluginAdminModule;
    }

    public function registerPluginAdminModule(Module $adminModule)
    {
        if($this->pluginAdminModule === null) {
            $this->pluginAdminModule = Yii::createObject([
                'class' => PluginAdminModule::class,
            ], [$this->getId(), $adminModule]);
            $adminModule->setModule($this->getId(), $this->pluginAdminModule);
        }
    }

    /*/
    public function registerDbPlugin(): bool
    {
        $status = $this->originalRegisterDbPlugin();
        // rebootstrap modules
        if ($this->getPluginCoreModule() instanceof PluginCoreBootstrapInterface) {
            $this->bootstrapCore(CoreModule::getInstance(), Yii::$app);
        }
        if ($this->getPluginAdminModule() instanceof PluginAdminBootstrapInterface) {
            $this->bootstrapAdmin(AdminModule::getInstance(), Yii::$app);
        }

        return $status;
    }
    /**/


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->migrationsNamespace = 'blackcube\\plugins\\graphql\\migrations';

        //TODO: should inherit db from Core
        // $this->registerErrorHandler();
        $this->setAlias();
        $this->registerDi();
        $this->registerTranslations();
    }

    public function bootstrapAdmin(AdminModule $adminModule, Application $app)
    {
        if ($adminModule !== null) {
            $this->registerPluginAdminModule($adminModule);
            if ($this->getPluginAdminModule() instanceof BootstrapInterface) {
                list($route,) = $app->urlManager->parseRequest($app->request);
                if ($route !== null && preg_match('#' . $adminModule->getUniqueId() . '/#', $route) > 0) {
                    $this->getPluginAdminModule()->bootstrap($app);
                    $this->configureRoute = '/'.$this->getPluginAdminModule()->getUniqueId().'/configure';
                }
            }
        }
    }

    /**
     * {inheritdoc}
     */
    public function bootstrapCore(CoreModule $coreModule, Application $app)
    {
        if ($coreModule !== null) {
            $this->registerPluginCoreModule($coreModule);
            if ($coreModule !== null && $this->getPluginCoreModule() instanceof BootstrapInterface) {
                $this->getPluginCoreModule()->bootstrap($app);
            }
        }
    }

    /**
     * Register translation stuff
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['blackcube/plugins/graphql/*'] = [
            'class' => GettextMessageSource::class,
            'sourceLanguage' => 'en',
            'useMoFile' => true,
            'basePath' => '@blackcube/plugins/graphql/i18n',
        ];
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function registerDi()
    {
        foreach($this->coreSingletons as $class => $definition) {
            if (Yii::$container->hasSingleton($class) === false) {
                Yii::$container->setSingleton($class, $definition);
            }
        }
        foreach($this->coreElements as $class => $definition) {
            if (Yii::$container->has($class) === false) {
                Yii::$container->set($class, $definition);
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getConfigureRoute()
    {
        // $pattern = '/'.$this->getPluginAdminModule()->getUniqueId().'/configure';
        return [$this->configureRoute];
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
     * echo Plugin::t('app', 'Hello, {username}!', ['username' => $username]);
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
        return Yii::t('blackcube/plugins/graphql/' . $category, $message, $params, $language);
    }

}
