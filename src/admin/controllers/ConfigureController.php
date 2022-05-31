<?php
/**
 * ConfigureController.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\admin\controllers
 */

namespace blackcube\plugins\graphql\admin\controllers;

use blackcube\admin\actions\ModalAction;
use blackcube\admin\actions\ToggleAction;
use blackcube\admin\components\Rbac;
use blackcube\core\interfaces\PluginManagerInterface;
use blackcube\core\interfaces\PluginsHandlerInterface;
use blackcube\core\models\Plugin as PluginModel;
use blackcube\plugins\graphql\Plugin as PluginManager;
use blackcube\plugins\graphql\models\ConfigureModel;
use blackcube\plugins\graphql\Plugin;
use blackcube\admin\Module as AdminModule;
use yii\base\Response;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use yii\helpers\Json;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class ConfigureController
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\admin\controllers
 * @since XXX
 */
class ConfigureController extends Controller
{

    public $layout  = '@blackcube/admin/views/layouts/main';


    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index',
                    ],
                    'roles' => [Rbac::PERMISSION_PLUGIN_UPDATE],
                ],
            ]
        ];

        return $behaviors;
    }

    /**
     * @param PluginsHandlerInterface $pluginsHandler
     * @return string|Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(PluginsHandlerInterface $pluginsHandler)
    {
        $pluginManager = Plugin::getInstance();
        $plugin = PluginModel::find()->andWhere(['id' => $pluginManager->getId()])->one();
        try {
            $config = Json::decode($plugin->runtimeConfig);
        } catch (\Exception $e) {
            $config = [];
        }

        $configureModel = Yii::createObject(ConfigureModel::class);
        $configureModel->name = $config['name'] ?? $pluginManager->getId();

        if (Yii::$app->request->isPost) {
            $configureModel->load(Yii::$app->request->bodyParams);
            if ($configureModel->validate()) {
                $config['name'] = $configureModel->name;
                $plugin->runtimeConfig = Json::encode($config);
                $plugin->save(['runtimeConfig']);
            }
        }
        //PluginManagerInterface::claas
        return $this->render('form', [
            'configureModel' => $configureModel,
            'boId' => '/'.AdminModule::getInstance()->getUniqueId(),
        ]);
    }
}
