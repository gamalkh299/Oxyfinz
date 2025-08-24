<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\User;
use app\models\Expense;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Expenses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Expense', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'user_id',
                'value' => function (Expense $model) {
                    return $model->user->getFullName();
                },
                'visible' => Yii::$app->user->identity->role === User::ROLE_ADMIN,
            ],
            [
                'attribute' => 'amount',
                'format' => 'currency',
            ],
            [
                'attribute' => 'category',
                'value' => function (Expense $model) {
                    $categories = Expense::getCategories();
                    return isset($categories[$model->category]) ? $categories[$model->category] : $model->category;
                },
            ],
            'spent_at:date',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function (Expense $model) {
                    return $model->getStatusBadge();
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}' . (Yii::$app->user->identity->role === User::ROLE_ADMIN ? ' {approve} {reject}' : ''),
                'visibleButtons' => [
                    'update' => function ($model) {
                        return $model->status === Expense::STATUS_PENDING &&
                            ($model->user_id === Yii::$app->user->id || Yii::$app->user->identity->role === User::ROLE_ADMIN);
                    },
                    'delete' => function ($model) {
                        return $model->status === Expense::STATUS_PENDING &&
                            ($model->user_id === Yii::$app->user->id || Yii::$app->user->identity->role === User::ROLE_ADMIN);
                    },
                    'approve' => function ($model) {
                        return $model->status === Expense::STATUS_PENDING &&
                            Yii::$app->user->identity->role === User::ROLE_ADMIN;
                    },
                    'reject' => function ($model) {
                        return $model->status === Expense::STATUS_PENDING &&
                            Yii::$app->user->identity->role === User::ROLE_ADMIN;
                    },
                ],
                'buttons' => [
                    'approve' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fas fa-check"></i> Approve',
                            ['approve', 'id' => $model->id],
                            [
                                'title' => 'Approve',
                                'data-confirm' => 'Are you sure you want to approve this expense?',
                                'data-method' => 'post',
                                'class' => 'btn btn-sm btn-success',
                            ]
                        );
                    },
                    'reject' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fas fa-times"></i> Reject ',
                            ['reject', 'id' => $model->id],
                            [
                                'title' => 'Reject',
                                'data-confirm' => 'Are you sure you want to reject this expense?',
                                'data-method' => 'post',
                                'class' => 'btn btn-sm btn-danger',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>