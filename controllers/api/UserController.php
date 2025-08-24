<?php

namespace app\controllers\api;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\models\User;

class UserController extends ActiveController
{
    public $modelClass = 'app\\models\\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'view', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->identity->role === User::ROLE_ADMIN;
                    }
                ],
            ],
        ];
        return $behaviors;
    }


    public function actionIndex()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => User::find(),
        ]);

        return $dataProvider;
    }


    public function actionView($id){
        return $this->findModel($id);

    }

    public function actionUpdate($id){
        $model = $this->findModel($id);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if($model->save()){
            return $model;
        }
        return $model->errors;
    }

    public function actionDelete($id){
        $model = $this->findModel($id);
        if($model->delete()){
            return ['status' => 'success'];
        }
        return ['status' => 'error'];
    }



    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('The requested user does not exist.');
    }


}

