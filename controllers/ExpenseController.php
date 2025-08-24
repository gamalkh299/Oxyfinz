<?php

namespace app\controllers;

use Yii;
use app\models\Expense;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;

/**
 * ExpenseController implements the CRUD actions for Expense model.
 */
class ExpenseController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['approve', 'reject'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === User::ROLE_ADMIN;
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Expense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Expense::find();

        // If user is not admin, only show their expenses
        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            $query->where(['user_id' => Yii::$app->user->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Expense model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Check if user has permission to view this expense
        if (!$this->canManageExpense($model)) {
            throw new ForbiddenHttpException('You are not allowed to view this expense.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Expense model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Expense();
        $model->user_id = Yii::$app->user->id;
        $model->status = Expense::STATUS_PENDING;
        $model->spent_at = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            $model->receiptFile = UploadedFile::getInstance($model, 'receiptFile');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Expense created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Expense model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Check if user has permission to update this expense
        if (!$this->canManageExpense($model)) {
            throw new ForbiddenHttpException('You are not allowed to update this expense.');
        }

        // Can only update pending expenses
        if ($model->status !== Expense::STATUS_PENDING) {
            Yii::$app->session->setFlash('error', 'Cannot edit expenses that have already been approved or rejected.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->receiptFile = UploadedFile::getInstance($model, 'receiptFile');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Expense updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Expense model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Check if user has permission to delete this expense
        if (!$this->canManageExpense($model)) {
            throw new ForbiddenHttpException('You are not allowed to delete this expense.');
        }

        // Can only delete pending expenses
        if ($model->status !== Expense::STATUS_PENDING) {
            Yii::$app->session->setFlash('error', 'Cannot delete expenses that have already been approved or rejected.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Expense deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Approve an expense
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the user is not an admin
     */
    public function actionApprove($id)
    {
        // Check if user is admin
        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new ForbiddenHttpException('Only administrators can approve expenses.');
        }

        $model = $this->findModel($id);

        // Can only approve pending expenses
        if ($model->status !== Expense::STATUS_PENDING) {
            Yii::$app->session->setFlash('error', 'Only pending expenses can be approved.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = Expense::STATUS_APPROVED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Expense approved successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to approve expense.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject an expense
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the user is not an admin
     */
    public function actionReject($id)
    {
        // Check if user is admin
        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new ForbiddenHttpException('Only administrators can reject expenses.');
        }

        $model = $this->findModel($id);

        // Can only reject pending expenses
        if ($model->status !== Expense::STATUS_PENDING) {
            Yii::$app->session->setFlash('error', 'Only pending expenses can be rejected.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = Expense::STATUS_REJECTED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Expense rejected successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to reject expense.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Expense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Expense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Expense::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Check if current user can manage (view, update, delete) an expense
     * @param Expense $expense
     * @return bool
     */
    protected function canManageExpense($expense)
    {
        // Admin can manage all expenses
        if (Yii::$app->user->identity->role === User::ROLE_ADMIN) {
            return true;
        }

        // Regular users can only manage their own expenses
        return $expense->user_id === Yii::$app->user->id;
    }
}