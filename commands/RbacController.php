<?php
namespace app\commands;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $role = Yii::$app->authManager->createRole('root');
        $role->description = 'Супер админ';
        try {
            Yii::$app->authManager->add($role);
        } catch (\Exception $e) {
        }

        $role = Yii::$app->authManager->createRole('administrator');
        $role->description = 'Администратор';
        try {
            Yii::$app->authManager->add($role);
        } catch (\Exception $e) {
        }

        $role = Yii::$app->authManager->createRole('manager');
        $role->description = 'Менеджер';
        try {
            Yii::$app->authManager->add($role);
        } catch (\Exception $e) {
        }

        $role = Yii::$app->authManager->createRole('user');
        $role->description = 'Пользователь';
        try {
            Yii::$app->authManager->add($role);
        } catch (\Exception $e) {
        }

        $userRole = Yii::$app->authManager->getRole('root');
        try {
            Yii::$app->authManager->assign($userRole, 1);
        } catch (\Exception $e) {
        }

    }
}