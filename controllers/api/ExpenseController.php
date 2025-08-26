<?php

namespace app\controllers\api;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\models\Expense;
use app\models\User;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ExpenseController extends ActiveController
{
    public $modelClass = 'app\\models\\Expense';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => \yii\filters\Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options']
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


    public function checkAccess($action, $model = null, $params = [])
    {

        if (Yii::$app->user->identity->role === User::ROLE_ADMIN) {
            return;
        }

        if (in_array($action, ['view', 'update', 'delete'])) {
            if ($model->user_id !== Yii::$app->user->id) {
                throw new ForbiddenHttpException('You do not have permission to access this resource.');
            }
        }
    }


    public function actionIndex() {
        $query = Expense::find();
        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            $query->where(['user_id' => Yii::$app->user->id]);
        }
        return new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);
    }

    public function actionView($id) {

        $model = Expense::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Expense not found.');
        }
        $this->checkAccess('view', $model);
        return $model;
    }


    public function actionCreate()
    {
        $model = new Expense();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->user_id = Yii::$app->user->identity->id ;// Set the user_id to the current Auth User
        if ($model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the expense for unknown reasons.');
        }
        return $model;
    }


    public function actionUpdate($id)
    {
        $params = Yii::$app->request->getBodyParams();
        var_dump($params);
        var_dump(Yii::$app->request->getRawBody());
        die;
        $model = Expense::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Expense not found.');
        }

        if ($model->status !== Expense::STATUS_PENDING) {
            throw new ForbiddenHttpException('You cannot edit an expense that has already been approved or rejected.');
        }
        $this->checkAccess('update', $model);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the expense for unknown reasons.');
        }

        return $model;
    }


    public function actionDelete($id)
    {
        $model = Expense::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Expense not found.');
        }
        $this->checkAccess('delete', $model);
        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the expense for unknown reasons.');
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }

    public function actionApprove($id)
    {
        $model = Expense::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Expense not found.');
        }
        // Only admins can approve expenses
        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new ForbiddenHttpException('You do not have permission to approve this expense.');
        }
        if ($model->status !== Expense::STATUS_PENDING) {
            throw new ForbiddenHttpException('You cannot approve an expense that has already been approved or rejected.');
        }

        $model->status = Expense::STATUS_APPROVED;
        if ($model->save()) {
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to approve the expense for unknown reasons.');
        }
        return $model;
    }


    public function actionReject($id){
        $model = Expense::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Expense not found.');
        }
        // Only admins can reject expenses
        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new ForbiddenHttpException('You do not have permission to reject this expense.');
        }
        if ($model->status !== Expense::STATUS_PENDING) {
            throw new ForbiddenHttpException('You cannot reject an expense that has already been approved or rejected.');
        }
        $model->status = Expense::STATUS_REJECTED;
        if ($model->save()) {
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to reject the expense for unknown reasons.');
        }
        return $model;
    }

}

