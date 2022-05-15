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
 * @package blackcube\graphql
 */

namespace blackcube\graphql;

use blackcube\admin\interfaces\PluginBootstrapInterface as PluginAdminBootstrapInterface;
use blackcube\admin\interfaces\RbacableInterface;
use blackcube\core\interfaces\ElementInterface;
use blackcube\core\interfaces\PluginBootstrapInterface as PluginCoreBootstrapInterface;
use blackcube\admin\Module as AdminModule;
use blackcube\core\interfaces\PluginManagerConfigurableInterface;
use blackcube\core\interfaces\PluginManagerInterface;
use blackcube\core\Module as CoreModule;
use blackcube\core\traits\PluginManagerMigrableTrait;
use blackcube\core\traits\PluginManagerTrait;
use blackcube\graphql\components\Rbac;
use blackcube\graphql\controllers\ConfigureController;
use blackcube\graphql\controllers\GraphqlController;
use blackcube\graphql\inputs\CompositeFilter;
use blackcube\graphql\inputs\NodeFilter;
use blackcube\graphql\inputs\Pagination;
use blackcube\graphql\types\Bloc;
use blackcube\graphql\types\Category;
use blackcube\graphql\types\Composite;
use blackcube\graphql\types\Language;
use blackcube\graphql\types\Node;
use blackcube\graphql\types\Parameter;
use blackcube\graphql\types\ReadQuery;
use blackcube\graphql\types\Slug;
use blackcube\graphql\types\Tag;
use blackcube\graphql\types\Technical;
use blackcube\graphql\types\Type;
use yii\base\BootstrapInterface;
use yii\base\Exception;
use yii\base\Module as BaseModule;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Json;
use yii\i18n\GettextMessageSource;
use yii\web\Application;
use yii\web\Application as WebApplication;
use yii\web\ErrorHandler;
use yii\web\UrlRule;
use yii\web\GroupUrlRule;
use yii\console\Application as ConsoleApplication;
use Yii;

/**
 * Class Plugin
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2020 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\graphql
 *
 */
class Plugin implements PluginManagerInterface, PluginCoreBootstrapInterface, PluginAdminBootstrapInterface, RbacableInterface, PluginManagerConfigurableInterface// implements /*/RbacableInterface, /**/ /*/ PluginBootstrapCoreInterface, PluginBootstrapAdminInterface, PluginManagerConfigurableInterface/**/
{
    use PluginManagerMigrableTrait {
        PluginManagerMigrableTrait::registerDbPlugin as originalRegisterDbPlugin;
    }

    const MODE_FRONT = 'front';
    const MODE_ADMIN = 'admin';

    private static $pluginId;
    private $_coreModuleId;

    public $mode = self::MODE_FRONT;

    public function __construct(string $id)
    {
        self::$pluginId = $id;
        $this->init();
    }

    public function setAlias() :void
    {
        Yii::setAlias('@blackcube/graphql', __DIR__);
    }

    public function getId()
    {
        return self::$pluginId;
    }

    public static function getStaticId()
    {
        return self::$pluginId;
    }

    public function getName() :string
    {
        return 'Plugin Graphql';
    }

    public function getVersion() :string
    {
        return '1.0.0';
    }

    public function getIsCompatible() :bool
    {
        return true;
    }

    public function upgrade() :bool
    {
        return true;
    }

    public static function getRbacClass()
    {
        return Rbac::class;
    }

    public function registerDbPlugin(): bool
    {
        $status = $this->originalRegisterDbPlugin();
        if ($this instanceof PluginAdminBootstrapInterface) {
            $this->bootstrapAdmin(AdminModule::getInstance()->getUniqueId(), Yii::$app);
        }
        if ($this instanceof PluginCoreBootstrapInterface) {
            $this->bootstrapCore(CoreModule::getInstance()->getUniqueId(), Yii::$app);
        }
        return $status;
    }
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'blackcube\graphql\controllers';

