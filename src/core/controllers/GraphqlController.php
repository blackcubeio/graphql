<?php
/**
 * GraphqlController.php
 *
 * PHP version 7.2+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2021 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\core\controllers
 */

namespace blackcube\plugins\graphql\core\controllers;

use blackcube\plugins\graphql\types\Blackcube;
use GraphQL\Error\DebugFlag;
use yii\web\Controller;
use Yii;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use yii\web\Response;

/**
 * GraphqlController class
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2021 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\core\controllers
 * @since XXX
 */
class GraphqlController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        /*/
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        /**/
        return $behaviors;
    }

    /**
     * @return string|array|yii\web\Response
     * @since XXX
     */
    public function actionIndex()
    {

        $schema = new Schema([
            'query' => Blackcube::readQuery()
        ]);

        $query = Yii::$app->request->getBodyParam('query');
        $variableValues = Yii::$app->request->getBodyParam('variables');
        $rootValue = [];
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
            return $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
        } catch (\Throwable $e) {
            return [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }
}
