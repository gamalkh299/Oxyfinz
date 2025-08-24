<?php

namespace app\controllers\api;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\models\Expense;
use app\models\User;

class ExpenseController extends ActiveController
{
    public $modelClass = 'app\\models\\Expense';

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
                ],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // Customize data provider for index to filter by user if not admin
        $actions['index']['prepareDataProvider'] = function ($action) {
            $query = Expense::find();
            if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
                $query->where(['user_id' => Yii::$app->user->id]);
            }
            return new \yii\data\ActiveDataProvider([
                'query' => $query,
            ]);
        };
        return $actions;
    }
}