    /**
     * @var string version number
     */
    public $version = 'v3.0-dev';

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

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->migrationsNamespace = 'blackcube\\graphql\\migrations';

        //TODO: should inherit db from Core
        // $this->registerErrorHandler();
        $this->setAlias();
        $this->registerDi();
        $this->registerTranslations();
    }

    public function bootstrapAdmin($moduleUid, $app)
    {
        $boModule = Yii::$app->getModule($moduleUid);
        /* @var $boModule AdminModule */
        if ($boModule !== null) {
            $app->controllerMap['bc:graphql:configure'] = ConfigureController::class;
            $pattern = $boModule->getUniqueId().'/'.$this->getId().'/configure';
            $app->getUrlManager()->addRules([
                // [
                //     'class' => GroupUrlRule::class,
                //     'routePrefix' => $this->getUniqueId(),
                //     'prefix' => $prefix,
                //     'rules' => [
                ['class' => UrlRule::class, 'pattern' => $pattern, 'route' => 'bc:graphql:configure'],
                //         ['class' => UrlRule::class, 'pattern' => '<controller:[\w\-]+>', 'route' => '<controller>'],
                //         ['class' => UrlRule::class, 'pattern' => '<controller:[\w\-]+>/<action:[\w\-]+>', 'route' => '<controller>/<action>'],
                //     ],
                // ]
            ], false);
            /*/
            $boModule->setModule($this->getId(), [
                'class' => Module::class,
                'mode' => Module::MODE_ADMIN
            ]);
            $ref = new \ReflectionClass(Module::class);
            if ($ref->implementsInterface(BootstrapInterface::class)) {
                $gqlModule = $boModule->getModule($this->getId());
                if ($gqlModule !== null) {
                    /* @var $gqlModule Module * /
                    $this->_adminModuleId = $gqlModule->getUniqueId();
                    $gqlModule->bootstrap($app);
                }
            }
            /**/
        }
    }

    public function bootstrapCore($moduleUid, $app)
    {
        if ($app instanceof Application) {
            // $prefix = $config['name']??$this->getUniqueId();
            $plugin = \blackcube\core\models\Plugin::find()->andWhere(['id' => $this->getId()])->one();
            try {
                $config = Json::decode($plugin->config);
            } catch (\Exception $e) {
                $config = [];
            }
            $pattern = $config['name']??$this->getId();
            $app->controllerMap['bc:graphql'] = GraphqlController::class;
            $app->getUrlManager()->addRules([
                // [
                //     'class' => GroupUrlRule::class,
                //     'routePrefix' => $this->getUniqueId(),
                //     'prefix' => $prefix,
                //     'rules' => [
                         ['class' => UrlRule::class, 'pattern' => $pattern, 'route' => 'bc:graphql/index'],
                //         ['class' => UrlRule::class, 'pattern' => '<controller:[\w\-]+>', 'route' => '<controller>'],
                //         ['class' => UrlRule::class, 'pattern' => '<controller:[\w\-]+>/<action:[\w\-]+>', 'route' => '<controller>/<action>'],
                //     ],
                // ]
            ], true);
        }
        /*/
        $app->setModule($this->getId(), [
            'class' => Module::class,
            'mode' => Module::MODE_FRONT
        ]);
        $ref = new \ReflectionClass(Module::class);
        if ($ref->implementsInterface(BootstrapInterface::class)) {
            $gqlModule = $app->getModule($this->getId());
            if ($gqlModule !== null) {
                /* @var $gqlModule Module * /
                $this->_coreModuleId = $gqlModule->getUniqueId();
                $gqlModule->bootstrap($app);
            }
        }
        /**/
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
        $pattern = $this->getId().'/configure';
        return [$pattern];
        /*/
        if ($this->_adminModuleId !== null) {
            $configureRoute = '/'.$this->_adminModuleId.'/configure';
            return [$configureRoute];
        } else {
            // return null;
            throw new Exception('Module id not defined');
        }
        /**/
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
        return Yii::t('blackcube/graphql/' . $category, $message, $params, $language);
    }

}
