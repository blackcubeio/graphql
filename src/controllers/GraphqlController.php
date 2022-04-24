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
 * @package blackcube\graphql\controllers
 */

namespace blackcube\graphql\controllers;

use blackcube\graphql\components\CompositeType;
use blackcube\graphql\components\QueryType;
use blackcube\graphql\components\Types;
use GraphQL\Error\DebugFlag;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\web\Controller;
use Yii;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
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
 * @package blackcube\graphql\controllers
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
            'query' => Types::readQuery()
        ]);
        /*/
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'echo' => [
                    'type' => Type::string(),
                    'args' => [
                        'message' => ['type' => Type::string()],
                    ],
                    'resolve' => static function ($rootValue, array $args): string {
                        return $rootValue['prefix'] . $args['message'];
                    },
                ],
            ],
        ]);

        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'sum' => [
                    'type' => Type::int(),
                    'args' => [
                        'x' => ['type' => Type::int()],
                        'y' => ['type' => Type::int()],
                    ],
                    'resolve' => static function ($calc, array $args): int {
                        return $args['x'] + $args['y'];
                    },
                ],
            ],
        ]);
        $schema = new Schema([
            'query' => $queryType,
            'mutation' => $mutationType,
        ]);
        /**/
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
