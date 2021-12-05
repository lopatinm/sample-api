<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Organization;
use app\modules\v1\models\Request;
use app\modules\v1\models\Subscription;
use app\modules\v1\models\User;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;

use app\modules\v1\models\UserAttributes;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{

    public $modelClass = 'app\modules\v1\models\User';

    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => Cors::className(),
            ],
        ]);
        $behaviors['authenticator']['class'] = HttpBearerAuth::className();
        $behaviors['authenticator']['only'] = ['create', 'update', 'delete', 'view', 'index', 'access'];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        $model = new User;
        $activeData = new ActiveDataProvider([
            'query' => $model::find()->orderBy("id DESC"),
            'pagination' => [
                'defaultPageSize' => -1,
                'pageSizeLimit' => -1,
            ],
        ]);
        return $activeData;

    }

    public function actionUpdate()
    {
        return User::updateUser(Yii::$app->user->identity['id'], Yii::$app->getRequest()->getBodyParams());
    }

    public function actionLogin()
    {
        return User::loginUser(Yii::$app->getRequest()->getBodyParams());
    }

    public function actionRegistration()
    {
        return User::registrationUser(Yii::$app->getRequest()->getBodyParams());
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update') {
            if (!empty($model->id))
                if ($model->id !== Yii::$app->user->identity['id'])
                    throw new ForbiddenHttpException(sprintf('Access is denied', $action));
        } elseif ($action === 'index') {
            if (!isset(Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity['id'])['root']))
                throw new ForbiddenHttpException(sprintf('Access is denied', $action));
        } elseif ($action === 'view') {
            if (!empty($model->id))
                if ($model->id !== Yii::$app->user->identity['id'])
                    if (!isset(Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity['id'])['root']))
                        throw new ForbiddenHttpException(sprintf('Access is denied.', $action));
        } elseif ($action === 'delete') {
            throw new ForbiddenHttpException(sprintf('Access is denied.', $action));
        } elseif ($action === 'create') {
            throw new ForbiddenHttpException(sprintf('Access is denied.', $action));
        }
    }
}